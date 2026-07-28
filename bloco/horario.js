// horario.js — FOAG

document.addEventListener('DOMContentLoaded', function () {
  console.log('horario.js carregado ✅');

  const SAVE_URL     = window.HORARIO_SAVE_URL || 'salvar_hora.php';
  const HORARIO_HTML = window.HORARIO_HTML || '';

  const tabela = document.getElementById('scheduleTable');
  const tbody  = tabela ? tabela.querySelector('tbody') : null;

  if (!tabela) {
    console.error('❌ Não encontrei a tabela #scheduleTable no DOM.');
  } else {
    console.log('✅ Tabela encontrada:', tabela);
  }

  // --------- CARREGAR HORÁRIO SALVO DO JSON ---------
  if (tbody && HORARIO_HTML && HORARIO_HTML.trim() !== '') {
    console.log('Carregando HTML salvo do horário...');
    tbody.innerHTML = HORARIO_HTML;
    tbody.querySelectorAll('td').forEach((td) => {
      td.contentEditable = true;
    });
  }

  // --------- MODAL DE SUCESSO ---------
  const modalSucesso   = document.getElementById('modal-sucesso');
  const btnFecharModal = document.getElementById('fechar-modal');

  function abrirModalSucesso() {
    console.log('abrirModalSucesso() chamado');
    if (!modalSucesso) {
      alert('Horário salvo com sucesso!');
      return;
    }
    // aparece centralizado (CSS faz o resto)
    modalSucesso.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  function fecharModalSucesso() {
    if (!modalSucesso) return;
    modalSucesso.style.display = 'none';
    document.body.style.overflow = '';
  }

  if (btnFecharModal && modalSucesso) {
    btnFecharModal.addEventListener('click', fecharModalSucesso);

    // clicar no fundo fecha também
    modalSucesso.addEventListener('click', (e) => {
      if (e.target === modalSucesso) {
        fecharModalSucesso();
      }
    });
  }

  // ---------- Funções globais (usadas no HTML via onclick) ----------
  window.salvarEdicoes = function () {
    console.log('🖱️ salvarEdicoes() foi chamada');
    if (!tbody) {
      alert('Erro: tabela não encontrada.');
      return;
    }

    const html = tbody.innerHTML;
    console.log('Enviando para', SAVE_URL);
    console.log('HTML a ser salvo:', html);

    fetch(SAVE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ html })
    })
      .then(async (res) => {
        const txt = await res.text();
        console.log('📩 Resposta bruta de salvar_hora.php:', res.status, txt);

        let json;
        try {
          json = JSON.parse(txt);
          console.log('JSON parseado:', json);
        } catch (e) {
          console.error('Erro ao fazer JSON.parse:', e);
          alert('Erro ao salvar horário: resposta inválida do servidor.');
          return;
        }

        if (json.ok === true) {
          abrirModalSucesso();
        } else {
          console.warn('Servidor respondeu, mas ok != true:', json);
          alert('Erro ao salvar horário. Verifique se está logado ou tente novamente.');
        }
      })
      .catch((err) => {
        console.error('Erro no fetch para salvar_hora.php:', err);
        alert('Erro ao salvar horário. Veja o console para detalhes.');
      });
  };

  window.adicionarLinha = function () {
    if (!tabela) return;
    const novaLinha = tabela.insertRow(tabela.rows.length);
    for (let i = 0; i < 6; i++) {
      const celula = novaLinha.insertCell(i);
      celula.contentEditable = true;
      if (i === 0) {
        celula.style.backgroundColor = '#38a5ff';
        celula.style.color = 'white';
      } else {
        celula.style.backgroundColor = '#ececec';
        celula.style.color = 'black';
      }
    }
  };

  window.removerLinha = function () {
    if (!tabela) return;
    if (tabela.rows.length > 1) {
      tabela.deleteRow(tabela.rows.length - 1);
    }
  };

  window.adicionarIntervalo = function () {
    if (!tabela) return;
    const novaLinha = tabela.insertRow(tabela.rows.length);
    const celula = novaLinha.insertCell(0);
    celula.colSpan = 6;
    celula.contentEditable = true;
    celula.style.backgroundColor = '#38a5ff';
    celula.style.color = 'white';
    celula.innerHTML = 'Intervalo';
  };

  window.salvarComoPDF = function () {
    if (!tabela) return;
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    const rows = tabela.rows;
    const data = [];
    const headers = [];

    // Cabeçalho
    for (let i = 0; i < rows[0].cells.length; i++) {
      headers.push(rows[0].cells[i].textContent);
    }

    // Linhas
    for (let i = 1; i < rows.length; i++) {
      let row = [];
      for (let j = 0; j < rows[i].cells.length; j++) {
        row.push(rows[i].cells[j].textContent);
      }
      data.push(row);
    }

    // Título FOAG
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(24);
    doc.text('FOAG', 10, 10);

    // Data
    const dataAtual = new Date();
    const dataFormatada = dataAtual.toLocaleDateString('pt-BR', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });
    doc.setFontSize(12);
    doc.text(`Gerado em: ${dataFormatada}`, 10, 20);

    // Tabela
    doc.autoTable({
      head: [headers],
      body: data,
      startY: 30,
      theme: 'grid',
      margin: { top: 10 },
      tableWidth: 'auto',
      headStyles: {
        fillColor: [56, 165, 255],
        textColor: [255, 255, 255],
        fontSize: 12,
        fontStyle: 'bold'
      },
      bodyStyles: {
        fillColor: [255, 255, 255],
        textColor: [56, 165, 255],
        fontSize: 10
      },
      alternateRowStyles: {
        fillColor: [240, 240, 240]
      }
    });

    doc.save('horario_escolar.pdf');
  };

  // ---------- Logout + Perfil ----------
  const logoutModal   = document.getElementById('logout-modal');
  const confirmLogout = document.getElementById('confirm-logout');
  const cancelLogout  = document.getElementById('cancel-logout');
  const iconSair      = document.getElementById('icon-sair');
  const iconPerfil    = document.getElementById('icon-perfil');

  if (iconSair && logoutModal) {
    iconSair.addEventListener('click', () => {
      logoutModal.style.display = 'flex';
    });

    confirmLogout &&
      confirmLogout.addEventListener('click', () => {
        window.location.href = '../login/index.php';
      });

    cancelLogout &&
      cancelLogout.addEventListener('click', () => {
        logoutModal.style.display = 'none';
      });

    logoutModal.addEventListener('click', (e) => {
      if (e.target === logoutModal) {
        logoutModal.style.display = 'none';
      }
    });
  }

  if (iconPerfil) {
    iconPerfil.addEventListener('click', () => {
      window.location.href = '../perfil/perfil.php';
    });
  }
});

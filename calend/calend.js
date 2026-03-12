// calend.js — FOAG (Calendário + Agenda + Horários)

document.addEventListener('DOMContentLoaded', () => {
  // ------- AGENDA (ligação com agenda.php) -------
  const agendaData = window.CAL_AGENDA_DATA || {
    notas: [],
    tarefas: [],
    nao_esquecer: []
  };
  const AGENDA_SAVE_URL = window.CAL_AGENDA_SAVE_URL || '../bloco/salvar_agenda.php';
  const HORARIO_API_URL = window.CAL_HORARIO_URL     || '../horario/horario_api.php';
  // ----- DADOS DO CALENDÁRIO (cores + metas) -----
  const rawCalendData = window.CAL_CALEND_DATA || {};

  const calendData = {
    dias:  (rawCalendData.dias  && typeof rawCalendData.dias  === 'object') ? rawCalendData.dias  : {},
    metas: (rawCalendData.metas && typeof rawCalendData.metas === 'object') ? rawCalendData.metas : {}
  };

  const CALEND_SAVE_URL = window.CAL_CALEND_SAVE_URL || 'salvar_calendario.php';

  function salvarCalendarioServidor() {
    try {
      fetch(CALEND_SAVE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(calendData)
      })
        .then(r => r.text())
        .then(txt => console.log('Calendário salvo:', txt))
        .catch(err => console.error('Erro ao salvar calendário:', err));
    } catch (e) {
      console.error('Erro fetch calendário:', e);
    }
  }


  function salvarAgendaServidor() {
    try {
      fetch(AGENDA_SAVE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(agendaData)
      })
        .then(res => res.text())
        .then(txt => {
          console.log('Agenda salva via calendário:', txt);
        })
        .catch(err => console.error('Erro ao salvar agenda via calendário:', err));
    } catch (e) {
      console.error('Erro fetch agenda:', e);
    }
  }

  function tarefasDoDia(iso) {
    const lista = Array.isArray(agendaData.tarefas) ? agendaData.tarefas : [];
    return lista.filter(t => t.data === iso && t.texto && t.texto.trim() !== '');
  }

  function marcarDiasComTarefa() {
    if (!agendaData || !Array.isArray(agendaData.tarefas)) return;

    agendaData.tarefas.forEach(t => {
      const iso = t.data;
      if (!iso) return;
      const diaEl = document.querySelector(`.calendario .dia[data-date="${iso}"]`);
      if (diaEl) {
        diaEl.classList.add('has-tarefa');
        atualizarDots(diaEl);
      }
    });
  }

  // salva / atualiza tarefa desse dia com origem "calendario"
  function salvarTextoDoDiaNaAgenda(iso, texto) {
    if (!Array.isArray(agendaData.tarefas)) {
      agendaData.tarefas = [];
    }

    // remove tarefas desse dia criadas pelo calendário (pra não duplicar)
    agendaData.tarefas = agendaData.tarefas.filter(
      t => !(t.data === iso && t.origem === 'calendario')
    );

    const txt = (texto || '').trim();
    if (txt !== '') {
      agendaData.tarefas.push({
        texto: txt,
        data: iso,
        origem: 'calendario'
      });
    }

    // atualiza bolinha azul no dia imediatamente
    const diaEl = document.querySelector(`.calendario .dia[data-date="${iso}"]`);
    if (diaEl) {
      if (txt !== '') {
        diaEl.classList.add('has-tarefa');
      } else {
        diaEl.classList.remove('has-tarefa');
      }
      atualizarDots(diaEl);
    }

    salvarAgendaServidor();
  }

  // ------- HORÁRIO (ligação com horário.php via API) -------
  async function buscarHorarios(iso) {
    if (!HORARIO_API_URL) return [];

    try {
      const url = `${HORARIO_API_URL}?data=${encodeURIComponent(iso)}`;
      const res = await fetch(url);
      if (!res.ok) throw new Error('HTTP ' + res.status);

      const json = await res.json();
      if (!json || !json.html) return [];

      const html = json.html;

      // pega o dia da semana pelo ISO
      const data = new Date(iso + 'T00:00:00');
      const diaSemana = data.getDay(); // 0=domingo ... 6=sábado

      const mapCol = {
        1: 1, // segunda
        2: 2, // terça
        3: 3, // quarta
        4: 4, // quinta
        5: 5  // sexta
      };

      const colIndex = mapCol[diaSemana];
      if (!colIndex) {
        return []; // sábado/domingo → sem aula
      }

      const parser = new DOMParser();
      const doc = parser.parseFromString(`<table>${html}</table>`, 'text/html');
      const trs = doc.querySelectorAll('tr');

      const materias = new Set();

      trs.forEach(tr => {
        const tds = tr.querySelectorAll('td');
        if (tds.length > colIndex) {
          const texto = tds[colIndex].textContent.trim();
          if (texto) materias.add(texto);
        }
      });

      return Array.from(materias);
    } catch (e) {
      console.error('Erro ao buscar horários do dia:', e);
      return [];
    }
  }

  // ========== UTIL: FECHAR MÊS ==========
  function fecharMes(mes) {
    if (!mes) return;
    mes.classList.remove('expanded');
    mes.__corSelecionada = null;
    mes?.__atualizarBotoesCor?.();

    // garante que o botão X some no card fechado
    const btnFechar = mes.querySelector('.fechar-btn');
    if (btnFechar) btnFechar.style.display = 'none';

    if (!document.querySelector('.mes.expanded')) {
      document.body.classList.remove('no-scroll');
      document.getElementById('cal-backdrop')?.classList.remove('ativo');
    }
  }

  // ========== EXPANDIR MÊS ==========
  document.querySelectorAll('.mes').forEach(mes => {
    mes.addEventListener('click', () => {
      const aberto = document.querySelector('.mes.expanded');
      if (aberto && aberto !== mes) return;

      if (!mes.classList.contains('expanded')) {
        mes.classList.add('expanded');
        document.body.classList.add('no-scroll');
        document.getElementById('cal-backdrop')?.classList.add('ativo');

        let fechar = mes.querySelector('.fechar-btn');
        if (!fechar) {
          fechar = document.createElement('button');
          fechar.textContent = '×';
          fechar.classList.add('fechar-btn');
          fechar.onclick = e => {
            e.stopPropagation();
            fecharMes(mes);
          };
          mes.appendChild(fechar);
        }
        fechar.style.display = 'flex';
      }
    });
  });

  // ========== SELEÇÃO DE COR ==========
  document.querySelectorAll('.mes').forEach(mes => {
    mes.__corSelecionada = null;
    const botoesCor = mes.querySelectorAll('.btn-cor');

    function atualizarBotoesCorLocal() {
      botoesCor.forEach(botao => {
        if (botao.dataset.cor === mes.__corSelecionada) {
          botao.classList.add('selecionado');
          botao.style.outline = '3px solid #555';
          botao.style.transform = 'scale(1.3)';
        } else {
          botao.classList.remove('selecionado');
          botao.style.outline = 'none';
          botao.style.transform = 'scale(1)';
        }
      });
    }

    mes.__atualizarBotoesCor = atualizarBotoesCorLocal;

    botoesCor.forEach(botao => {
      botao.addEventListener('click', e => {
        e.stopPropagation();
        const cor = botao.dataset.cor;

        botoesCor.forEach(b => b.classList.remove('selecionado'));

        if (mes.__corSelecionada === cor) {
          mes.__corSelecionada = null;
        } else {
          mes.__corSelecionada = cor;
          botao.classList.add('selecionado');
        }

        atualizarBotoesCorLocal();
      });
    });
  });

  // ========== DOTS ==========
  function atualizarDots(diaEl) {
    const dots = diaEl.querySelector('.dots');
    if (!dots) return;
    dots.innerHTML = '';

    if (diaEl.classList.contains('vermelho')) dots.appendChild(criaDot('vermelho'));
    if (diaEl.classList.contains('amarelo'))  dots.appendChild(criaDot('amarelo'));
    if (diaEl.classList.contains('sem-aula')) dots.appendChild(criaDot('semaula'));
    if (diaEl.classList.contains('roxo'))     dots.appendChild(criaDot('roxo'));

    // pontinho azul escuro se tiver tarefa
    if (diaEl.classList.contains('has-tarefa')) {
      dots.appendChild(criaDot('tarefa'));
    }
  }

  function criaDot(tipo) {
    const s = document.createElement('span');
    s.className = `dot ${tipo}`;
    return s;
  }

    // ========== CLIQUE NOS DIAS (para cores) ==========
  document.querySelectorAll('.mes').forEach(mes => {
    mes.addEventListener('click', e => {
      if (!mes.classList.contains('expanded')) return;
      if (!mes.__corSelecionada) return;

      const t = e.target.closest?.('.dia');
      if (!t || t.classList.contains('header-dia') || t.textContent.trim() === '') return;

      if (t.classList.contains('feriado')) {
        alert('Este dia é feriado automático e não pode ser alterado.');
        return;
      }

      t.classList.remove('vermelho', 'amarelo', 'sem-aula', 'roxo');

      if (mes.__corSelecionada !== 'limpar') {
        t.classList.add(mes.__corSelecionada);
      }

      const iso = t.getAttribute('data-date');
      if (iso) {
        // qual status ficou no final?
        let status = null;
        if (t.classList.contains('vermelho'))  status = 'vermelho';
        else if (t.classList.contains('amarelo'))  status = 'amarelo';
        else if (t.classList.contains('sem-aula')) status = 'sem-aula';
        else if (t.classList.contains('roxo'))     status = 'roxo';

        if (status) {
          calendData.dias[iso] = status;
        } else {
          delete calendData.dias[iso];
        }
        salvarCalendarioServidor();
      }

      setTimeout(() => {
        atualizarDots(t);
        recalcularMetricasDoMes(mes);
      }, 0);
    });
  });


  // ========== MÉTRICAS / METAS ==========
  function clamp(n, min, max) {
    return Math.max(min, Math.min(max, n));
  }

  function recalcularMetricasDoMes(mes) {
    const dias = [...mes.querySelectorAll('.dia')].filter(
      d => !d.classList.contains('header-dia') && d.querySelector('.num-dia')
    );

    let pres = 0, falt = 0, atest = 0, sem = 0, provas = 0, totalValidos = 0;

    dias.forEach(d => {
      if (d.classList.contains('sem-aula')) { sem++; return; }
      if (!d.classList.contains('feriado')) totalValidos++;

      if (d.classList.contains('vermelho')) falt++;
      if (d.classList.contains('amarelo'))  atest++;
      if (d.classList.contains('roxo'))     provas++;

      const marcado = d.classList.contains('vermelho') || d.classList.contains('amarelo');
      const feriado = d.classList.contains('feriado');
      if (!marcado && !feriado) pres++;
    });

    const metaInput = mes.querySelector('.meta-presenca');
    const progress  = mes.querySelector('.progress-bar');
    const label     = mes.querySelector('.label-presenca');

    mes.querySelector('.count-presenca').textContent = pres;
    mes.querySelector('.count-falta').textContent    = falt;
    mes.querySelector('.count-atestado').textContent = atest;
    mes.querySelector('.count-semaula').textContent  = sem;
    mes.querySelector('.count-prova').textContent    = provas;

    const meta     = clamp(parseInt(metaInput?.value || '80', 10), 0, 100);
    const percPres = totalValidos > 0 ? Math.round((pres / totalValidos) * 100) : 0;

    if (progress) progress.style.width = Math.min(100, Math.round((percPres / meta) * 100)) + '%';
    if (label)    label.textContent = `${percPres}%`;

    const ano = mes.dataset.ano;
    const idx = mes.dataset.mes;
    localStorage.setItem(
      `foag_meta_${ano}_${idx}`,
      JSON.stringify({ meta, percPres, pres, falt, atest, sem, provas })
    );
  }

    document.querySelectorAll('.mes .meta-presenca').forEach(inp => {
    inp.addEventListener('change', e => {
      const mes = e.target.closest('.mes');
      const ano = mes.dataset.ano;
      const idx = mes.dataset.mes;
      const key = `${ano}-${idx}`;

      const valor = parseInt(e.target.value || '0', 10);
      calendData.metas[key] = isNaN(valor) ? 0 : valor;

      salvarCalendarioServidor();
      recalcularMetricasDoMes(mes);
    });
  });


  // ========== MINI-AGENDA (Agenda + Horários) ==========
  function formataDataBR(iso) {
    const [y, m, d] = iso.split('-').map(Number);
    return `${String(d).padStart(2, '0')}/${String(m).padStart(2, '0')}/${y}`;
  }

  // Duplo clique = ir direto pro editor
  document.querySelectorAll('.mes .dia').forEach(d => {
    d.addEventListener('dblclick', e => {
      const mes = d.closest('.mes');
      if (!mes || !mes.classList.contains('expanded')) return;
      if (d.classList.contains('header-dia')) return;

      const box     = mes.querySelector('.mini-agenda');
      const dataEl  = box?.querySelector('.agenda-data');
      const notasEl = box?.querySelector('.agenda-notas');

      if (!box || !dataEl || !notasEl) return;

      const iso = d.getAttribute('data-date');
      if (!iso) return;

      box.dataset.date = iso;
      dataEl.textContent = formataDataBR(iso);

      const lista     = tarefasDoDia(iso);
      const tarefaCal = lista.find(t => t.origem === 'calendario');
      notasEl.value = tarefaCal ? tarefaCal.texto : '';

      const resumo = box.querySelector('.agenda-resumo');
      const editor = box.querySelector('.agenda-editor');
      if (resumo) resumo.style.display = 'none';
      if (editor) editor.style.display = 'block';

      box.classList.add('aberto');
      notasEl.focus();
      e.stopPropagation();
    });
  });

  document.querySelectorAll('.mes .agenda-fechar').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const mes = e.target.closest('.mes');
      const box = mes.querySelector('.mini-agenda');
      box.classList.remove('aberto');
    });
  });

  document.querySelectorAll('.mes .agenda-salvar').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const mes    = e.target.closest('.mes');
      const box    = mes.querySelector('.mini-agenda');
      const notasEl = box?.querySelector('.agenda-notas');
      const iso    = box?.dataset.date;

      if (!iso || !notasEl) {
        box?.classList.remove('aberto');
        return;
      }

      salvarTextoDoDiaNaAgenda(iso, notasEl.value);
      box.classList.remove('aberto');
    });
  });

  // Clique simples no dia (sem cor selecionada) → mini-agenda com opções
  document.querySelectorAll('.mes .dia').forEach(diaEl => {
    diaEl.addEventListener('click', e => {
      const mes = diaEl.closest('.mes');
      if (!mes || !mes.classList.contains('expanded')) return;
      if (mes.__corSelecionada) return;
      if (diaEl.classList.contains('header-dia')) return;

      const iso = diaEl.getAttribute('data-date');
      if (!iso) return;

      e.stopPropagation();

      const mini   = mes.querySelector('.mini-agenda');
      const dataEl = mini?.querySelector('.agenda-data');
      const resumo = mini?.querySelector('.agenda-resumo');
      const editor = mini?.querySelector('.agenda-editor');
      const notas  = mini?.querySelector('.agenda-notas');
      const btnVer  = mini?.querySelector('.btn-ver-tarefas');
      const btnNova = mini?.querySelector('.btn-nova-tarefa');
      const btnHor  = mini?.querySelector('.btn-ver-horarios');

      if (!mini || !dataEl || !resumo || !editor || !notas || !btnVer || !btnNova || !btnHor) return;

      mini.dataset.date = iso;
      dataEl.textContent = formataDataBR(iso);

      // Ver tarefas do dia
      btnVer.onclick = () => {
        const ts = tarefasDoDia(iso);
        if (!ts.length) {
          resumo.innerHTML = `<p class="agenda-resumo-vazio">Nenhuma tarefa cadastrada para este dia.</p>`;
        } else {
          resumo.innerHTML = `
            <div class="agenda-bloco">
              <strong>Tarefas do dia</strong>
              <ul>${ts.map(t => `<li>${t.texto}</li>`).join('')}</ul>
            </div>
          `;
        }
        resumo.style.display = 'block';
        editor.style.display = 'none';
      };

      // Agendar nova tarefa
      btnNova.onclick = () => {
        const lista = tarefasDoDia(iso);
        const tCal  = lista.find(t => t.origem === 'calendario');
        notas.value = tCal ? (tCal.texto || '') : '';
        editor.style.display = 'block';
        resumo.style.display = 'none';
        notas.focus();
      };

      // Ver horários do dia
      btnHor.onclick = async () => {
        resumo.style.display = 'block';
        editor.style.display = 'none';
        resumo.innerHTML = `<p>Carregando horários...</p>`;

        const horarios = await buscarHorarios(iso);

        if (!horarios.length) {
          resumo.innerHTML = `<p class="agenda-resumo-vazio">Nenhum horário cadastrado para este dia.</p>`;
        } else {
          const materias = Array.from(new Set(horarios)); // remove duplicados

          resumo.innerHTML = `
            <div class="agenda-bloco">
              <strong>Horários do dia</strong>
              <p>${materias.join(', ')}</p>
            </div>
          `;
        }
      };

      // padrão ao clicar
      if (tarefasDoDia(iso).length) {
        btnVer.click();
      } else {
        btnNova.click();
      }

      mini.classList.add('aberto');
    });
  });

  // ========== EXPORTAR / IMPRIMIR ==========
  document.querySelectorAll('.mes .btn-imprimir').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const mes = e.target.closest('.mes');
      if (!mes.classList.contains('expanded')) {
        mes.classList.add('expanded');
        document.body.classList.add('no-scroll');
        document.getElementById('cal-backdrop')?.classList.add('ativo');

        let fechar = mes.querySelector('.fechar-btn');
        if (!fechar) {
          fechar = document.createElement('button');
          fechar.textContent = '×';
          fechar.classList.add('fechar-btn');
          fechar.onclick = ev => {
            ev.stopPropagation();
            fecharMes(mes);
          };
          mes.appendChild(fechar);
        }
        fechar.style.display = 'flex';
      }
      window.print();
    });
  });

  document.querySelectorAll('.mes .btn-exportar-png').forEach(btn => {
    btn.addEventListener('click', async e => {
      e.preventDefault();
      const mes = e.target.closest('.mes');
      const bloco = mes.querySelector('.calendario-mes');
      if (!bloco) return;

      const ano = mes.dataset.ano;
      const idx = mes.dataset.mes;
      const nomes = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
      const nomeMes = nomes[parseInt(idx, 10) - 1] || idx;

      if (typeof html2canvas !== 'function') {
        console.warn('html2canvas não carregado.');
        return;
      }

      const canvas = await html2canvas(bloco, {
        useCORS: true,
        backgroundColor: '#ffffff',
        scale: 2
      });

      const link = document.createElement('a');
      link.download = `Calendario_${nomeMes}_${ano}.png`;
      link.href = canvas.toDataURL('image/png');
      link.click();
    });
  });

  // ========== SELETOR DE ANO ==========
  document.querySelectorAll('.mes .anoSelect').forEach(sel => {
    const urlParams = new URLSearchParams(location.search);
    const anoAtual = parseInt(urlParams.get('ano') || new Date().getFullYear(), 10);

    for (let a = anoAtual - 4; a <= anoAtual + 4; a++) {
      const op = document.createElement('option');
      op.value = a;
      op.textContent = a;
      if (a === anoAtual) op.selected = true;
      sel.appendChild(op);
    }

    sel.addEventListener('change', () => {
      const url = new URL(location.href);
      url.searchParams.set('ano', sel.value);
      location.href = url.toString();
    });
  });

  // ========== GARANTIR VISIBILIDADE DO PAINEL DE METAS ==========
  function verificarVisibilidadeMeta(mes) {
    const metas = mes.querySelector('.painel-metas');
    if (!metas) return;
    metas.style.display = 'flex';
    metas.style.visibility = 'visible';
    metas.style.opacity = '1';
  }

    // ========== INICIALIZAÇÃO ==========

  // 1) aplicar cores salvas
  document.querySelectorAll('.calendario .dia').forEach(diaEl => {
    const iso = diaEl.getAttribute('data-date');
    if (!iso) return;
    const status = calendData.dias?.[iso];
    if (status) {
      diaEl.classList.add(status); // 'vermelho', 'amarelo', 'sem-aula', 'roxo'
    }
    atualizarDots(diaEl);
  });

  // 2) marcar dias com tarefa
  marcarDiasComTarefa();

  // 3) aplicar metas salvas e recalcular
  document.querySelectorAll('.mes').forEach(mes => {
    const ano = mes.dataset.ano;
    const idx = mes.dataset.mes;
    const key = `${ano}-${idx}`;

    const metaSalva = calendData.metas?.[key];
    const metaInput = mes.querySelector('.meta-presenca');
    if (metaInput && metaSalva != null) {
      metaInput.value = metaSalva;
    }

    recalcularMetricasDoMes(mes);

    mes.addEventListener('click', () => {
      setTimeout(() => {
        if (mes.classList.contains('expanded')) {
          verificarVisibilidadeMeta(mes);
        }
      }, 80);
    });
  });


  // ========== ÍCONES HEADER (PERFIL / LOGOUT) ==========
  const perfilIcon    = document.getElementById('icon-perfil');
  const logoutModal   = document.getElementById('logout-modal');
  const iconSair      = document.getElementById('icon-sair');
  const confirmLogout = document.getElementById('confirm-logout');
  const cancelLogout  = document.getElementById('cancel-logout');

  if (perfilIcon) {
    perfilIcon.addEventListener('click', () => {
      window.location.href = '../perfil/perfil.php';
    });
  }

  if (iconSair && logoutModal) {
    iconSair.addEventListener('click', () => {
      logoutModal.style.display = 'flex';
    });
  }

  if (confirmLogout) {
    confirmLogout.addEventListener('click', () => {
      window.location.href = '../login/index.php';
    });
  }

  if (cancelLogout && logoutModal) {
    cancelLogout.addEventListener('click', () => {
      logoutModal.style.display = 'none';
    });

    logoutModal.addEventListener('click', e => {
      if (e.target === logoutModal) logoutModal.style.display = 'none';
    });
  }

  // ========== MODAL FOGi ==========
  const fogiBtn   = document.getElementById('icon-fogi');
  const fogiModal = document.getElementById('fogi-modal');
  const fogiFrame = document.getElementById('fogi-iframe');
  const fogiClose = document.getElementById('fogi-close');

  if (fogiBtn && fogiModal && fogiFrame && fogiClose) {
    fogiBtn.addEventListener('click', () => {
      fogiFrame.src = 'http://127.0.0.1:5000'; // Flask/Ollama
      fogiModal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    });

    fogiClose.addEventListener('click', () => {
      fogiModal.style.display = 'none';
      fogiFrame.src = 'about:blank';
      document.body.style.overflow = '';
    });

    window.addEventListener('message', ev => {
      if (ev.data && ev.data.type === 'FOGI_CLOSE') {
        fogiModal.style.display = 'none';
        fogiFrame.src = 'about:blank';
        document.body.style.overflow = '';
      }
    });
  }
});

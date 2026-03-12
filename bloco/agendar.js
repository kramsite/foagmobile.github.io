document.addEventListener('DOMContentLoaded', function() {
    console.log('agenda.js carregado, DOM pronto');

    // Dados vindos do PHP (agenda.php)
    let agendaData = window.AGENDA_DATA || {
        notas: [],
        tarefas: [],
        nao_esquecer: []
    };
    console.log('AGENDA_DATA inicial:', agendaData);

    const SAVE_URL = window.AGENDA_SAVE_URL || 'salvar_agenda.php';
    console.log('SAVE_URL:', SAVE_URL);

    // -----------------------------------------
    // Função para salvar no servidor (JSON)
    // -----------------------------------------
    function salvarNoServidor() {
        console.log('Salvando no servidor...', SAVE_URL, agendaData);

        fetch(SAVE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(agendaData)
        })
        .then(async (res) => {
            const txt = await res.text();
            console.log('Resposta do salvar_agenda.php:', res.status, txt);
        })
        .catch(err => {
            console.error('Erro ao salvar agenda:', err);
        });
    }

    // Elementos do DOM
    const listaTarefas = document.getElementById('lista-tarefas');
    const listaNaoEsquecer = document.getElementById('lista-nao-esquecer');
    const salvarNotaButton = document.getElementById('btn-salvar-nota');
    const textareaNotas = document.querySelector('#notas textarea');
    const noteList = document.getElementById('noteList');
    
    // Elementos dos modais
    const modalNomearNota = document.getElementById('modal-nomear-nota');
    const inputNomeNota = document.getElementById('nome-nota');
    const btnConfirmarNomeNota = document.getElementById('confirmar-nome-nota');
    const btnCancelarNomeNota = document.getElementById('cancelar-nome-nota');
    
    // Elementos do modal de exclusão
    const modalExcluir = document.getElementById('modal-excluir');
    const excluirTitulo = document.getElementById('excluir-titulo');
    const excluirMensagem = document.getElementById('excluir-mensagem');
    const btnConfirmarExclusao = document.getElementById('confirmar-exclusao');
    const btnCancelarExclusao = document.getElementById('cancelar-exclusao');
    
    let notaPendente = null;
    let tipoExclusao = ''; // 'nota', 'tarefa', 'nao-esquecer', 'sobrescrever'
    let dadosExclusao = null;

    // =============================================
    // FUNÇÕES PARA OS MODAIS
    // =============================================

    function abrirModalNomearNota() {
        modalNomearNota.style.display = 'flex';
        inputNomeNota.value = '';
        inputNomeNota.focus();
    }
    
    function fecharModalNomearNota() {
        modalNomearNota.style.display = 'none';
        notaPendente = null;
    }

    function abrirModalExclusao(titulo, mensagem, tipo, dados) {
        excluirTitulo.textContent = titulo;
        excluirMensagem.textContent = mensagem;
        tipoExclusao = tipo;
        dadosExclusao = dados;
        modalExcluir.style.display = 'flex';
    }
    
    function fecharModalExclusao() {
        modalExcluir.style.display = 'none';
        tipoExclusao = '';
        dadosExclusao = null;
    }

    function executarExclusao() {
        switch (tipoExclusao) {
            case 'nota':
                excluirNota(dadosExclusao.id);
                break;
            case 'tarefa':
                excluirTarefa(dadosExclusao.linha);
                break;
            case 'nao-esquecer':
                excluirNaoEsquecer(dadosExclusao.linha);
                break;
            case 'sobrescrever':
                sobrescreverNota(dadosExclusao.titulo, dadosExclusao.texto);
                break;
        }
        fecharModalExclusao();
    }

    // =============================================
    // FUNÇÕES PARA TAREFAS E "NÃO ESQUECER"
    // =============================================

    function criarLinha(lista) {
        const index = lista.rows.length + 1;
        const row = lista.insertRow();

        // Célula do índice
        const cellIndex = row.insertCell(0);
        cellIndex.textContent = index;

        // Célula do conteúdo (editável)
        const cellConteudo = row.insertCell(1);
        cellConteudo.contentEditable = true;
        cellConteudo.style.wordBreak = 'break-word';

        // Célula da data
        const cellData = row.insertCell(2);
        const inputData = document.createElement('input');
        inputData.type = 'date';
        cellData.appendChild(inputData);

        // Célula de ações (botão excluir)
        const cellAcoes = row.insertCell(3);
        const btnExcluir = document.createElement('button');
        btnExcluir.textContent = 'Excluir';
        btnExcluir.className = 'btn-excluir';
        
        btnExcluir.addEventListener('click', function() {
            const texto = cellConteudo.textContent || 'Item sem título';
            const tipo = lista.id === 'lista-tarefas' ? 'tarefa' : 'nao-esquecer';
            const titulo = tipo === 'tarefa' ? 'Excluir Tarefa' : 'Excluir Item';
            const mensagem = `Tem certeza que deseja excluir "${texto.substring(0, 50)}${texto.length > 50 ? '...' : ''}"?`;
            
            abrirModalExclusao(titulo, mensagem, tipo, { linha: row });
        });

        cellAcoes.appendChild(btnExcluir);
        return row;
    }

    function excluirTarefa(linha) {
        listaTarefas.deleteRow(linha.rowIndex - 1);
        atualizarIndices(listaTarefas);
        salvarDados();
    }

    function excluirNaoEsquecer(linha) {
        listaNaoEsquecer.deleteRow(linha.rowIndex - 1);
        atualizarIndices(listaNaoEsquecer);
        salvarDados();
    }

    function atualizarIndices(lista) {
        Array.from(lista.rows).forEach((row, idx) => {
            row.cells[0].textContent = idx + 1;
        });
    }

    // =============================================
    // PERSISTÊNCIA DE DADOS (TAREFAS E ITENS)
    // =============================================

    function salvarDados() {
        console.log('salvarDados() chamado');

        // Atualiza tarefas em agendaData
        agendaData.tarefas = [];
        document.querySelectorAll('#lista-tarefas tr').forEach(linha => {
            agendaData.tarefas.push({
                texto: linha.cells[1].textContent,
                data: linha.cells[2].querySelector('input').value
            });
        });

        // Atualiza "Não Esquecer" em agendaData
        agendaData.nao_esquecer = [];
        document.querySelectorAll('#lista-nao-esquecer tr').forEach(linha => {
            agendaData.nao_esquecer.push({
                texto: linha.cells[1].textContent,
                data: linha.cells[2].querySelector('input').value
            });
        });

        salvarNoServidor();
    }

    function carregarDados() {
        console.log('carregarDados() chamado com:', agendaData);

        // Carregar tarefas
        listaTarefas.innerHTML = '';
        (agendaData.tarefas || []).forEach(tarefa => {
            const linha = criarLinha(listaTarefas);
            linha.cells[1].textContent = tarefa.texto || '';
            linha.cells[2].querySelector('input').value = tarefa.data || '';
        });

        // Carregar "Não Esquecer"
        listaNaoEsquecer.innerHTML = '';
        (agendaData.nao_esquecer || []).forEach(item => {
            const linha = criarLinha(listaNaoEsquecer);
            linha.cells[1].textContent = item.texto || '';
            linha.cells[2].querySelector('input').value = item.data || '';
        });
    }

    // =============================================
    // FUNÇÕES PARA NOTAS (COM MODAL)
    // =============================================
    
    function salvarNotaComTitulo(texto, titulo) {
        if (!titulo.trim()) {
            alert('Por favor, dê um nome para sua nota.');
            return false;
        }
        
        // Verificar se já existe uma nota com o mesmo título
        const notaExistente = (agendaData.notas || []).find(nota => nota.titulo === titulo);
        if (notaExistente) {
            abrirModalExclusao(
                'Sobrescrever Nota', 
                `Já existe uma nota com o título "${titulo}". Deseja sobrescrever?`, 
                'sobrescrever', 
                { titulo: titulo, texto: texto }
            );
            return false;
        }
        
        const novaNota = {
            id: Date.now(),
            titulo: titulo,
            texto: texto,
            data: new Date().toLocaleString('pt-BR')
        };
        
        if (!Array.isArray(agendaData.notas)) {
            agendaData.notas = [];
        }

        agendaData.notas.push(novaNota);
        salvarNoServidor();
        carregarNotas();
        return true;
    }

    function sobrescreverNota(titulo, texto) {
        if (!Array.isArray(agendaData.notas)) {
            agendaData.notas = [];
        }

        // remove nota antiga
        agendaData.notas = agendaData.notas.filter(nota => nota.titulo !== titulo);

        const novaNota = {
            id: Date.now(),
            titulo: titulo,
            texto: texto,
            data: new Date().toLocaleString('pt-BR')
        };

        agendaData.notas.push(novaNota);
        salvarNoServidor();
        carregarNotas();
    }
    
    function carregarNotas() {
        const notas = Array.isArray(agendaData.notas) ? agendaData.notas : [];
        noteList.innerHTML = '';
        
        if (notas.length === 0) {
            noteList.innerHTML = '<div class="sem-notas">Nenhuma nota salva ainda.</div>';
            return;
        }
        
        // Ordenar notas por data (mais recente primeiro)
        notas.sort((a, b) => b.id - a.id);
        
        // Adicionar cada nota à lista
        notas.forEach(nota => {
            const notaElement = document.createElement('div');
            notaElement.className = 'nota-item';
            notaElement.innerHTML = `
                <span class="nota-titulo">${nota.titulo}</span>
                <span class="nota-data">${nota.data}</span>
                <div class="nota-conteudo">${nota.texto}</div>
                <div class="nota-acoes">
                    <button class="btn-nota btn-editar" data-id="${nota.id}">Editar</button>
                    <button class="btn-nota btn-excluir-nota" data-id="${nota.id}" data-titulo="${nota.titulo}">Excluir</button>
                    <button class="btn-nota btn-pequeno" data-title="${nota.titulo}">Baixar PDF</button>
                </div>
            `;
            
            noteList.appendChild(notaElement);
        });
        
        // Adicionar eventos aos botões
        document.querySelectorAll('.btn-excluir-nota').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                const titulo = this.getAttribute('data-titulo');
                abrirModalExclusao(
                    'Excluir Nota', 
                    `Tem certeza que deseja excluir a nota "${titulo}"? Esta ação não pode ser desfeita.`, 
                    'nota', 
                    { id: id, titulo: titulo }
                );
            });
        });
        
        document.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                editarNota(id);
            });
        });
        
        document.querySelectorAll('.btn-pequeno').forEach(btn => {
            btn.addEventListener('click', function() {
                const title = this.getAttribute('data-title');
                const notasAtual = Array.isArray(agendaData.notas) ? agendaData.notas : [];
                const nota = notasAtual.find(n => n.titulo === title);
                if (nota) {
                    baixarPdf(nota.titulo, nota.texto);
                }
            });
        });
    }
    
    function excluirNota(id) {
        if (!Array.isArray(agendaData.notas)) {
            agendaData.notas = [];
        }
        agendaData.notas = agendaData.notas.filter(nota => nota.id !== id);
        salvarNoServidor();
        carregarNotas();
    }
    
    function editarNota(id) {
        if (!Array.isArray(agendaData.notas)) {
            agendaData.notas = [];
        }

        const idx = agendaData.notas.findIndex(nota => nota.id === id);
        if (idx === -1) return;

        const nota = agendaData.notas[idx];

        // Preencher o textarea com o conteúdo da nota
        textareaNotas.value = nota.texto;

        // Preencher o campo de título no modal
        inputNomeNota.value = nota.titulo;

        // Abrir o modal para edição
        notaPendente = nota.texto;

        // remove a antiga (vai ser recriada ao salvar)
        agendaData.notas.splice(idx, 1);
        salvarNoServidor();
        carregarNotas();

        abrirModalNomearNota();
    }
    
    function baixarPdf(title, content) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const pageWidth = doc.internal.pageSize.getWidth();
        const margin = 10;
        let y = 10;

        // Cabeçalho
        doc.setFontSize(20);
        doc.setTextColor(40, 40, 120);
        doc.text('FOAG - Minhas Notas', pageWidth / 2, y, { align: 'center' });
        y += 8;

        doc.setFontSize(10);
        doc.text(`Exportado em: ${new Date().toLocaleString('pt-BR')}`, pageWidth / 2, y, { align: 'center' });
        y += 10;

        // Título da nota
        doc.setFontSize(16);
        doc.setTextColor(0);
        doc.text(title, margin, y);
        y += 10;

        // Conteúdo
        doc.setFontSize(12);
        const splitContent = doc.splitTextToSize(content, pageWidth - 2 * margin);
        doc.text(splitContent, margin, y);

        doc.save(`${title}.pdf`);
    }

    // =============================================
    // EVENT LISTENERS
    // =============================================

    // Tarefas e Itens
    document.getElementById('add-tarefa').addEventListener('click', () => {
        criarLinha(listaTarefas);
        salvarDados();
    });

    document.getElementById('add-nao-esquecer').addEventListener('click', () => {
        criarLinha(listaNaoEsquecer);
        salvarDados();
    });

    // Notas com modal
    salvarNotaButton.addEventListener('click', function() {
        const textoNota = textareaNotas.value.trim();
        
        if (textoNota) {
            notaPendente = textoNota;
            textareaNotas.value = '';
            abrirModalNomearNota();
        } else {
            alert('Por favor, escreva algo na nota antes de salvar.');
        }
    });
    
    btnConfirmarNomeNota.addEventListener('click', function() {
        const nomeNota = inputNomeNota.value.trim();
        
        if (salvarNotaComTitulo(notaPendente, nomeNota)) {
            fecharModalNomearNota();
        }
    });
    
    btnCancelarNomeNota.addEventListener('click', function() {
        fecharModalNomearNota();
    });
    
    // Modal de exclusão
    btnConfirmarExclusao.addEventListener('click', executarExclusao);
    btnCancelarExclusao.addEventListener('click', fecharModalExclusao);
    
    // Fechar modais ao clicar fora
    modalNomearNota.addEventListener('click', function(e) {
        if (e.target === modalNomearNota) {
            fecharModalNomearNota();
        }
    });
    
    modalExcluir.addEventListener('click', function(e) {
        if (e.target === modalExcluir) {
            fecharModalExclusao();
        }
    });

    // Salvar automaticamente ao editar
    listaTarefas.addEventListener('input', salvarDados);
    listaNaoEsquecer.addEventListener('input', salvarDados);

    // Carregar tudo ao iniciar
    carregarDados();
    carregarNotas();

    // Logout Modal
    const logoutModal = document.getElementById('logout-modal');
    if (logoutModal) {
        document.getElementById('icon-sair').addEventListener('click', () => {
            logoutModal.style.display = 'flex';
        });

        document.getElementById('confirm-logout').addEventListener('click', () => {
            window.location.href = '../login/index.php';
        });

        document.getElementById('cancel-logout').addEventListener('click', () => {
            logoutModal.style.display = 'none';
        });

        logoutModal.addEventListener('click', (e) => {
            if (e.target === logoutModal) {
                logoutModal.style.display = 'none';
            }
        });
    }

    // Expor funções pra debug no console
    window._debugSalvar = salvarNoServidor;
    window._debugAgenda = agendaData;
    console.log('Debug pronto: use _debugSalvar() e veja window._debugAgenda');
});

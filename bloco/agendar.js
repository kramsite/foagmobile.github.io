// =====================================================
// agendar.js — FOAG
// Agenda + Notas + Tarefas + Horário semanal
// =====================================================

document.addEventListener('DOMContentLoaded', function () {
    console.log('Agenda + Horário carregados ✅');

    // =================================================
    // CONFIGURAÇÕES
    // =================================================

    const AGENDA_SAVE_URL =
        window.AGENDA_SAVE_URL || 'salvar_agenda.php';

    const HORARIO_SAVE_URL =
        window.HORARIO_SAVE_URL || '../horario/salvar_horario.php';

    const HORARIO_HTML =
        typeof window.HORARIO_HTML === 'string'
            ? window.HORARIO_HTML
            : '';

    let agendaData = window.AGENDA_DATA;

    if (
        !agendaData ||
        typeof agendaData !== 'object' ||
        Array.isArray(agendaData)
    ) {
        agendaData = {
            notas: [],
            tarefas: [],
            nao_esquecer: []
        };
    }

    if (!Array.isArray(agendaData.notas)) {
        agendaData.notas = [];
    }

    if (!Array.isArray(agendaData.tarefas)) {
        agendaData.tarefas = [];
    }

    if (!Array.isArray(agendaData.nao_esquecer)) {
        agendaData.nao_esquecer = [];
    }

    // =================================================
    // FUNÇÕES AUXILIARES
    // =================================================

    function debounce(funcao, tempo = 500) {
        let temporizador;

        return function (...argumentos) {
            clearTimeout(temporizador);

            temporizador = setTimeout(function () {
                funcao.apply(null, argumentos);
            }, tempo);
        };
    }

    function escaparHtml(valor) {
        return String(valor ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function nomeArquivoSeguro(nome) {
        const nomeTratado = String(nome || 'nota')
            .replace(/[<>:"/\\|?*\x00-\x1F]/g, '_')
            .trim();

        return nomeTratado || 'nota';
    }

    // =================================================
    // AGENDA — ELEMENTOS
    // =================================================

    const listaTarefas =
        document.getElementById('lista-tarefas');

    const listaNaoEsquecer =
        document.getElementById('lista-nao-esquecer');

    const salvarNotaButton =
        document.getElementById('btn-salvar-nota');

    const textareaNotas =
        document.querySelector('#notas textarea');

    const noteList =
        document.getElementById('noteList');

    const addTarefaButton =
        document.getElementById('add-tarefa');

    const addNaoEsquecerButton =
        document.getElementById('add-nao-esquecer');

    // =================================================
    // MODAL PARA NOMEAR NOTA
    // =================================================

    const modalNomearNota =
        document.getElementById('modal-nomear-nota');

    const inputNomeNota =
        document.getElementById('nome-nota');

    const btnConfirmarNomeNota =
        document.getElementById('confirmar-nome-nota');

    const btnCancelarNomeNota =
        document.getElementById('cancelar-nome-nota');

    // =================================================
    // MODAL DE EXCLUSÃO
    // =================================================

    const modalExcluir =
        document.getElementById('modal-excluir');

    const excluirTitulo =
        document.getElementById('excluir-titulo');

    const excluirMensagem =
        document.getElementById('excluir-mensagem');

    const btnConfirmarExclusao =
        document.getElementById('confirmar-exclusao');

    const btnCancelarExclusao =
        document.getElementById('cancelar-exclusao');

    let notaPendente = '';
    let notaEmEdicaoId = null;

    let tipoExclusao = '';
    let dadosExclusao = null;

    // =================================================
    // SALVAR AGENDA NO SERVIDOR
    // =================================================

    async function salvarAgendaNoServidor() {
        try {
            const resposta = await fetch(AGENDA_SAVE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(agendaData)
            });

            const textoResposta = await resposta.text();

            console.log(
                'Resposta do salvar_agenda.php:',
                resposta.status,
                textoResposta
            );

            if (!resposta.ok) {
                throw new Error(
                    `Erro HTTP ${resposta.status}: ${textoResposta}`
                );
            }

            return true;
        } catch (erro) {
            console.error('Erro ao salvar a Agenda:', erro);

            alert(
                'Não foi possível salvar a Agenda. Tente novamente.'
            );

            return false;
        }
    }

    // =================================================
    // MODAIS DA AGENDA
    // =================================================

    function abrirModalNomearNota(tituloInicial = '') {
        if (!modalNomearNota || !inputNomeNota) {
            return;
        }

        inputNomeNota.value = tituloInicial;
        modalNomearNota.style.display = 'flex';

        setTimeout(function () {
            inputNomeNota.focus();
            inputNomeNota.select();
        }, 50);
    }

    function fecharModalNomearNota() {
        if (modalNomearNota) {
            modalNomearNota.style.display = 'none';
        }

        notaPendente = '';
        notaEmEdicaoId = null;
    }

    function abrirModalExclusao(
        titulo,
        mensagem,
        tipo,
        dados
    ) {
        if (
            !modalExcluir ||
            !excluirTitulo ||
            !excluirMensagem
        ) {
            return;
        }

        excluirTitulo.textContent = titulo;
        excluirMensagem.textContent = mensagem;

        tipoExclusao = tipo;
        dadosExclusao = dados;

        modalExcluir.style.display = 'flex';
    }

    function fecharModalExclusao() {
        if (modalExcluir) {
            modalExcluir.style.display = 'none';
        }

        tipoExclusao = '';
        dadosExclusao = null;
    }

    // =================================================
    // TAREFAS E NÃO ESQUECER
    // =================================================

    function atualizarIndices(lista) {
        if (!lista) {
            return;
        }

        Array.from(lista.rows).forEach(function (linha, indice) {
            if (linha.cells[0]) {
                linha.cells[0].textContent = indice + 1;
            }
        });
    }

    function criarLinhaAgenda(lista) {
        if (!lista) {
            return null;
        }

        const linha = lista.insertRow();

        // Número
        const celulaIndice = linha.insertCell(0);
        celulaIndice.textContent = lista.rows.length;

        // Conteúdo
        const celulaConteudo = linha.insertCell(1);
        celulaConteudo.contentEditable = 'true';
        celulaConteudo.style.wordBreak = 'break-word';

        // Data
        const celulaData = linha.insertCell(2);

        const inputData = document.createElement('input');
        inputData.type = 'date';

        celulaData.appendChild(inputData);

        // Ações
        const celulaAcoes = linha.insertCell(3);

        const botaoExcluir = document.createElement('button');
        botaoExcluir.type = 'button';
        botaoExcluir.textContent = 'Excluir';
        botaoExcluir.className = 'btn-excluir';

        botaoExcluir.addEventListener('click', function () {
            const texto =
                celulaConteudo.textContent.trim() ||
                'Item sem título';

            const tipo =
                lista.id === 'lista-tarefas'
                    ? 'tarefa'
                    : 'nao-esquecer';

            const titulo =
                tipo === 'tarefa'
                    ? 'Excluir Tarefa'
                    : 'Excluir Item';

            const resumo =
                texto.length > 50
                    ? `${texto.substring(0, 50)}...`
                    : texto;

            abrirModalExclusao(
                titulo,
                `Tem certeza que deseja excluir "${resumo}"?`,
                tipo,
                {
                    linha: linha
                }
            );
        });

        celulaAcoes.appendChild(botaoExcluir);

        return linha;
    }

    function salvarDadosAgenda() {
        if (!listaTarefas || !listaNaoEsquecer) {
            return;
        }

        agendaData.tarefas = Array.from(
            listaTarefas.rows
        ).map(function (linha) {
            const inputData =
                linha.cells[2]?.querySelector('input');

            return {
                texto:
                    linha.cells[1]?.textContent.trim() || '',
                data:
                    inputData?.value || ''
            };
        });

        agendaData.nao_esquecer = Array.from(
            listaNaoEsquecer.rows
        ).map(function (linha) {
            const inputData =
                linha.cells[2]?.querySelector('input');

            return {
                texto:
                    linha.cells[1]?.textContent.trim() || '',
                data:
                    inputData?.value || ''
            };
        });

        salvarAgendaNoServidor();
    }

    const salvarDadosComAtraso =
        debounce(salvarDadosAgenda, 500);

    function carregarDadosAgenda() {
        if (!listaTarefas || !listaNaoEsquecer) {
            return;
        }

        listaTarefas.innerHTML = '';
        listaNaoEsquecer.innerHTML = '';

        agendaData.tarefas.forEach(function (tarefa) {
            const linha = criarLinhaAgenda(listaTarefas);

            if (!linha) {
                return;
            }

            linha.cells[1].textContent =
                tarefa.texto || '';

            const inputData =
                linha.cells[2].querySelector('input');

            if (inputData) {
                inputData.value =
                    tarefa.data || '';
            }
        });

        agendaData.nao_esquecer.forEach(function (item) {
            const linha =
                criarLinhaAgenda(listaNaoEsquecer);

            if (!linha) {
                return;
            }

            linha.cells[1].textContent =
                item.texto || '';

            const inputData =
                linha.cells[2].querySelector('input');

            if (inputData) {
                inputData.value =
                    item.data || '';
            }
        });

        atualizarIndices(listaTarefas);
        atualizarIndices(listaNaoEsquecer);
    }

    function excluirTarefa(linha) {
        if (!linha) {
            return;
        }

        linha.remove();
        atualizarIndices(listaTarefas);
        salvarDadosAgenda();
    }

    function excluirNaoEsquecer(linha) {
        if (!linha) {
            return;
        }

        linha.remove();
        atualizarIndices(listaNaoEsquecer);
        salvarDadosAgenda();
    }

    // =================================================
    // NOTAS
    // =================================================

    function baixarPdfNota(titulo, conteudo) {
        if (!window.jspdf || !window.jspdf.jsPDF) {
            alert('A biblioteca de PDF não foi carregada.');
            return;
        }

        const { jsPDF } = window.jspdf;
        const documento = new jsPDF();

        const larguraPagina =
            documento.internal.pageSize.getWidth();

        const margem = 15;
        let posicaoY = 15;

        documento.setFont(
            'helvetica',
            'bold'
        );

        documento.setFontSize(20);
        documento.setTextColor(40, 40, 120);

        documento.text(
            'FOAG — Minhas Notas',
            larguraPagina / 2,
            posicaoY,
            {
                align: 'center'
            }
        );

        posicaoY += 9;

        documento.setFont(
            'helvetica',
            'normal'
        );

        documento.setFontSize(10);

        documento.text(
            `Exportado em: ${new Date().toLocaleString('pt-BR')}`,
            larguraPagina / 2,
            posicaoY,
            {
                align: 'center'
            }
        );

        posicaoY += 13;

        documento.setFont(
            'helvetica',
            'bold'
        );

        documento.setFontSize(16);
        documento.setTextColor(0, 0, 0);

        const tituloQuebrado =
            documento.splitTextToSize(
                titulo,
                larguraPagina - margem * 2
            );

        documento.text(
            tituloQuebrado,
            margem,
            posicaoY
        );

        posicaoY +=
            tituloQuebrado.length * 8 + 4;

        documento.setFont(
            'helvetica',
            'normal'
        );

        documento.setFontSize(12);

        const conteudoQuebrado =
            documento.splitTextToSize(
                conteudo,
                larguraPagina - margem * 2
            );

        documento.text(
            conteudoQuebrado,
            margem,
            posicaoY
        );

        documento.save(
            `${nomeArquivoSeguro(titulo)}.pdf`
        );
    }

    function carregarNotas() {
        if (!noteList) {
            return;
        }

        noteList.innerHTML = '';

        const notasOrdenadas = [
            ...agendaData.notas
        ].sort(function (notaA, notaB) {
            return Number(notaB.id) - Number(notaA.id);
        });

        if (notasOrdenadas.length === 0) {
            const semNotas =
                document.createElement('div');

            semNotas.className = 'sem-notas';
            semNotas.textContent =
                'Nenhuma nota salva ainda.';

            noteList.appendChild(semNotas);
            return;
        }

        notasOrdenadas.forEach(function (nota) {
            const elementoNota =
                document.createElement('div');

            elementoNota.className = 'nota-item';

            elementoNota.innerHTML = `
                <span class="nota-titulo">
                    ${escaparHtml(nota.titulo)}
                </span>

                <span class="nota-data">
                    ${escaparHtml(nota.data)}
                </span>

                <div class="nota-conteudo">
                    ${escaparHtml(nota.texto)}
                </div>

                <div class="nota-acoes">
                    <button
                        type="button"
                        class="btn-nota btn-editar">
                        Editar
                    </button>

                    <button
                        type="button"
                        class="btn-nota btn-excluir-nota">
                        Excluir
                    </button>

                    <button
                        type="button"
                        class="btn-nota btn-pequeno">
                        Baixar PDF
                    </button>
                </div>
            `;

            const botaoEditar =
                elementoNota.querySelector('.btn-editar');

            const botaoExcluir =
                elementoNota.querySelector(
                    '.btn-excluir-nota'
                );

            const botaoPdf =
                elementoNota.querySelector('.btn-pequeno');

            botaoEditar?.addEventListener(
                'click',
                function () {
                    editarNota(nota.id);
                }
            );

            botaoExcluir?.addEventListener(
                'click',
                function () {
                    abrirModalExclusao(
                        'Excluir Nota',
                        `Tem certeza que deseja excluir a nota "${nota.titulo}"? Esta ação não pode ser desfeita.`,
                        'nota',
                        {
                            id: nota.id
                        }
                    );
                }
            );

            botaoPdf?.addEventListener(
                'click',
                function () {
                    baixarPdfNota(
                        nota.titulo,
                        nota.texto
                    );
                }
            );

            noteList.appendChild(elementoNota);
        });
    }

    function salvarNotaComTitulo(texto, titulo) {
        const textoTratado =
            String(texto || '').trim();

        const tituloTratado =
            String(titulo || '').trim();

        if (!textoTratado) {
            alert('Escreva o conteúdo da nota.');
            return false;
        }

        if (!tituloTratado) {
            alert('Dê um nome para sua nota.');
            return false;
        }

        const notaComMesmoTitulo =
            agendaData.notas.find(function (nota) {
                return (
                    nota.titulo === tituloTratado &&
                    nota.id !== notaEmEdicaoId
                );
            });

        if (notaComMesmoTitulo) {
            abrirModalExclusao(
                'Sobrescrever Nota',
                `Já existe uma nota com o título "${tituloTratado}". Deseja sobrescrever?`,
                'sobrescrever',
                {
                    titulo: tituloTratado,
                    texto: textoTratado,
                    notaEmEdicaoId: notaEmEdicaoId
                }
            );

            return false;
        }

        const indiceEdicao =
            agendaData.notas.findIndex(function (nota) {
                return nota.id === notaEmEdicaoId;
            });

        if (indiceEdicao >= 0) {
            agendaData.notas[indiceEdicao] = {
                ...agendaData.notas[indiceEdicao],
                titulo: tituloTratado,
                texto: textoTratado,
                data: new Date().toLocaleString('pt-BR')
            };
        } else {
            agendaData.notas.push({
                id: Date.now(),
                titulo: tituloTratado,
                texto: textoTratado,
                data: new Date().toLocaleString('pt-BR')
            });
        }

        salvarAgendaNoServidor();
        carregarNotas();

        if (textareaNotas) {
            textareaNotas.value = '';
        }

        return true;
    }

    function sobrescreverNota(dados) {
        if (!dados) {
            return;
        }

        const idEdicao =
            dados.notaEmEdicaoId || null;

        agendaData.notas =
            agendaData.notas.filter(function (nota) {
                return (
                    nota.titulo !== dados.titulo &&
                    nota.id !== idEdicao
                );
            });

        agendaData.notas.push({
            id: idEdicao || Date.now(),
            titulo: dados.titulo,
            texto: dados.texto,
            data: new Date().toLocaleString('pt-BR')
        });

        salvarAgendaNoServidor();
        carregarNotas();

        if (textareaNotas) {
            textareaNotas.value = '';
        }

        fecharModalNomearNota();
    }

    function excluirNota(id) {
        agendaData.notas =
            agendaData.notas.filter(function (nota) {
                return nota.id !== id;
            });

        salvarAgendaNoServidor();
        carregarNotas();
    }

    function editarNota(id) {
        const nota =
            agendaData.notas.find(function (item) {
                return item.id === id;
            });

        if (!nota) {
            return;
        }

        notaEmEdicaoId = nota.id;
        notaPendente = nota.texto;

        if (textareaNotas) {
            textareaNotas.value = nota.texto;
        }

        abrirModalNomearNota(nota.titulo);
    }

    function executarExclusao() {
        if (!dadosExclusao) {
            fecharModalExclusao();
            return;
        }

        switch (tipoExclusao) {
            case 'nota':
                excluirNota(dadosExclusao.id);
                break;

            case 'tarefa':
                excluirTarefa(dadosExclusao.linha);
                break;

            case 'nao-esquecer':
                excluirNaoEsquecer(
                    dadosExclusao.linha
                );
                break;

            case 'sobrescrever':
                sobrescreverNota(
                    dadosExclusao
                );
                break;
        }

        fecharModalExclusao();
    }

    // =================================================
    // EVENTOS DA AGENDA
    // =================================================

    addTarefaButton?.addEventListener(
        'click',
        function () {
            criarLinhaAgenda(listaTarefas);
            salvarDadosAgenda();
        }
    );

    addNaoEsquecerButton?.addEventListener(
        'click',
        function () {
            criarLinhaAgenda(listaNaoEsquecer);
            salvarDadosAgenda();
        }
    );

    salvarNotaButton?.addEventListener(
        'click',
        function () {
            const texto =
                textareaNotas?.value.trim() || '';

            if (!texto) {
                alert(
                    'Escreva algo na nota antes de salvar.'
                );

                return;
            }

            notaPendente = texto;
            notaEmEdicaoId = null;

            abrirModalNomearNota();
        }
    );

    btnConfirmarNomeNota?.addEventListener(
        'click',
        function () {
            const titulo =
                inputNomeNota?.value.trim() || '';

            const salvou =
                salvarNotaComTitulo(
                    notaPendente,
                    titulo
                );

            if (salvou) {
                fecharModalNomearNota();
            }
        }
    );

    inputNomeNota?.addEventListener(
        'keydown',
        function (evento) {
            if (evento.key === 'Enter') {
                evento.preventDefault();
                btnConfirmarNomeNota?.click();
            }
        }
    );

    btnCancelarNomeNota?.addEventListener(
        'click',
        fecharModalNomearNota
    );

    btnConfirmarExclusao?.addEventListener(
        'click',
        executarExclusao
    );

    btnCancelarExclusao?.addEventListener(
        'click',
        fecharModalExclusao
    );

    modalNomearNota?.addEventListener(
        'click',
        function (evento) {
            if (evento.target === modalNomearNota) {
                fecharModalNomearNota();
            }
        }
    );

    modalExcluir?.addEventListener(
        'click',
        function (evento) {
            if (evento.target === modalExcluir) {
                fecharModalExclusao();
            }
        }
    );

    listaTarefas?.addEventListener(
        'input',
        salvarDadosComAtraso
    );

    listaTarefas?.addEventListener(
        'change',
        salvarDadosAgenda
    );

    listaNaoEsquecer?.addEventListener(
        'input',
        salvarDadosComAtraso
    );

    listaNaoEsquecer?.addEventListener(
        'change',
        salvarDadosAgenda
    );

    // =================================================
    // HORÁRIO
    // =================================================

    const tabelaHorario =
        document.getElementById('scheduleTable');

    const corpoTabelaHorario =
        tabelaHorario?.querySelector('tbody') || null;

    function tornarHorarioEditavel() {
        if (!corpoTabelaHorario) {
            return;
        }

        corpoTabelaHorario
            .querySelectorAll('td')
            .forEach(function (celula) {
                celula.contentEditable = 'true';
            });
    }

    if (
        corpoTabelaHorario &&
        HORARIO_HTML.trim() !== ''
    ) {
        corpoTabelaHorario.innerHTML =
            HORARIO_HTML;

        tornarHorarioEditavel();
    }

    // =================================================
    // MODAL DE SUCESSO DO HORÁRIO
    // =================================================

    const modalSucesso =
        document.getElementById('modal-sucesso');

    const btnFecharModal =
        document.getElementById('fechar-modal');

    function abrirModalSucessoHorario() {
        if (!modalSucesso) {
            alert('Horário salvo com sucesso!');
            return;
        }

        modalSucesso.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function fecharModalSucessoHorario() {
        if (!modalSucesso) {
            return;
        }

        modalSucesso.style.display = 'none';
        document.body.style.overflow = '';
    }

    window.abrirModalSucesso =
        abrirModalSucessoHorario;

    btnFecharModal?.addEventListener(
        'click',
        fecharModalSucessoHorario
    );

    modalSucesso?.addEventListener(
        'click',
        function (evento) {
            if (evento.target === modalSucesso) {
                fecharModalSucessoHorario();
            }
        }
    );

    // =================================================
    // SALVAR HORÁRIO
    // =================================================

    window.salvarEdicoes = async function () {
        if (!corpoTabelaHorario) {
            alert('A tabela de horário não foi encontrada.');
            return;
        }

        try {
            const resposta = await fetch(
                HORARIO_SAVE_URL,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type':
                            'application/json'
                    },
                    body: JSON.stringify({
                        html:
                            corpoTabelaHorario.innerHTML
                    })
                }
            );

            const textoResposta =
                await resposta.text();

            console.log(
                'Resposta do horário:',
                resposta.status,
                textoResposta
            );

            let respostaJson;

            try {
                respostaJson =
                    JSON.parse(textoResposta);
            } catch (erroJson) {
                throw new Error(
                    'O servidor retornou uma resposta inválida.'
                );
            }

            if (
                !resposta.ok ||
                respostaJson.ok !== true
            ) {
                throw new Error(
                    respostaJson.mensagem ||
                    respostaJson.message ||
                    'Não foi possível salvar o horário.'
                );
            }

            abrirModalSucessoHorario();
        } catch (erro) {
            console.error(
                'Erro ao salvar o horário:',
                erro
            );

            alert(
                `Erro ao salvar o horário: ${erro.message}`
            );
        }
    };

    // =================================================
    // ADICIONAR LINHA AO HORÁRIO
    // =================================================

    window.adicionarLinha = function () {
        if (!corpoTabelaHorario) {
            return;
        }

        const novaLinha =
            corpoTabelaHorario.insertRow();

        for (let coluna = 0; coluna < 6; coluna++) {
            const celula =
                novaLinha.insertCell();

            celula.contentEditable = 'true';
        }

        novaLinha.cells[0]?.focus();
    };

    // =================================================
    // REMOVER LINHA DO HORÁRIO
    // =================================================

    window.removerLinha = function () {
        if (!corpoTabelaHorario) {
            return;
        }

        const quantidadeLinhas =
            corpoTabelaHorario.rows.length;

        if (quantidadeLinhas === 0) {
            alert('Não existem linhas para remover.');
            return;
        }

        corpoTabelaHorario.deleteRow(
            quantidadeLinhas - 1
        );
    };

    // =================================================
    // ADICIONAR INTERVALO
    // =================================================

    window.adicionarIntervalo = function () {
        if (!corpoTabelaHorario) {
            return;
        }

        const novaLinha =
            corpoTabelaHorario.insertRow();

        const celula =
            novaLinha.insertCell();

        celula.colSpan = 6;
        celula.contentEditable = 'true';
        celula.textContent = 'Intervalo';

        celula.focus();
    };

    // =================================================
    // SALVAR HORÁRIO COMO PDF
    // =================================================

    window.salvarComoPDF = function () {
        if (!tabelaHorario) {
            alert('A tabela de horário não foi encontrada.');
            return;
        }

        if (
            !window.jspdf ||
            !window.jspdf.jsPDF
        ) {
            alert('A biblioteca jsPDF não foi carregada.');
            return;
        }

        const { jsPDF } = window.jspdf;

        const documento = new jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: 'a4'
        });

        if (typeof documento.autoTable !== 'function') {
            alert(
                'A biblioteca jsPDF AutoTable não foi carregada.'
            );

            return;
        }

        const cabecalhoTabela =
            tabelaHorario.querySelector(
                'thead tr'
            );

        const cabecalhos =
            cabecalhoTabela
                ? Array.from(
                    cabecalhoTabela.cells
                ).map(function (celula) {
                    return celula.textContent.trim();
                })
                : [
                    'Horário',
                    'Segunda-feira',
                    'Terça-feira',
                    'Quarta-feira',
                    'Quinta-feira',
                    'Sexta-feira'
                ];

        const linhasPdf =
            corpoTabelaHorario
                ? Array.from(
                    corpoTabelaHorario.rows
                ).map(function (linha) {
                    const celulas =
                        Array.from(linha.cells);

                    if (
                        celulas.length === 1 &&
                        Number(
                            celulas[0].colSpan
                        ) > 1
                    ) {
                        return [
                            celulas[0].textContent.trim(),
                            '',
                            '',
                            '',
                            '',
                            ''
                        ];
                    }

                    const valores =
                        celulas.map(function (celula) {
                            return celula.textContent.trim();
                        });

                    while (
                        valores.length <
                        cabecalhos.length
                    ) {
                        valores.push('');
                    }

                    return valores;
                })
                : [];

        documento.setFont(
            'helvetica',
            'bold'
        );

        documento.setFontSize(22);
        documento.setTextColor(56, 165, 255);

        documento.text(
            'FOAG — Horário Escolar',
            14,
            15
        );

        documento.setFont(
            'helvetica',
            'normal'
        );

        documento.setFontSize(10);
        documento.setTextColor(60, 60, 60);

        const dataFormatada =
            new Date().toLocaleString(
                'pt-BR',
                {
                    dateStyle: 'full',
                    timeStyle: 'short'
                }
            );

        documento.text(
            `Gerado em: ${dataFormatada}`,
            14,
            22
        );

        documento.autoTable({
            head: [cabecalhos],
            body: linhasPdf,
            startY: 28,
            theme: 'grid',

            headStyles: {
                fillColor: [56, 165, 255],
                textColor: [255, 255, 255],
                fontSize: 10,
                fontStyle: 'bold',
                halign: 'center'
            },

            bodyStyles: {
                fillColor: [255, 255, 255],
                textColor: [30, 41, 59],
                fontSize: 9,
                halign: 'center',
                valign: 'middle'
            },

            alternateRowStyles: {
                fillColor: [241, 245, 249]
            },

            margin: {
                left: 10,
                right: 10
            }
        });

        documento.save(
            'horario_escolar.pdf'
        );
    };

    // =================================================
    // PERFIL E LOGOUT
    // =================================================

    const perfilIcon =
        document.getElementById('icon-perfil');

    const logoutModal =
        document.getElementById('logout-modal');

    const iconSair =
        document.getElementById('icon-sair');

    const confirmarLogout =
        document.getElementById('confirm-logout');

    const cancelarLogout =
        document.getElementById('cancel-logout');

    perfilIcon?.addEventListener(
        'click',
        function () {
            window.location.href =
                '../perfil/perfil.php';
        }
    );

    iconSair?.addEventListener(
        'click',
        function () {
            if (logoutModal) {
                logoutModal.style.display = 'flex';
            }
        }
    );

    confirmarLogout?.addEventListener(
        'click',
        function () {
            window.location.href =
                '../login/index.php';
        }
    );

    cancelarLogout?.addEventListener(
        'click',
        function () {
            if (logoutModal) {
                logoutModal.style.display = 'none';
            }
        }
    );

    logoutModal?.addEventListener(
        'click',
        function (evento) {
            if (evento.target === logoutModal) {
                logoutModal.style.display = 'none';
            }
        }
    );

    // =================================================
    // TECLA ESC
    // =================================================

    document.addEventListener(
        'keydown',
        function (evento) {
            if (evento.key !== 'Escape') {
                return;
            }

            if (
                modalNomearNota?.style.display === 'flex'
            ) {
                fecharModalNomearNota();
            }

            if (
                modalExcluir?.style.display === 'flex'
            ) {
                fecharModalExclusao();
            }

            if (
                modalSucesso?.style.display === 'flex'
            ) {
                fecharModalSucessoHorario();
            }

            if (
                logoutModal?.style.display === 'flex'
            ) {
                logoutModal.style.display = 'none';
            }
        }
    );

    // =================================================
    // CARREGAMENTO INICIAL
    // =================================================

    carregarDadosAgenda();
    carregarNotas();
    tornarHorarioEditavel();

    // Debug
    window._debugSalvarAgenda =
        salvarAgendaNoServidor;

    window._debugAgenda =
        agendaData;

    console.log(
        'Tudo pronto: Agenda + Horário ✅'
    );
});
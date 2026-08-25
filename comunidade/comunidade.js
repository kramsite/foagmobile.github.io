document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // =================================================
    // CONFIGURAÇÕES
    // =================================================

    let chatData = window.CHAT_DATA || { perguntas: [] };
    let todasPerguntas = window.TODAS_PERGUNTAS || [];
    let interacoes = window.INTERACOES || { curtidas: [], salvos: [] };

    const usuarioCodigo = window.USUARIO_CODIGO || '';
    const usuarioNome = window.USUARIO_NOME || 'Usuário';
    const CHAT_SAVE_URL = window.CHAT_SAVE_URL || 'salvar_chat.php';
    const INTERACOES_SAVE_URL =
        window.INTERACOES_SAVE_URL || 'salvar_interacoes.php';

    const palavrasProibidas = window.PALAVRAS_PROIBIDAS || [];

    if (!Array.isArray(chatData.perguntas)) {
        chatData.perguntas = [];
    }

    if (!Array.isArray(todasPerguntas)) {
        todasPerguntas = [];
    }

    if (!Array.isArray(interacoes.curtidas)) {
        interacoes.curtidas = [];
    }

    if (!Array.isArray(interacoes.salvos)) {
        interacoes.salvos = [];
    }

    // =================================================
    // CENSURA
    // =================================================

    function escaparRegex(texto) {
        return texto.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function censurarTexto(texto) {
        if (!texto) return '';

        let textoCensurado = String(texto);

        const palavras = [...palavrasProibidas]
            .filter(Boolean)
            .sort((a, b) => b.length - a.length);

        palavras.forEach(function(palavra) {
            const padrao = new RegExp(
                '\\b' + escaparRegex(palavra) + '\\b',
                'gi'
            );

            textoCensurado = textoCensurado.replace(
                padrao,
                function(match) {
                    return '*'.repeat(match.length);
                }
            );
        });

        return textoCensurado;
    }

    function verificarCensura(texto) {
        if (!texto) {
            return {
                censurado: false,
                palavras: []
            };
        }

        const palavrasEncontradas = [];

        palavrasProibidas.forEach(function(palavra) {
            if (!palavra) return;

            const padrao = new RegExp(
                '\\b' + escaparRegex(palavra) + '\\b',
                'i'
            );

            if (padrao.test(texto)) {
                palavrasEncontradas.push(palavra);
            }
        });

        return {
            censurado: palavrasEncontradas.length > 0,
            palavras: [...new Set(palavrasEncontradas)]
        };
    }

    // =================================================
    // FUNÇÕES AUXILIARES
    // =================================================

    function gerarId() {
        return (
            Date.now() +
            '_' +
            Math.random().toString(36).substring(2, 8)
        );
    }

    function escaparHtml(valor) {
        if (valor === null || valor === undefined) {
            return '';
        }

        return String(valor)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function obterIniciais(nome) {
        if (!nome) {
            return '?';
        }

        const partes = nome
            .trim()
            .split(/\s+/)
            .filter(Boolean);

        if (partes.length === 0) {
            return '?';
        }

        if (partes.length === 1) {
            return partes[0]
                .charAt(0)
                .toUpperCase();
        }

        return (
            partes[0].charAt(0) +
            partes[partes.length - 1].charAt(0)
        ).toUpperCase();
    }

    function formatarData(data) {
        if (!data) {
            return 'Data desconhecida';
        }

        const d = new Date(data);

        if (Number.isNaN(d.getTime())) {
            return 'Data inválida';
        }

        return d.toLocaleString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function isCurtido(id) {
        return interacoes.curtidas.includes(id);
    }

    function isSalvo(id) {
        return interacoes.salvos.includes(id);
    }

    // =================================================
    // SALVAR CHAT
    // =================================================

    async function salvarChat() {
        try {
            const resposta = await fetch(CHAT_SAVE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(chatData)
            });

            if (!resposta.ok) {
                console.error(
                    'Erro HTTP ao salvar chat:',
                    resposta.status
                );

                return false;
            }

            return true;

        } catch (erro) {
            console.error(
                'Erro ao salvar chat:',
                erro
            );

            return false;
        }
    }

    // =================================================
    // SALVAR INTERAÇÕES
    // =================================================

    async function salvarInteracoes() {
        try {
            const resposta = await fetch(
                INTERACOES_SAVE_URL,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(interacoes)
                }
            );

            if (!resposta.ok) {
                console.error(
                    'Erro HTTP ao salvar interações:',
                    resposta.status
                );

                return false;
            }

            return true;

        } catch (erro) {
            console.error(
                'Erro ao salvar interações:',
                erro
            );

            return false;
        }
    }

    // =================================================
    // RENDERIZAR RESPOSTAS
    // =================================================

    function renderizarRespostas(respostas) {
        if (
            !Array.isArray(respostas) ||
            respostas.length === 0
        ) {
            return `
                <div class="sem-respostas">
                    Nenhuma resposta ainda.
                </div>
            `;
        }

        let html = '';

        respostas.forEach(function(resposta) {
            const texto =
                resposta.texto ||
                resposta.texto_original ||
                '';

            html += `
                <div class="resposta-item">

                    <div class="resposta-header">

                        <div class="avatar-pequeno">
                            ${escaparHtml(
                                obterIniciais(
                                    resposta.autor || '?'
                                )
                            )}
                        </div>

                        <span class="nome">
                            ${escaparHtml(
                                resposta.autor ||
                                'Anônimo'
                            )}
                        </span>

                        <span class="data">
                            ${formatarData(
                                resposta.data
                            )}
                        </span>

                    </div>

                    <div class="resposta-texto">
                        ${escaparHtml(
                            censurarTexto(texto)
                        )}
                    </div>

                </div>
            `;
        });

        return html;
    }

    // =================================================
    // RENDERIZAR MINHAS PERGUNTAS
    // =================================================

    function renderizarMinhasPerguntas() {
        const container =
            document.getElementById(
                'minhas-perguntas-lista'
            );

        if (!container) {
            return;
        }

        const perguntas =
            Array.isArray(chatData.perguntas)
                ? chatData.perguntas
                : [];

        if (perguntas.length === 0) {
            container.innerHTML = `
                <div class="sem-resultados">

                    <i class="fa-regular fa-comment-dots"></i>

                    <h3>
                        Nenhuma pergunta sua ainda
                    </h3>

                    <p>
                        Seja o primeiro a fazer uma
                        pergunta para a comunidade!
                    </p>

                </div>
            `;

            return;
        }

        const perguntasOrdenadas =
            [...perguntas].sort(
                function(a, b) {
                    return (
                        new Date(b.data) -
                        new Date(a.data)
                    );
                }
            );

        let html = '';

        perguntasOrdenadas.forEach(
            function(pergunta) {
                const respostas =
                    Array.isArray(pergunta.respostas)
                        ? pergunta.respostas
                        : [];

                const totalRespostas =
                    respostas.length;

                const id =
                    pergunta.id || '';

                const texto =
                    pergunta.texto ||
                    pergunta.texto_original ||
                    '';

                const textoCensurado =
                    censurarTexto(texto);

                html += `
                    <div
                        class="pergunta-item"
                        data-id="${escaparHtml(id)}"
                    >

                        <div class="pergunta-topo">

                            <div class="pergunta-autor">

                                <div class="avatar">
                                    ${escaparHtml(
                                        obterIniciais(
                                            usuarioNome
                                        )
                                    )}
                                </div>

                                <div>

                                    <span class="nome">
                                        ${escaparHtml(
                                            usuarioNome
                                        )}
                                    </span>

                                    <span class="data">
                                        ${formatarData(
                                            pergunta.data
                                        )}
                                    </span>

                                    <span class="usuario-tag">
                                        <i
                                            class="fa-regular fa-user"
                                        ></i>
                                        Você
                                    </span>

                                </div>

                            </div>

                            <span class="pergunta-materia">
                                ${escaparHtml(
                                    pergunta.materia ||
                                    'Geral'
                                )}
                            </span>

                        </div>

                        <div class="pergunta-texto">
                            ${escaparHtml(
                                textoCensurado
                            )}
                        </div>

                        <div class="pergunta-rodape">

                            <div class="pergunta-acoes">

                                <button
                                    type="button"
                                    class="btn-ver-respostas"
                                    data-id="${escaparHtml(id)}"
                                >

                                    <i
                                        class="fa-regular fa-comment"
                                    ></i>

                                    ${totalRespostas}

                                    resposta${
                                        totalRespostas !== 1
                                            ? 's'
                                            : ''
                                    }

                                </button>

                                <button
                                    type="button"
                                    class="btn-excluir"
                                    data-id="${escaparHtml(id)}"
                                >

                                    <i
                                        class="fa-regular fa-trash-can"
                                    ></i>

                                    Excluir

                                </button>

                            </div>

                        </div>

                        <div
                            class="respostas-container"
                            id="minhas-respostas-${escaparHtml(id)}"
                        >
                            ${renderizarRespostas(
                                respostas
                            )}
                        </div>

                    </div>
                `;
            }
        );

        container.innerHTML = html;

        adicionarEventosMinhasPerguntas();
    }

    // =================================================
    // RENDERIZAR EXPLORAR
    // =================================================

    function renderizarExplorar() {
        const container =
            document.getElementById(
                'explorar-perguntas-lista'
            );

        if (!container) {
            return;
        }

        const perguntas =
            Array.isArray(todasPerguntas)
                ? todasPerguntas
                : [];

        if (perguntas.length === 0) {
            container.innerHTML = `
                <div class="sem-resultados">

                    <i class="fa-regular fa-face-frown"></i>

                    <h3>
                        Nenhuma pergunta encontrada
                    </h3>

                    <p>
                        Tente ajustar os filtros de busca.
                    </p>

                </div>
            `;

            return;
        }

        let html = '';

        perguntas.forEach(
            function(pergunta) {
                const respostas =
                    Array.isArray(pergunta.respostas)
                        ? pergunta.respostas
                        : [];

                const totalRespostas =
                    respostas.length;

                const id =
                    pergunta.id || '';

                const ehDoUsuario =
                    String(
                        pergunta.usuario_id || ''
                    ) ===
                    String(usuarioCodigo);

                const curtido =
                    isCurtido(id);

                const salvo =
                    isSalvo(id);

                const texto =
                    pergunta.texto ||
                    pergunta.texto_original ||
                    '';

                html += `
                    <div
                        class="pergunta-item"
                        data-id="${escaparHtml(id)}"
                    >

                        <div class="pergunta-topo">

                            <div class="pergunta-autor">

                                <div class="avatar">

                                    ${escaparHtml(
                                        obterIniciais(
                                            pergunta.autor ||
                                            '?'
                                        )
                                    )}

                                </div>

                                <div>

                                    <span class="nome">
                                        ${escaparHtml(
                                            pergunta.autor ||
                                            'Anônimo'
                                        )}
                                    </span>

                                    <span class="data">
                                        ${formatarData(
                                            pergunta.data
                                        )}
                                    </span>

                                    ${
                                        ehDoUsuario
                                            ? `
                                                <span class="usuario-tag">
                                                    <i
                                                        class="fa-regular fa-user"
                                                    ></i>
                                                    Você
                                                </span>
                                            `
                                            : ''
                                    }

                                </div>

                            </div>

                            <span class="pergunta-materia">

                                ${escaparHtml(
                                    pergunta.materia ||
                                    'Geral'
                                )}

                            </span>

                        </div>

                        <div class="pergunta-texto">

                            ${escaparHtml(
                                censurarTexto(texto)
                            )}

                        </div>

                        <div class="pergunta-rodape">

                            <div class="pergunta-acoes">

                                <button
                                    type="button"
                                    class="btn-curtir ${
                                        curtido
                                            ? 'curtido'
                                            : ''
                                    }"
                                    data-id="${escaparHtml(id)}"
                                >

                                    <i
                                        class="${
                                            curtido
                                                ? 'fa-solid'
                                                : 'fa-regular'
                                        } fa-heart"
                                    ></i>

                                    <span>
                                        ${
                                            curtido
                                                ? 'Curtido'
                                                : 'Curtir'
                                        }
                                    </span>

                                </button>

                                <button
                                    type="button"
                                    class="btn-salvar ${
                                        salvo
                                            ? 'salvo'
                                            : ''
                                    }"
                                    data-id="${escaparHtml(id)}"
                                >

                                    <i
                                        class="${
                                            salvo
                                                ? 'fa-solid'
                                                : 'fa-regular'
                                        } fa-bookmark"
                                    ></i>

                                    <span>
                                        ${
                                            salvo
                                                ? 'Salvo'
                                                : 'Salvar'
                                        }
                                    </span>

                                </button>

                                <button
                                    type="button"
                                    class="btn-ver-respostas"
                                    data-id="${escaparHtml(id)}"
                                >

                                    <i
                                        class="fa-regular fa-comment"
                                    ></i>

                                    ${totalRespostas}

                                    resposta${
                                        totalRespostas !== 1
                                            ? 's'
                                            : ''
                                    }

                                </button>

                            </div>

                        </div>

                        <div
                            class="respostas-container"
                            id="explorar-respostas-${escaparHtml(id)}"
                        >

                            ${renderizarRespostas(
                                respostas
                            )}

                        </div>

                    </div>
                `;
            }
        );

        container.innerHTML = html;

        adicionarEventosExplorar();
    }

    // =================================================
    // EVENTOS DAS MINHAS PERGUNTAS
    // =================================================

    function adicionarEventosMinhasPerguntas() {
        document
            .querySelectorAll(
                '#aba-minhas .btn-ver-respostas'
            )
            .forEach(function(botao) {

                botao.addEventListener(
                    'click',
                    function() {
                        const id =
                            this.dataset.id;

                        const respostas =
                            document.getElementById(
                                `minhas-respostas-${id}`
                            );

                        if (respostas) {
                            respostas.classList.toggle(
                                'visivel'
                            );
                        }
                    }
                );

            });

        document
            .querySelectorAll(
                '#aba-minhas .btn-excluir'
            )
            .forEach(function(botao) {

                botao.addEventListener(
                    'click',
                    function() {
                        abrirModalExclusao(
                            this.dataset.id
                        );
                    }
                );

            });
    }

    // =================================================
    // EVENTOS DO EXPLORAR
    // =================================================

    function adicionarEventosExplorar() {
        document
            .querySelectorAll(
                '#aba-explorar .btn-ver-respostas'
            )
            .forEach(function(botao) {

                botao.addEventListener(
                    'click',
                    function() {
                        const id =
                            this.dataset.id;

                        const respostas =
                            document.getElementById(
                                `explorar-respostas-${id}`
                            );

                        if (respostas) {
                            respostas.classList.toggle(
                                'visivel'
                            );
                        }
                    }
                );

            });

        document
            .querySelectorAll(
                '#aba-explorar .btn-curtir'
            )
            .forEach(function(botao) {

                botao.addEventListener(
                    'click',
                    function() {
                        toggleCurtir(
                            this.dataset.id
                        );
                    }
                );

            });

        document
            .querySelectorAll(
                '#aba-explorar .btn-salvar'
            )
            .forEach(function(botao) {

                botao.addEventListener(
                    'click',
                    function() {
                        toggleSalvar(
                            this.dataset.id
                        );
                    }
                );

            });
    }

    // =================================================
    // CURTIR
    // =================================================

    async function toggleCurtir(id) {
        const index =
            interacoes.curtidas.indexOf(id);

        if (index >= 0) {
            interacoes.curtidas.splice(
                index,
                1
            );
        } else {
            interacoes.curtidas.push(id);
        }

        renderizarExplorar();

        const salvou =
            await salvarInteracoes();

        if (!salvou) {
            console.error(
                'Não foi possível salvar a curtida.'
            );
        }
    }

    // =================================================
    // SALVAR PERGUNTA
    // =================================================

    async function toggleSalvar(id) {
        const index =
            interacoes.salvos.indexOf(id);

        if (index >= 0) {
            interacoes.salvos.splice(
                index,
                1
            );
        } else {
            interacoes.salvos.push(id);
        }

        renderizarExplorar();

        const salvou =
            await salvarInteracoes();

        if (!salvou) {
            console.error(
                'Não foi possível salvar a pergunta.'
            );
        }
    }

    // =================================================
    // POSTAR PERGUNTA
    // =================================================

    const btnPostar =
        document.getElementById(
            'btn-postar-pergunta'
        );

    const perguntaTexto =
        document.getElementById(
            'pergunta-texto'
        );

    const perguntaMateria =
        document.getElementById(
            'pergunta-materia'
        );

    const censurePreview =
        document.getElementById(
            'censure-preview'
        );

    const censurePreviewText =
        document.getElementById(
            'censure-preview-text'
        );

    // =================================================
    // VERIFICAR CENSURA ENQUANTO DIGITA
    // =================================================

    perguntaTexto?.addEventListener(
        'input',
        function() {
            const resultado =
                verificarCensura(
                    this.value
                );

            if (
                resultado.censurado &&
                censurePreview &&
                censurePreviewText
            ) {
                censurePreview.style.display =
                    'block';

                censurePreviewText.textContent =
                    'Palavra ofensiva detectada. Ela será censurada automaticamente ao publicar.';
            } else if (censurePreview) {
                censurePreview.style.display =
                    'none';
            }
        }
    );

    // =================================================
    // PUBLICAR
    // =================================================

    btnPostar?.addEventListener(
        'click',
        async function() {
            const texto =
                perguntaTexto
                    ?.value
                    .trim() || '';

            if (!texto) {
                alert(
                    'Escreva sua pergunta antes de publicar.'
                );

                perguntaTexto?.focus();

                return;
            }

            if (texto.length < 5) {
                alert(
                    'Sua pergunta é muito curta. Escreva mais detalhes.'
                );

                perguntaTexto?.focus();

                return;
            }

            const materia =
                perguntaMateria?.value ||
                'Geral';

            const novaPergunta = {
                id: gerarId(),

                autor: usuarioNome,

                texto: censurarTexto(texto),

                materia: materia,

                data: new Date().toISOString(),

                respostas: []
            };

            chatData.perguntas.push(
                novaPergunta
            );

            this.disabled = true;

            this.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin"></i> Publicando...';

            const salvou =
                await salvarChat();

            this.disabled = false;

            this.innerHTML =
                '<i class="fa-regular fa-paper-plane"></i> Publicar pergunta';

            if (!salvou) {
                chatData.perguntas =
                    chatData.perguntas.filter(
                        function(pergunta) {
                            return (
                                pergunta.id !==
                                novaPergunta.id
                            );
                        }
                    );

                alert(
                    'Não foi possível publicar a pergunta.'
                );

                return;
            }

            if (perguntaTexto) {
                perguntaTexto.value = '';
            }

            if (censurePreview) {
                censurePreview.style.display =
                    'none';
            }

            renderizarMinhasPerguntas();

            const badge =
                document.querySelector(
                    '.aba-btn[data-aba="minhas"] .badge'
                );

            if (badge) {
                badge.textContent =
                    chatData.perguntas.length;
            }

            location.reload();
        }
    );

    // =================================================
    // CTRL + ENTER PARA PUBLICAR
    // =================================================

    perguntaTexto?.addEventListener(
        'keydown',
        function(evento) {
            if (
                evento.key === 'Enter' &&
                evento.ctrlKey
            ) {
                evento.preventDefault();

                btnPostar?.click();
            }
        }
    );

    // =================================================
    // MODAL DE EXCLUSÃO
    // =================================================

    const modalExcluir =
        document.getElementById(
            'modal-excluir'
        );

    const btnConfirmarExclusao =
        document.getElementById(
            'confirmar-exclusao'
        );

    const btnCancelarExclusao =
        document.getElementById(
            'cancelar-exclusao'
        );

    let idParaExcluir = null;

    function abrirModalExclusao(id) {
        idParaExcluir = id;

        if (modalExcluir) {
            modalExcluir.style.display =
                'flex';
        }
    }

    function fecharModalExclusao() {
        if (modalExcluir) {
            modalExcluir.style.display =
                'none';
        }

        idParaExcluir = null;
    }

    btnCancelarExclusao?.addEventListener(
        'click',
        fecharModalExclusao
    );

    modalExcluir?.addEventListener(
        'click',
        function(evento) {
            if (
                evento.target ===
                modalExcluir
            ) {
                fecharModalExclusao();
            }
        }
    );

    // =================================================
    // CONFIRMAR EXCLUSÃO
    // =================================================

    btnConfirmarExclusao?.addEventListener(
        'click',
        async function() {
            if (!idParaExcluir) {
                return;
            }

            const perguntasAnteriores =
                [...chatData.perguntas];

            chatData.perguntas =
                chatData.perguntas.filter(
                    function(pergunta) {
                        return (
                            pergunta.id !==
                            idParaExcluir
                        );
                    }
                );

            const salvou =
                await salvarChat();

            if (!salvou) {
                chatData.perguntas =
                    perguntasAnteriores;

                alert(
                    'Não foi possível excluir a pergunta.'
                );

                return;
            }

            fecharModalExclusao();

            renderizarMinhasPerguntas();

            const badge =
                document.querySelector(
                    '.aba-btn[data-aba="minhas"] .badge'
                );

            if (badge) {
                badge.textContent =
                    chatData.perguntas.length;
            }

            location.reload();
        }
    );

    // =================================================
    // ABAS
    // =================================================

    document
        .querySelectorAll('.aba-btn')
        .forEach(function(botao) {

            botao.addEventListener(
                'click',
                function() {
                    const aba =
                        this.dataset.aba;

                    document
                        .querySelectorAll(
                            '.aba-btn'
                        )
                        .forEach(
                            function(item) {
                                item.classList.remove(
                                    'ativo'
                                );
                            }
                        );

                    document
                        .querySelectorAll(
                            '.aba-conteudo'
                        )
                        .forEach(
                            function(item) {
                                item.classList.remove(
                                    'ativo'
                                );
                            }
                        );

                    this.classList.add(
                        'ativo'
                    );

                    const conteudo =
                        document.getElementById(
                            `aba-${aba}`
                        );

                    if (conteudo) {
                        conteudo.classList.add(
                            'ativo'
                        );
                    }

                    const url =
                        new URL(
                            window.location.href
                        );

                    url.searchParams.set(
                        'aba',
                        aba
                    );

                    window.history.replaceState(
                        {},
                        '',
                        url
                    );
                }
            );

        });

    // =================================================
    // FILTROS
    // =================================================

    const buscaInput =
        document.getElementById(
            'busca-input'
        );

    const btnBuscar =
        document.getElementById(
            'btn-buscar'
        );

    const filtroMateriaSelect =
        document.getElementById(
            'filtro-materia'
        );

    const btnLimparFiltros =
        document.getElementById(
            'btn-limpar-filtros'
        );

    function aplicarFiltros() {
        const busca =
            buscaInput?.value.trim() ||
            '';

        const materia =
            filtroMateriaSelect?.value ||
            'todas';

        const url =
            new URL(
                window.location.href
            );

        url.searchParams.set(
            'aba',
            'explorar'
        );

        if (busca) {
            url.searchParams.set(
                'busca',
                busca
            );
        } else {
            url.searchParams.delete(
                'busca'
            );
        }

        if (materia !== 'todas') {
            url.searchParams.set(
                'materia',
                materia
            );
        } else {
            url.searchParams.delete(
                'materia'
            );
        }

        window.location.href =
            url.toString();
    }

    btnBuscar?.addEventListener(
        'click',
        aplicarFiltros
    );

    buscaInput?.addEventListener(
        'keydown',
        function(evento) {
            if (evento.key === 'Enter') {
                evento.preventDefault();

                aplicarFiltros();
            }
        }
    );

    filtroMateriaSelect?.addEventListener(
        'change',
        aplicarFiltros
    );

    btnLimparFiltros?.addEventListener(
        'click',
        function() {
            const url =
                new URL(
                    window.location.href
                );

            url.searchParams.delete(
                'busca'
            );

            url.searchParams.delete(
                'materia'
            );

            url.searchParams.set(
                'aba',
                'explorar'
            );

            window.location.href =
                url.toString();
        }
    );

    // =================================================
    // CONFIGURAÇÕES
    // =================================================

    document
        .getElementById(
            'icon-configuracoes'
        )
        ?.addEventListener(
            'click',
            function() {
                window.location.href =
                    '../configuracoes/configuracoes.php';
            }
        );

    // =================================================
    // PERFIL
    // =================================================

    document
        .getElementById(
            'icon-perfil'
        )
        ?.addEventListener(
            'click',
            function() {
                window.location.href =
                    '../perfil/perfil.php';
            }
        );

    // =================================================
    // LOGOUT
    // =================================================

    const iconSair =
        document.getElementById(
            'icon-sair'
        );

    const logoutModal =
        document.getElementById(
            'logout-modal'
        );

    const confirmLogout =
        document.getElementById(
            'confirm-logout'
        );

    const cancelLogout =
        document.getElementById(
            'cancel-logout'
        );

    iconSair?.addEventListener(
        'click',
        function() {
            if (logoutModal) {
                logoutModal.style.display =
                    'flex';
            }
        }
    );

    cancelLogout?.addEventListener(
        'click',
        function() {
            if (logoutModal) {
                logoutModal.style.display =
                    'none';
            }
        }
    );

    logoutModal?.addEventListener(
        'click',
        function(evento) {
            if (
                evento.target ===
                logoutModal
            ) {
                logoutModal.style.display =
                    'none';
            }
        }
    );

    confirmLogout?.addEventListener(
        'click',
        function() {
            window.location.href =
                '../login/logout.php';
        }
    );

    // =================================================
    // ESC FECHA OS MODAIS
    // =================================================

    document.addEventListener(
        'keydown',
        function(evento) {
            if (evento.key !== 'Escape') {
                return;
            }

            if (logoutModal) {
                logoutModal.style.display =
                    'none';
            }

            fecharModalExclusao();
        }
    );

    // =================================================
    // INICIALIZAR
    // =================================================

    renderizarMinhasPerguntas();
    renderizarExplorar();

    console.log(
        'Comunidade FOAG carregada ✅'
    );

    console.log(
        'Palavras proibidas:',
        palavrasProibidas.length
    );

    console.log(
        'Minhas perguntas:',
        chatData.perguntas.length
    );

    console.log(
        'Perguntas da comunidade:',
        todasPerguntas.length
    );
});
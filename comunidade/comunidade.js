// comunidade.js - Versão corrigida com a mesma lógica da agenda

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
    const INTERACOES_SAVE_URL = window.INTERACOES_SAVE_URL || 'salvar_interacoes.php';

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
    // FUNÇÃO DE DEBOUNCE (igual da agenda)
    // =================================================

    function debounce(funcao, tempo = 500) {
        let temporizador;
        return function(...argumentos) {
            clearTimeout(temporizador);
            temporizador = setTimeout(function() {
                funcao.apply(null, argumentos);
            }, tempo);
        };
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
        const palavras = [...palavrasProibidas].filter(Boolean).sort((a, b) => b.length - a.length);

        palavras.forEach(function(palavra) {
            const padrao = new RegExp('\\b' + escaparRegex(palavra) + '\\b', 'gi');
            textoCensurado = textoCensurado.replace(padrao, function(match) {
                return '*'.repeat(match.length);
            });
        });

        return textoCensurado;
    }

    function verificarCensura(texto) {
        if (!texto) {
            return { censurado: false, palavras: [] };
        }

        const palavrasEncontradas = [];
        palavrasProibidas.forEach(function(palavra) {
            if (!palavra) return;
            const padrao = new RegExp('\\b' + escaparRegex(palavra) + '\\b', 'i');
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
        return Date.now() + '_' + Math.random().toString(36).substring(2, 8);
    }

    function escaparHtml(valor) {
        if (valor === null || valor === undefined) return '';
        return String(valor)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function obterIniciais(nome) {
        if (!nome) return '?';
        const partes = nome.trim().split(/\s+/).filter(Boolean);
        if (partes.length === 0) return '?';
        if (partes.length === 1) return partes[0].charAt(0).toUpperCase();
        return (partes[0].charAt(0) + partes[partes.length - 1].charAt(0)).toUpperCase();
    }

    function formatarData(data) {
        if (!data) return 'Data desconhecida';
        const d = new Date(data);
        if (Number.isNaN(d.getTime())) return 'Data inválida';
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
    // SALVAR CHAT (com fila igual à agenda)
    // =================================================

    let filaSalvamentoChat = Promise.resolve();

    async function enviarChatParaServidor(payload) {
        const resposta = await fetch(CHAT_SAVE_URL, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: payload
        });

        const textoResposta = await resposta.text();
        let retorno = null;
        try {
            retorno = JSON.parse(textoResposta);
        } catch (_) {
            retorno = null;
        }

        if (!resposta.ok) {
            throw new Error(retorno?.mensagem || retorno?.erro || textoResposta || `Erro HTTP ${resposta.status}`);
        }

        if (retorno && retorno.ok === false) {
            throw new Error(retorno.mensagem || retorno.erro || 'Não foi possível salvar o chat.');
        }

        return true;
    }

    function salvarChatNoServidor() {
        const payload = JSON.stringify(chatData);

        filaSalvamentoChat = filaSalvamentoChat.then(
            function() {
                return enviarChatParaServidor(payload);
            },
            function() {
                return enviarChatParaServidor(payload);
            }
        );

        filaSalvamentoChat = filaSalvamentoChat
            .then(function() {
                return true;
            })
            .catch(function(erro) {
                console.error('Erro ao salvar o chat:', erro);
                return false;
            });

        return filaSalvamentoChat;
    }

    const salvarChatDebounce = debounce(salvarChatNoServidor, 500);

    // =================================================
    // SALVAR INTERAÇÕES
    // =================================================

    async function salvarInteracoes() {
        try {
            const resposta = await fetch(INTERACOES_SAVE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(interacoes)
            });

            if (!resposta.ok) {
                console.error('Erro HTTP ao salvar interações:', resposta.status);
                return false;
            }

            return true;
        } catch (erro) {
            console.error('Erro ao salvar interações:', erro);
            return false;
        }
    }

    // =================================================
    // RENDERIZAR RESPOSTAS
    // =================================================

    function renderizarRespostas(respostas) {
        if (!Array.isArray(respostas) || respostas.length === 0) {
            return `<div class="sem-respostas">Nenhuma resposta ainda.</div>`;
        }

        let html = '';
        respostas.forEach(function(resposta) {
            const texto = resposta.texto || resposta.texto_original || '';
            const ehDoUsuario = resposta.autor === usuarioNome;

            html += `
                <div class="resposta-item" data-id="${escaparHtml(resposta.id || '')}">
                    <div class="resposta-header">
                        <div class="avatar-pequeno">${escaparHtml(obterIniciais(resposta.autor || '?'))}</div>
                        <span class="nome">
                            ${escaparHtml(resposta.autor || 'Anônimo')}
                            ${ehDoUsuario ? '<span class="usuario-tag-resposta">Você</span>' : ''}
                        </span>
                        <span class="data">${formatarData(resposta.data)}</span>
                        ${ehDoUsuario ? `
                            <button class="btn-excluir-resposta" data-id="${escaparHtml(resposta.id || '')}" title="Excluir resposta">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        ` : ''}
                    </div>
                    <div class="resposta-texto">${escaparHtml(censurarTexto(texto))}</div>
                </div>
            `;
        });

        return html;
    }

    // =================================================
    // RENDERIZAR FORMULÁRIO DE RESPOSTA
    // =================================================

    function renderizarFormularioResposta(perguntaId) {
        return `
            <div class="resposta-form" id="resposta-form-${escaparHtml(perguntaId)}" style="display:none;">
                <div class="resposta-form-wrapper">
                    <textarea 
                        placeholder="Escreva sua resposta..." 
                        rows="3"
                        id="resposta-texto-${escaparHtml(perguntaId)}"
                        data-pergunta-id="${escaparHtml(perguntaId)}"
                    ></textarea>
                    <div class="resposta-form-actions">
                        <div class="resposta-censure-preview" id="resposta-censure-${escaparHtml(perguntaId)}" style="display:none;">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Palavra ofensiva detectada. Ela será censurada automaticamente.</span>
                        </div>
                        <div class="resposta-buttons">
                            <button class="btn-cancelar-resposta" data-id="${escaparHtml(perguntaId)}">
                                Cancelar
                            </button>
                            <button class="btn-enviar-resposta" data-id="${escaparHtml(perguntaId)}">
                                <i class="fa-regular fa-paper-plane"></i> Responder
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // =================================================
    // RENDERIZAR MINHAS PERGUNTAS
    // =================================================

    function renderizarMinhasPerguntas() {
        const container = document.getElementById('minhas-perguntas-lista');
        if (!container) return;

        const perguntas = Array.isArray(chatData.perguntas) ? chatData.perguntas : [];

        if (perguntas.length === 0) {
            container.innerHTML = `
                <div class="sem-resultados">
                    <i class="fa-regular fa-comment-dots"></i>
                    <h3>Nenhuma pergunta sua ainda</h3>
                    <p>Seja o primeiro a fazer uma pergunta para a comunidade!</p>
                </div>
            `;
            return;
        }

        const perguntasOrdenadas = [...perguntas].sort(function(a, b) {
            return new Date(b.data) - new Date(a.data);
        });

        let html = '';
        perguntasOrdenadas.forEach(function(pergunta) {
            const respostas = Array.isArray(pergunta.respostas) ? pergunta.respostas : [];
            const totalRespostas = respostas.length;
            const id = pergunta.id || '';
            const texto = pergunta.texto || pergunta.texto_original || '';
            const textoCensurado = censurarTexto(texto);

            html += `
                <div class="pergunta-item" data-id="${escaparHtml(id)}">
                    <div class="pergunta-topo">
                        <div class="pergunta-autor">
                            <div class="avatar">${escaparHtml(obterIniciais(usuarioNome))}</div>
                            <div>
                                <span class="nome">${escaparHtml(usuarioNome)}</span>
                                <span class="data">${formatarData(pergunta.data)}</span>
                                <span class="usuario-tag"><i class="fa-regular fa-user"></i> Você</span>
                            </div>
                        </div>
                        <span class="pergunta-materia">${escaparHtml(pergunta.materia || 'Geral')}</span>
                    </div>
                    <div class="pergunta-texto">${escaparHtml(textoCensurado)}</div>
                    <div class="pergunta-rodape">
                        <div class="pergunta-acoes">
                            <button type="button" class="btn-ver-respostas" data-id="${escaparHtml(id)}">
                                <i class="fa-regular fa-comment"></i>
                                ${totalRespostas} resposta${totalRespostas !== 1 ? 's' : ''}
                            </button>
                            <button type="button" class="btn-responder" data-id="${escaparHtml(id)}">
                                <i class="fa-regular fa-pen-to-square"></i> Responder
                            </button>
                            <button type="button" class="btn-excluir" data-id="${escaparHtml(id)}">
                                <i class="fa-regular fa-trash-can"></i> Excluir
                            </button>
                        </div>
                    </div>
                    <div class="respostas-container" id="minhas-respostas-${escaparHtml(id)}">
                        ${renderizarRespostas(respostas)}
                        ${renderizarFormularioResposta(id)}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        adicionarEventosMinhasPerguntas();
        adicionarEventosRespostas();
    }

    // =================================================
    // RENDERIZAR EXPLORAR
    // =================================================

    function renderizarExplorar() {
        const container = document.getElementById('explorar-perguntas-lista');
        if (!container) return;

        const perguntas = Array.isArray(todasPerguntas) ? todasPerguntas : [];

        if (perguntas.length === 0) {
            container.innerHTML = `
                <div class="sem-resultados">
                    <i class="fa-regular fa-face-frown"></i>
                    <h3>Nenhuma pergunta encontrada</h3>
                    <p>Tente ajustar os filtros de busca.</p>
                </div>
            `;
            return;
        }

        let html = '';
        perguntas.forEach(function(pergunta) {
            const respostas = Array.isArray(pergunta.respostas) ? pergunta.respostas : [];
            const totalRespostas = respostas.length;
            const id = pergunta.id || '';
            const ehDoUsuario = String(pergunta.usuario_id || '') === String(usuarioCodigo);
            const curtido = isCurtido(id);
            const salvo = isSalvo(id);
            const texto = pergunta.texto || pergunta.texto_original || '';

            html += `
                <div class="pergunta-item" data-id="${escaparHtml(id)}">
                    <div class="pergunta-topo">
                        <div class="pergunta-autor">
                            <div class="avatar">${escaparHtml(obterIniciais(pergunta.autor || '?'))}</div>
                            <div>
                                <span class="nome">${escaparHtml(pergunta.autor || 'Anônimo')}</span>
                                <span class="data">${formatarData(pergunta.data)}</span>
                                ${ehDoUsuario ? `<span class="usuario-tag"><i class="fa-regular fa-user"></i> Você</span>` : ''}
                            </div>
                        </div>
                        <span class="pergunta-materia">${escaparHtml(pergunta.materia || 'Geral')}</span>
                    </div>
                    <div class="pergunta-texto">${escaparHtml(censurarTexto(texto))}</div>
                    <div class="pergunta-rodape">
                        <div class="pergunta-acoes">
                            <button type="button" class="btn-curtir ${curtido ? 'curtido' : ''}" data-id="${escaparHtml(id)}">
                                <i class="${curtido ? 'fa-solid' : 'fa-regular'} fa-heart"></i>
                                <span>${curtido ? 'Curtido' : 'Curtir'}</span>
                            </button>
                            <button type="button" class="btn-salvar ${salvo ? 'salvo' : ''}" data-id="${escaparHtml(id)}">
                                <i class="${salvo ? 'fa-solid' : 'fa-regular'} fa-bookmark"></i>
                                <span>${salvo ? 'Salvo' : 'Salvar'}</span>
                            </button>
                            <button type="button" class="btn-ver-respostas" data-id="${escaparHtml(id)}">
                                <i class="fa-regular fa-comment"></i>
                                ${totalRespostas} resposta${totalRespostas !== 1 ? 's' : ''}
                            </button>
                            <button type="button" class="btn-responder" data-id="${escaparHtml(id)}">
                                <i class="fa-regular fa-pen-to-square"></i> Responder
                            </button>
                        </div>
                    </div>
                    <div class="respostas-container" id="explorar-respostas-${escaparHtml(id)}">
                        ${renderizarRespostas(respostas)}
                        ${renderizarFormularioResposta(id)}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        adicionarEventosExplorar();
        adicionarEventosRespostas();
    }

    // =================================================
    // FUNÇÃO PARA ENVIAR RESPOSTA (separada igual à agenda)
    // =================================================

    async function enviarResposta(perguntaId, texto) {
        console.log('Enviando resposta para pergunta:', perguntaId);
        console.log('Texto:', texto);

        if (!texto || texto.trim() === '') {
            alert('Escreva sua resposta antes de enviar.');
            return false;
        }

        const textoCensurado = censurarTexto(texto);

        // Procura a pergunta em ambos os arrays
        let pergunta = null;
        let encontradaEm = null;

        // Procura em chatData.perguntas
        for (let i = 0; i < chatData.perguntas.length; i++) {
            if (chatData.perguntas[i].id === perguntaId) {
                pergunta = chatData.perguntas[i];
                encontradaEm = 'chatData';
                break;
            }
        }

        // Se não encontrou, procura em todasPerguntas
        if (!pergunta) {
            for (let i = 0; i < todasPerguntas.length; i++) {
                if (todasPerguntas[i].id === perguntaId) {
                    pergunta = todasPerguntas[i];
                    encontradaEm = 'todasPerguntas';
                    break;
                }
            }
        }

        if (!pergunta) {
            alert('Não foi possível encontrar a pergunta.');
            console.error('Pergunta não encontrada com ID:', perguntaId);
            return false;
        }

        console.log('Pergunta encontrada em:', encontradaEm);

        if (!pergunta.respostas) {
            pergunta.respostas = [];
        }

        const novaResposta = {
            id: gerarId(),
            autor: usuarioNome,
            texto: textoCensurado,
            texto_original: texto,
            data: new Date().toISOString()
        };

        pergunta.respostas.push(novaResposta);

        // Atualiza em chatData
        for (let i = 0; i < chatData.perguntas.length; i++) {
            if (chatData.perguntas[i].id === perguntaId) {
                chatData.perguntas[i] = pergunta;
                break;
            }
        }

        // Atualiza em todasPerguntas
        for (let i = 0; i < todasPerguntas.length; i++) {
            if (todasPerguntas[i].id === perguntaId) {
                todasPerguntas[i] = pergunta;
                break;
            }
        }

        // Salva usando a mesma lógica da agenda (com fila)
        const salvou = await salvarChatNoServidor();

        if (salvou) {
            console.log('Resposta salva com sucesso!');
            return true;
        } else {
            alert('Erro ao salvar a resposta. Tente novamente.');
            return false;
        }
    }

    // =================================================
    // EVENTOS DAS RESPOSTAS
    // =================================================

    function adicionarEventosRespostas() {
        console.log('Adicionando eventos de resposta...');

        // ======================================
        // BOTÃO RESPONDER - ABRE O FORMULÁRIO
        // ======================================
        document.querySelectorAll('.btn-responder').forEach(function(botao) {
            botao.removeEventListener('click', botao._handleResponder);

            botao._handleResponder = function(e) {
                e.preventDefault();
                e.stopPropagation();

                const id = this.dataset.id;
                console.log('Botão Responder clicado para pergunta:', id);

                let container = document.getElementById(`explorar-respostas-${id}`);
                if (!container) {
                    container = document.getElementById(`minhas-respostas-${id}`);
                }

                if (!container) {
                    console.error('Container não encontrado para a pergunta:', id);
                    return;
                }

                container.classList.add('visivel');

                const form = container.querySelector(`#resposta-form-${id}`);
                if (form) {
                    form.style.display = 'block';
                    const textarea = form.querySelector('textarea');
                    if (textarea) {
                        setTimeout(function() {
                            textarea.focus();
                        }, 300);
                    }
                } else {
                    console.error('Formulário não encontrado para a pergunta:', id);
                }
            };

            botao.addEventListener('click', botao._handleResponder);
        });

        // ======================================
        // BOTÃO VER RESPOSTAS
        // ======================================
        document.querySelectorAll('.btn-ver-respostas').forEach(function(botao) {
            botao.removeEventListener('click', botao._handleClick);

            botao._handleClick = function(e) {
                e.preventDefault();
                e.stopPropagation();

                const id = this.dataset.id;
                console.log('Botão Ver Respostas clicado para pergunta:', id);

                const container = document.getElementById(`minhas-respostas-${id}`) ||
                    document.getElementById(`explorar-respostas-${id}`);

                if (container) {
                    container.classList.toggle('visivel');

                    if (container.classList.contains('visivel')) {
                        const form = container.querySelector(`#resposta-form-${id}`);
                        if (form) {
                            form.style.display = 'block';
                            const textarea = form.querySelector('textarea');
                            if (textarea) {
                                setTimeout(function() {
                                    textarea.focus();
                                }, 300);
                            }
                        }
                    }
                }
            };

            botao.addEventListener('click', botao._handleClick);
        });

        // ======================================
        // ENVIAR RESPOSTA
        // ======================================
        document.querySelectorAll('.btn-enviar-resposta').forEach(function(botao) {
            botao.removeEventListener('click', botao._handleEnviar);

            botao._handleEnviar = async function(e) {
                e.preventDefault();
                e.stopPropagation();

                const perguntaId = this.dataset.id;
                console.log('Botão Enviar Resposta clicado para pergunta:', perguntaId);

                const textarea = document.getElementById(`resposta-texto-${perguntaId}`);
                if (!textarea) {
                    console.error('Textarea não encontrado para pergunta:', perguntaId);
                    alert('Erro: campo de texto não encontrado.');
                    return;
                }

                const texto = textarea.value.trim();
                console.log('Texto digitado:', texto);

                const sucesso = await enviarResposta(perguntaId, texto);

                if (sucesso) {
                    textarea.value = '';
                    const form = document.getElementById(`resposta-form-${perguntaId}`);
                    if (form) {
                        form.style.display = 'none';
                    }

                    const container = document.getElementById(`explorar-respostas-${perguntaId}`) ||
                        document.getElementById(`minhas-respostas-${perguntaId}`);
                    if (container) {
                        container.classList.remove('visivel');
                    }

                    renderizarMinhasPerguntas();
                    renderizarExplorar();

                    // Atualiza badges
                    const badge = document.querySelector('.aba-btn[data-aba="minhas"] .badge');
                    if (badge) {
                        badge.textContent = chatData.perguntas.length;
                    }

                    const badgeExplorar = document.querySelector('.aba-btn[data-aba="explorar"] .badge');
                    if (badgeExplorar) {
                        badgeExplorar.textContent = todasPerguntas.length;
                    }
                }
            };

            botao.addEventListener('click', botao._handleEnviar);
        });

        // ======================================
        // CANCELAR RESPOSTA
        // ======================================
        document.querySelectorAll('.btn-cancelar-resposta').forEach(function(botao) {
            botao.removeEventListener('click', botao._handleCancelar);

            botao._handleCancelar = function(e) {
                e.preventDefault();
                e.stopPropagation();

                const id = this.dataset.id;
                console.log('Botão Cancelar Resposta clicado para pergunta:', id);

                const form = document.getElementById(`resposta-form-${id}`);
                if (form) {
                    const textarea = document.getElementById(`resposta-texto-${id}`);
                    if (textarea) {
                        textarea.value = '';
                    }
                    form.style.display = 'none';

                    const container = document.getElementById(`explorar-respostas-${id}`) ||
                        document.getElementById(`minhas-respostas-${id}`);
                    if (container) {
                        container.classList.remove('visivel');
                    }
                }
            };

            botao.addEventListener('click', botao._handleCancelar);
        });

        // ======================================
        // EXCLUIR RESPOSTA
        // ======================================
        document.querySelectorAll('.btn-excluir-resposta').forEach(function(botao) {
            botao.removeEventListener('click', botao._handleExcluir);

            botao._handleExcluir = function(e) {
                e.preventDefault();
                e.stopPropagation();

                const respostaId = this.dataset.id;
                console.log('Botão Excluir Resposta clicado para resposta:', respostaId);

                let perguntaEncontrada = null;
                let perguntaId = null;

                // Busca em todas as perguntas
                for (let i = 0; i < chatData.perguntas.length; i++) {
                    const p = chatData.perguntas[i];
                    if (p.respostas) {
                        for (let j = 0; j < p.respostas.length; j++) {
                            if (p.respostas[j].id === respostaId) {
                                perguntaEncontrada = p;
                                perguntaId = p.id;
                                break;
                            }
                        }
                    }
                    if (perguntaEncontrada) break;
                }

                if (!perguntaEncontrada) {
                    for (let i = 0; i < todasPerguntas.length; i++) {
                        const p = todasPerguntas[i];
                        if (p.respostas) {
                            for (let j = 0; j < p.respostas.length; j++) {
                                if (p.respostas[j].id === respostaId) {
                                    perguntaEncontrada = p;
                                    perguntaId = p.id;
                                    break;
                                }
                            }
                        }
                        if (perguntaEncontrada) break;
                    }
                }

                if (!perguntaEncontrada) {
                    alert('Não foi possível encontrar a resposta.');
                    return;
                }

                if (confirm('Tem certeza que deseja excluir esta resposta?')) {
                    perguntaEncontrada.respostas = perguntaEncontrada.respostas.filter(function(r) {
                        return r.id !== respostaId;
                    });

                    for (let i = 0; i < chatData.perguntas.length; i++) {
                        if (chatData.perguntas[i].id === perguntaId) {
                            chatData.perguntas[i] = perguntaEncontrada;
                            break;
                        }
                    }

                    for (let i = 0; i < todasPerguntas.length; i++) {
                        if (todasPerguntas[i].id === perguntaId) {
                            todasPerguntas[i] = perguntaEncontrada;
                            break;
                        }
                    }

                    salvarChatNoServidor().then(function() {
                        renderizarMinhasPerguntas();
                        renderizarExplorar();
                    });
                }
            };

            botao.addEventListener('click', botao._handleExcluir);
        });

        // ======================================
        // CENSURA EM TEMPO REAL NAS RESPOSTAS
        // ======================================
        document.querySelectorAll('.resposta-form textarea').forEach(function(textarea) {
            textarea.removeEventListener('input', textarea._handleCensure);

            textarea._handleCensure = function() {
                const form = this.closest('.resposta-form');
                if (!form) return;

                const id = form.id.replace('resposta-form-', '');
                const preview = document.getElementById(`resposta-censure-${id}`);

                if (!preview) return;

                const resultado = verificarCensura(this.value);

                if (resultado.censurado && this.value.trim()) {
                    preview.style.display = 'flex';
                } else {
                    preview.style.display = 'none';
                }
            };

            textarea.addEventListener('input', textarea._handleCensure);
        });

        // ======================================
        // CTRL+ENTER PARA ENVIAR RESPOSTA
        // ======================================
        document.querySelectorAll('.resposta-form textarea').forEach(function(textarea) {
            textarea.removeEventListener('keydown', textarea._handleKeyDown);

            textarea._handleKeyDown = function(e) {
                if (e.key === 'Enter' && e.ctrlKey) {
                    e.preventDefault();
                    const form = this.closest('.resposta-form');
                    if (form) {
                        const id = form.id.replace('resposta-form-', '');
                        const btnEnviar = form.querySelector('.btn-enviar-resposta');
                        if (btnEnviar) {
                            btnEnviar.click();
                        }
                    }
                }
            };

            textarea.addEventListener('keydown', textarea._handleKeyDown);
        });
    }

    // =================================================
    // EVENTOS DAS MINHAS PERGUNTAS
    // =================================================

    function adicionarEventosMinhasPerguntas() {
        document.querySelectorAll('#aba-minhas .btn-excluir').forEach(function(botao) {
            botao.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                abrirModalExclusao(this.dataset.id);
            });
        });
    }

    // =================================================
    // EVENTOS DO EXPLORAR
    // =================================================

    function adicionarEventosExplorar() {
        document.querySelectorAll('#aba-explorar .btn-curtir').forEach(function(botao) {
            botao.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleCurtir(this.dataset.id);
            });
        });

        document.querySelectorAll('#aba-explorar .btn-salvar').forEach(function(botao) {
            botao.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSalvar(this.dataset.id);
            });
        });
    }

    // =================================================
    // CURTIR
    // =================================================

    async function toggleCurtir(id) {
        const index = interacoes.curtidas.indexOf(id);

        if (index >= 0) {
            interacoes.curtidas.splice(index, 1);
        } else {
            interacoes.curtidas.push(id);
        }

        renderizarExplorar();
        await salvarInteracoes();
    }

    // =================================================
    // SALVAR PERGUNTA
    // =================================================

    async function toggleSalvar(id) {
        const index = interacoes.salvos.indexOf(id);

        if (index >= 0) {
            interacoes.salvos.splice(index, 1);
        } else {
            interacoes.salvos.push(id);
        }

        renderizarExplorar();
        await salvarInteracoes();
    }

    // =================================================
    // POSTAR PERGUNTA
    // =================================================

    const btnPostar = document.getElementById('btn-postar-pergunta');
    const perguntaTexto = document.getElementById('pergunta-texto');
    const perguntaMateria = document.getElementById('pergunta-materia');
    const censurePreview = document.getElementById('censure-preview');
    const censurePreviewText = document.getElementById('censure-preview-text');

    // Verificar censura enquanto digita
    perguntaTexto?.addEventListener('input', function() {
        const resultado = verificarCensura(this.value);

        if (resultado.censurado && censurePreview && censurePreviewText) {
            censurePreview.style.display = 'block';
            censurePreviewText.textContent = 'Palavra ofensiva detectada. Ela será censurada automaticamente ao publicar.';
        } else if (censurePreview) {
            censurePreview.style.display = 'none';
        }
    });

    // Publicar pergunta
    btnPostar?.addEventListener('click', async function() {
        const texto = perguntaTexto?.value.trim() || '';

        if (!texto) {
            alert('Escreva sua pergunta antes de publicar.');
            perguntaTexto?.focus();
            return;
        }

        if (texto.length < 5) {
            alert('Sua pergunta é muito curta. Escreva mais detalhes.');
            perguntaTexto?.focus();
            return;
        }

        const materia = perguntaMateria?.value || 'Geral';
        const textoCensurado = censurarTexto(texto);

        const novaPergunta = {
            id: gerarId(),
            autor: usuarioNome,
            texto: textoCensurado,
            texto_original: texto,
            materia: materia,
            data: new Date().toISOString(),
            respostas: []
        };

        chatData.perguntas.push(novaPergunta);
        todasPerguntas.push({
            ...novaPergunta,
            usuario_id: usuarioCodigo
        });

        this.disabled = true;
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Publicando...';

        const salvou = await salvarChatNoServidor();

        this.disabled = false;
        this.innerHTML = '<i class="fa-regular fa-paper-plane"></i> Publicar pergunta';

        if (!salvou) {
            chatData.perguntas = chatData.perguntas.filter(function(pergunta) {
                return pergunta.id !== novaPergunta.id;
            });
            todasPerguntas = todasPerguntas.filter(function(pergunta) {
                return pergunta.id !== novaPergunta.id;
            });
            alert('Não foi possível publicar a pergunta.');
            return;
        }

        if (perguntaTexto) perguntaTexto.value = '';
        if (censurePreview) censurePreview.style.display = 'none';

        renderizarMinhasPerguntas();
        renderizarExplorar();

        const badge = document.querySelector('.aba-btn[data-aba="minhas"] .badge');
        if (badge) badge.textContent = chatData.perguntas.length;

        const badgeExplorar = document.querySelector('.aba-btn[data-aba="explorar"] .badge');
        if (badgeExplorar) badgeExplorar.textContent = todasPerguntas.length;
    });

    // CTRL + ENTER para publicar
    perguntaTexto?.addEventListener('keydown', function(evento) {
        if (evento.key === 'Enter' && evento.ctrlKey) {
            evento.preventDefault();
            btnPostar?.click();
        }
    });

    // =================================================
    // MODAL DE EXCLUSÃO
    // =================================================

    const modalExcluir = document.getElementById('modal-excluir');
    const btnConfirmarExclusao = document.getElementById('confirmar-exclusao');
    const btnCancelarExclusao = document.getElementById('cancelar-exclusao');
    let idParaExcluir = null;

    function abrirModalExclusao(id) {
        idParaExcluir = id;
        if (modalExcluir) modalExcluir.style.display = 'flex';
    }

    function fecharModalExclusao() {
        if (modalExcluir) modalExcluir.style.display = 'none';
        idParaExcluir = null;
    }

    btnCancelarExclusao?.addEventListener('click', fecharModalExclusao);

    modalExcluir?.addEventListener('click', function(evento) {
        if (evento.target === modalExcluir) fecharModalExclusao();
    });

    btnConfirmarExclusao?.addEventListener('click', async function() {
        if (!idParaExcluir) return;

        const perguntasAnteriores = [...chatData.perguntas];

        chatData.perguntas = chatData.perguntas.filter(function(pergunta) {
            return pergunta.id !== idParaExcluir;
        });

        todasPerguntas = todasPerguntas.filter(function(pergunta) {
            return pergunta.id !== idParaExcluir;
        });

        const salvou = await salvarChatNoServidor();

        if (!salvou) {
            chatData.perguntas = perguntasAnteriores;
            alert('Não foi possível excluir a pergunta.');
            return;
        }

        fecharModalExclusao();
        renderizarMinhasPerguntas();
        renderizarExplorar();

        const badge = document.querySelector('.aba-btn[data-aba="minhas"] .badge');
        if (badge) badge.textContent = chatData.perguntas.length;

        const badgeExplorar = document.querySelector('.aba-btn[data-aba="explorar"] .badge');
        if (badgeExplorar) badgeExplorar.textContent = todasPerguntas.length;
    });

    // =================================================
    // ABAS
    // =================================================

    document.querySelectorAll('.aba-btn').forEach(function(botao) {
        botao.addEventListener('click', function() {
            const aba = this.dataset.aba;

            document.querySelectorAll('.aba-btn').forEach(function(item) {
                item.classList.remove('ativo');
            });

            document.querySelectorAll('.aba-conteudo').forEach(function(item) {
                item.classList.remove('ativo');
            });

            this.classList.add('ativo');

            const conteudo = document.getElementById(`aba-${aba}`);
            if (conteudo) conteudo.classList.add('ativo');

            const url = new URL(window.location.href);
            url.searchParams.set('aba', aba);
            window.history.replaceState({}, '', url);
        });
    });

    // =================================================
    // FILTROS
    // =================================================

    const buscaInput = document.getElementById('busca-input');
    const btnBuscar = document.getElementById('btn-buscar');
    const filtroMateriaSelect = document.getElementById('filtro-materia');
    const btnLimparFiltros = document.getElementById('btn-limpar-filtros');

    function aplicarFiltros() {
        const busca = buscaInput?.value.trim() || '';
        const materia = filtroMateriaSelect?.value || 'todas';

        const url = new URL(window.location.href);
        url.searchParams.set('aba', 'explorar');

        if (busca) {
            url.searchParams.set('busca', busca);
        } else {
            url.searchParams.delete('busca');
        }

        if (materia !== 'todas') {
            url.searchParams.set('materia', materia);
        } else {
            url.searchParams.delete('materia');
        }

        window.location.href = url.toString();
    }

    btnBuscar?.addEventListener('click', aplicarFiltros);

    buscaInput?.addEventListener('keydown', function(evento) {
        if (evento.key === 'Enter') {
            evento.preventDefault();
            aplicarFiltros();
        }
    });

    filtroMateriaSelect?.addEventListener('change', aplicarFiltros);

    btnLimparFiltros?.addEventListener('click', function() {
        const url = new URL(window.location.href);
        url.searchParams.delete('busca');
        url.searchParams.delete('materia');
        url.searchParams.set('aba', 'explorar');
        window.location.href = url.toString();
    });

    // =================================================
    // HEADER - CONFIGURAÇÕES, PERFIL, LOGOUT
    // =================================================

    document.getElementById('icon-configuracoes')?.addEventListener('click', function() {
        window.location.href = '../configuracoes/configuracoes.php';
    });

    document.getElementById('icon-perfil')?.addEventListener('click', function() {
        window.location.href = '../perfil/perfil.php';
    });

    const iconSair = document.getElementById('icon-sair');
    const logoutModal = document.getElementById('logout-modal');
    const confirmLogout = document.getElementById('confirm-logout');
    const cancelLogout = document.getElementById('cancel-logout');

    iconSair?.addEventListener('click', function() {
        if (logoutModal) logoutModal.style.display = 'flex';
    });

    cancelLogout?.addEventListener('click', function() {
        if (logoutModal) logoutModal.style.display = 'none';
    });

    logoutModal?.addEventListener('click', function(evento) {
        if (evento.target === logoutModal) logoutModal.style.display = 'none';
    });

    confirmLogout?.addEventListener('click', function() {
        window.location.href = '../login/logout.php';
    });

    // =================================================
    // ESC FECHA OS MODAIS
    // =================================================

    document.addEventListener('keydown', function(evento) {
        if (evento.key !== 'Escape') return;

        if (logoutModal) logoutModal.style.display = 'none';
        fecharModalExclusao();

        document.querySelectorAll('.resposta-form.visivel').forEach(function(form) {
            form.classList.remove('visivel');
            const textarea = form.querySelector('textarea');
            if (textarea) textarea.value = '';
        });
    });

    // =================================================
    // INICIALIZAR
    // =================================================

    renderizarMinhasPerguntas();
    renderizarExplorar();

    console.log('Comunidade FOAG carregada ✅');
    console.log('Palavras proibidas:', palavrasProibidas.length);
    console.log('Minhas perguntas:', chatData.perguntas.length);
    console.log('Perguntas da comunidade:', todasPerguntas.length);
});
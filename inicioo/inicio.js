document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // ELEMENTOS PRINCIPAIS
    // =====================================================

    const noteModal =
        document.getElementById(
            'note-modal'
        );

    const addNoteBtn =
        document.getElementById(
            'add-note'
        );

    const closeNoteModal =
        document.getElementById(
            'close-note-modal'
        );

    const cancelNoteBtn =
        document.getElementById(
            'cancel-note'
        );

    const saveNoteBtn =
        document.getElementById(
            'save-note'
        );

    const noteText =
        document.getElementById(
            'note-text'
        );

    const notesList =
        document.getElementById(
            'notes-list'
        );

    const emptyNotes =
        document.getElementById(
            'empty-notes'
        );

    const createFirstNoteBtn =
        document.getElementById(
            'create-first-note'
        );


    // =====================================================
    // PERFIL
    // =====================================================

    const perfilIcon =
        document.getElementById(
            'icon-perfil'
        );


    // =====================================================
    // LOGOUT
    // =====================================================

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

    const iconSair =
        document.getElementById(
            'icon-sair'
        );


    // =====================================================
    // FOGI
    // =====================================================

    const fogiBtn =
        document.getElementById(
            'icon-fogi'
        );

    const fogiModal =
        document.getElementById(
            'fogi-modal'
        );

    const fogiFrame =
        document.getElementById(
            'fogi-iframe'
        );

    const fogiClose =
        document.getElementById(
            'fogi-close'
        );


    // =====================================================
    // ACESSIBILIDADE
    // =====================================================

    const mensagemAcessibilidade =
        document.getElementById(
            'mensagem-acessibilidade'
        );


    // =====================================================
    // URL PARA SALVAR ANOTAÇÃO
    // =====================================================

    const NOTE_SAVE_URL =
        window.INICIO_NOTE_SAVE_URL ||
        'salvar_anotacao.php';


    let ultimoFocoAntesDoModal =
        null;


    // =====================================================
    // INICIALIZAÇÃO
    // =====================================================

    iniciarPagina();


    function iniciarPagina() {

        configurarEventos();


        if (noteModal) {

            noteModal.setAttribute(
                'aria-hidden',
                'true'
            );

        }


        if (logoutModal) {

            logoutModal.setAttribute(
                'aria-hidden',
                'true'
            );

        }

    }


    // =====================================================
    // EVENTOS
    // =====================================================

    function configurarEventos() {


        // ==========================================
        // PERFIL
        // ==========================================

        if (perfilIcon) {

            perfilIcon.addEventListener(
                'click',
                function () {

                    window.location.href =
                        '../perfil/perfil.php';

                }
            );

        }


        // ==========================================
        // NOVA ANOTAÇÃO
        // ==========================================

        addNoteBtn?.addEventListener(
            'click',
            abrirModalAnotacao
        );


        createFirstNoteBtn?.addEventListener(
            'click',
            abrirModalAnotacao
        );


        closeNoteModal?.addEventListener(
            'click',
            fecharModalAnotacao
        );


        cancelNoteBtn?.addEventListener(
            'click',
            fecharModalAnotacao
        );


        saveNoteBtn?.addEventListener(
            'click',
            salvarAnotacao
        );


        // CTRL + ENTER SALVA ANOTAÇÃO

        noteText?.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.ctrlKey &&
                    event.key === 'Enter'
                ) {

                    salvarAnotacao();

                }

            }
        );


        // ==========================================
        // CLICAR FORA DO MODAL
        // ==========================================

        noteModal?.addEventListener(
            'click',
            function (event) {

                if (
                    event.target ===
                    noteModal
                ) {

                    fecharModalAnotacao();

                }

            }
        );


        // ==========================================
        // LOGOUT
        // ==========================================

        iconSair?.addEventListener(
            'click',
            abrirModalLogout
        );


        confirmLogout?.addEventListener(
            'click',
            function () {

                window.location.href =
                    '../login/logout.php';

            }
        );


        cancelLogout?.addEventListener(
            'click',
            fecharModalLogout
        );


        logoutModal?.addEventListener(
            'click',
            function (event) {

                if (
                    event.target ===
                    logoutModal
                ) {

                    fecharModalLogout();

                }

            }
        );


        // ==========================================
        // FOGI
        // ==========================================

        fogiBtn?.addEventListener(
            'click',
            abrirFogi
        );


        fogiClose?.addEventListener(
            'click',
            fecharFogi
        );


        // ==========================================
        // ESC FECHA MODAIS
        // ==========================================

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key !==
                    'Escape'
                ) {

                    return;

                }


                if (
                    noteModal &&
                    noteModal.style.display ===
                    'flex'
                ) {

                    fecharModalAnotacao();

                    return;

                }


                if (
                    logoutModal &&
                    logoutModal.style.display ===
                    'flex'
                ) {

                    fecharModalLogout();

                    return;

                }


                if (
                    fogiModal &&
                    fogiModal.style.display ===
                    'flex'
                ) {

                    fecharFogi();

                }

            }
        );

    }


    // =====================================================
    // ACESSIBILIDADE
    // =====================================================

    function anunciar(texto) {

        if (
            !mensagemAcessibilidade
        ) {

            return;

        }


        mensagemAcessibilidade.textContent =
            '';


        setTimeout(
            function () {

                mensagemAcessibilidade.textContent =
                    texto;

            },
            50
        );

    }


    function guardarFocoAtual() {

        if (
            document.activeElement
            instanceof HTMLElement
        ) {

            ultimoFocoAntesDoModal =
                document.activeElement;

        }

    }


    function devolverFoco() {

        if (
            ultimoFocoAntesDoModal &&
            typeof ultimoFocoAntesDoModal.focus ===
            'function'
        ) {

            ultimoFocoAntesDoModal.focus();

        }


        ultimoFocoAntesDoModal =
            null;

    }


    // =====================================================
    // MODAL DE ANOTAÇÃO
    // =====================================================

    function abrirModalAnotacao() {

        if (
            !noteModal
        ) {

            return;

        }


        guardarFocoAtual();


        noteModal.style.display =
            'flex';


        noteModal.setAttribute(
            'aria-hidden',
            'false'
        );


        if (noteText) {

            noteText.value =
                '';


            requestAnimationFrame(
                function () {

                    noteText.focus();

                }
            );

        }


        anunciar(
            'Janela de nova anotação aberta.'
        );

    }


    function fecharModalAnotacao() {

        if (
            !noteModal
        ) {

            return;

        }


        noteModal.style.display =
            'none';


        noteModal.setAttribute(
            'aria-hidden',
            'true'
        );


        if (noteText) {

            noteText.value =
                '';

        }


        anunciar(
            'Janela de nova anotação fechada.'
        );


        devolverFoco();

    }


    // =====================================================
    // SALVAR ANOTAÇÃO
    // =====================================================

    async function salvarAnotacao() {

        if (
            !noteText
        ) {

            return;

        }


        const texto =
            noteText.value.trim();


        if (
            texto === ''
        ) {

            anunciar(
                'Digite uma anotação antes de salvar.'
            );


            noteText.focus();

            return;

        }


        if (
            texto.length > 200
        ) {

            mostrarNotificacao(
                'A anotação deve ter no máximo 200 caracteres.',
                'erro'
            );

            return;

        }


        const conteudoOriginalBotao =
            saveNoteBtn
                ? saveNoteBtn.innerHTML
                : 'Salvar';


        if (saveNoteBtn) {

            saveNoteBtn.disabled =
                true;


            saveNoteBtn.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Salvando...
            `;

        }


        try {

            const resposta =
                await fetch(
                    NOTE_SAVE_URL,
                    {

                        method:
                            'POST',

                        credentials:
                            'same-origin',

                        headers: {

                            'Content-Type':
                                'application/json'

                        },

                        body:
                            JSON.stringify({
                                text:
                                    texto
                            })

                    }
                );


            let dados;


            try {

                dados =
                    await resposta.json();

            } catch (erro) {

                throw new Error(
                    'Resposta inválida do servidor.'
                );

            }


            if (
                !resposta.ok ||
                !dados.sucesso
            ) {

                throw new Error(
                    dados.mensagem ||
                    'Não foi possível salvar a anotação.'
                );

            }


            adicionarAnotacaoNaTela(
                dados.anotacao
            );


            fecharModalAnotacao();


            mostrarNotificacao(
                'Anotação salva com sucesso!'
            );


        } catch (erro) {

            console.error(
                'Erro ao salvar anotação:',
                erro
            );


            mostrarNotificacao(
                erro.message ||
                'Erro ao salvar anotação.',
                'erro'
            );


        } finally {

            if (saveNoteBtn) {

                saveNoteBtn.disabled =
                    false;


                saveNoteBtn.innerHTML =
                    conteudoOriginalBotao;

            }

        }

    }


    // =====================================================
    // ADICIONAR ANOTAÇÃO NA TELA
    // =====================================================

    function adicionarAnotacaoNaTela(
        anotacao
    ) {

        if (
            !notesList ||
            !anotacao
        ) {

            return;

        }


        const item =
            document.createElement(
                'div'
            );


        item.className =
            'note-item';


        const texto =
            document.createElement(
                'p'
            );


        texto.className =
            'note-text';


        texto.textContent =
            anotacao.text ||
            '';


        const data =
            document.createElement(
                'span'
            );


        data.className =
            'note-date';


        data.textContent =
            anotacao.date ||
            '';


        item.appendChild(
            texto
        );


        item.appendChild(
            data
        );


        notesList.prepend(
            item
        );


        notesList.style.display =
            'flex';


        if (emptyNotes) {

            emptyNotes.style.display =
                'none';

        }


        // MÁXIMO DE 5 NA TELA

        while (
            notesList.children.length >
            5
        ) {

            notesList
                .lastElementChild
                ?.remove();

        }

    }


    // =====================================================
    // LOGOUT
    // =====================================================

    function abrirModalLogout() {

        if (
            !logoutModal
        ) {

            return;

        }


        guardarFocoAtual();


        logoutModal.style.display =
            'flex';


        logoutModal.setAttribute(
            'aria-hidden',
            'false'
        );


        requestAnimationFrame(
            function () {

                cancelLogout?.focus();

            }
        );


        anunciar(
            'Confirmação de saída aberta.'
        );

    }


    function fecharModalLogout() {

        if (
            !logoutModal
        ) {

            return;

        }


        logoutModal.style.display =
            'none';


        logoutModal.setAttribute(
            'aria-hidden',
            'true'
        );


        anunciar(
            'Confirmação de saída fechada.'
        );


        devolverFoco();

    }


    // =====================================================
    // FOGI
    // =====================================================

    function abrirFogi() {

        if (
            !fogiModal ||
            !fogiFrame
        ) {

            return;

        }


        fogiFrame.src =
            'http://127.0.0.1:5000';


        fogiModal.style.display =
            'flex';


        document.body.style.overflow =
            'hidden';

    }


    function fecharFogi() {

        if (
            !fogiModal ||
            !fogiFrame
        ) {

            return;

        }


        fogiModal.style.display =
            'none';


        fogiFrame.src =
            'about:blank';


        document.body.style.overflow =
            '';

    }


    window.addEventListener(
        'message',
        function (event) {

            if (
                event.data &&
                event.data.type ===
                'FOGI_CLOSE'
            ) {

                fecharFogi();

            }

        }
    );


    // =====================================================
    // NOTIFICAÇÕES
    // =====================================================

    function mostrarNotificacao(
        mensagem,
        tipo = 'sucesso'
    ) {

        const notification =
            document.createElement(
                'div'
            );


        notification.setAttribute(
            'role',
            'status'
        );


        notification.setAttribute(
            'aria-live',
            'polite'
        );


        const background =
            tipo === 'erro'
                ? '#dc4c4c'
                : '#38a5ff';


        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            max-width: 360px;
            background: ${background};
            color: white;
            padding: 12px 18px;
            border-radius: 9px;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.18);
            z-index: 10000;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
        `;


        notification.textContent =
            mensagem;


        document.body.appendChild(
            notification
        );


        anunciar(
            mensagem
        );


        setTimeout(
            function () {

                notification.remove();

            },
            3000
        );

    }

});


// =====================================================
// MODO ESCURO
// =====================================================

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const isDark =
            localStorage.getItem(
                'darkMode'
            ) ===
            'true';


        document.body.classList.toggle(
            'dark-mode',
            isDark
        );


        const themeToggle =
            document.getElementById(
                'themeToggle'
            );


        if (
            !themeToggle
        ) {

            return;

        }


        themeToggle.className =
            isDark
                ? 'fa-solid fa-sun'
                : 'fa-solid fa-moon';


        themeToggle.title =
            isDark
                ? 'Modo Claro'
                : 'Modo Escuro';


        themeToggle.setAttribute(
            'aria-label',
            isDark
                ? 'Ativar modo claro'
                : 'Ativar modo escuro'
        );


        themeToggle.addEventListener(
            'click',
            function () {

                const isNowDark =
                    document.body
                        .classList
                        .toggle(
                            'dark-mode'
                        );


                localStorage.setItem(
                    'darkMode',
                    isNowDark
                );


                this.className =
                    isNowDark
                        ? 'fa-solid fa-sun'
                        : 'fa-solid fa-moon';


                this.title =
                    isNowDark
                        ? 'Modo Claro'
                        : 'Modo Escuro';


                this.setAttribute(
                    'aria-label',
                    isNowDark
                        ? 'Ativar modo claro'
                        : 'Ativar modo escuro'
                );

            }
        );

    }
);
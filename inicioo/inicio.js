document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // ELEMENTOS PRINCIPAIS
    // =====================================================

    const noteModal = document.getElementById('note-modal');
    const addNoteBtn = document.getElementById('add-note');
    const closeNoteModal = document.getElementById('close-note-modal');
    const cancelNoteBtn = document.getElementById('cancel-note');
    const saveNoteBtn = document.getElementById('save-note');
    const noteText = document.getElementById('note-text');
    const notesList = document.getElementById('notes-list');
    const emptyNotes = document.getElementById('empty-notes');
    const createFirstNoteBtn = document.getElementById('create-first-note');

    const perfilIcon = document.getElementById('icon-perfil');

    const logoutModal = document.getElementById('logout-modal');
    const confirmLogout = document.getElementById('confirm-logout');
    const cancelLogout = document.getElementById('cancel-logout');
    const iconSair = document.getElementById('icon-sair');

    const mensagemAcessibilidade =
        document.getElementById('mensagem-acessibilidade');

    let ultimoFocoAntesDoModal = null;


    // =====================================================
    // FRASES MOTIVACIONAIS
    // =====================================================

    const motivationalQuotes = [
        'Organizar é o primeiro passo para o sucesso!',
        'Cada tarefa concluída é uma vitória!',
        'A consistência leva à excelência!',
        'Hoje é um novo dia para ser produtivo!',
        'Pequenos passos levam a grandes conquistas!',
        'A organização transforma sonhos em realidade!',
        'Você está no controle do seu tempo!',
        'Cada dia é uma nova oportunidade!',
        'A disciplina é a ponte entre metas e realizações!',
        'Seu potencial é ilimitado!'
    ];


    // =====================================================
    // INICIALIZAÇÃO
    // =====================================================

    initializePage();

    function initializePage() {

        loadMotivationalQuote();
        loadImportantNotes();
        loadReminders();
        setupEventListeners();
        startLiveUpdates();

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

    function setupEventListeners() {

        // PERFIL
        if (perfilIcon) {

            perfilIcon.addEventListener(
                'click',
                function () {

                    window.location.href =
                        '../perfil/perfil.php';

                }
            );
        }


        // ANOTAÇÃO
        addNoteBtn?.addEventListener(
            'click',
            openNoteModal
        );

        closeNoteModal?.addEventListener(
            'click',
            closeNoteModalFunc
        );

        cancelNoteBtn?.addEventListener(
            'click',
            closeNoteModalFunc
        );

        saveNoteBtn?.addEventListener(
            'click',
            saveNote
        );

        createFirstNoteBtn?.addEventListener(
            'click',
            openNoteModal
        );


        // FECHAR CLICANDO FORA
        noteModal?.addEventListener(
            'click',
            function (event) {

                if (event.target === noteModal) {
                    closeNoteModalFunc();
                }

            }
        );


        // LOGOUT
        if (iconSair && logoutModal) {

            iconSair.addEventListener(
                'click',
                openLogoutModal
            );
        }


        if (confirmLogout) {

            confirmLogout.addEventListener(
                'click',
                function () {

                    window.location.href =
                        '../login/logout.php';

                }
            );
        }


        if (cancelLogout) {

            cancelLogout.addEventListener(
                'click',
                closeLogoutModal
            );
        }


        logoutModal?.addEventListener(
            'click',
            function (event) {

                if (event.target === logoutModal) {
                    closeLogoutModal();
                }

            }
        );


        // ESC FECHA MODAIS
        document.addEventListener(
            'keydown',
            function (event) {

                if (event.key !== 'Escape') {
                    return;
                }


                if (
                    noteModal &&
                    noteModal.style.display === 'flex'
                ) {

                    closeNoteModalFunc();
                    return;
                }


                if (
                    logoutModal &&
                    logoutModal.style.display === 'flex'
                ) {

                    closeLogoutModal();
                }

            }
        );
    }


    // =====================================================
    // ACESSIBILIDADE
    // =====================================================

    function anunciar(texto) {

        if (!mensagemAcessibilidade) {
            return;
        }

        mensagemAcessibilidade.textContent = '';

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
            typeof ultimoFocoAntesDoModal.focus
                === 'function'
        ) {

            ultimoFocoAntesDoModal.focus();
        }

        ultimoFocoAntesDoModal = null;
    }


    // =====================================================
    // FRASE MOTIVACIONAL
    // =====================================================

    function loadMotivationalQuote() {

        const quoteText =
            document.getElementById(
                'quote-text'
            );

        if (!quoteText) {
            return;
        }

        const randomQuote =
            motivationalQuotes[
                Math.floor(
                    Math.random()
                    * motivationalQuotes.length
                )
            ];

        quoteText.textContent =
            randomQuote;
    }


    // =====================================================
    // MODAL DE ANOTAÇÃO
    // =====================================================

    function openNoteModal() {

        if (!noteModal) {
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

            noteText.value = '';

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


    function closeNoteModalFunc() {

        if (!noteModal) {
            return;
        }

        noteModal.style.display =
            'none';

        noteModal.setAttribute(
            'aria-hidden',
            'true'
        );


        if (noteText) {
            noteText.value = '';
        }


        anunciar(
            'Janela de nova anotação fechada.'
        );

        devolverFoco();
    }


    // =====================================================
    // ANOTAÇÕES
    // =====================================================

    function saveNote() {

        if (!noteText) {
            return;
        }


        const text =
            noteText.value.trim();


        if (!text) {

            anunciar(
                'Digite uma anotação antes de salvar.'
            );

            noteText.focus();

            return;
        }


        const notes =
            getImportantNotes();


        const newNote = {

            id: Date.now(),

            text: text,

            date:
                new Date()
                    .toLocaleDateString(
                        'pt-BR'
                    ),

            timestamp:
                Date.now()
        };


        notes.unshift(
            newNote
        );


        localStorage.setItem(
            'foag_important_notes',
            JSON.stringify(notes)
        );


        loadImportantNotes();

        closeNoteModalFunc();

        showNotification(
            'Anotação salva com sucesso!'
        );
    }


    function getImportantNotes() {

        try {

            return JSON.parse(
                localStorage.getItem(
                    'foag_important_notes'
                ) || '[]'
            );

        } catch (error) {

            console.error(
                'Erro ao carregar anotações:',
                error
            );

            return [];
        }
    }


    function loadImportantNotes() {

        if (
            !notesList ||
            !emptyNotes
        ) {

            return;
        }


        const notes =
            getImportantNotes();


        if (
            notes.length === 0
        ) {

            notesList.style.display =
                'none';

            emptyNotes.style.display =
                'block';

            return;
        }


        emptyNotes.style.display =
            'none';

        notesList.style.display =
            'flex';

        notesList.innerHTML =
            '';


        const recentNotes =
            notes.slice(
                0,
                3
            );


        recentNotes.forEach(
            function (note) {

                const noteElement =
                    document.createElement(
                        'div'
                    );

                noteElement.className =
                    'note-item';


                const noteTextElement =
                    document.createElement(
                        'p'
                    );

                noteTextElement.className =
                    'note-text';

                noteTextElement.textContent =
                    note.text;


                const noteDateElement =
                    document.createElement(
                        'span'
                    );

                noteDateElement.className =
                    'note-date';

                noteDateElement.textContent =
                    note.date;


                noteElement.appendChild(
                    noteTextElement
                );

                noteElement.appendChild(
                    noteDateElement
                );

                notesList.appendChild(
                    noteElement
                );

            }
        );
    }


    // =====================================================
    // LEMBRETES
    // =====================================================

    function loadReminders() {

        const remindersList =
            document.getElementById(
                'reminders-list'
            );

        const emptyReminders =
            document.getElementById(
                'empty-reminders'
            );


        if (
            !remindersList ||
            !emptyReminders
        ) {

            return;
        }


        const reminders = [

            {
                text:
                    'Reunião com orientador',

                time:
                    '14:00'
            },

            {
                text:
                    'Entrega do projeto',

                time:
                    'Amanhã'
            },

            {
                text:
                    'Estudar para prova',

                time:
                    '18:00'
            }

        ];


        if (
            reminders.length === 0
        ) {

            remindersList.style.display =
                'none';

            emptyReminders.style.display =
                'block';

            return;
        }


        emptyReminders.style.display =
            'none';

        remindersList.style.display =
            'block';

        remindersList.innerHTML =
            '';


        reminders.slice(
            0,
            2
        ).forEach(
            function (reminder) {

                const reminderElement =
                    document.createElement(
                        'div'
                    );

                reminderElement.className =
                    'reminder-item';


                const iconContainer =
                    document.createElement(
                        'div'
                    );

                iconContainer.className =
                    'reminder-icon';

                iconContainer.setAttribute(
                    'aria-hidden',
                    'true'
                );


                const icon =
                    document.createElement(
                        'i'
                    );

                icon.className =
                    'fa-solid fa-clock';


                iconContainer.appendChild(
                    icon
                );


                const reminderText =
                    document.createElement(
                        'div'
                    );

                reminderText.className =
                    'reminder-text';

                reminderText.textContent =
                    reminder.text;


                const reminderTime =
                    document.createElement(
                        'div'
                    );

                reminderTime.className =
                    'reminder-time';

                reminderTime.textContent =
                    reminder.time;


                reminderElement.appendChild(
                    iconContainer
                );

                reminderElement.appendChild(
                    reminderText
                );

                reminderElement.appendChild(
                    reminderTime
                );

                remindersList.appendChild(
                    reminderElement
                );

            }
        );
    }


    // =====================================================
    // LOGOUT
    // =====================================================

    function openLogoutModal() {

        if (!logoutModal) {
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


    function closeLogoutModal() {

        if (!logoutModal) {
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
    // NOTIFICAÇÕES
    // =====================================================

    function showNotification(message) {

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


        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #38a5ff;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 10000;
        `;


        notification.textContent =
            message;


        document.body.appendChild(
            notification
        );


        anunciar(
            message
        );


        setTimeout(
            function () {

                notification.remove();

            },
            3000
        );
    }


    // =====================================================
    // ATUALIZAÇÕES
    // =====================================================

    function startLiveUpdates() {

        setInterval(
            function () {

                loadMotivationalQuote();

            },
            3600000
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
            ) === 'true';


        document.body.classList.toggle(
            'dark-mode',
            isDark
        );


        const themeToggle =
            document.getElementById(
                'themeToggle'
            );


        if (!themeToggle) {
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
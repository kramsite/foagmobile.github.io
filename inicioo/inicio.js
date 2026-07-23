document.addEventListener('DOMContentLoaded', function () {
    // Elementos do DOM
    const noteModal = document.getElementById('note-modal');
    const addNoteBtn = document.getElementById('add-note');
    const closeNoteModal = document.getElementById('close-note-modal');
    const cancelNoteBtn = document.getElementById('cancel-note');
    const saveNoteBtn = document.getElementById('save-note');
    const noteText = document.getElementById('note-text');
    const notesList = document.getElementById('notes-list');
    const emptyNotes = document.getElementById('empty-notes');
    const createFirstNoteBtn = document.getElementById('create-first-note');

    // Frases motivacionais
    const motivationalQuotes = [
        "Organizar é o primeiro passo para o sucesso!",
        "Cada tarefa concluída é uma vitória!",
        "A consistência leva à excelência!",
        "Hoje é um novo dia para ser produtivo!",
        "Pequenos passos levam a grandes conquistas!",
        "A organização transforma sonhos em realidade!",
        "Você está no controle do seu tempo!",
        "Cada dia é uma nova oportunidade!",
        "A disciplina é a ponte entre metas e realizações!",
        "Seu potencial é ilimitado!"
    ];

    // ========== ÍCONE DO PERFIL ==========
    const perfilIcon = document.getElementById('icon-perfil');

    if (perfilIcon) {
        perfilIcon.addEventListener('click', function () {
            window.location.href = '../perfil/perfil.php';
        });
    }

    // Inicialização
    initializePage();

    // ============= FUNÇÕES DE INICIALIZAÇÃO =============

    function initializePage() {
        loadMotivationalQuote();
        loadImportantNotes();
        loadReminders();
        updateStatistics();
        setupEventListeners();
        startLiveUpdates();
    }

    function setupEventListeners() {
        // Modal de anotações
        addNoteBtn?.addEventListener('click', openNoteModal);
        closeNoteModal?.addEventListener('click', closeNoteModalFunc);
        cancelNoteBtn?.addEventListener('click', closeNoteModalFunc);
        saveNoteBtn?.addEventListener('click', saveNote);
        createFirstNoteBtn?.addEventListener('click', openNoteModal);

        // Fechar modal clicando fora
        noteModal?.addEventListener('click', function (e) {
            if (e.target === noteModal) {
                closeNoteModalFunc();
            }
        });

        setupLogoutModal();
    }

    // ============= FRASE MOTIVACIONAL =============

    function loadMotivationalQuote() {
        const quoteText = document.getElementById('quote-text');

        if (quoteText) {
            const randomQuote =
                motivationalQuotes[
                    Math.floor(Math.random() * motivationalQuotes.length)
                ];

            quoteText.textContent = randomQuote;
        }
    }

    // ============= ANOTAÇÕES IMPORTANTES =============

    function openNoteModal() {
        if (noteModal) {
            if (noteText) {
                noteText.value = '';
                noteText.focus();
            }

            noteModal.style.display = 'flex';
        }
    }

    function closeNoteModalFunc() {
        if (noteModal) {
            noteModal.style.display = 'none';
        }

        if (noteText) {
            noteText.value = '';
        }
    }

    function saveNote() {
        if (!noteText) {
            return;
        }

        const text = noteText.value.trim();

        if (!text) {
            alert('Por favor, digite uma anotação!');
            return;
        }

        const notes = getImportantNotes();

        const newNote = {
            id: Date.now(),
            text: text,
            date: new Date().toLocaleDateString('pt-BR'),
            timestamp: new Date().getTime()
        };

        notes.unshift(newNote);

        localStorage.setItem(
            'foag_important_notes',
            JSON.stringify(notes)
        );

        loadImportantNotes();
        closeNoteModalFunc();
        showNotification('Anotação salva com sucesso!');
    }

    function getImportantNotes() {
        try {
            return JSON.parse(
                localStorage.getItem('foag_important_notes') || '[]'
            );
        } catch (error) {
            console.error('Erro ao carregar anotações:', error);
            return [];
        }
    }

    function loadImportantNotes() {
        if (!notesList || !emptyNotes) {
            return;
        }

        const notes = getImportantNotes();

        if (notes.length === 0) {
            notesList.style.display = 'none';
            emptyNotes.style.display = 'block';
            return;
        }

        emptyNotes.style.display = 'none';
        notesList.style.display = 'flex';
        notesList.innerHTML = '';

        const recentNotes = notes.slice(0, 3);

        recentNotes.forEach(function (note) {
            const noteElement = document.createElement('div');
            noteElement.className = 'note-item';

            const noteTextElement = document.createElement('div');
            noteTextElement.className = 'note-text';
            noteTextElement.textContent = note.text;

            const noteDateElement = document.createElement('div');
            noteDateElement.className = 'note-date';
            noteDateElement.textContent = note.date;

            noteElement.appendChild(noteTextElement);
            noteElement.appendChild(noteDateElement);

            notesList.appendChild(noteElement);
        });
    }

    function deleteNote(noteId) {
        const notes = getImportantNotes();

        const updatedNotes = notes.filter(function (note) {
            return note.id !== noteId;
        });

        localStorage.setItem(
            'foag_important_notes',
            JSON.stringify(updatedNotes)
        );

        loadImportantNotes();
    }

    // ============= ESTATÍSTICAS =============

    function updateStatistics() {
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1;
        const currentYear = currentDate.getFullYear();

        const monthData = loadMonthData(
            currentYear,
            currentMonth
        );

        updatePresenceStats(monthData);
        updateTaskStats();
        updateStreak();
    }

    function loadMonthData(year, month) {
        const key = `foag_meta_${year}_${month}`;
        const data = localStorage.getItem(key);

        if (data) {
            try {
                return JSON.parse(data);
            } catch (error) {
                console.error(
                    'Erro ao carregar dados do calendário:',
                    error
                );
            }
        }

        return {
            pres: 22,
            falt: 3,
            atest: 1,
            sem: 2,
            provas: 2,
            percPres: 88
        };
    }

    function updatePresenceStats(data) {
        const totalPresencas =
            document.getElementById('total-presencas');

        const totalFaltas =
            document.getElementById('total-faltas');

        const percentualPresenca =
            document.getElementById('percentual-presenca');

        const progressFill =
            document.querySelector('.progress-fill');

        if (totalPresencas) {
            totalPresencas.textContent = data.pres;
        }

        if (totalFaltas) {
            totalFaltas.textContent = data.falt;
        }

        if (percentualPresenca) {
            percentualPresenca.textContent =
                data.percPres + '%';
        }

        if (progressFill) {
            progressFill.style.width =
                data.percPres + '%';
        }
    }

    function updateTaskStats() {
        let tarefas = [];

        try {
            tarefas = JSON.parse(
                localStorage.getItem('tarefas-salvas') || '[]'
            );
        } catch (error) {
            console.error('Erro ao carregar tarefas:', error);
        }

        const tarefasPendentes =
            document.getElementById('tarefas-pendentes');

        if (tarefasPendentes) {
            const pendentes = tarefas.filter(function (tarefa) {
                return (
                    tarefa.texto &&
                    tarefa.texto.trim() !== ''
                );
            }).length;

            tarefasPendentes.textContent = pendentes;
        }
    }

    function updateStreak() {
        const diasConsecutivos =
            document.getElementById('dias-consecutivos');

        if (diasConsecutivos) {
            const streak =
                Math.floor(Math.random() * 10) + 1;

            diasConsecutivos.textContent = streak;
        }
    }

    // ============= LEMBRETES =============

    function loadReminders() {
        const remindersList =
            document.getElementById('reminders-list');

        const emptyReminders =
            document.getElementById('empty-reminders');

        if (!remindersList || !emptyReminders) {
            return;
        }

        const reminders = [
            {
                text: "Reunião com orientador",
                time: "14:00"
            },
            {
                text: "Entrega do projeto",
                time: "Amanhã"
            },
            {
                text: "Estudar para prova",
                time: "18:00"
            }
        ];

        if (reminders.length === 0) {
            remindersList.style.display = 'none';
            emptyReminders.style.display = 'block';
            return;
        }

        emptyReminders.style.display = 'none';
        remindersList.style.display = 'block';
        remindersList.innerHTML = '';

        reminders.slice(0, 2).forEach(function (reminder) {
            const reminderElement =
                document.createElement('div');

            reminderElement.className = 'reminder-item';

            const iconContainer =
                document.createElement('div');

            iconContainer.className = 'reminder-icon';

            const icon =
                document.createElement('i');

            icon.className = 'fa-solid fa-clock';

            iconContainer.appendChild(icon);

            const reminderText =
                document.createElement('div');

            reminderText.className = 'reminder-text';
            reminderText.textContent = reminder.text;

            const reminderTime =
                document.createElement('div');

            reminderTime.className = 'reminder-time';
            reminderTime.textContent = reminder.time;

            reminderElement.appendChild(iconContainer);
            reminderElement.appendChild(reminderText);
            reminderElement.appendChild(reminderTime);

            remindersList.appendChild(reminderElement);
        });
    }

    // ============= ATUALIZAÇÕES EM TEMPO REAL =============

    function startLiveUpdates() {
        setInterval(function () {
            updateStatistics();
        }, 60000);

        setInterval(function () {
            loadMotivationalQuote();
        }, 3600000);
    }

    // ============= NOTIFICAÇÕES =============

    function showNotification(message) {
        const notification =
            document.createElement('div');

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
            animation: slideIn 0.3s ease;
        `;

        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(function () {
            notification.style.animation =
                'slideOut 0.3s ease';

            setTimeout(function () {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // ============= MODAL DE LOGOUT =============

    function setupLogoutModal() {
        const logoutModal =
            document.getElementById('logout-modal');

        const confirmLogout =
            document.getElementById('confirm-logout');

        const cancelLogout =
            document.getElementById('cancel-logout');

        const iconSair =
            document.getElementById('icon-sair');

        if (iconSair && logoutModal) {
            iconSair.addEventListener('click', function () {
                logoutModal.style.display = 'flex';
            });
        }

        if (confirmLogout) {
            confirmLogout.addEventListener(
                'click',
                function () {
                    window.location.href =
                        '../login/index.php';
                }
            );
        }

        if (cancelLogout && logoutModal) {
            cancelLogout.addEventListener(
                'click',
                function () {
                    logoutModal.style.display = 'none';
                }
            );
        }

        if (logoutModal) {
            logoutModal.addEventListener(
                'click',
                function (e) {
                    if (e.target === logoutModal) {
                        logoutModal.style.display = 'none';
                    }
                }
            );
        }
    }

    // ============= ANIMAÇÕES CSS ADICIONAIS =============

    const style = document.createElement('style');

    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }
    `;

    document.head.appendChild(style);
});


// ============= MODO ESCURO =============

document.addEventListener('DOMContentLoaded', function () {
    const isDark =
        localStorage.getItem('darkMode') === 'true';

    document.body.classList.toggle(
        'dark-mode',
        isDark
    );

    const themeToggle =
        document.getElementById('themeToggle');

    if (themeToggle) {
        themeToggle.className = isDark
            ? 'fa-solid fa-sun'
            : 'fa-solid fa-moon';

        themeToggle.title = isDark
            ? 'Modo Claro'
            : 'Modo Escuro';

        themeToggle.addEventListener(
            'click',
            function () {
                const isNowDark =
                    document.body.classList.toggle(
                        'dark-mode'
                    );

                localStorage.setItem(
                    'darkMode',
                    isNowDark
                );

                this.className = isNowDark
                    ? 'fa-solid fa-sun'
                    : 'fa-solid fa-moon';

                this.title = isNowDark
                    ? 'Modo Claro'
                    : 'Modo Escuro';
            }
        );
    }
});
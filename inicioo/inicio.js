document.addEventListener('DOMContentLoaded', function() {
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

        // Fechar modal ao clicar fora
        noteModal?.addEventListener('click', function(e) {
            if (e.target === noteModal) {
                closeNoteModalFunc();
            }
        });

        // Logout modal
        setupLogoutModal();
    }

    // ============= FRASE MOTIVACIONAL =============
    function loadMotivationalQuote() {
        const quoteText = document.getElementById('quote-text');
        if (quoteText) {
            const randomQuote = motivationalQuotes[Math.floor(Math.random() * motivationalQuotes.length)];
            quoteText.textContent = randomQuote;
        }
    }

    // ============= ANOTAÇÕES IMPORTANTES =============
    function openNoteModal() {
        if (noteModal) {
            noteText.value = '';
            noteModal.style.display = 'flex';
            noteText.focus();
        }
    }

    function closeNoteModalFunc() {
        if (noteModal) {
            noteModal.style.display = 'none';
            noteText.value = '';
        }
    }

    function saveNote() {
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

        notes.unshift(newNote); // Adiciona no início
        localStorage.setItem('foag_important_notes', JSON.stringify(notes));
        
        loadImportantNotes();
        closeNoteModalFunc();
        showNotification('Anotação salva com sucesso!');
    }

    function getImportantNotes() {
        return JSON.parse(localStorage.getItem('foag_important_notes') || '[]');
    }

    function loadImportantNotes() {
        const notes = getImportantNotes();
        
        if (notes.length === 0) {
            notesList.style.display = 'none';
            emptyNotes.style.display = 'block';
            return;
        }

        emptyNotes.style.display = 'none';
        notesList.style.display = 'flex';
        notesList.innerHTML = '';

        // Mostrar apenas as 3 notas mais recentes
        const recentNotes = notes.slice(0, 3);
        
        recentNotes.forEach(note => {
            const noteElement = document.createElement('div');
            noteElement.className = 'note-item';
            noteElement.innerHTML = `
                <div class="note-text">${note.text}</div>
                <div class="note-date">${note.date}</div>
            `;
            notesList.appendChild(noteElement);
        });
    }

    function deleteNote(noteId) {
        const notes = getImportantNotes();
        const updatedNotes = notes.filter(note => note.id !== noteId);
        localStorage.setItem('foag_important_notes', JSON.stringify(updatedNotes));
        loadImportantNotes();
    }

    // ============= ESTATÍSTICAS =============
    function updateStatistics() {
        // Carregar dados do calendário
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1;
        const currentYear = currentDate.getFullYear();
        
        // Simular dados (substitua pelos dados reais do seu sistema)
        const monthData = loadMonthData(currentYear, currentMonth);
        
        updatePresenceStats(monthData);
        updateTaskStats();
        updateStreak();
    }

    function loadMonthData(year, month) {
        // Carregar dados do localStorage do calendário
        const key = `foag_meta_${year}_${month}`;
        const data = localStorage.getItem(key);
        
        if (data) {
            return JSON.parse(data);
        }
        
        // Dados padrão se não existir
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
        const totalPresencas = document.getElementById('total-presencas');
        const totalFaltas = document.getElementById('total-faltas');
        const percentualPresenca = document.getElementById('percentual-presenca');
        const progressFill = document.querySelector('.progress-fill');

        if (totalPresencas) totalPresencas.textContent = data.pres;
        if (totalFaltas) totalFaltas.textContent = data.falt;
        if (percentualPresenca) percentualPresenca.textContent = data.percPres + '%';
        if (progressFill) progressFill.style.width = data.percPres + '%';
    }

    function updateTaskStats() {
        // Carregar tarefas da agenda
        const tarefas = JSON.parse(localStorage.getItem('tarefas-salvas') || '[]');
        const tarefasPendentes = document.getElementById('tarefas-pendentes');
        
        if (tarefasPendentes) {
            // Contar tarefas não concluídas (simulação)
            const pendentes = tarefas.filter(tarefa => 
                tarefa.texto && tarefa.texto.trim() !== ''
            ).length;
            tarefasPendentes.textContent = pendentes;
        }
    }

    function updateStreak() {
        // Simular dias consecutivos produtivos
        const diasConsecutivos = document.getElementById('dias-consecutivos');
        if (diasConsecutivos) {
            // Lógica para calcular dias consecutivos (simulação)
            const streak = Math.floor(Math.random() * 10) + 1;
            diasConsecutivos.textContent = streak;
        }
    }

    // ============= LEMBRETES =============
    function loadReminders() {
        const remindersList = document.getElementById('reminders-list');
        const emptyReminders = document.getElementById('empty-reminders');
        
        // Simular lembretes (substitua pelos dados reais)
        const reminders = [
            { text: "Reunião com orientador", time: "14:00" },
            { text: "Entrega do projeto", time: "Amanhã" },
            { text: "Estudar para prova", time: "18:00" }
        ];

        if (reminders.length === 0) {
            remindersList.style.display = 'none';
            emptyReminders.style.display = 'block';
            return;
        }

        emptyReminders.style.display = 'none';
        remindersList.style.display = 'block';
        remindersList.innerHTML = '';

        // Mostrar apenas os 2 primeiros lembretes
        reminders.slice(0, 2).forEach(reminder => {
            const reminderElement = document.createElement('div');
            reminderElement.className = 'reminder-item';
            reminderElement.innerHTML = `
                <div class="reminder-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="reminder-text">${reminder.text}</div>
                <div class="reminder-time">${reminder.time}</div>
            `;
            remindersList.appendChild(reminderElement);
        });
    }

    // ============= ATUALIZAÇÕES EM TEMPO REAL =============
    function startLiveUpdates() {
        // Atualizar a cada minuto
        setInterval(() => {
            updateStatistics();
        }, 60000);

        // Atualizar frase motivacional a cada hora
        setInterval(() => {
            loadMotivationalQuote();
        }, 3600000);
    }

    // ============= NOTIFICAÇÕES =============
    function showNotification(message) {
        // Criar notificação temporária
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #38a5ff;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // ============= MODAL DE LOGOUT =============
    function setupLogoutModal() {
        const logoutModal = document.getElementById('logout-modal');
        const confirmLogout = document.getElementById('confirm-logout');
        const cancelLogout = document.getElementById('cancel-logout');
        const iconSair = document.getElementById('icon-sair');

        if (iconSair) {
            iconSair.addEventListener('click', () => {
                logoutModal.style.display = 'flex';
            });
        }

        if (confirmLogout) {
            confirmLogout.addEventListener('click', () => {
                window.location.href = '../login/index.php';
            });
        }

        if (cancelLogout) {
            cancelLogout.addEventListener('click', () => {
                logoutModal.style.display = 'none';
            });
        }

        if (logoutModal) {
            logoutModal.addEventListener('click', (e) => {
                if (e.target === logoutModal) {
                    logoutModal.style.display = 'none';
                }
            });
        }
    }



    // ============= ANIMAÇÕES CSS ADICIONAIS =============
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    `;
    document.head.appendChild(style);
});


document.addEventListener('DOMContentLoaded', function() {
    // Inicializar modo escuro
    const isDark = localStorage.getItem('darkMode') === 'true';
    document.body.classList.toggle('dark-mode', isDark);
    
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        themeToggle.title = isDark ? 'Modo Claro' : 'Modo Escuro';
        
        themeToggle.addEventListener('click', function() {
            const isNowDark = document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', isNowDark);
            this.className = isNowDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            this.title = isNowDark ? 'Modo Claro' : 'Modo Escuro';
        });
    }
});
// dark-mode.js - Sistema unificado de modo escuro
(function() {
    'use strict';

    const STORAGE_KEY = 'darkMode';
    const CLASS_NAME = 'dark-mode';

    // ============================================
    // Funções principais
    // ============================================
    function getStoredPreference() {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored === null) return null;
        return stored === 'true';
    }

    function getSystemPreference() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    function applyDarkMode(enable) {
        // Aplica a classe no body
        document.body.classList.toggle(CLASS_NAME, enable);
        
        // Salva no localStorage
        localStorage.setItem(STORAGE_KEY, String(enable));

        // Atualiza o ícone do toggle
        updateToggleIcon(enable);

        // Dispara evento para outros scripts
        const event = new CustomEvent('darkModeChange', { 
            detail: { enabled: enable } 
        });
        document.dispatchEvent(event);

        // Atualiza meta tag de tema (para navegadores móveis)
        updateThemeColor(enable);

        // Sincroniza com o select de tema se existir
        syncThemeSelect(enable);

        console.log(`🌓 Modo escuro: ${enable ? 'ativado' : 'desativado'}`);
    }

    function toggleDarkMode() {
        const isCurrentlyDark = document.body.classList.contains(CLASS_NAME);
        applyDarkMode(!isCurrentlyDark);
    }

    function updateToggleIcon(enable) {
        const themeToggle = document.getElementById('themeToggle');
        if (!themeToggle) return;

        // Remove todas as classes de ícone
        themeToggle.className = '';

        if (enable) {
            // Modo escuro ativado -> mostrar sol (para voltar ao claro)
            themeToggle.classList.add('fa-solid', 'fa-sun');
            themeToggle.title = 'Modo Claro';
        } else {
            // Modo claro -> mostrar lua (para ativar escuro)
            themeToggle.classList.add('fa-solid', 'fa-moon');
            themeToggle.title = 'Modo Escuro';
        }
    }

    function updateThemeColor(enable) {
        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) {
            meta.content = enable ? '#0f172a' : '#38a5ff';
        }
    }

    function syncThemeSelect(enable) {
        const temaSelect = document.getElementById('tema');
        if (temaSelect) {
            const value = enable ? 'escuro' : 'claro';
            if (temaSelect.value !== value) {
                temaSelect.value = value;
            }
        }
    }

    // ============================================
    // Inicialização
    // ============================================
    function initDarkMode() {
        // Verificar preferência salva
        let stored = getStoredPreference();

        if (stored !== null) {
            // Usa preferência salva
            applyDarkMode(stored);
        } else {
            // Usa preferência do sistema
            const systemDark = getSystemPreference();
            applyDarkMode(systemDark);
        }

        // Configurar evento do botão toggle
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            // Remove listeners antigos para evitar duplicação
            const newToggle = themeToggle.cloneNode(true);
            themeToggle.parentNode.replaceChild(newToggle, themeToggle);
            
            newToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleDarkMode();
            });
        }

        // Configurar evento do select de tema (se existir)
        const temaSelect = document.getElementById('tema');
        if (temaSelect) {
            temaSelect.addEventListener('change', function() {
                const valor = this.value;
                if (valor === 'escuro') {
                    applyDarkMode(true);
                } else if (valor === 'claro') {
                    applyDarkMode(false);
                } else if (valor === 'sistema') {
                    applyDarkMode(getSystemPreference());
                }
            });
        }

        // Ouvir mudanças na preferência do sistema
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', function(e) {
            // Só aplicar se não houver preferência salva
            if (localStorage.getItem(STORAGE_KEY) === null) {
                applyDarkMode(e.matches);
            }
        });

        // Ouvir mudanças no localStorage de outras abas
        window.addEventListener('storage', function(e) {
            if (e.key === STORAGE_KEY) {
                const isDark = e.newValue === 'true';
                if (document.body.classList.contains(CLASS_NAME) !== isDark) {
                    applyDarkMode(isDark);
                }
            }
        });
    }

    // ============================================
    // Expor funções globalmente
    // ============================================
    window.darkMode = {
        toggle: toggleDarkMode,
        enable: function() { applyDarkMode(true); },
        disable: function() { applyDarkMode(false); },
        isEnabled: function() { return document.body.classList.contains(CLASS_NAME); },
        set: applyDarkMode
    };

    // ============================================
    // Iniciar quando o DOM estiver pronto
    // ============================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDarkMode);
    } else {
        initDarkMode();
    }

})();
function applyDarkMode() {
    const isDark = localStorage.getItem('darkMode') === 'true';
    document.body.classList.toggle('dark-mode', isDark);

    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        // Alterna entre ícones da lua e do sol do FontAwesome
        themeToggle.classList.toggle('fa-moon', !isDark);
        themeToggle.classList.toggle('fa-sun', isDark);

        // Atualiza o título (tooltip)
        themeToggle.title = isDark ? 'Modo Claro' : 'Modo Escuro';

        // Para garantir o ícone correto e evitar duplicação, remova outras classes relacionadas, se existirem
        if (isDark) {
            themeToggle.classList.remove('fa-solid', 'fa-regular', 'fa-moon');
            themeToggle.classList.add('fa-solid', 'fa-sun');
        } else {
            themeToggle.classList.remove('fa-solid', 'fa-regular', 'fa-sun');
            themeToggle.classList.add('fa-solid', 'fa-moon');
        }
    }
}

function toggleDarkMode() {
    const isDark = !document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark);
    applyDarkMode();
    window.dispatchEvent(new Event('storage'));
}

window.addEventListener('storage', () => {
    applyDarkMode();
});

document.addEventListener('DOMContentLoaded', () => {
    applyDarkMode();

    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleDarkMode);
    }
});

// ========== ÍCONE DO PERFIL ==========
const perfilIcon = document.getElementById('icon-perfil');

if (perfilIcon) {
    perfilIcon.addEventListener('click', () => {
        window.location.href = '../perfil/perfil.php';
    });
}

// ========== LOGOUT ==========
const logoutModal = document.getElementById('logout-modal');
const iconSair = document.getElementById('icon-sair');
const confirmarLogout = document.getElementById('confirm-logout');
const cancelarLogout = document.getElementById('cancel-logout');

if (logoutModal && iconSair) {
    iconSair.addEventListener('click', () => {
        logoutModal.style.display = 'flex';
    });
}

if (confirmarLogout) {
    confirmarLogout.addEventListener('click', () => {
        window.location.href = '../login/index.php';
    });
}

if (cancelarLogout && logoutModal) {
    cancelarLogout.addEventListener('click', () => {
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
// ==========================================
// MODAL FOGi
// ==========================================

const fogiBtn = document.getElementById("icon-fogi");
const fogiModal = document.getElementById("fogi-modal");
const fogiFrame = document.getElementById("fogi-iframe");
const fogiClose = document.getElementById("fogi-close");

if (fogiBtn && fogiModal && fogiFrame) {
  fogiBtn.addEventListener("click", () => {
    fogiFrame.src = "http://127.0.0.1:5000";
    fogiModal.style.display = "flex";
    document.body.style.overflow = "hidden";
  });
}

if (fogiClose && fogiModal && fogiFrame) {
  fogiClose.addEventListener("click", () => {
    fogiModal.style.display = "none";
    fogiFrame.src = "about:blank";
    document.body.style.overflow = "";
  });
}

window.addEventListener("message", (ev) => {
  if (
    ev.data &&
    ev.data.type === "FOGI_CLOSE" &&
    fogiModal &&
    fogiFrame
  ) {
    fogiModal.style.display = "none";
    fogiFrame.src = "about:blank";
    document.body.style.overflow = "";
  }
});


// ==========================================
// PERFIL E LOGOUT
// ==========================================

const logoutModal = document.getElementById("logout-modal");
const iconPerfil = document.getElementById("icon-perfil");
const iconSair = document.getElementById("icon-sair");
const confirmLogout = document.getElementById("confirm-logout");
const cancelLogout = document.getElementById("cancel-logout");

if (iconPerfil) {
  iconPerfil.addEventListener("click", () => {
    window.location.href = "../perfil/perfil.php";
  });
}

if (iconSair && logoutModal) {
  iconSair.addEventListener("click", () => {
    logoutModal.style.display = "flex";
  });
}

if (confirmLogout) {
  confirmLogout.addEventListener("click", () => {
    window.location.href = "../login/logout.php";
  });
}

if (cancelLogout && logoutModal) {
  cancelLogout.addEventListener("click", () => {
    logoutModal.style.display = "none";
  });
}

if (logoutModal) {
  logoutModal.addEventListener("click", (e) => {
    if (e.target === logoutModal) {
      logoutModal.style.display = "none";
    }
  });
}
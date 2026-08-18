document.addEventListener('DOMContentLoaded', () => {
  const subjectModal = document.getElementById('subject-modal');
  const subjectForm = document.getElementById('subject-form');
  const subjectName = document.getElementById('subject-name');
  const subjectColor = document.getElementById('subject-color');
  const subjectIcon = document.getElementById('subject-icon');
  const subjectsGrid = document.getElementById('subjects-grid');
  const subjectsEmpty = document.getElementById('subjects-empty');
  const statSubjects = document.getElementById('stat-subjects');
  const toast = document.getElementById('toast');
  const logoutModal = document.getElementById('logout-modal');

  let subjectsCount = 0;
  let toastTimer = null;

  // =========================
  // ATUALIZAR ESTADO DAS MATÉRIAS
  // =========================

  const updateSubjectsState = () => {
    subjectsCount = document.querySelectorAll('.subject-card').length;

    // Atualiza contador
    if (statSubjects) {
      statSubjects.textContent = subjectsCount;
    }

    // Se já existem matérias
    if (subjectsCount > 0) {
      if (subjectsEmpty) {
        subjectsEmpty.hidden = true;
      }

      if (subjectsGrid) {
        subjectsGrid.hidden = false;
      }
    }

    // Se ainda não existem matérias
    else {
      if (subjectsEmpty) {
        subjectsEmpty.hidden = false;
      }

      if (subjectsGrid) {
        subjectsGrid.hidden = true;
      }
    }
  };

  // Verifica o estado quando a página abre
  updateSubjectsState();

  // =========================
  // ABRIR MODAL DE MATÉRIA
  // =========================

  const openSubjectModal = () => {
    if (!subjectModal) return;

    subjectModal.classList.add('open');
    subjectModal.setAttribute('aria-hidden', 'false');

    setTimeout(() => {
      subjectName?.focus();
    }, 50);
  };

  // =========================
  // FECHAR MODAL DE MATÉRIA
  // =========================

  const closeSubjectModal = () => {
    if (!subjectModal) return;

    subjectModal.classList.remove('open');
    subjectModal.setAttribute('aria-hidden', 'true');

    if (subjectForm) {
      subjectForm.reset();
    }

    // Volta para a cor padrão
    if (subjectColor) {
      subjectColor.value = '#38a5ff';
    }
  };

  // =========================
  // TOAST
  // =========================

  const showToast = (message) => {
    if (!toast) return;

    toast.textContent = message;
    toast.classList.add('show');

    clearTimeout(toastTimer);

    toastTimer = setTimeout(() => {
      toast.classList.remove('show');
    }, 2600);
  };

  // =========================
  // ABRIR MODAL
  // =========================

  document
    .getElementById('open-subject-modal')
    ?.addEventListener('click', openSubjectModal);

  document
    .getElementById('open-subject-modal-secondary')
    ?.addEventListener('click', openSubjectModal);

  document
    .getElementById('open-subject-modal-empty')
    ?.addEventListener('click', openSubjectModal);

  // =========================
  // FECHAR MODAL
  // =========================

  document
    .getElementById('close-subject-modal')
    ?.addEventListener('click', closeSubjectModal);

  document
    .getElementById('cancel-subject-modal')
    ?.addEventListener('click', closeSubjectModal);

  // =========================
  // FECHAR CLICANDO FORA
  // =========================

  subjectModal?.addEventListener('click', (event) => {
    if (event.target === subjectModal) {
      closeSubjectModal();
    }
  });

  // =========================
  // FECHAR COM ESC
  // =========================

  document.addEventListener('keydown', (event) => {
    if (
      event.key === 'Escape' &&
      subjectModal?.classList.contains('open')
    ) {
      closeSubjectModal();
    }
  });

  // =========================
  // ADICIONAR MATÉRIA
  // =========================

  subjectForm?.addEventListener('submit', (event) => {
    event.preventDefault();

    const name = subjectName?.value.trim();
    const color = subjectColor?.value || '#38a5ff';
    const icon = subjectIcon?.value || 'fa-book';

    // Nome obrigatório
    if (!name) {
      subjectName?.focus();
      return;
    }

    // Cria o card
    const card = document.createElement('article');

    card.className = 'subject-card';

    // Aplica a cor escolhida
    card.style.setProperty('--subject-color', color);

    card.innerHTML = `
      <div class="subject-card-top">

        <div class="subject-card-icon">
          <i class="fa-solid ${icon}"></i>
        </div>

        <h3></h3>

      </div>

      <div class="subject-card-meta">

        <span>
          <i class="fa-solid fa-clock"></i>
          0h estudadas
        </span>

        <span>
          <i class="fa-solid fa-layer-group"></i>
          0 flashcards
        </span>

      </div>
    `;

    // Coloca o nome com segurança
    const title = card.querySelector('h3');

    if (title) {
      title.textContent = name;
    }

    // Adiciona card
    if (subjectsGrid) {
      subjectsGrid.appendChild(card);
    }

    // Atualiza contador e remove estado vazio
    updateSubjectsState();

    // Fecha modal
    closeSubjectModal();

    // Mensagem
    showToast(`Matéria “${name}” adicionada.`);
  });

  // =========================
  // MÉTODOS AINDA NÃO CRIADOS
  // =========================

  document.querySelectorAll('[data-coming-soon]').forEach((card) => {
    card.addEventListener('click', (event) => {
      event.preventDefault();

      const nomeMetodo = card.dataset.comingSoon;

      showToast(`${nomeMetodo} será adicionado em breve.`);
    });
  });

  // =========================
  // PERFIL
  // =========================

  document
    .getElementById('icon-perfil')
    ?.addEventListener('click', () => {
      window.location.href = '../perfil/perfil.php';
    });

  // =========================
  // LOGOUT
  // =========================

  document
    .getElementById('icon-sair')
    ?.addEventListener('click', () => {
      logoutModal?.classList.add('open');
    });

  document
    .getElementById('cancel-logout')
    ?.addEventListener('click', () => {
      logoutModal?.classList.remove('open');
    });

  document
    .getElementById('confirm-logout')
    ?.addEventListener('click', () => {
      window.location.href = '../login/logout.php';
    });

  // =========================
  // CONFIGURAÇÕES
  // =========================

  document
    .getElementById('icon-configuracoes')
    ?.addEventListener('click', () => {
      showToast('Abra aqui a página de configurações.');
    });
});
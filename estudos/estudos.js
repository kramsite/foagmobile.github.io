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

  const submitButton = subjectForm?.querySelector(
    'button[type="submit"]'
  );

  let subjectsCount = 0;
  let toastTimer = null;


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
  // ATUALIZAR ESTADO
  // =========================

  const updateSubjectsState = () => {

    subjectsCount =
      document.querySelectorAll('.subject-card').length;

    if (statSubjects) {
      statSubjects.textContent = subjectsCount;
    }

    if (subjectsCount > 0) {

      if (subjectsEmpty) {
        subjectsEmpty.hidden = true;
      }

      if (subjectsGrid) {
        subjectsGrid.hidden = false;
      }

    } else {

      if (subjectsEmpty) {
        subjectsEmpty.hidden = false;
      }

      if (subjectsGrid) {
        subjectsGrid.hidden = true;
      }
    }
  };


  // =========================
  // CRIAR CARD DA MATÉRIA
  // =========================

  const createSubjectCard = (materia) => {

    if (!subjectsGrid) return;

    const card = document.createElement('article');

    card.className = 'subject-card';

    card.dataset.id = materia.id;

    card.style.setProperty(
      '--subject-color',
      materia.cor || '#38a5ff'
    );


    card.innerHTML = `
      <div class="subject-card-top">

        <div class="subject-card-icon">
          <i class="fa-solid ${materia.icone || 'fa-book'}"></i>
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


    const title = card.querySelector('h3');

    if (title) {
      title.textContent = materia.nome;
    }


    subjectsGrid.appendChild(card);
  };


  // =========================
  // CARREGAR MATÉRIAS DO JSON
  // =========================

  const carregarMaterias = () => {

    if (!subjectsGrid) return;

    subjectsGrid.innerHTML = '';

    const dados = window.MATERIAS_DATA || {
      materias: []
    };

    const materias = Array.isArray(dados.materias)
      ? dados.materias
      : [];


    materias.forEach((materia) => {
      createSubjectCard(materia);
    });


    updateSubjectsState();
  };


  // Carrega assim que a página abre
  carregarMaterias();


  // =========================
  // ABRIR MODAL
  // =========================

  const openSubjectModal = () => {

    if (!subjectModal) return;

    subjectModal.classList.add('open');

    subjectModal.setAttribute(
      'aria-hidden',
      'false'
    );

    setTimeout(() => {
      subjectName?.focus();
    }, 50);
  };


  // =========================
  // FECHAR MODAL
  // =========================

  const closeSubjectModal = () => {

    if (!subjectModal) return;

    subjectModal.classList.remove('open');

    subjectModal.setAttribute(
      'aria-hidden',
      'true'
    );


    if (subjectForm) {
      subjectForm.reset();
    }


    if (subjectColor) {
      subjectColor.value = '#38a5ff';
    }
  };


  // =========================
  // BOTÕES ABRIR MODAL
  // =========================

  document
    .getElementById('open-subject-modal')
    ?.addEventListener(
      'click',
      openSubjectModal
    );


  document
    .getElementById('open-subject-modal-secondary')
    ?.addEventListener(
      'click',
      openSubjectModal
    );


  document
    .getElementById('open-subject-modal-empty')
    ?.addEventListener(
      'click',
      openSubjectModal
    );


  // =========================
  // BOTÕES FECHAR MODAL
  // =========================

  document
    .getElementById('close-subject-modal')
    ?.addEventListener(
      'click',
      closeSubjectModal
    );


  document
    .getElementById('cancel-subject-modal')
    ?.addEventListener(
      'click',
      closeSubjectModal
    );


  // =========================
  // FECHAR CLICANDO FORA
  // =========================

  subjectModal?.addEventListener(
    'click',
    (event) => {

      if (event.target === subjectModal) {
        closeSubjectModal();
      }

    }
  );


  // =========================
  // ESC
  // =========================

  document.addEventListener(
    'keydown',
    (event) => {

      if (
        event.key === 'Escape' &&
        subjectModal?.classList.contains('open')
      ) {
        closeSubjectModal();
      }

    }
  );


  // =========================
  // SALVAR MATÉRIA NO JSON
  // =========================

  subjectForm?.addEventListener(
    'submit',
    async (event) => {

      event.preventDefault();


      const nome =
        subjectName?.value.trim();

      const cor =
        subjectColor?.value || '#38a5ff';

      const icone =
        subjectIcon?.value || 'fa-book';


      if (!nome) {

        subjectName?.focus();

        return;
      }


      // =========================
      // BLOQUEAR BOTÃO
      // =========================

      let textoBotaoOriginal = '';

      if (submitButton) {

        textoBotaoOriginal =
          submitButton.innerHTML;

        submitButton.disabled = true;

        submitButton.innerHTML = `
          <i class="fa-solid fa-spinner fa-spin"></i>
          Salvando...
        `;
      }


      try {

        const response = await fetch(
          window.MATERIAS_SAVE_URL ||
          'salvar_materia.php',
          {
            method: 'POST',

            credentials: 'same-origin',

            headers: {
              'Content-Type':
                'application/json'
            },

            body: JSON.stringify({
              nome: nome,
              cor: cor,
              icone: icone
            })
          }
        );


        const data = await response.json();


        if (!response.ok || !data.sucesso) {

          throw new Error(
            data.mensagem ||
            'Não foi possível salvar.'
          );
        }


        // =========================
        // ADICIONAR CARD
        // =========================

        createSubjectCard(
          data.materia
        );


        updateSubjectsState();


        closeSubjectModal();


        showToast(
          `Matéria “${data.materia.nome}” adicionada.`
        );


      } catch (error) {

        console.error(
          'Erro ao salvar matéria:',
          error
        );


        showToast(
          error.message ||
          'Erro ao salvar matéria.'
        );

      } finally {

        if (submitButton) {

          submitButton.disabled = false;

          submitButton.innerHTML =
            textoBotaoOriginal;
        }

      }

    }
  );


  // =========================
  // MÉTODOS EM BREVE
  // =========================

  document
    .querySelectorAll('[data-coming-soon]')
    .forEach((card) => {

      card.addEventListener(
        'click',
        (event) => {

          event.preventDefault();

          const nomeMetodo =
            card.dataset.comingSoon;

          showToast(
            `${nomeMetodo} será adicionado em breve.`
          );

        }
      );

    });


  // =========================
  // PERFIL
  // =========================

  document
    .getElementById('icon-perfil')
    ?.addEventListener(
      'click',
      () => {

        window.location.href =
          '../perfil/perfil.php';

      }
    );


  // =========================
  // LOGOUT
  // =========================

  document
    .getElementById('icon-sair')
    ?.addEventListener(
      'click',
      () => {

        logoutModal?.classList.add(
          'open'
        );

      }
    );


  document
    .getElementById('cancel-logout')
    ?.addEventListener(
      'click',
      () => {

        logoutModal?.classList.remove(
          'open'
        );

      }
    );


  document
    .getElementById('confirm-logout')
    ?.addEventListener(
      'click',
      () => {

        window.location.href =
          '../login/logout.php';

      }
    );


  // =========================
  // CONFIGURAÇÕES
  // =========================

  document
    .getElementById('icon-configuracoes')
    ?.addEventListener(
      'click',
      () => {

        window.location.href =
          '../configuracoes/configuracoes.php';

      }
    );

});
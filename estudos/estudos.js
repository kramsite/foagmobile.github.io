document.addEventListener('DOMContentLoaded', () => {

  // ==========================================
  // ELEMENTOS
  // ==========================================

  const subjectModal =
    document.getElementById('subject-modal');

  const subjectForm =
    document.getElementById('subject-form');

  const subjectName =
    document.getElementById('subject-name');

  const subjectColor =
    document.getElementById('subject-color');

  const subjectIcon =
    document.getElementById('subject-icon');

  const subjectsGrid =
    document.getElementById('subjects-grid');

  const subjectsEmpty =
    document.getElementById('subjects-empty');

  const statSubjects =
    document.getElementById('stat-subjects');

  const toast =
    document.getElementById('toast');

  const logoutModal =
    document.getElementById('logout-modal');

  const deleteSubjectModal =
    document.getElementById('delete-subject-modal');

  const deleteSubjectName =
    document.getElementById('delete-subject-name');

  const confirmDeleteSubject =
    document.getElementById('confirm-delete-subject');

  const cancelDeleteSubject =
    document.getElementById('cancel-delete-subject');

  const submitButton =
    subjectForm?.querySelector(
      'button[type="submit"]'
    );


  // ==========================================
  // URLS
  // ==========================================

  const SAVE_MATERIA_URL =
    window.MATERIAS_SAVE_URL ||
    'salvar_materia.php';

  const DELETE_MATERIA_URL =
    window.MATERIAS_DELETE_URL ||
    'excluir_materia.php';


  // ==========================================
  // DADOS DAS MATÉRIAS
  // ==========================================

  const materiasData =
    window.MATERIAS_DATA &&
    typeof window.MATERIAS_DATA === 'object'
      ? window.MATERIAS_DATA
      : {
          materias: []
        };


  let materias =
    Array.isArray(materiasData.materias)
      ? [...materiasData.materias]
      : [];


  let subjectsCount = 0;

  let toastTimer = null;

  let materiaParaExcluir = null;

  let cardParaExcluir = null;


  // ==========================================
  // TOAST
  // ==========================================

  const showToast = (message) => {

    if (!toast) {
      return;
    }


    toast.textContent =
      message;


    toast.classList.add(
      'show'
    );


    clearTimeout(
      toastTimer
    );


    toastTimer =
      setTimeout(
        () => {

          toast.classList.remove(
            'show'
          );

        },
        2600
      );

  };


  // ==========================================
  // ATUALIZAR ESTADO
  // ==========================================

  const updateSubjectsState = () => {

    subjectsCount =
      materias.length;


    if (statSubjects) {

      statSubjects.textContent =
        subjectsCount;

    }


    if (subjectsCount > 0) {

      if (subjectsEmpty) {

        subjectsEmpty.hidden =
          true;

      }


      if (subjectsGrid) {

        subjectsGrid.hidden =
          false;

      }

    } else {

      if (subjectsEmpty) {

        subjectsEmpty.hidden =
          false;

      }


      if (subjectsGrid) {

        subjectsGrid.hidden =
          true;

      }

    }

  };


  // ==========================================
  // ABRIR MODAL DE EXCLUSÃO
  // ==========================================

  function excluirMateria(
    materia,
    card
  ) {

    if (!deleteSubjectModal) {

      showToast(
        'Não foi possível abrir a confirmação de exclusão.'
      );

      return;
    }


    materiaParaExcluir =
      materia;


    cardParaExcluir =
      card;


    if (deleteSubjectName) {

      deleteSubjectName.textContent =
        `“${materia.nome}”`;

    }


    deleteSubjectModal.classList.add(
      'open'
    );


    deleteSubjectModal.setAttribute(
      'aria-hidden',
      'false'
    );

  }


  // ==========================================
  // FECHAR MODAL DE EXCLUSÃO
  // ==========================================

  function fecharModalExcluirMateria() {

    if (!deleteSubjectModal) {
      return;
    }


    if (
      confirmDeleteSubject?.disabled
    ) {
      return;
    }


    deleteSubjectModal.classList.remove(
      'open'
    );


    deleteSubjectModal.setAttribute(
      'aria-hidden',
      'true'
    );


    materiaParaExcluir =
      null;


    cardParaExcluir =
      null;

  }


  // ==========================================
  // EXECUTAR EXCLUSÃO
  // ==========================================

  async function executarExclusaoMateria() {

    if (
      !materiaParaExcluir ||
      !cardParaExcluir
    ) {

      return;
    }


    const materia =
      materiaParaExcluir;


    const card =
      cardParaExcluir;


    const botaoCard =
      card.querySelector(
        '.subject-delete-btn'
      );


    const conteudoOriginalConfirmacao =
      confirmDeleteSubject?.innerHTML;


    const conteudoOriginalBotaoCard =
      botaoCard?.innerHTML;


    if (confirmDeleteSubject) {

      confirmDeleteSubject.disabled =
        true;


      confirmDeleteSubject.innerHTML = `
        <i class="fa-solid fa-spinner fa-spin"></i>
        Excluindo...
      `;

    }


    if (botaoCard) {

      botaoCard.disabled =
        true;


      botaoCard.innerHTML = `
        <i class="fa-solid fa-spinner fa-spin"></i>
      `;

    }


    try {

      const response =
        await fetch(
          DELETE_MATERIA_URL,
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

                id:
                  materia.id

              })

          }
        );


      let result;


      try {

        result =
          await response.json();

      } catch (error) {

        throw new Error(
          'Resposta inválida do servidor.'
        );

      }


      if (
        !response.ok ||
        !result.sucesso
      ) {

        throw new Error(
          result.mensagem ||
          'Não foi possível excluir a matéria.'
        );

      }


      // ==================================
      // REMOVER DO ARRAY
      // ==================================

      materias =
        materias.filter(
          (item) => {

            return (
              item.id !==
              materia.id
            );

          }
        );


      // ==================================
      // REMOVER CARD DA TELA
      // ==================================

      card.remove();


      // ==================================
      // ATUALIZAR ESTADO
      // ==================================

      updateSubjectsState();


      // ==================================
      // FECHAR MODAL
      // ==================================

      deleteSubjectModal.classList.remove(
        'open'
      );


      deleteSubjectModal.setAttribute(
        'aria-hidden',
        'true'
      );


      materiaParaExcluir =
        null;


      cardParaExcluir =
        null;


      showToast(
        `Matéria “${materia.nome}” e seus flashcards foram excluídos.`
      );


    } catch (error) {

      console.error(
        'Erro ao excluir matéria:',
        error
      );


      showToast(
        error.message ||
        'Erro ao excluir matéria.'
      );


      if (botaoCard) {

        botaoCard.disabled =
          false;


        botaoCard.innerHTML =
          conteudoOriginalBotaoCard ||
          `
            <i class="fa-regular fa-trash-can"></i>
          `;

      }


    } finally {

      if (confirmDeleteSubject) {

        confirmDeleteSubject.disabled =
          false;


        confirmDeleteSubject.innerHTML =
          conteudoOriginalConfirmacao ||
          `
            <i class="fa-regular fa-trash-can"></i>
            Excluir matéria
          `;

      }

    }

  }


  // ==========================================
  // BOTÕES DO MODAL DE EXCLUSÃO
  // ==========================================

  confirmDeleteSubject
    ?.addEventListener(
      'click',
      executarExclusaoMateria
    );


  cancelDeleteSubject
    ?.addEventListener(
      'click',
      fecharModalExcluirMateria
    );


  deleteSubjectModal
    ?.addEventListener(
      'click',
      (event) => {

        if (
          event.target ===
          deleteSubjectModal
        ) {

          fecharModalExcluirMateria();

        }

      }
    );


  // ==========================================
  // CRIAR CARD DA MATÉRIA
  // ==========================================

  const createSubjectCard = (materia) => {

    if (!subjectsGrid) {
      return;
    }


    const card =
      document.createElement(
        'article'
      );


    card.className =
      'subject-card';


    card.dataset.id =
      materia.id || '';


    card.style.setProperty(
      '--subject-color',
      materia.cor ||
      '#38a5ff'
    );


    card.innerHTML = `
      <div class="subject-card-top">

        <div class="subject-card-icon">

          <i
            class="fa-solid ${materia.icone || 'fa-book'}"
          ></i>

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


    // ==================================
    // NOME DA MATÉRIA
    // ==================================

    const title =
      card.querySelector(
        'h3'
      );


    if (title) {

      title.textContent =
        materia.nome ||
        'Sem nome';

    }


    // ==================================
    // BOTÃO EXCLUIR
    // ==================================

    const deleteButton =
      document.createElement(
        'button'
      );


    deleteButton.type =
      'button';


    deleteButton.className =
      'subject-delete-btn';


    deleteButton.title =
      'Excluir matéria';


    deleteButton.setAttribute(
      'aria-label',
      `Excluir ${materia.nome}`
    );


    deleteButton.innerHTML = `
      <i class="fa-regular fa-trash-can"></i>
    `;


    deleteButton.addEventListener(
      'click',
      (event) => {

        event.preventDefault();

        event.stopPropagation();


        excluirMateria(
          materia,
          card
        );

      }
    );


    card.appendChild(
      deleteButton
    );


    subjectsGrid.appendChild(
      card
    );

  };


  // ==========================================
  // CARREGAR MATÉRIAS
  // ==========================================

  const carregarMaterias = () => {

    if (!subjectsGrid) {
      return;
    }


    subjectsGrid.innerHTML =
      '';


    materias.forEach(
      (materia) => {

        createSubjectCard(
          materia
        );

      }
    );


    updateSubjectsState();

  };


  // ==========================================
  // CARREGAR AO ABRIR
  // ==========================================

  carregarMaterias();


  // ==========================================
  // ABRIR MODAL NOVA MATÉRIA
  // ==========================================

  const openSubjectModal = () => {

    if (!subjectModal) {
      return;
    }


    subjectModal.classList.add(
      'open'
    );


    subjectModal.setAttribute(
      'aria-hidden',
      'false'
    );


    setTimeout(
      () => {

        subjectName?.focus();

      },
      50
    );

  };


  // ==========================================
  // FECHAR MODAL NOVA MATÉRIA
  // ==========================================

  const closeSubjectModal = () => {

    if (!subjectModal) {
      return;
    }


    subjectModal.classList.remove(
      'open'
    );


    subjectModal.setAttribute(
      'aria-hidden',
      'true'
    );


    if (subjectForm) {

      subjectForm.reset();

    }


    if (subjectColor) {

      subjectColor.value =
        '#38a5ff';

    }

  };


  // ==========================================
  // BOTÕES ABRIR MODAL
  // ==========================================

  document
    .getElementById(
      'open-subject-modal'
    )
    ?.addEventListener(
      'click',
      openSubjectModal
    );


  document
    .getElementById(
      'open-subject-modal-secondary'
    )
    ?.addEventListener(
      'click',
      openSubjectModal
    );


  document
    .getElementById(
      'open-subject-modal-empty'
    )
    ?.addEventListener(
      'click',
      openSubjectModal
    );


  // ==========================================
  // BOTÕES FECHAR MODAL
  // ==========================================

  document
    .getElementById(
      'close-subject-modal'
    )
    ?.addEventListener(
      'click',
      closeSubjectModal
    );


  document
    .getElementById(
      'cancel-subject-modal'
    )
    ?.addEventListener(
      'click',
      closeSubjectModal
    );


  // ==========================================
  // FECHAR MODAL CLICANDO FORA
  // ==========================================

  subjectModal?.addEventListener(
    'click',
    (event) => {

      if (
        event.target ===
        subjectModal
      ) {

        closeSubjectModal();

      }

    }
  );


  // ==========================================
  // ESC
  // ==========================================

  document.addEventListener(
    'keydown',
    (event) => {

      if (
        event.key ===
        'Escape'
      ) {

        if (
          subjectModal?.classList.contains(
            'open'
          )
        ) {

          closeSubjectModal();

        }


        if (
          logoutModal?.classList.contains(
            'open'
          )
        ) {

          logoutModal.classList.remove(
            'open'
          );

        }


        if (
          deleteSubjectModal?.classList.contains(
            'open'
          )
        ) {

          fecharModalExcluirMateria();

        }

      }

    }
  );


  // ==========================================
  // SALVAR MATÉRIA
  // ==========================================

  subjectForm?.addEventListener(
    'submit',
    async (event) => {

      event.preventDefault();


      const nome =
        subjectName?.value
          ?.trim();


      const cor =
        subjectColor?.value ||
        '#38a5ff';


      const icone =
        subjectIcon?.value ||
        'fa-book';


      if (!nome) {

        subjectName?.focus();

        return;

      }


      // ==================================
      // EVITAR REPETIDA
      // ==================================

      const existe =
        materias.some(
          (materia) => {

            return (
              String(
                materia.nome ||
                ''
              )
                .trim()
                .toLowerCase() ===
              nome.toLowerCase()
            );

          }
        );


      if (existe) {

        showToast(
          'Essa matéria já foi cadastrada.'
        );

        return;

      }


      // ==================================
      // BLOQUEAR BOTÃO
      // ==================================

      let textoBotaoOriginal =
        '';


      if (submitButton) {

        textoBotaoOriginal =
          submitButton.innerHTML;


        submitButton.disabled =
          true;


        submitButton.innerHTML = `
          <i class="fa-solid fa-spinner fa-spin"></i>
          Salvando...
        `;

      }


      try {

        const response =
          await fetch(
            SAVE_MATERIA_URL,
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

                  nome:
                    nome,

                  cor:
                    cor,

                  icone:
                    icone

                })

            }
          );


        let data;


        try {

          data =
            await response.json();

        } catch (error) {

          throw new Error(
            'Resposta inválida do servidor.'
          );

        }


        if (
          !response.ok ||
          !data.sucesso
        ) {

          throw new Error(
            data.mensagem ||
            'Não foi possível salvar.'
          );

        }


        // ==================================
        // ADICIONAR NO ARRAY
        // ==================================

        materias.push(
          data.materia
        );


        // ==================================
        // CRIAR CARD
        // ==================================

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

          submitButton.disabled =
            false;


          submitButton.innerHTML =
            textoBotaoOriginal;

        }

      }

    }
  );


  // ==========================================
  // MÉTODOS EM BREVE
  // ==========================================

  document
    .querySelectorAll(
      '[data-coming-soon]'
    )
    .forEach(
      (card) => {

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

      }
    );


  // ==========================================
  // PERFIL
  // ==========================================

  document
    .getElementById(
      'icon-perfil'
    )
    ?.addEventListener(
      'click',
      () => {

        window.location.href =
          '../perfil/perfil.php';

      }
    );


  // ==========================================
  // CONFIGURAÇÕES
  // ==========================================

  document
    .getElementById(
      'icon-configuracoes'
    )
    ?.addEventListener(
      'click',
      () => {

        window.location.href =
          '../configuracoes/configuracoes.php';

      }
    );


  // ==========================================
  // LOGOUT
  // ==========================================

  document
    .getElementById(
      'icon-sair'
    )
    ?.addEventListener(
      'click',
      () => {

        logoutModal?.classList.add(
          'open'
        );

      }
    );


  document
    .getElementById(
      'cancel-logout'
    )
    ?.addEventListener(
      'click',
      () => {

        logoutModal?.classList.remove(
          'open'
        );

      }
    );


  document
    .getElementById(
      'confirm-logout'
    )
    ?.addEventListener(
      'click',
      () => {

        window.location.href =
          '../login/logout.php';

      }
    );


  // ==========================================
  // FECHAR LOGOUT CLICANDO FORA
  // ==========================================

  logoutModal?.addEventListener(
    'click',
    (event) => {

      if (
        event.target ===
        logoutModal
      ) {

        logoutModal.classList.remove(
          'open'
        );

      }

    }
  );

});
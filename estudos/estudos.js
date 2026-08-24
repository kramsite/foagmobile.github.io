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

  const subjectModalTitle =
    document.getElementById('subject-modal-title');

  const subjectModalDescription =
    document.getElementById('subject-modal-description');

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

  const UPDATE_MATERIA_URL =
    window.MATERIAS_UPDATE_URL ||
    'editar_materia.php';

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


  // ==========================================
  // DADOS DO POMODORO
  // ==========================================

  const pomodoroData =
    window.POMODORO_DATA &&
    typeof window.POMODORO_DATA === 'object'
      ? window.POMODORO_DATA
      : {
          sessions: []
        };


  const sessoesPomodoro =
    Array.isArray(
      pomodoroData.sessions
    )
      ? pomodoroData.sessions
      : [];


  // ==========================================
  // DADOS DOS FLASHCARDS
  // ==========================================

  const flashcardsData =
    window.FLASHCARDS_DATA &&
    typeof window.FLASHCARDS_DATA === 'object'
      ? window.FLASHCARDS_DATA
      : {
          baralhos: []
        };


  const baralhos =
    Array.isArray(
      flashcardsData.baralhos
    )
      ? flashcardsData.baralhos
      : [];


  let subjectsCount = 0;

  let toastTimer = null;

  let materiaParaExcluir = null;

  let cardParaExcluir = null;

  let materiaEmEdicao = null;


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
  // NORMALIZAR NOME DA MATÉRIA
  // ==========================================

  function normalizarMateria(
    valor
  ) {

    return String(
      valor || ''
    )
      .trim()
      .toLocaleLowerCase(
        'pt-BR'
      );

  }


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
  // CALCULAR TEMPO ESTUDADO
  // ==========================================

  function getMinutosEstudados(
    nomeMateria
  ) {

    const nomeNormalizado =
      normalizarMateria(
        nomeMateria
      );


    let totalMinutos =
      0;


    sessoesPomodoro.forEach(
      (sessao) => {

        const disciplina =
          sessao.discipline ??
          sessao.disciplina ??
          sessao.materia ??
          '';


        const modo =
          sessao.mode ??
          sessao.modo ??
          'focus';


        const minutos =
          Number(
            sessao.minutes ??
            sessao.minutos ??
            0
          );


        if (
          modo !== 'focus'
        ) {

          return;

        }


        if (
          normalizarMateria(
            disciplina
          ) !==
          nomeNormalizado
        ) {

          return;

        }


        if (
          Number.isFinite(
            minutos
          ) &&
          minutos > 0
        ) {

          totalMinutos +=
            minutos;

        }

      }
    );


    return Math.round(
      totalMinutos
    );

  }


  // ==========================================
  // FORMATAR TEMPO ESTUDADO
  // ==========================================

  function formatarTempoEstudado(
    minutos
  ) {

    if (
      !minutos ||
      minutos <= 0
    ) {

      return '0h estudadas';

    }


    const horas =
      Math.floor(
        minutos / 60
      );


    const minutosRestantes =
      minutos % 60;


    if (
      horas === 0
    ) {

      return `${minutosRestantes}min estudados`;

    }


    if (
      minutosRestantes === 0
    ) {

      return `${horas}h estudadas`;

    }


    return `${horas}h ${minutosRestantes}min estudadas`;

  }


  // ==========================================
  // CONTAR FLASHCARDS DA MATÉRIA
  // ==========================================

  function getTotalFlashcards(
    nomeMateria
  ) {

    const nomeNormalizado =
      normalizarMateria(
        nomeMateria
      );


    let total =
      0;


    baralhos.forEach(
      (baralho) => {

        if (
          normalizarMateria(
            baralho.materia
          ) !==
          nomeNormalizado
        ) {

          return;

        }


        const cartoes =
          Array.isArray(
            baralho.cartoes
          )
            ? baralho.cartoes
            : [];


        total +=
          cartoes.length;

      }
    );


    return total;

  }


  // ==========================================
  // ABRIR MODAL NOVA MATÉRIA
  // ==========================================

  const openSubjectModal = () => {

    if (!subjectModal) {
      return;
    }


    materiaEmEdicao =
      null;


    if (subjectForm) {

      subjectForm.reset();

    }


    if (subjectColor) {

      subjectColor.value =
        '#38a5ff';

    }


    if (subjectIcon) {

      subjectIcon.value =
        'fa-book';

    }


    if (subjectModalTitle) {

      subjectModalTitle.textContent =
        'Nova matéria';

    }


    if (subjectModalDescription) {

      subjectModalDescription.textContent =
        'Escolha um nome, uma cor e um ícone para identificar a matéria.';

    }


    if (submitButton) {

      submitButton.innerHTML = `
        <i class="fa-solid fa-plus"></i>
        Adicionar
      `;

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
  // ABRIR MODAL EDITAR MATÉRIA
  // ==========================================

  const openEditSubjectModal = (
    materia
  ) => {

    if (
      !subjectModal ||
      !materia
    ) {

      return;

    }


    materiaEmEdicao =
      materia;


    if (subjectName) {

      subjectName.value =
        materia.nome ||
        '';

    }


    if (subjectColor) {

      subjectColor.value =
        materia.cor ||
        '#94a3b8';

    }


    if (subjectIcon) {

      const iconeAtual =
        materia.icone ||
        'fa-circle-question';


      const existeOpcao =
        Array.from(
          subjectIcon.options
        ).some(
          (option) => {

            return (
              option.value ===
              iconeAtual
            );

          }
        );


      if (!existeOpcao) {

        const option =
          document.createElement(
            'option'
          );


        option.value =
          iconeAtual;


        option.textContent =
          'Ícone atual';


        subjectIcon.appendChild(
          option
        );

      }


      subjectIcon.value =
        iconeAtual;

    }


    if (subjectModalTitle) {

      subjectModalTitle.textContent =
        'Editar matéria';

    }


    if (subjectModalDescription) {

      subjectModalDescription.textContent =
        'Altere o nome, a cor ou o ícone. O vínculo com o boletim será mantido.';

    }


    if (submitButton) {

      submitButton.innerHTML = `
        <i class="fa-solid fa-check"></i>
        Salvar alterações
      `;

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
  // FECHAR MODAL DE MATÉRIA
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


    materiaEmEdicao =
      null;


    if (subjectForm) {

      subjectForm.reset();

    }


    if (subjectColor) {

      subjectColor.value =
        '#38a5ff';

    }


    if (subjectIcon) {

      subjectIcon.value =
        'fa-book';

    }


    if (subjectModalTitle) {

      subjectModalTitle.textContent =
        'Nova matéria';

    }


    if (subjectModalDescription) {

      subjectModalDescription.textContent =
        'Escolha um nome, uma cor e um ícone para identificar a matéria.';

    }


    if (
      submitButton &&
      !submitButton.disabled
    ) {

      submitButton.innerHTML = `
        <i class="fa-solid fa-plus"></i>
        Adicionar
      `;

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


      materias =
        materias.filter(
          (item) => {

            return (
              item.id !==
              materia.id
            );

          }
        );


      card.remove();


      updateSubjectsState();


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
  // CRIAR CARD DA MATÉRIA
  // ==========================================

  const createSubjectCard = (
    materia
  ) => {

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
      materia.id ||
      '';


    card.style.setProperty(
      '--subject-color',
      materia.cor ||
      '#94a3b8'
    );


    const minutosEstudados =
      getMinutosEstudados(
        materia.nome
      );


    const tempoEstudado =
      formatarTempoEstudado(
        minutosEstudados
      );


    const totalFlashcards =
      getTotalFlashcards(
        materia.nome
      );


    card.innerHTML = `
      <div class="subject-card-top">

        <div class="subject-card-icon">

          <i
            class="fa-solid ${materia.icone || 'fa-circle-question'}"
          ></i>

        </div>

        <h3></h3>

      </div>


      <div class="subject-card-meta">

        <span class="subject-study-time">

          <i class="fa-solid fa-clock"></i>

          ${tempoEstudado}

        </span>


        <span class="subject-flashcards-count">

          <i class="fa-solid fa-layer-group"></i>

          ${totalFlashcards}

          ${
            totalFlashcards === 1
              ? 'flashcard'
              : 'flashcards'
          }

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
    // ÁREA DOS BOTÕES
    // ==================================

    const actions =
      document.createElement(
        'div'
      );


    actions.className =
      'subject-card-actions';


    // ==================================
    // BOTÃO EDITAR
    // ==================================

    const editButton =
      document.createElement(
        'button'
      );


    editButton.type =
      'button';


    editButton.className =
      'subject-edit-btn';


    editButton.title =
      'Editar matéria';


    editButton.setAttribute(
      'aria-label',
      `Editar ${materia.nome}`
    );


    editButton.innerHTML = `
      <i class="fa-regular fa-pen-to-square"></i>
    `;


    editButton.addEventListener(
      'click',
      (event) => {

        event.preventDefault();

        event.stopPropagation();


        openEditSubjectModal(
          materia
        );

      }
    );


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


    actions.appendChild(
      editButton
    );


    actions.appendChild(
      deleteButton
    );


    card.appendChild(
      actions
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
  // BOTÕES ABRIR NOVA MATÉRIA
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
  // MODAL DE EXCLUSÃO
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
  // SALVAR / EDITAR MATÉRIA
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
      // EVITAR MATÉRIA REPETIDA
      // ==================================

      const existe =
        materias.some(
          (materia) => {

            const mesmoNome =
              normalizarMateria(
                materia.nome
              ) ===
              normalizarMateria(
                nome
              );


            const mesmaMateria =
              materiaEmEdicao &&
              String(
                materia.id
              ) ===
              String(
                materiaEmEdicao.id
              );


            return (
              mesmoNome &&
              !mesmaMateria
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
      // DESCOBRIR SE É EDIÇÃO OU CRIAÇÃO
      // ==================================

      const editando =
        Boolean(
          materiaEmEdicao
        );


      const materiaId =
        materiaEmEdicao?.id ||
        null;


      const url =
        editando
          ? UPDATE_MATERIA_URL
          : SAVE_MATERIA_URL;


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

        const payload = {

          nome:
            nome,

          cor:
            cor,

          icone:
            icone

        };


        if (editando) {

          payload.id =
            materiaId;

        }


        const response =
          await fetch(
            url,
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
                JSON.stringify(
                  payload
                )

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
        // SE ESTIVER EDITANDO
        // ==================================

        if (editando) {

          materias =
            materias.map(
              (materia) => {

                if (
                  String(
                    materia.id
                  ) ===
                  String(
                    materiaId
                  )
                ) {

                  return (
                    data.materia
                  );

                }


                return materia;

              }
            );


          carregarMaterias();


          showToast(
            `Matéria “${data.materia.nome}” atualizada.`
          );

        }


        // ==================================
        // SE FOR NOVA MATÉRIA
        // ==================================

        else {

          materias.push(
            data.materia
          );


          createSubjectCard(
            data.materia
          );


          updateSubjectsState();


          showToast(
            `Matéria “${data.materia.nome}” adicionada.`
          );

        }


        closeSubjectModal();


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


          if (
            subjectModal?.classList.contains(
              'open'
            )
          ) {

            submitButton.innerHTML =
              textoBotaoOriginal;

          }

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
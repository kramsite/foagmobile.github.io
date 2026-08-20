document.addEventListener(
  'DOMContentLoaded',
  () => {

    // ==========================================
    // DADOS
    // ==========================================

    const baralho =
      window.BARALHO_DATA &&
      typeof window.BARALHO_DATA === 'object'
        ? window.BARALHO_DATA
        : {
            cartoes: []
          };


    const BARALHO_ID =
      window.BARALHO_ID || '';


    const SAVE_URL =
      window.CARTAO_SAVE_URL ||
      'salvar_cartao.php';


    const BARALHO_COR =
      window.BARALHO_COR ||
      '#38a5ff';


    let cartoes =
      Array.isArray(baralho.cartoes)
        ? baralho.cartoes
        : [];


    // ==========================================
    // ELEMENTOS
    // ==========================================

    const cardsGrid =
      document.getElementById(
        'cards-grid'
      );

    const cardsEmpty =
      document.getElementById(
        'cards-empty'
      );

    const cardsTotal =
      document.getElementById(
        'cards-total'
      );

    const cardModal =
      document.getElementById(
        'card-modal'
      );

    const cardForm =
      document.getElementById(
        'card-form'
      );

    const cardQuestion =
      document.getElementById(
        'card-question'
      );

    const cardAnswer =
      document.getElementById(
        'card-answer'
      );

    const submitButton =
      cardForm?.querySelector(
        'button[type="submit"]'
      );

    const toast =
      document.getElementById(
        'toast'
      );

    const logoutModal =
      document.getElementById(
        'logout-modal'
      );


    let toastTimer = null;


    // ==========================================
    // TOAST
    // ==========================================

    function showToast(
      message
    ) {

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

    }


    // ==========================================
    // DATA
    // ==========================================

    function formatDate(
      value
    ) {

      if (!value) {
        return '';
      }


      // Formato vindo do PHP:
      // 2026-08-20 08:30:00

      const date =
        new Date(
          String(value)
            .replace(
              ' ',
              'T'
            )
        );


      if (
        Number.isNaN(
          date.getTime()
        )
      ) {

        return '';

      }


      return (
        date.toLocaleDateString(
          'pt-BR'
        )
      );

    }


    // ==========================================
    // ESTADO DA LISTA
    // ==========================================

    function updateCardsState() {

      const quantidade =
        cartoes.length;


      if (cardsTotal) {

        cardsTotal.textContent =
          quantidade;

      }


      if (
        quantidade > 0
      ) {

        if (cardsGrid) {
          cardsGrid.hidden =
            false;
        }


        if (cardsEmpty) {
          cardsEmpty.hidden =
            true;
        }

      } else {

        if (cardsGrid) {
          cardsGrid.hidden =
            true;
        }


        if (cardsEmpty) {
          cardsEmpty.hidden =
            false;
        }

      }

    }


    // ==========================================
    // CRIAR CARTÃO VISUAL
    // ==========================================

    function createCardElement(
      cartao
    ) {

      const card =
        document.createElement(
          'article'
        );


      card.className =
        'flash-card-item';


      card.dataset.id =
        cartao.id || '';


      card.style.setProperty(
        '--deck-color',
        BARALHO_COR
      );


      // ==========================
      // PERGUNTA
      // ==========================

      const questionBlock =
        document.createElement(
          'div'
        );


      questionBlock.className =
        'card-block';


      const questionLabel =
        document.createElement(
          'div'
        );


      questionLabel.className =
        'card-label';


      questionLabel.innerHTML = `
        <i class="fa-regular fa-circle-question"></i>
        Pergunta
      `;


      const questionText =
        document.createElement(
          'div'
        );


      questionText.className =
        'card-text';


      questionText.textContent =
        cartao.pergunta || '';


      questionBlock.appendChild(
        questionLabel
      );


      questionBlock.appendChild(
        questionText
      );


      // ==========================
      // RESPOSTA
      // ==========================

      const answerBlock =
        document.createElement(
          'div'
        );


      answerBlock.className =
        'card-block';


      const answerLabel =
        document.createElement(
          'div'
        );


      answerLabel.className =
        'card-label';


      answerLabel.innerHTML = `
        <i class="fa-regular fa-lightbulb"></i>
        Resposta
      `;


      const answerText =
        document.createElement(
          'div'
        );


      answerText.className =
        'card-text';


      answerText.textContent =
        cartao.resposta || '';


      answerBlock.appendChild(
        answerLabel
      );


      answerBlock.appendChild(
        answerText
      );


      // ==========================
      // RODAPÉ
      // ==========================

      const footer =
        document.createElement(
          'div'
        );


      footer.className =
        'card-footer';


      const created =
        document.createElement(
          'span'
        );


      created.className =
        'card-created';


      const dataCriacao =
        formatDate(
          cartao.criado_em
        );


      created.textContent =
        dataCriacao
          ? `Criado em ${dataCriacao}`
          : '';


      const actions =
        document.createElement(
          'div'
        );


      actions.className =
        'card-actions';


      // EDITAR
      const editBtn =
        document.createElement(
          'button'
        );


      editBtn.type =
        'button';


      editBtn.className =
        'card-action-btn';


      editBtn.title =
        'Editar cartão';


      editBtn.innerHTML = `
        <i class="fa-solid fa-pen"></i>
      `;


      editBtn.addEventListener(
        'click',
        () => {

          showToast(
            'A edição dos cartões será adicionada na próxima etapa.'
          );

        }
      );


      // EXCLUIR
      const deleteBtn =
        document.createElement(
          'button'
        );


      deleteBtn.type =
        'button';


      deleteBtn.className =
        'card-action-btn';


      deleteBtn.title =
        'Excluir cartão';


      deleteBtn.innerHTML = `
        <i class="fa-regular fa-trash-can"></i>
      `;


      deleteBtn.addEventListener(
        'click',
        () => {

          showToast(
            'A exclusão será adicionada na próxima etapa.'
          );

        }
      );


      actions.appendChild(
        editBtn
      );


      actions.appendChild(
        deleteBtn
      );


      footer.appendChild(
        created
      );


      footer.appendChild(
        actions
      );


      // ==========================
      // MONTAR
      // ==========================

      card.appendChild(
        questionBlock
      );


      card.appendChild(
        answerBlock
      );


      card.appendChild(
        footer
      );


      return card;

    }


    // ==========================================
    // RENDERIZAR CARTÕES
    // ==========================================

    function renderCards() {

      if (!cardsGrid) {
        return;
      }


      cardsGrid.innerHTML =
        '';


      cartoes.forEach(
        (cartao) => {

          const element =
            createCardElement(
              cartao
            );


          cardsGrid.appendChild(
            element
          );

        }
      );


      updateCardsState();

    }


    // ==========================================
    // ABRIR MODAL
    // ==========================================

    function openCardModal() {

      if (!cardModal) {
        return;
      }


      cardModal.classList.add(
        'open'
      );


      cardModal.setAttribute(
        'aria-hidden',
        'false'
      );


      setTimeout(
        () => {

          cardQuestion?.focus();

        },
        50
      );

    }


    // ==========================================
    // FECHAR MODAL
    // ==========================================

    function closeCardModal() {

      if (!cardModal) {
        return;
      }


      cardModal.classList.remove(
        'open'
      );


      cardModal.setAttribute(
        'aria-hidden',
        'true'
      );


      cardForm?.reset();

    }


    // ==========================================
    // BOTÕES DO MODAL
    // ==========================================

    document
      .getElementById(
        'open-card-modal'
      )
      ?.addEventListener(
        'click',
        openCardModal
      );


    document
      .getElementById(
        'open-card-modal-empty'
      )
      ?.addEventListener(
        'click',
        openCardModal
      );


    document
      .getElementById(
        'close-card-modal'
      )
      ?.addEventListener(
        'click',
        closeCardModal
      );


    document
      .getElementById(
        'cancel-card-modal'
      )
      ?.addEventListener(
        'click',
        closeCardModal
      );


    cardModal?.addEventListener(
      'click',
      (event) => {

        if (
          event.target ===
          cardModal
        ) {

          closeCardModal();

        }

      }
    );


    // ==========================================
    // CRIAR CARTÃO
    // ==========================================

    cardForm?.addEventListener(
      'submit',
      async (event) => {

        event.preventDefault();


        const pergunta =
          cardQuestion?.value
            ?.trim();


        const resposta =
          cardAnswer?.value
            ?.trim();


        if (!pergunta) {

          showToast(
            'Digite a pergunta do cartão.'
          );


          cardQuestion?.focus();

          return;

        }


        if (!resposta) {

          showToast(
            'Digite a resposta do cartão.'
          );


          cardAnswer?.focus();

          return;

        }


        const dados = {

          baralho_id:
            BARALHO_ID,

          pergunta:
            pergunta,

          resposta:
            resposta

        };


        const originalText =
          submitButton?.innerHTML;


        if (submitButton) {

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
              SAVE_URL,
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
                    dados
                  )

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
              'Não foi possível salvar o cartão.'
            );

          }


          if (result.cartao) {

            cartoes.push(
              result.cartao
            );

          }


          closeCardModal();


          renderCards();


          showToast(
            'Cartão adicionado com sucesso!'
          );


        } catch (error) {

          console.error(
            'Erro ao salvar cartão:',
            error
          );


          showToast(
            error.message ||
            'Erro ao salvar cartão.'
          );


        } finally {

          if (submitButton) {

            submitButton.disabled =
              false;


            submitButton.innerHTML =
              originalText;

          }

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
          event.key !==
          'Escape'
        ) {

          return;

        }


        if (
          cardModal?.classList.contains(
            'open'
          )
        ) {

          closeCardModal();

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
            '../../perfil/perfil.php';

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
            '../../configuracoes/configuracoes.php';

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
            '../../login/logout.php';

        }
      );


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


    // ==========================================
    // INICIALIZAÇÃO
    // ==========================================

    renderCards();

  }
);
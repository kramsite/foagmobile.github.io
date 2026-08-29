// ==========================================
// FOAG — MODAL GLOBAL DE ESTRELAS
// ==========================================

(function () {

    // ======================================
    // CRIAR MODAL
    // ======================================

    function criarModalEstrelas() {

        let modal =
            document.getElementById(
                'modal-estrelas'
            );


        if (modal) {
            return modal;
        }


        modal =
            document.createElement(
                'div'
            );


        modal.id =
            'modal-estrelas';


        modal.innerHTML = `
            <div class="modal-estrelas-box">

                <span class="modal-estrelas-brilho um">
                    ✨
                </span>

                <span class="modal-estrelas-brilho dois">
                    ⭐
                </span>

                <span class="modal-estrelas-brilho tres">
                    ✨
                </span>


                <div class="modal-estrelas-icone">
                    ⭐
                </div>


                <h2>
                    Parabéns!
                </h2>


                <p>
                    Você ganhou
                </p>


                <strong
                    class="modal-estrelas-quantidade"
                    id="modal-estrelas-quantidade"
                >
                    ⭐ 0 estrelas
                </strong>


                <p id="modal-estrelas-mensagem">
                    Continue assim! :)
                </p>


                <button
                    type="button"
                    class="modal-estrelas-fechar"
                >
                    Continuar
                </button>

            </div>
        `;


        document.body.appendChild(
            modal
        );


        // ==================================
        // FECHAR
        // ==================================

        modal
            .querySelector(
                '.modal-estrelas-fechar'
            )
            ?.addEventListener(
                'click',
                () => {

                    modal.classList.remove(
                        'ativo'
                    );
                }
            );


        // ==================================
        // CLICAR FORA
        // ==================================

        modal.addEventListener(
            'click',
            evento => {

                if (
                    evento.target === modal
                ) {

                    modal.classList.remove(
                        'ativo'
                    );
                }
            }
        );


        return modal;
    }


    // ======================================
    // MOSTRAR MODAL
    // ======================================

    window.mostrarModalEstrelas =
        function (
            quantidade,
            mensagem = ''
        ) {

            quantidade =
                Number(
                    quantidade
                ) || 0;


            if (
                quantidade <= 0
            ) {

                return;
            }


            const modal =
                criarModalEstrelas();


            const quantidadeEl =
                modal.querySelector(
                    '#modal-estrelas-quantidade'
                );


            const mensagemEl =
                modal.querySelector(
                    '#modal-estrelas-mensagem'
                );


            if (quantidadeEl) {

                quantidadeEl.textContent =

                    quantidade === 1

                        ? '⭐ 1 estrela'

                        : `⭐ ${quantidade} estrelas`;
            }


            if (mensagemEl) {

                mensagemEl.textContent =

                    mensagem ||

                    'Continue assim! :)';
            }


            modal.classList.add(
                'ativo'
            );
        };


})();
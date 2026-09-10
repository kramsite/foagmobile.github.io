/* =========================================================
   FOAG - LIBRAS v26
   - Mostra SOMENTE um ícone flutuante.
   - NÃO carrega o VLibras ao abrir a página.
   - O VLibras só é carregado e aberto depois do clique.
   ========================================================= */

(function () {
    'use strict';

    const CHAVE_LIBRAS = 'foag_libras_v19';
    const ID_BOTAO = 'foag-botao-libras';
    const ID_SCRIPT = 'foag-script-vlibras';
    const ID_CONTAINER = 'foag-container-vlibras';

    // Se não foi ativado e salvo, não mostra nada.
    if (localStorage.getItem(CHAVE_LIBRAS) !== '1') {
        document.getElementById(ID_BOTAO)?.remove();
        document.getElementById(ID_CONTAINER)?.remove();
        return;
    }

    // Evita duplicação.
    if (document.getElementById(ID_BOTAO)) {
        return;
    }

    // =====================================================
    // BOTÃO FLUTUANTE - SOMENTE ÍCONE
    // =====================================================
    const botao = document.createElement('button');

    botao.id = ID_BOTAO;
    botao.type = 'button';
    botao.title = 'Libras';
    botao.setAttribute('aria-label', 'Abrir tradução em Libras');

    /*
     * Usa o ícone do Font Awesome caso esteja disponível.
     * O FOAG já carrega Font Awesome nas configurações.
     */
    botao.innerHTML = `
        <i class="fa-solid fa-hands-asl-interpreting" aria-hidden="true"></i>
    `;

    const style = document.createElement('style');

    style.id = 'foag-estilo-libras';

    style.textContent = `
        #${ID_BOTAO} {
            position: fixed;
            right: 22px;
            bottom: 22px;
            z-index: 2147483000;

            width: 58px;
            height: 58px;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 0;

            border: 0;
            border-radius: 50%;

            background: #1261c9;
            color: #ffffff;

            font-size: 27px;

            cursor: pointer;

            box-shadow:
                0 5px 18px rgba(0, 0, 0, 0.28);

            transition:
                transform .18s ease,
                background .18s ease,
                box-shadow .18s ease;
        }

        #${ID_BOTAO}:hover {
            transform: translateY(-2px) scale(1.04);
            background: #0f55b4;
            box-shadow:
                0 7px 22px rgba(0, 0, 0, 0.32);
        }

        #${ID_BOTAO}:focus-visible {
            outline: 3px solid #ffffff;
            outline-offset: 3px;
        }

        #${ID_BOTAO}:disabled {
            opacity: .7;
            cursor: wait;
        }

        @media (max-width: 600px) {
            #${ID_BOTAO} {
                right: 14px;
                bottom: 14px;
                width: 54px;
                height: 54px;
                font-size: 25px;
            }
        }
    `;

    document.head.appendChild(style);
    document.body.appendChild(botao);

    let carregando = false;
    let iniciado = false;

    // =====================================================
    // ESTRUTURA OFICIAL DO VLIBRAS
    // SÓ É CRIADA DEPOIS DO CLIQUE
    // =====================================================
    function criarEstrutura() {
        let container = document.getElementById(ID_CONTAINER);

        if (container) {
            return container;
        }

        container = document.createElement('div');
        container.id = ID_CONTAINER;
        container.setAttribute('vw', '');
        container.className = 'enabled';

        container.innerHTML = `
            <div vw-access-button class="active"></div>

            <div vw-plugin-wrapper>
                <div class="vw-plugin-top-wrapper"></div>
            </div>
        `;

        document.body.appendChild(container);

        return container;
    }

    function clicarNoBotaoOficial() {
        let tentativas = 0;

        const procurar = setInterval(function () {
            tentativas++;

            const botaoOficial =
                document.querySelector(
                    `#${ID_CONTAINER} [vw-access-button]`
                );

            if (botaoOficial) {
                clearInterval(procurar);

                /*
                 * O plugin só chegou aqui porque o usuário
                 * clicou primeiro no botão flutuante do FOAG.
                 */
                botaoOficial.click();

                // Depois, o próprio botão oficial fica disponível.
                botao.remove();
            }

            if (tentativas >= 30) {
                clearInterval(procurar);
                botao.disabled = false;
                carregando = false;
            }
        }, 100);
    }

    function iniciarWidget() {
        if (!window.VLibras || iniciado) {
            return;
        }

        criarEstrutura();

        try {
            new window.VLibras.Widget(
                'https://vlibras.gov.br/app'
            );

            iniciado = true;

            clicarNoBotaoOficial();

        } catch (erro) {
            console.error(
                'FOAG: erro ao iniciar VLibras:',
                erro
            );

            botao.disabled = false;
            carregando = false;
        }
    }

    // =====================================================
    // O VLIBRAS SÓ É CARREGADO AQUI, APÓS CLIQUE
    // =====================================================
    function carregarVLibras() {
        if (carregando || iniciado) {
            return;
        }

        carregando = true;
        botao.disabled = true;

        if (window.VLibras) {
            iniciarWidget();
            return;
        }

        let script =
            document.getElementById(ID_SCRIPT);

        if (script) {
            script.addEventListener(
                'load',
                iniciarWidget,
                { once: true }
            );
            return;
        }

        script = document.createElement('script');

        script.id = ID_SCRIPT;
        script.src =
            'https://vlibras.gov.br/app/vlibras-plugin.js';

        script.async = true;

        script.onload = iniciarWidget;

        script.onerror = function () {
            carregando = false;
            botao.disabled = false;

            console.error(
                'FOAG: não foi possível carregar o VLibras.'
            );
        };

        document.body.appendChild(script);
    }

    botao.addEventListener(
        'click',
        carregarVLibras
    );

})();

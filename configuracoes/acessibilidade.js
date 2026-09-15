/* =========================================================
   FOAG - VLIBRAS v28
   COMPORTAMENTO CORRETO:
   - Libras desativada: não aparece nada.
   - Libras ativada: aparece DIRETO o botão OFICIAL do VLibras.
   - Um único clique no botão oficial abre o tradutor.
   - Não existe botão intermediário do FOAG.
   ========================================================= */

(function () {
    'use strict';

    const CHAVE_LIBRAS = 'foag_libras_v19';
    const SCRIPT_ID = 'foag-vlibras-script-oficial';
    const CONTAINER_ID = 'foag-vlibras-oficial';

    // =====================================================
    // LIMPAR RESTOS DAS VERSÕES ANTIGAS
    // =====================================================
    document.getElementById('foag-botao-libras')?.remove();
    document.getElementById('foag-container-vlibras')?.remove();
    document.getElementById('foag-vlibras')?.remove();
    document.getElementById('foag-vlibras-style')?.remove();
    document.getElementById('foag-vlibras-style-v27')?.remove();
    document.getElementById('foag-estilo-libras')?.remove();
    document.getElementById('foag-estilo-botao-libras')?.remove();

    // =====================================================
    // LIBRAS DESATIVADA
    // =====================================================
    if (localStorage.getItem(CHAVE_LIBRAS) !== '1') {
        document.querySelectorAll('[vw]').forEach(function (elemento) {
            elemento.remove();
        });

        return;
    }

    // =====================================================
    // EVITA DUPLICAÇÃO
    // =====================================================
    if (document.getElementById(CONTAINER_ID)) {
        return;
    }

    /*
     * Se alguma versão anterior tiver deixado uma estrutura
     * [vw] na página, remove antes de criar a oficial.
     */
    document.querySelectorAll('[vw]').forEach(function (elemento) {
        elemento.remove();
    });

    // =====================================================
    // ESTRUTURA OFICIAL DO VLIBRAS
    //
    // IMPORTANTE:
    // vw-access-button começa com "active":
    // isso mostra DIRETO o ícone oficial.
    //
    // vw-plugin-wrapper NÃO começa "active":
    // por isso a janela começa fechada.
    // =====================================================
    const container = document.createElement('div');

    container.id = CONTAINER_ID;
    container.setAttribute('vw', '');
    container.className = 'enabled';

    container.innerHTML = `
        <div vw-access-button class="active"></div>

        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    `;

    document.body.appendChild(container);

    // =====================================================
    // INICIALIZAÇÃO
    // =====================================================
    let inicializado = false;

    function iniciarWidget() {
        if (inicializado || !window.VLibras) {
            return;
        }

        inicializado = true;

        try {
            new window.VLibras.Widget(
                'https://vlibras.gov.br/app'
            );

            /*
             * O Widget oficial monta o botão no evento load.
             * Se o script terminou de carregar depois do load
             * da página, disparamos o evento para concluir
             * a montagem imediatamente.
             */
            if (document.readyState === 'complete') {
                setTimeout(function () {
                    window.dispatchEvent(new Event('load'));
                }, 0);
            }

            console.log(
                'FOAG: botão oficial do VLibras carregado.'
            );

        } catch (erro) {
            inicializado = false;

            console.error(
                'FOAG: erro ao iniciar VLibras:',
                erro
            );
        }
    }

    // =====================================================
    // SCRIPT OFICIAL
    // =====================================================
    if (window.VLibras) {
        iniciarWidget();
        return;
    }

    let script = document.getElementById(SCRIPT_ID);

    if (!script) {
        script = document.createElement('script');

        script.id = SCRIPT_ID;
        script.src =
            'https://vlibras.gov.br/app/vlibras-plugin.js';

        script.async = true;

        script.onload = iniciarWidget;

        script.onerror = function () {
            console.error(
                'FOAG: não foi possível carregar o VLibras.'
            );
        };

        document.body.appendChild(script);
    } else {
        script.addEventListener(
            'load',
            iniciarWidget,
            { once: true }
        );
    }

})();

// ==========================================
// FOAG - ACESSIBILIDADE GLOBAL
// ==========================================

console.log('✅ acessibilidade.js carregou');

document.addEventListener('DOMContentLoaded', function () {

    const salvo =
        localStorage.getItem(
            'foag_acessibilidade'
        );

    let configuracoes = {
        libras: false
    };

    if (salvo) {

        try {

            configuracoes = {
                ...configuracoes,
                ...JSON.parse(salvo)
            };

        } catch (erro) {

            console.error(
                'Erro ao carregar acessibilidade:',
                erro
            );

        }
    }


    // ==========================================
    // LIBRAS
    // ==========================================

    if (configuracoes.libras) {

        ativarVLibras();

    }

});


// ==========================================
// ATIVAR VLIBRAS
// ==========================================

function ativarVLibras() {

    // Evita adicionar duas vezes
    if (
        document.querySelector('[vw]')
    ) {
        return;
    }


    // ======================================
    // CONTAINER
    // ======================================

    const container =
        document.createElement('div');

    container.setAttribute(
        'vw',
        ''
    );

    container.classList.add(
        'enabled'
    );

    container.innerHTML = `
        <div
            vw-access-button
            class="active">
        </div>

        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    `;

    document.body.appendChild(
        container
    );


    // ======================================
    // SCRIPT DO VLIBRAS
    // ======================================

    const scriptExistente =
        document.querySelector(
            'script[src*="vlibras-plugin.js"]'
        );

    if (scriptExistente) {

        iniciarWidgetVLibras();
        return;

    }


    const script =
        document.createElement(
            'script'
        );

    script.src =
        'https://vlibras.gov.br/app/vlibras-plugin.js';

    script.onload =
        function () {

            iniciarWidgetVLibras();

        };

    document.body.appendChild(
        script
    );

}


// ==========================================
// INICIAR WIDGET
// ==========================================

function iniciarWidgetVLibras() {

    if (
        window.VLibras &&
        window.VLibras.Widget
    ) {

        new window.VLibras.Widget(
            'https://vlibras.gov.br/app'
        );

    }

}


// ==========================================
// DESATIVAR VLIBRAS
// ==========================================

function desativarVLibras() {

    const estavaAtivo =
        document.querySelector('[vw]') !== null;


    // ======================================
    // REMOVE ELEMENTOS
    // ======================================

    document
        .querySelectorAll('[vw]')
        .forEach(
            function (elemento) {

                elemento.remove();

            }
        );


    // ======================================
    // REMOVE SCRIPT
    // ======================================

    document
        .querySelectorAll(
            'script[src*="vlibras.gov.br"]'
        )
        .forEach(
            function (script) {

                script.remove();

            }
        );


    // ======================================
    // RECARREGA A PÁGINA
    // ======================================

    if (estavaAtivo) {

        setTimeout(
            function () {

                window.location.reload();

            },
            200
        );

    }

}


// ==========================================
// ATUALIZAR ACESSIBILIDADE
// ==========================================

window.atualizarAcessibilidade =
    function (configuracoes) {

        // ======================================
        // ATIVAR
        // ======================================

        if (
            configuracoes.libras
        ) {

            ativarVLibras();
            return;

        }


        // ======================================
        // DESATIVAR
        // ======================================

        desativarVLibras();

    };
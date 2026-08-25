// ==========================================
// FOAG - ACESSIBILIDADE GLOBAL
// ==========================================
console.log('✅ acessibilidade.js carregou');
document.addEventListener('DOMContentLoaded', function () {

    const salvo = localStorage.getItem('foag_acessibilidade');

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
            console.error('Erro ao carregar acessibilidade:', erro);
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

    if (document.querySelector('[vw]')) {
        return;
    }

    const container = document.createElement('div');

    container.setAttribute('vw', '');
    container.classList.add('enabled');

    container.innerHTML = `
        <div vw-access-button class="active"></div>

        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    `;

    document.body.appendChild(container);


    const script = document.createElement('script');

    script.src = 'https://vlibras.gov.br/app/vlibras-plugin.js';

    script.onload = function () {

        if (
            window.VLibras &&
            window.VLibras.Widget
        ) {
            new window.VLibras.Widget(
                'https://vlibras.gov.br/app'
            );
        }

    };

    document.body.appendChild(script);
}


// ==========================================
// ATUALIZAR ACESSIBILIDADE
// ==========================================

window.atualizarAcessibilidade = function (configuracoes) {

    if (configuracoes.libras) {

        ativarVLibras();

    } else {

        document
            .querySelectorAll('[vw]')
            .forEach(function (elemento) {
                elemento.remove();
            });

    }

};
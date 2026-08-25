// ==========================================
// FOAG - ACESSIBILIDADE GLOBAL
// ==========================================

console.log('✅ acessibilidade.js v13 carregou');

const FOAG_ACESSIBILIDADE_CHAVE =
    'foag_acessibilidade';


// ==========================================
// CARREGAR CONFIGURAÇÕES
// ==========================================

function carregarAcessibilidade() {

    const padrao = {
        libras: false
    };

    const salvo =
        localStorage.getItem(
            FOAG_ACESSIBILIDADE_CHAVE
        );

    if (!salvo) {
        return padrao;
    }

    try {

        const dados =
            JSON.parse(salvo);

        return {
            ...padrao,
            ...dados
        };

    } catch (erro) {

        console.error(
            '❌ Erro ao carregar acessibilidade:',
            erro
        );

        return padrao;

    }

}


// ==========================================
// CONVERTER LIBRAS PARA BOOLEANO
// ==========================================

function librasEstaAtivo(valor) {

    return (
        valor === true ||
        valor === 1 ||
        valor === '1' ||
        valor === 'true'
    );

}


// ==========================================
// MOSTRAR / ESCONDER VLIBRAS
// ==========================================

function aplicarEstadoVLibras(
    configuracoes
) {

    const ativo =
        librasEstaAtivo(
            configuracoes.libras
        );


    const widget =
        document.querySelector('[vw]');


    // ======================================
    // CONTAINER PRINCIPAL
    // ======================================

    if (widget) {

        if (ativo) {

            widget.style.removeProperty(
                'display'
            );

            widget.removeAttribute(
                'aria-hidden'
            );

        } else {

            widget.style.setProperty(
                'display',
                'none',
                'important'
            );

            widget.setAttribute(
                'aria-hidden',
                'true'
            );

        }

    }


    // ======================================
    // ELEMENTOS AUXILIARES
    // ======================================

    document
        .querySelectorAll('.vw-links')
        .forEach(
            function (elemento) {

                if (ativo) {

                    elemento.style
                        .removeProperty(
                            'display'
                        );

                } else {

                    elemento.style
                        .setProperty(
                            'display',
                            'none',
                            'important'
                        );

                }

            }
        );


    console.log(
        ativo
            ? '✅ VLibras visível'
            : '✅ VLibras oculto'
    );

}


// ==========================================
// ATUALIZAR ACESSIBILIDADE
// ==========================================

window.atualizarAcessibilidade =
    function (
        novasConfiguracoes
    ) {

        if (
            !novasConfiguracoes ||
            typeof novasConfiguracoes
                !== 'object'
        ) {

            return;

        }


        const atuais =
            carregarAcessibilidade();


        const configuracoes = {
            ...atuais,
            ...novasConfiguracoes
        };


        configuracoes.libras =
            librasEstaAtivo(
                configuracoes.libras
            );


        // ======================================
        // SALVAR
        // ======================================

        localStorage.setItem(
            FOAG_ACESSIBILIDADE_CHAVE,
            JSON.stringify(
                configuracoes
            )
        );


        // ======================================
        // APLICAR
        // ======================================

        aplicarEstadoVLibras(
            configuracoes
        );

    };


// ==========================================
// APLICAR ESTADO SALVO
// ==========================================

function aplicarAcessibilidadeInicial() {

    aplicarEstadoVLibras(
        carregarAcessibilidade()
    );

}


// ==========================================
// DOM CARREGADO
// ==========================================

document.addEventListener(
    'DOMContentLoaded',
    function () {

        aplicarAcessibilidadeInicial();

    }
);


// ==========================================
// PÁGINA COMPLETAMENTE CARREGADA
// ==========================================
//
// O VLibras termina a montagem no load.
// Aplicamos novamente depois.
// ==========================================

window.addEventListener(
    'load',
    function () {

        setTimeout(
            function () {

                aplicarAcessibilidadeInicial();

            },
            100
        );

    }
);


// ==========================================
// VLIBRAS CRIOU ELEMENTOS DEPOIS
// ==========================================

const observerVLibras =
    new MutationObserver(
        function (mutacoes) {

            const criouElemento =
                mutacoes.some(
                    function (mutacao) {

                        return (
                            mutacao.addedNodes &&
                            mutacao.addedNodes
                                .length > 0
                        );

                    }
                );


            if (criouElemento) {

                aplicarEstadoVLibras(
                    carregarAcessibilidade()
                );

            }

        }
    );


document.addEventListener(
    'DOMContentLoaded',
    function () {

        observerVLibras.observe(
            document.body,
            {
                childList: true,
                subtree: true
            }
        );

    }
);


// ==========================================
// SINCRONIZAR ENTRE ABAS
// ==========================================

window.addEventListener(
    'storage',
    function (evento) {

        if (
            evento.key ===
            FOAG_ACESSIBILIDADE_CHAVE
        ) {

            aplicarAcessibilidadeInicial();

        }

    }
);
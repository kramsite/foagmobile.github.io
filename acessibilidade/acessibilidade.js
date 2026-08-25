// ==========================================
// FOAG - ACESSIBILIDADE GLOBAL
// ==========================================

console.log('✅ acessibilidade.js carregou');


// ==========================================
// CONSTANTES
// ==========================================

const FOAG_ACESSIBILIDADE_CHAVE =
    'foag_acessibilidade';

const FOAG_VLIBRAS_BASE =
    'https://vlibras.gov.br/app';


// ==========================================
// CONFIGURAÇÃO PADRÃO
// ==========================================

const FOAG_ACESSIBILIDADE_PADRAO = {
    libras: false
};


// ==========================================
// CONTROLE INTERNO
// ==========================================

let vlibrasInicializado = false;

let tentativasVLibras = 0;

const MAX_TENTATIVAS_VLIBRAS = 30;


// ==========================================
// CONVERTER PARA BOOLEANO
// ==========================================

function valorBooleano(valor) {

    return (
        valor === true ||
        valor === 1 ||
        valor === '1' ||
        valor === 'true'
    );

}


// ==========================================
// CARREGAR CONFIGURAÇÕES SALVAS
// ==========================================

function carregarAcessibilidadeSalva() {

    let configuracoes = {
        ...FOAG_ACESSIBILIDADE_PADRAO
    };


    const salvo =
        localStorage.getItem(
            FOAG_ACESSIBILIDADE_CHAVE
        );


    if (!salvo) {

        return configuracoes;

    }


    try {

        const dadosSalvos =
            JSON.parse(salvo);


        if (
            dadosSalvos &&
            typeof dadosSalvos === 'object'
        ) {

            configuracoes = {
                ...configuracoes,
                ...dadosSalvos
            };

        }

    } catch (erro) {

        console.error(
            '❌ Erro ao carregar acessibilidade:',
            erro
        );

    }


    configuracoes.libras =
        valorBooleano(
            configuracoes.libras
        );


    return configuracoes;

}


// ==========================================
// SALVAR CONFIGURAÇÕES
// ==========================================

function salvarAcessibilidade(
    novasConfiguracoes
) {

    const atuais =
        carregarAcessibilidadeSalva();


    const configuracoes = {
        ...atuais,
        ...novasConfiguracoes
    };


    configuracoes.libras =
        valorBooleano(
            configuracoes.libras
        );


    localStorage.setItem(
        FOAG_ACESSIBILIDADE_CHAVE,
        JSON.stringify(
            configuracoes
        )
    );


    return configuracoes;

}


// ==========================================
// CRIAR ESTRUTURA DO VLIBRAS
// ==========================================

function criarEstruturaVLibras() {

    if (
        document.querySelector('[vw]')
    ) {

        return;

    }


    const container =
        document.createElement(
            'div'
        );


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

}


// ==========================================
// INICIAR WIDGET DO VLIBRAS
// ==========================================

function iniciarWidgetVLibras() {

    if (
        vlibrasInicializado ||
        window.__foagVLibrasInicializado
    ) {

        return true;

    }


    if (
        !window.VLibras ||
        !window.VLibras.Widget
    ) {

        return false;

    }


    try {

        new window.VLibras.Widget(
            FOAG_VLIBRAS_BASE
        );


        vlibrasInicializado =
            true;


        window.__foagVLibrasInicializado =
            true;


        tentativasVLibras =
            0;


        console.log(
            '✅ VLibras inicializado'
        );


        return true;

    } catch (erro) {

        console.error(
            '❌ Erro ao iniciar VLibras:',
            erro
        );


        return false;

    }

}


// ==========================================
// AGUARDAR SCRIPT OFICIAL DO VLIBRAS
// ==========================================

function aguardarVLibras() {

    if (
        iniciarWidgetVLibras()
    ) {

        return;

    }


    tentativasVLibras++;


    if (
        tentativasVLibras >=
        MAX_TENTATIVAS_VLIBRAS
    ) {

        console.error(
            '❌ O script oficial do VLibras não ficou disponível.'
        );


        tentativasVLibras =
            0;


        return;

    }


    setTimeout(
        aguardarVLibras,
        200
    );

}


// ==========================================
// ATIVAR VLIBRAS
// ==========================================

function ativarVLibras() {

    criarEstruturaVLibras();


    tentativasVLibras =
        0;


    aguardarVLibras();

}


// ==========================================
// DESATIVAR VLIBRAS
// ==========================================

function desativarVLibras() {

    const estavaAtivo =
        document.querySelector('[vw]')
        !== null;


    document
        .querySelectorAll('[vw]')
        .forEach(
            function (elemento) {

                elemento.remove();

            }
        );


    vlibrasInicializado =
        false;


    window.__foagVLibrasInicializado =
        false;


    tentativasVLibras =
        0;


    if (estavaAtivo) {

        setTimeout(
            function () {

                window.location.reload();

            },
            150
        );

    }

}


// ==========================================
// APLICAR CONFIGURAÇÃO SALVA
// ==========================================

function aplicarAcessibilidadeSalva() {

    const configuracoes =
        carregarAcessibilidadeSalva();


    if (
        configuracoes.libras === true
    ) {

        ativarVLibras();

    }

}


// ==========================================
// ATUALIZAR ACESSIBILIDADE
// ==========================================

window.atualizarAcessibilidade =
    function (configuracoes) {

        if (
            !configuracoes ||
            typeof configuracoes !==
                'object'
        ) {

            return;

        }


        const configuracoesSalvas =
            salvarAcessibilidade(
                configuracoes
            );


        if (
            configuracoesSalvas.libras
            === true
        ) {

            ativarVLibras();

        } else {

            desativarVLibras();

        }

    };


// ==========================================
// AO CARREGAR A PÁGINA
// ==========================================

document.addEventListener(
    'DOMContentLoaded',
    function () {

        aplicarAcessibilidadeSalva();

    }
);


// ==========================================
// AO VOLTAR PARA A PÁGINA
// ==========================================

window.addEventListener(
    'pageshow',
    function () {

        const configuracoes =
            carregarAcessibilidadeSalva();


        if (
            configuracoes.libras
                === true &&
            !document.querySelector('[vw]')
        ) {

            ativarVLibras();

        }

    }
);


// ==========================================
// SINCRONIZAR ENTRE ABAS
// ==========================================

window.addEventListener(
    'storage',
    function (evento) {

        if (
            evento.key !==
            FOAG_ACESSIBILIDADE_CHAVE
        ) {

            return;

        }


        const configuracoes =
            carregarAcessibilidadeSalva();


        if (
            configuracoes.libras === true
        ) {

            ativarVLibras();

        } else {

            desativarVLibras();

        }

    }
);
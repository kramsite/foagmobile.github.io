/* =========================================================
   FOAG — VLIBRAS GLOBAL COM PERSISTÊNCIA DE PAINEL
   Versão 22

   O que fica salvo:
   - foag_libras_v19:
       "1" = VLibras ativado nas Configurações
       "0" = VLibras desativado

   - foag_vlibras_aberto:
       "1" = painel do VLibras estava aberto
       "0" = painel estava fechado

   Assim, ao navegar entre páginas PHP, o widget é carregado
   novamente, mas o FOAG tenta restaurar automaticamente o
   estado aberto/fechado que o usuário deixou.
========================================================= */

(function () {
    'use strict';

    const CHAVE_ATIVO = 'foag_libras_v19';
    const CHAVE_ABERTO = 'foag_vlibras_aberto';
    const SCRIPT_ID = 'foag-vlibras-oficial-v22';
    const SCRIPT_URL = 'https://vlibras.gov.br/app/vlibras-plugin.js';

    let observerWrapper = null;
    let observerRoot = null;
    let restaurando = false;
    let widgetPreparado = false;

    /* =====================================================
       ESTADO SALVO
    ===================================================== */

    function librasAtiva() {
        return localStorage.getItem(CHAVE_ATIVO) === '1';
    }

    function painelDeveAbrir() {
        return localStorage.getItem(CHAVE_ABERTO) === '1';
    }

    function salvarPainelAberto(aberto) {
        localStorage.setItem(
            CHAVE_ABERTO,
            aberto ? '1' : '0'
        );
    }

    /* =====================================================
       ELEMENTOS DO WIDGET
    ===================================================== */

    function obterElementos() {
        const root = document.querySelector('[vw]');

        if (!root) {
            return {
                root: null,
                botao: null,
                wrapper: null
            };
        }

        return {
            root: root,
            botao: root.querySelector('[vw-access-button]'),
            wrapper: root.querySelector('[vw-plugin-wrapper]')
        };
    }

    function painelEstaAberto() {
        const { root, wrapper } = obterElementos();

        if (!root || !wrapper) {
            return false;
        }

        /*
         * No VLibras, o wrapper recebe a classe "active"
         * quando a janela está aberta.
         */
        return (
            wrapper.classList.contains('active') ||
            root.classList.contains('maximize')
        );
    }

    /* =====================================================
       SALVAR O ESTADO QUANDO O USUÁRIO ABRE/FECHA
    ===================================================== */

    function registrarEstadoAtual() {
        if (!librasAtiva()) {
            salvarPainelAberto(false);
            return;
        }

        salvarPainelAberto(
            painelEstaAberto()
        );
    }

    function observarWidget() {
        const { root, botao, wrapper } = obterElementos();

        if (!root || !wrapper) {
            return false;
        }

        if (widgetPreparado) {
            return true;
        }

        widgetPreparado = true;

        /*
         * Observa mudanças de classe e estilo do painel.
         * Isso detecta abertura, fechamento, maximização etc.
         */
        observerWrapper = new MutationObserver(function () {
            if (!restaurando) {
                registrarEstadoAtual();
            }
        });

        observerWrapper.observe(wrapper, {
            attributes: true,
            attributeFilter: ['class', 'style']
        });

        observerRoot = new MutationObserver(function () {
            if (!restaurando) {
                registrarEstadoAtual();
            }
        });

        observerRoot.observe(root, {
            attributes: true,
            attributeFilter: ['class', 'style']
        });

        /*
         * Salva também depois de cliques no botão principal,
         * porque algumas versões do widget atualizam a classe
         * alguns milissegundos depois do clique.
         */
        if (botao) {
            botao.addEventListener('click', function () {
                setTimeout(registrarEstadoAtual, 150);
                setTimeout(registrarEstadoAtual, 450);
            });
        }

        return true;
    }

    /* =====================================================
       RESTAURAR PAINEL ABERTO
    ===================================================== */

    function tentarRestaurarPainel(tentativa = 0) {
        if (!librasAtiva()) {
            salvarPainelAberto(false);
            return;
        }

        const { botao, wrapper } = obterElementos();

        /*
         * O script oficial pode demorar um pouco para construir
         * o botão e o wrapper. Aguarda até eles existirem.
         */
        if (!botao || !wrapper) {
            if (tentativa < 50) {
                setTimeout(function () {
                    tentarRestaurarPainel(tentativa + 1);
                }, 120);
            }
            return;
        }

        observarWidget();

        /*
         * Se o usuário deixou fechado, não fazemos nada.
         */
        if (!painelDeveAbrir()) {
            return;
        }

        /*
         * Se já abriu sozinho, terminou.
         */
        if (painelEstaAberto()) {
            return;
        }

        /*
         * Simula o mesmo clique que o usuário faria no botão
         * oficial. Isso é mais seguro que forçar só uma classe,
         * pois permite ao próprio VLibras executar sua lógica.
         */
        restaurando = true;

        try {
            botao.click();
        } catch (erro) {
            console.warn(
                'FOAG: não foi possível clicar automaticamente no botão do VLibras.',
                erro
            );
        }

        setTimeout(function () {
            restaurando = false;

            /*
             * Algumas versões demoram para abrir.
             * Se ainda não abriu, tentamos novamente algumas vezes.
             */
            if (
                painelDeveAbrir() &&
                !painelEstaAberto() &&
                tentativa < 12
            ) {
                tentarRestaurarPainel(tentativa + 1);
            } else {
                registrarEstadoAtual();
            }
        }, 350);
    }

    /* =====================================================
       CARREGAR SCRIPT OFICIAL
    ===================================================== */

    function scriptJaExiste() {
        return (
            document.getElementById(SCRIPT_ID) ||
            document.querySelector(
                'script[src*="vlibras.gov.br/app/vlibras-plugin.js"]'
            )
        );
    }

    function depoisDoCarregamento() {
        /*
         * Dá um pequeno intervalo para o VLibras terminar
         * de criar seus elementos.
         */
        setTimeout(function () {
            tentarRestaurarPainel();
        }, 250);
    }

    function carregarVLibras() {
        if (!librasAtiva()) {
            salvarPainelAberto(false);
            return;
        }

        const existente = scriptJaExiste();

        if (existente) {
            /*
             * Se a biblioteca já está pronta, apenas espera o DOM
             * interno do widget aparecer.
             */
            depoisDoCarregamento();

            /*
             * Se ainda estiver carregando, este listener também ajuda.
             */
            existente.addEventListener?.(
                'load',
                depoisDoCarregamento,
                { once: true }
            );

            return;
        }

        const script = document.createElement('script');

        script.id = SCRIPT_ID;
        script.src = SCRIPT_URL;
        script.async = true;

        script.addEventListener(
            'load',
            depoisDoCarregamento,
            { once: true }
        );

        script.addEventListener(
            'error',
            function () {
                console.error(
                    'FOAG: não foi possível carregar o VLibras.'
                );
            },
            { once: true }
        );

        document.body.appendChild(script);
    }

    /* =====================================================
       SALVAR ANTES DE SAIR DA PÁGINA
    ===================================================== */

    window.addEventListener('pagehide', function () {
        if (librasAtiva()) {
            registrarEstadoAtual();
        }
    });

    window.addEventListener('beforeunload', function () {
        if (librasAtiva()) {
            registrarEstadoAtual();
        }
    });

    /* =====================================================
       API GLOBAL
    ===================================================== */

    window.FOAGLibras = {
        estaAtiva: function () {
            return librasAtiva();
        },

        estaAberto: function () {
            return painelEstaAberto();
        },

        salvarEstadoPainel: function () {
            registrarEstadoAtual();
        },

        abrirNaProximaPagina: function () {
            salvarPainelAberto(true);
        },

        fecharNaProximaPagina: function () {
            salvarPainelAberto(false);
        }
    };

    /* =====================================================
       INICIALIZAÇÃO
    ===================================================== */

    function iniciar() {
        if (librasAtiva()) {
            carregarVLibras();
        } else {
            salvarPainelAberto(false);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            iniciar,
            { once: true }
        );
    } else {
        iniciar();
    }

})();

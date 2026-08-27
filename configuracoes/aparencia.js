/* ============================================================
   FOAG - APARÊNCIA GLOBAL
   Controle global do tamanho da fonte
============================================================ */

(function () {
    'use strict';

    const CHAVE_STORAGE = 'foag_aparencia';

    const CONFIG_PADRAO = {
        tamanho_fonte: 'media'
    };

    const ESCALAS = {
        pequena: 0.88,
        media: 1,
        grande: 1.15
    };


    /* ========================================================
       CARREGAR APARÊNCIA SALVA
    ======================================================== */

    function carregar() {
        try {
            const salvo = localStorage.getItem(CHAVE_STORAGE);

            if (!salvo) {
                return { ...CONFIG_PADRAO };
            }

            const dados = JSON.parse(salvo);

            return {
                ...CONFIG_PADRAO,
                ...dados
            };

        } catch (erro) {
            console.error(
                'FOAG - Erro ao carregar aparência:',
                erro
            );

            return { ...CONFIG_PADRAO };
        }
    }


    /* ========================================================
       SALVAR APARÊNCIA
    ======================================================== */

    function salvar(config) {
        try {
            localStorage.setItem(
                CHAVE_STORAGE,
                JSON.stringify(config)
            );
        } catch (erro) {
            console.error(
                'FOAG - Erro ao salvar aparência:',
                erro
            );
        }
    }


    /* ========================================================
       VERIFICAR SE O ELEMENTO É ÍCONE
    ======================================================== */

    function ehIcone(elemento) {
        if (!elemento || !elemento.classList) {
            return false;
        }

        const classes = elemento.classList;

        return (
            classes.contains('fa') ||
            classes.contains('fas') ||
            classes.contains('far') ||
            classes.contains('fab') ||
            classes.contains('fa-solid') ||
            classes.contains('fa-regular') ||
            classes.contains('fa-brands')
        );
    }


    /* ========================================================
       IGNORAR ELEMENTOS EXTERNOS
    ======================================================== */

    function deveIgnorar(elemento) {
        if (!elemento) {
            return true;
        }

        if (ehIcone(elemento)) {
            return true;
        }

        /*
         * Não mexer no VLibras
         */
        if (
            elemento.closest('[vw]') ||
            elemento.closest('[vw-access-button]') ||
            elemento.closest('[vw-plugin-wrapper]')
        ) {
            return true;
        }

        return false;
    }


    /* ========================================================
       ELEMENTOS DE TEXTO DO SITE
    ======================================================== */

    const SELETOR_TEXTOS = [
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',

        'p',
        'span',
        'small',
        'strong',
        'b',
        'em',

        'label',
        'a',

        'button',

        'input',
        'textarea',
        'select',

        'li',

        'td',
        'th',

        'legend',
        'caption'
    ].join(',');


    /* ========================================================
       GUARDAR O TAMANHO ORIGINAL
    ======================================================== */

    function guardarTamanhoOriginal(elemento) {
        if (deveIgnorar(elemento)) {
            return;
        }

        /*
         * Só guarda uma vez.
         *
         * Dessa forma:
         *
         * média -> grande -> pequena -> média
         *
         * sempre usa o tamanho ORIGINAL.
         */
        if (elemento.dataset.foagFonteOriginal) {
            return;
        }

        const estilo = window.getComputedStyle(elemento);

        const tamanho = parseFloat(
            estilo.fontSize
        );

        if (
            !isNaN(tamanho) &&
            tamanho > 0
        ) {
            elemento.dataset.foagFonteOriginal =
                String(tamanho);
        }
    }


    /* ========================================================
       APLICAR FONTE EM UM ELEMENTO
    ======================================================== */

    function aplicarEmElemento(elemento, escala) {
        if (deveIgnorar(elemento)) {
            return;
        }

        guardarTamanhoOriginal(elemento);

        const tamanhoOriginal = parseFloat(
            elemento.dataset.foagFonteOriginal
        );

        if (
            isNaN(tamanhoOriginal) ||
            tamanhoOriginal <= 0
        ) {
            return;
        }

        const novoTamanho =
            tamanhoOriginal * escala;

        elemento.style.fontSize =
            `${novoTamanho}px`;
    }


    /* ========================================================
       APLICAR TAMANHO NO SITE INTEIRO
    ======================================================== */

    function aplicarTamanhoFonte(tamanho) {
        if (!ESCALAS[tamanho]) {
            tamanho = 'media';
        }

        const escala =
            ESCALAS[tamanho];

        /*
         * Marca o tamanho atual no HTML.
         */
        document.documentElement.setAttribute(
            'data-foag-fonte',
            tamanho
        );

        /*
         * Marca também no BODY.
         */
        if (document.body) {
            document.body.classList.remove(
                'foag-fonte-pequena',
                'foag-fonte-media',
                'foag-fonte-grande'
            );

            document.body.classList.add(
                `foag-fonte-${tamanho}`
            );
        }

        /*
         * Busca todos os textos da página.
         */
        const elementos =
            document.querySelectorAll(
                SELETOR_TEXTOS
            );

        elementos.forEach(elemento => {
            aplicarEmElemento(
                elemento,
                escala
            );
        });
    }


    /* ========================================================
       ATUALIZAR APARÊNCIA
    ======================================================== */

    function atualizar(novosDados) {
        const atual =
            carregar();

        const novaConfig = {
            ...atual,
            ...novosDados
        };

        salvar(novaConfig);

        aplicarTamanhoFonte(
            novaConfig.tamanho_fonte
        );

        return novaConfig;
    }


    /* ========================================================
       SALVAR SOMENTE TAMANHO DA FONTE
    ======================================================== */

    function salvarTamanhoFonte(tamanho) {
        return atualizar({
            tamanho_fonte: tamanho
        });
    }


    /* ========================================================
       API GLOBAL
    ======================================================== */

    /*
     * Compatibilidade com os códigos que já fizemos.
     */

    window.foagAparencia = {
        carregar,
        salvar,
        atualizar,
        aplicarFonte: aplicarTamanhoFonte,
        salvarTamanhoFonte
    };


    window.FOAGAparencia = {
        carregar,
        salvar,
        atualizar,
        aplicarFonte: aplicarTamanhoFonte,
        salvarTamanhoFonte
    };


    /* ========================================================
       CONFIGURAÇÕES
       PRÉ-VISUALIZAR PEQUENA / MÉDIA / GRANDE
    ======================================================== */

    function configurarSeletor() {
        const seletor =
            document.getElementById(
                'tamanho-fonte'
            );

        if (!seletor) {
            return;
        }

        /*
         * Carrega o tamanho já salvo.
         */
        const config =
            carregar();

        seletor.value =
            config.tamanho_fonte ||
            'media';


        /*
         * Ao trocar no select,
         * mostra a alteração imediatamente.
         *
         * Ainda não salva.
         */
        seletor.addEventListener(
            'change',
            function () {
                aplicarTamanhoFonte(
                    this.value
                );
            }
        );


        /*
         * Se apertar cancelar,
         * volta para o tamanho salvo.
         */
        const formulario =
            document.getElementById(
                'form-configuracoes'
            );

        if (formulario) {
            formulario.addEventListener(
                'reset',
                function () {
                    setTimeout(() => {
                        const salvo =
                            carregar();

                        seletor.value =
                            salvo.tamanho_fonte;

                        aplicarTamanhoFonte(
                            salvo.tamanho_fonte
                        );
                    }, 0);
                }
            );
        }
    }


    /* ========================================================
       ELEMENTOS CRIADOS DEPOIS
       MODAIS, CARDS, TAREFAS, ETC.
    ======================================================== */

    function observarNovosElementos() {
        if (!document.body) {
            return;
        }

        const observer =
            new MutationObserver(
                function (mutacoes) {

                    const config =
                        carregar();

                    const escala =
                        ESCALAS[
                            config.tamanho_fonte
                        ] || 1;


                    mutacoes.forEach(
                        mutacao => {

                            mutacao.addedNodes
                                .forEach(no => {

                                    if (
                                        no.nodeType !==
                                        Node.ELEMENT_NODE
                                    ) {
                                        return;
                                    }


                                    /*
                                     * Se o próprio elemento
                                     * for texto.
                                     */
                                    if (
                                        no.matches &&
                                        no.matches(
                                            SELETOR_TEXTOS
                                        )
                                    ) {
                                        aplicarEmElemento(
                                            no,
                                            escala
                                        );
                                    }


                                    /*
                                     * Busca textos dentro dele.
                                     */
                                    if (
                                        no.querySelectorAll
                                    ) {
                                        no
                                            .querySelectorAll(
                                                SELETOR_TEXTOS
                                            )
                                            .forEach(
                                                elemento => {
                                                    aplicarEmElemento(
                                                        elemento,
                                                        escala
                                                    );
                                                }
                                            );
                                    }

                                });

                        });

                }
            );


        observer.observe(
            document.body,
            {
                childList: true,
                subtree: true
            }
        );
    }


    /* ========================================================
       ATUALIZAR ENTRE ABAS
    ======================================================== */

    window.addEventListener(
        'storage',
        function (evento) {

            if (
                evento.key !==
                CHAVE_STORAGE
            ) {
                return;
            }

            const config =
                carregar();

            aplicarTamanhoFonte(
                config.tamanho_fonte
            );
        }
    );


    /* ========================================================
       INICIAR GLOBALMENTE
    ======================================================== */

    function iniciar() {
        const config =
            carregar();

        /*
         * Aplica automaticamente a fonte salva
         * sempre que qualquer página abrir.
         */
        aplicarTamanhoFonte(
            config.tamanho_fonte
        );

        /*
         * Só funciona na configuração se
         * existir o seletor.
         */
        configurarSeletor();

        /*
         * Controla elementos que aparecem
         * depois do carregamento.
         */
        observarNovosElementos();
    }


    if (
        document.readyState ===
        'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            iniciar
        );
    } else {
        iniciar();
    }

})();
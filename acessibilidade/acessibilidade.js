document.addEventListener('DOMContentLoaded', () => {

    const html = document.documentElement;
    const body = document.body;

    // ================================
    // ELEMENTOS
    // ================================

    const btnAbrir = document.getElementById('btn-acessibilidade');
    const menu = document.getElementById('menu-acessibilidade');
    const btnFechar = document.getElementById('fechar-acessibilidade');

    const btnDiminuirFonte = document.getElementById('diminuir-fonte');
    const btnFontePadrao = document.getElementById('fonte-padrao');
    const btnAumentarFonte = document.getElementById('aumentar-fonte');

    const btnContraste = document.getElementById('alto-contraste');
    const btnLinks = document.getElementById('destacar-links');
    const btnAnimacoes = document.getElementById('reduzir-animacoes');

    const btnRestaurar = document.getElementById('restaurar-acessibilidade');

    const mensagem = document.getElementById('mensagem-acessibilidade');


    // ================================
    // CONFIGURAÇÕES PADRÃO
    // ================================

    const CONFIG_PADRAO = {
        fonte: 100,
        altoContraste: false,
        destacarLinks: false,
        reduzirAnimacoes: false
    };

    let config = carregarConfiguracoes();


    // ================================
    // LOCAL STORAGE
    // ================================

    function carregarConfiguracoes() {

        try {

            const salvo = localStorage.getItem('foag_acessibilidade');

            if (!salvo) {
                return { ...CONFIG_PADRAO };
            }

            const dados = JSON.parse(salvo);

            return {
                ...CONFIG_PADRAO,
                ...dados
            };

        } catch (erro) {

            console.warn(
                'Não foi possível carregar as configurações de acessibilidade.',
                erro
            );

            return { ...CONFIG_PADRAO };
        }
    }


    function salvarConfiguracoes() {

        try {

            localStorage.setItem(
                'foag_acessibilidade',
                JSON.stringify(config)
            );

        } catch (erro) {

            console.warn(
                'Não foi possível salvar as configurações de acessibilidade.',
                erro
            );
        }
    }


    // ================================
    // MENSAGEM PARA LEITOR DE TELA
    // ================================

    function anunciar(texto) {

        if (!mensagem) return;

        mensagem.textContent = '';

        setTimeout(() => {
            mensagem.textContent = texto;
        }, 50);
    }


    // ================================
    // MENU
    // ================================

    function abrirMenu() {

        if (!menu || !btnAbrir) return;

        menu.hidden = false;

        btnAbrir.setAttribute(
            'aria-expanded',
            'true'
        );

        if (btnFechar) {
            btnFechar.focus();
        }
    }


    function fecharMenu() {

        if (!menu || !btnAbrir) return;

        menu.hidden = true;

        btnAbrir.setAttribute(
            'aria-expanded',
            'false'
        );

        btnAbrir.focus();
    }


    if (btnAbrir) {

        btnAbrir.addEventListener('click', () => {

            if (menu.hidden) {
                abrirMenu();
            } else {
                fecharMenu();
            }

        });
    }


    if (btnFechar) {

        btnFechar.addEventListener(
            'click',
            fecharMenu
        );
    }


    // ESC fecha o menu
    document.addEventListener('keydown', (evento) => {

        if (evento.key === 'Escape') {

            if (menu && !menu.hidden) {
                fecharMenu();
            }

        }

    });


    // Fecha clicando fora
    document.addEventListener('click', (evento) => {

        if (!menu || !btnAbrir) return;

        if (menu.hidden) return;

        const clicouNoMenu = menu.contains(evento.target);
        const clicouNoBotao = btnAbrir.contains(evento.target);

        if (!clicouNoMenu && !clicouNoBotao) {

            menu.hidden = true;

            btnAbrir.setAttribute(
                'aria-expanded',
                'false'
            );
        }

    });


    // ================================
    // TAMANHO DA FONTE
    // ================================

    const tamanhosFonte = [
        100,
        110,
        125,
        140
    ];


    function aplicarFonte() {

        html.classList.remove(
            'a11y-fonte-110',
            'a11y-fonte-125',
            'a11y-fonte-140'
        );

        if (config.fonte === 110) {

            html.classList.add(
                'a11y-fonte-110'
            );

        }

        if (config.fonte === 125) {

            html.classList.add(
                'a11y-fonte-125'
            );

        }

        if (config.fonte === 140) {

            html.classList.add(
                'a11y-fonte-140'
            );

        }
    }


    function aumentarFonte() {

        const indiceAtual =
            tamanhosFonte.indexOf(config.fonte);

        if (
            indiceAtual <
            tamanhosFonte.length - 1
        ) {

            config.fonte =
                tamanhosFonte[indiceAtual + 1];

            aplicarFonte();
            salvarConfiguracoes();

            anunciar(
                `Tamanho do texto aumentado para ${config.fonte}%`
            );

        } else {

            anunciar(
                'O texto já está no tamanho máximo.'
            );

        }
    }


    function diminuirFonte() {

        const indiceAtual =
            tamanhosFonte.indexOf(config.fonte);

        if (indiceAtual > 0) {

            config.fonte =
                tamanhosFonte[indiceAtual - 1];

            aplicarFonte();
            salvarConfiguracoes();

            anunciar(
                `Tamanho do texto alterado para ${config.fonte}%`
            );

        } else {

            anunciar(
                'O texto já está no tamanho padrão.'
            );

        }
    }


    function restaurarFonte() {

        config.fonte = 100;

        aplicarFonte();
        salvarConfiguracoes();

        anunciar(
            'Tamanho do texto restaurado.'
        );
    }


    if (btnAumentarFonte) {

        btnAumentarFonte.addEventListener(
            'click',
            aumentarFonte
        );
    }


    if (btnDiminuirFonte) {

        btnDiminuirFonte.addEventListener(
            'click',
            diminuirFonte
        );
    }


    if (btnFontePadrao) {

        btnFontePadrao.addEventListener(
            'click',
            restaurarFonte
        );
    }


    // ================================
    // ALTO CONTRASTE
    // ================================

    function aplicarContraste() {

        body.classList.toggle(
            'a11y-alto-contraste',
            config.altoContraste
        );

        if (btnContraste) {

            btnContraste.setAttribute(
                'aria-pressed',
                String(config.altoContraste)
            );
        }
    }


    if (btnContraste) {

        btnContraste.addEventListener(
            'click',
            () => {

                config.altoContraste =
                    !config.altoContraste;

                aplicarContraste();

                salvarConfiguracoes();

                anunciar(
                    config.altoContraste
                        ? 'Alto contraste ativado.'
                        : 'Alto contraste desativado.'
                );

            }
        );
    }


    // ================================
    // DESTACAR LINKS
    // ================================

    function aplicarLinks() {

        body.classList.toggle(
            'a11y-destacar-links',
            config.destacarLinks
        );

        if (btnLinks) {

            btnLinks.setAttribute(
                'aria-pressed',
                String(config.destacarLinks)
            );
        }
    }


    if (btnLinks) {

        btnLinks.addEventListener(
            'click',
            () => {

                config.destacarLinks =
                    !config.destacarLinks;

                aplicarLinks();

                salvarConfiguracoes();

                anunciar(
                    config.destacarLinks
                        ? 'Destaque de links ativado.'
                        : 'Destaque de links desativado.'
                );

            }
        );
    }


    // ================================
    // REDUZIR ANIMAÇÕES
    // ================================

    function aplicarAnimacoes() {

        body.classList.toggle(
            'a11y-reduzir-animacoes',
            config.reduzirAnimacoes
        );

        if (btnAnimacoes) {

            btnAnimacoes.setAttribute(
                'aria-pressed',
                String(config.reduzirAnimacoes)
            );
        }
    }


    if (btnAnimacoes) {

        btnAnimacoes.addEventListener(
            'click',
            () => {

                config.reduzirAnimacoes =
                    !config.reduzirAnimacoes;

                aplicarAnimacoes();

                salvarConfiguracoes();

                anunciar(
                    config.reduzirAnimacoes
                        ? 'Redução de animações ativada.'
                        : 'Redução de animações desativada.'
                );

            }
        );
    }


    // ================================
    // RESTAURAR TUDO
    // ================================

    function restaurarTudo() {

        config = {
            ...CONFIG_PADRAO
        };

        aplicarTodasConfiguracoes();

        salvarConfiguracoes();

        anunciar(
            'Todas as configurações de acessibilidade foram restauradas.'
        );
    }


    if (btnRestaurar) {

        btnRestaurar.addEventListener(
            'click',
            restaurarTudo
        );
    }


    // ================================
    // APLICAR CONFIGURAÇÕES
    // ================================

    function aplicarTodasConfiguracoes() {

        aplicarFonte();
        aplicarContraste();
        aplicarLinks();
        aplicarAnimacoes();
    }


    aplicarTodasConfiguracoes();

});
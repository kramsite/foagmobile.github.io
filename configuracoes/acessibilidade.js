/* =========================================================
   FOAG — LIBRAS GLOBAL
   Versão 17

   COMPORTAMENTO:
   1. O switch "Libras" da página Configurações sempre abre desligado.
   2. Marcar ou desmarcar o switch NÃO altera nada imediatamente.
   3. Somente depois de clicar em SALVAR a preferência é gravada.
   4. Depois de salva como ativa, o VLibras aparece em qualquer
      página do FOAG que carregue este arquivo.
========================================================= */

(function () {
    'use strict';

    const CHAVE_CONFIG = 'foag_acessibilidade';
    const ID_SCRIPT_VLIBRAS = 'foag-vlibras-plugin';

    /* =====================================================
       LER ESTADO JÁ SALVO
       Não altera nada.
    ===================================================== */
    function lerEstadoSalvo() {
        try {
            const salvo = localStorage.getItem(CHAVE_CONFIG);

            if (!salvo) {
                return false;
            }

            const dados = JSON.parse(salvo);

            return dados && dados.libras === true;

        } catch (erro) {
            console.error(
                'FOAG: erro ao ler configuração de Libras:',
                erro
            );

            return false;
        }
    }

    /* =====================================================
       GRAVAR ESTADO
       Chamada somente após o botão SALVAR da Configurações.
    ===================================================== */
    function gravarEstado(ativo) {
        localStorage.setItem(
            CHAVE_CONFIG,
            JSON.stringify({
                libras: Boolean(ativo)
            })
        );
    }

    /* =====================================================
       ESCONDER RESTOS DE UMA INTEGRAÇÃO ANTIGA
    ===================================================== */
    function esconderVLibrasExistente() {
        document.querySelectorAll('[vw]').forEach(function (elemento) {
            elemento.style.display = 'none';
        });
    }

    /* =====================================================
       MOSTRAR ELEMENTOS JÁ CRIADOS
    ===================================================== */
    function mostrarVLibrasExistente() {
        document.querySelectorAll('[vw]').forEach(function (elemento) {
            elemento.style.display = '';
        });
    }

    /* =====================================================
       CARREGAR VLIBRAS
    ===================================================== */
    function carregarVLibras() {
        if (!lerEstadoSalvo()) {
            esconderVLibrasExistente();
            return;
        }

        const scriptExistente =
            document.getElementById(ID_SCRIPT_VLIBRAS) ||
            document.querySelector(
                'script[src*="vlibras.gov.br/app/vlibras-plugin.js"]'
            );

        if (scriptExistente) {
            mostrarVLibrasExistente();
            return;
        }

        const script = document.createElement('script');

        script.id = ID_SCRIPT_VLIBRAS;
        script.src = 'https://vlibras.gov.br/app/vlibras-plugin.js';
        script.async = true;

        script.onload = function () {
            console.log('FOAG: VLibras carregado.');
        };

        script.onerror = function () {
            console.error(
                'FOAG: não foi possível carregar o VLibras.'
            );
        };

        document.body.appendChild(script);
    }

    /* =====================================================
       API PARA CONFIGURACOES.PHP
    ===================================================== */
    window.FOAGLibras = {

        estaAtiva: function () {
            return lerEstadoSalvo();
        },

        /*
         * configuracoes.php chama esta função SOMENTE
         * quando o POST de Salvar retorna success=true.
         */
        salvar: function (ativo) {
            const estavaAtiva = lerEstadoSalvo();
            const novoEstado = Boolean(ativo);

            gravarEstado(novoEstado);

            // ATIVAR
            if (novoEstado) {
                carregarVLibras();
                return;
            }

            // DESATIVAR
            if (estavaAtiva && !novoEstado) {
                /*
                 * Recarrega para remover totalmente o widget
                 * criado pelo script oficial.
                 */
                window.location.reload();
                return;
            }

            esconderVLibrasExistente();
        }
    };

    /* =====================================================
       INICIALIZAÇÃO EM TODAS AS PÁGINAS

       Se o usuário já salvou Libras=true anteriormente,
       carrega automaticamente o VLibras.

       IMPORTANTE:
       isso NÃO mexe no switch da página Configurações.
       O switch é controlado pela própria configuracoes.php
       e começa sempre desligado.
    ===================================================== */
    function iniciar() {
        if (lerEstadoSalvo()) {
            carregarVLibras();
        } else {
            esconderVLibrasExistente();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }

})();

/* =========================================================
   FOAG — LIBRAS GLOBAL
   Versão 19

   REGRA:
   - O switch das Configurações sozinho NÃO ativa/desativa.
   - A preferência só é gravada depois de clicar em SALVAR.
   - Depois do SALVAR, a página recarrega.
   - Este arquivo lê a preferência salva e carrega o VLibras.
   - Todas as páginas do FOAG que carregarem este arquivo
     respeitam a mesma preferência.
========================================================= */

(function () {
    'use strict';

    const CHAVE = 'foag_libras_v19';
    const ID_SCRIPT = 'foag-vlibras-oficial';

    function librasAtiva() {
        return localStorage.getItem(CHAVE) === '1';
    }

    function carregarVLibras() {

        if (!librasAtiva()) {
            return;
        }

        /*
         * Evita carregar mais de uma vez.
         */
        if (
            document.getElementById(ID_SCRIPT) ||
            document.querySelector(
                'script[src="https://vlibras.gov.br/app/vlibras-plugin.js"]'
            )
        ) {
            return;
        }

        /*
         * VLibras Widget 7:
         * segundo a documentação oficial, apenas carregar
         * este script já inicializa o widget automaticamente.
         */
        const script = document.createElement('script');

        script.id = ID_SCRIPT;
        script.src =
            'https://vlibras.gov.br/app/vlibras-plugin.js';

        script.async = true;

        script.onload = function () {
            console.log('FOAG: VLibras carregado com sucesso.');
        };

        script.onerror = function () {
            console.error(
                'FOAG: erro ao carregar o VLibras oficial.'
            );
        };

        document.body.appendChild(script);
    }

    function iniciar() {

        if (librasAtiva()) {
            carregarVLibras();
        }
    }

    /*
     * Disponível para diagnóstico.
     * NÃO altera o estado salvo.
     */
    window.FOAGLibras = {
        estaAtiva: librasAtiva
    };

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

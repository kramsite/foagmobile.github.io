<a href="#conteudo-principal" class="skip-link">
    Pular para o conteúdo principal
</a>

<div class="acessibilidade-container">

    <button
        type="button"
        id="btn-acessibilidade"
        class="btn-acessibilidade"
        aria-label="Abrir menu de acessibilidade"
        aria-controls="menu-acessibilidade"
        aria-expanded="false"
    >
        <span aria-hidden="true">♿</span>
        <span class="texto-acessibilidade">Acessibilidade</span>
    </button>


    <aside
        id="menu-acessibilidade"
        class="menu-acessibilidade"
        aria-label="Opções de acessibilidade"
        hidden
    >

        <div class="acessibilidade-topo">

            <h2>Acessibilidade</h2>

            <button
                type="button"
                id="fechar-acessibilidade"
                class="fechar-acessibilidade"
                aria-label="Fechar menu de acessibilidade"
            >
                ×
            </button>

        </div>


        <div class="acessibilidade-opcoes">

            <!-- TAMANHO DO TEXTO -->
            <div class="acessibilidade-item">

                <span class="acessibilidade-titulo">
                    Tamanho do texto
                </span>

                <div class="controle-fonte">

                    <button
                        type="button"
                        id="diminuir-fonte"
                        aria-label="Diminuir tamanho do texto"
                    >
                        A−
                    </button>

                    <button
                        type="button"
                        id="fonte-padrao"
                        aria-label="Restaurar tamanho padrão do texto"
                    >
                        A
                    </button>

                    <button
                        type="button"
                        id="aumentar-fonte"
                        aria-label="Aumentar tamanho do texto"
                    >
                        A+
                    </button>

                </div>

            </div>


            <!-- ALTO CONTRASTE -->
            <button
                type="button"
                id="alto-contraste"
                class="opcao-acessibilidade"
                aria-pressed="false"
            >
                <span aria-hidden="true">◐</span>

                <span>
                    Alto contraste
                </span>
            </button>


            <!-- DESTACAR LINKS -->
            <button
                type="button"
                id="destacar-links"
                class="opcao-acessibilidade"
                aria-pressed="false"
            >
                <span aria-hidden="true">🔗</span>

                <span>
                    Destacar links
                </span>
            </button>


            <!-- REDUZIR ANIMAÇÕES -->
            <button
                type="button"
                id="reduzir-animacoes"
                class="opcao-acessibilidade"
                aria-pressed="false"
            >
                <span aria-hidden="true">◉</span>

                <span>
                    Reduzir animações
                </span>
            </button>


            <!-- RESTAURAR -->
            <button
                type="button"
                id="restaurar-acessibilidade"
                class="restaurar-acessibilidade"
            >
                Restaurar padrão
            </button>

        </div>

    </aside>

</div>


<div
    id="mensagem-acessibilidade"
    class="sr-only"
    aria-live="polite"
    aria-atomic="true"
></div>
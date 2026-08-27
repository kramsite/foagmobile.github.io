// =====================================================
// loja.js — Loja de Estrelas FOAG
// =====================================================

// =====================================================
// DADOS GLOBAIS
// =====================================================

const LOJA_SAVE_URL =
    window.LOJA_SAVE_URL ||
    'salvar_loja.php';

let lojaData =
    window.LOJA_DATA || {
        estrelas: 0,
        total_estudado: 0,
        itens: [],
        itens_comprados: []
    };

let perfilData =
    window.PERFIL_DATA || {};

let itemSelecionado = null;
let filtroAtual = 'todos';


// =====================================================
// INICIALIZAÇÃO
// =====================================================

document.addEventListener('DOMContentLoaded', function () {

    console.log('Loja carregada ✅');

    // =================================================
    // ELEMENTOS PRINCIPAIS
    // =================================================

    const lojaGrid =
        document.getElementById('lojaGrid');

    const saldoEstrelas =
        document.getElementById('saldoEstrelas');

    const filtrosBtns =
        document.querySelectorAll('.filtro-btn');


    // =================================================
    // CABEÇALHO
    // =================================================

    const perfilIcon =
        document.getElementById('icon-perfil');

    const iconSair =
        document.getElementById('icon-sair');


    // =================================================
    // PERFIL
    // =================================================

    if (perfilIcon) {

        perfilIcon.style.cursor = 'pointer';

        perfilIcon.addEventListener(
            'click',
            function () {

                window.location.href =
                    '../perfil/perfil.php';
            }
        );
    }


    // =================================================
    // LOGOUT
    // =================================================

    const logoutModal =
        document.getElementById('logout-modal');

    const confirmarLogout =
        document.getElementById('confirm-logout');

    const cancelarLogout =
        document.getElementById('cancel-logout');


    if (iconSair) {

        iconSair.style.cursor = 'pointer';

        iconSair.addEventListener(
            'click',
            function () {

                if (logoutModal) {

                    logoutModal.style.display =
                        'flex';
                }
            }
        );
    }


    confirmarLogout?.addEventListener(
        'click',
        function () {

            window.location.href =
                '../login/logout.php';
        }
    );


    cancelarLogout?.addEventListener(
        'click',
        function () {

            if (logoutModal) {

                logoutModal.style.display =
                    'none';
            }
        }
    );


    logoutModal?.addEventListener(
        'click',
        function (evento) {

            if (
                evento.target ===
                logoutModal
            ) {

                logoutModal.style.display =
                    'none';
            }
        }
    );


    // =================================================
    // MODAL DE COMPRA
    // =================================================

    const modalCompra =
        document.getElementById(
            'modal-compra'
        );

    const modalIcone =
        document.getElementById(
            'modalIcone'
        );

    const modalTitulo =
        document.getElementById(
            'modalTitulo'
        );

    const modalDescricao =
        document.getElementById(
            'modalDescricao'
        );

    const modalPreco =
        document.getElementById(
            'modalPreco'
        );

    const confirmarCompra =
        document.getElementById(
            'confirmar-compra'
        );

    const cancelarCompra =
        document.getElementById(
            'cancelar-compra'
        );


    // =================================================
    // MODAL DE SUCESSO
    // =================================================

    const modalSucesso =
        document.getElementById(
            'modal-sucesso'
        );

    const mensagemSucesso =
        document.getElementById(
            'mensagemSucesso'
        );

    const fecharSucesso =
        document.getElementById(
            'fechar-sucesso'
        );


    // =================================================
    // FUNÇÃO ATUALIZAR SALDO
    // =================================================

    function atualizarSaldo() {

        if (!saldoEstrelas) {
            return;
        }

        saldoEstrelas.textContent =
            lojaData.estrelas || 0;
    }


    // =================================================
    // VERIFICAR ITEM COMPRADO
    // =================================================

    function verificarSeComprado(
        itemId
    ) {

        return (
            lojaData.itens_comprados || []
        ).includes(itemId);
    }


    // =================================================
    // VERIFICAR ITEM ATIVO
    // =================================================

    function verificarSeAtivo(
        itemId
    ) {

        return (
            perfilData.item_ativo ===
            itemId
        );
    }


    // =================================================
    // STATUS DO ITEM
    // =================================================

    function getStatusItem(item) {

        const comprado =
            verificarSeComprado(
                item.id
            );

        const estrelas =
            Number(
                lojaData.estrelas || 0
            );

        const preco =
            Number(
                item.preco || 0
            );


        if (comprado) {

            return {
                status: 'comprado',
                texto: '✅ Comprado',
                classe: 'comprado'
            };
        }


        if (
            estrelas >=
            preco
        ) {

            return {
                status: 'disponivel',
                texto: '🛒 Comprar',
                classe: 'disponivel'
            };
        }


        return {
            status: 'insuficiente',
            texto: '⭐ Insuficiente',
            classe: 'insuficiente'
        };
    }


    // =================================================
    // RENDERIZAR ITENS
    // =================================================

    function renderizarItens(
        filtro = 'todos'
    ) {

        if (!lojaGrid) {
            return;
        }


        lojaGrid.innerHTML = '';


        const itens =
            Array.isArray(
                lojaData.itens
            )
                ? lojaData.itens
                : [];


        let itensFiltrados =
            itens;


        if (
            filtro ===
            'comprados'
        ) {

            itensFiltrados =
                itens.filter(
                    item =>
                        verificarSeComprado(
                            item.id
                        )
                );

        } else if (
            filtro !==
            'todos'
        ) {

            itensFiltrados =
                itens.filter(
                    item =>
                        item.categoria ===
                        filtro
                );
        }


        if (
            itensFiltrados.length ===
            0
        ) {

            lojaGrid.innerHTML = `
                <div class="sem-itens">
                    <i class="fa-regular fa-face-frown"></i>
                    <p>
                        Nenhum item encontrado nesta categoria.
                    </p>
                </div>
            `;

            return;
        }


        itensFiltrados.forEach(
            function (item) {

                const card =
                    document.createElement(
                        'div'
                    );


                card.className =
                    'item-card';


                const status =
                    getStatusItem(
                        item
                    );


                const comprado =
                    verificarSeComprado(
                        item.id
                    );


                const ativo =
                    verificarSeAtivo(
                        item.id
                    );


                // Verifica se o item tem imagem
                const temImagem = item.imagem && item.imagem.trim() !== '';


                card.innerHTML = `
                    <div class="icone">
                        ${temImagem 
                            ? `<img src="${item.imagem}" alt="${item.nome}" class="item-imagem">` 
                            : `<i class="${item.icone || 'fa-solid fa-gift'}"></i>`
                        }
                    </div>

                    <div class="nome">
                        ${item.nome}
                    </div>

                    <div class="descricao">
                        ${item.descricao}
                    </div>

                    <div class="preco">
                        <i class="fa-solid fa-star"></i>
                        ${item.preco}
                    </div>

                    <div class="status ${status.classe}">
                        ${status.texto}
                    </div>

                    ${
                        ativo
                            ? `
                                <div class="badge-ativo">
                                    ✔️ ATIVO
                                </div>
                            `
                            : ''
                    }
                `;


                if (comprado) {

                    card.style.cursor =
                        'pointer';


                    card.addEventListener(
                        'click',
                        function () {

                            ativarItem(
                                item.id
                            );
                        }
                    );

                } else if (
                    status.status ===
                    'disponivel'
                ) {

                    card.style.cursor =
                        'pointer';


                    card.addEventListener(
                        'click',
                        function () {

                            abrirModalCompra(
                                item
                            );
                        }
                    );

                } else {

                    card.style.cursor =
                        'not-allowed';
                }


                lojaGrid.appendChild(
                    card
                );
            }
        );
    }


    // =================================================
    // ABRIR MODAL DE COMPRA
    // =================================================

    function abrirModalCompra(
        item
    ) {

        itemSelecionado =
            item;


        if (modalIcone) {

            // Verifica se o item tem imagem
            const temImagem = item.imagem && item.imagem.trim() !== '';

            modalIcone.innerHTML =
                temImagem
                    ? `<img src="${item.imagem}" alt="${item.nome}" class="modal-item-imagem">`
                    : `<i class="${item.icone || 'fa-solid fa-gift'}"></i>`;
        }


        if (modalTitulo) {

            modalTitulo.textContent =
                `Comprar ${item.nome}`;
        }


        if (modalDescricao) {

            modalDescricao.textContent =
                item.descricao;
        }


        if (modalPreco) {

            modalPreco.textContent =
                item.preco;
        }


        if (modalCompra) {

            modalCompra.style.display =
                'flex';

            document.body.style.overflow =
                'hidden';
        }
    }


    // =================================================
    // FECHAR MODAL DE COMPRA
    // =================================================

    function fecharModalCompra() {

        if (modalCompra) {

            modalCompra.style.display =
                'none';
        }


        document.body.style.overflow =
            '';


        itemSelecionado =
            null;
    }


    // =================================================
    // COMPRAR ITEM
    // =================================================

    async function comprarItem() {

        if (!itemSelecionado) {
            return;
        }


        const itemCompra = {
            ...itemSelecionado
        };


        const estrelas =
            Number(
                lojaData.estrelas || 0
            );


        const preco =
            Number(
                itemCompra.preco || 0
            );


        if (
            verificarSeComprado(
                itemCompra.id
            )
        ) {

            fecharModalCompra();

            mostrarSucesso(
                'Este item já foi comprado.'
            );

            return;
        }


        if (
            estrelas <
            preco
        ) {

            mostrarSucesso(
                'Você não tem estrelas suficientes para comprar este item.'
            );

            return;
        }


        lojaData.estrelas =
            estrelas -
            preco;


        if (
            !Array.isArray(
                lojaData.itens_comprados
            )
        ) {

            lojaData.itens_comprados =
                [];
        }


        if (
            !lojaData.itens_comprados.includes(
                itemCompra.id
            )
        ) {

            lojaData.itens_comprados.push(
                itemCompra.id
            );
        }


        atualizarSaldo();

        renderizarItens(
            filtroAtual
        );


        const salvou =
            await salvarLoja();


        fecharModalCompra();


        if (salvou) {

            mostrarSucesso(
                `🎉 Você comprou "${itemCompra.nome}"!`
            );

        } else {

            mostrarSucesso(
                'O item foi atualizado na tela, mas houve um problema ao salvar. Tente novamente.'
            );
        }


        atualizarPerfilComItens();

        notificarPerfilAtualizado();
    }


    // =================================================
    // ATIVAR ITEM
    // =================================================

    async function ativarItem(
        itemId
    ) {

        if (
            !verificarSeComprado(
                itemId
            )
        ) {

            return;
        }


        perfilData.item_ativo =
            itemId;


        const item =
            (
                lojaData.itens ||
                []
            ).find(
                i =>
                    i.id ===
                    itemId
            );


        const salvou =
            await salvarPerfil();


        if (salvou) {

            if (item) {

                mostrarSucesso(
                    `✅ "${item.nome}" ativado no seu perfil!`
                );
            }

        } else {

            mostrarSucesso(
                'Não foi possível ativar o item no perfil.'
            );
        }


        renderizarItens(
            filtroAtual
        );


        atualizarPerfilComItens();

        notificarPerfilAtualizado();
    }


    // =================================================
    // MOSTRAR SUCESSO
    // =================================================

    function mostrarSucesso(
        mensagem
    ) {

        if (mensagemSucesso) {

            mensagemSucesso.textContent =
                mensagem;
        }


        if (modalSucesso) {

            modalSucesso.style.display =
                'flex';

            document.body.style.overflow =
                'hidden';
        }
    }


    // =================================================
    // FECHAR MODAL SUCESSO
    // =================================================

    function fecharSucessoModal() {

        if (modalSucesso) {

            modalSucesso.style.display =
                'none';
        }


        document.body.style.overflow =
            '';
    }


    // =================================================
    // SALVAR LOJA
    // =================================================

    async function salvarLoja() {

        try {

            const dadosParaEnviar = {

                estrelas:
                    Number(
                        lojaData.estrelas ||
                        0
                    ),

                total_estudado:
                    Number(
                        lojaData.total_estudado ||
                        0
                    ),

                itens_comprados:
                    lojaData.itens_comprados ||
                    []
            };


            console.log(
                '📤 Salvando loja:',
                dadosParaEnviar
            );


            const resposta =
                await fetch(
                    LOJA_SAVE_URL,
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json'
                        },

                        body:
                            JSON.stringify(
                                dadosParaEnviar
                            )
                    }
                );


            const texto =
                await resposta.text();


            console.log(
                '📥 Resposta:',
                texto
            );


            if (!resposta.ok) {

                console.error(
                    'Erro HTTP:',
                    resposta.status
                );

                return false;
            }


            let dados;


            try {

                dados =
                    JSON.parse(
                        texto
                    );

            } catch (erro) {

                console.error(
                    'Resposta inválida:',
                    texto
                );

                return false;
            }


            if (
                dados.ok ||
                dados.sucesso
            ) {

                if (dados.dados) {

                    if (
                        dados.dados.estrelas !==
                        undefined
                    ) {

                        lojaData.estrelas =
                            Number(
                                dados.dados.estrelas
                            );
                    }


                    if (
                        Array.isArray(
                            dados.dados.itens_comprados
                        )
                    ) {

                        lojaData.itens_comprados =
                            dados.dados.itens_comprados;
                    }
                }


                atualizarSaldo();

                renderizarItens(
                    filtroAtual
                );


                return true;
            }


            console.error(
                'Erro ao salvar:',
                dados
            );


            return false;

        } catch (erro) {

            console.error(
                '❌ Erro ao salvar loja:',
                erro
            );


            return false;
        }
    }


    // =================================================
    // SALVAR PERFIL
    // =================================================

    async function salvarPerfil() {

        try {

            const resposta =
                await fetch(
                    '../perfil/salvar_perfil.php',
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json'
                        },

                        body:
                            JSON.stringify(
                                perfilData
                            )
                    }
                );


            if (!resposta.ok) {

                console.error(
                    'Erro ao salvar perfil:',
                    resposta.status
                );

                return false;
            }


            console.log(
                '✅ Perfil salvo'
            );


            return true;

        } catch (erro) {

            console.error(
                '❌ Erro ao salvar perfil:',
                erro
            );


            return false;
        }
    }


    // =================================================
    // ATUALIZAR PERFIL COM ITENS
    // =================================================

    function atualizarPerfilComItens() {

        const itensComprados =
            lojaData.itens_comprados ||
            [];


        const todosItens =
            lojaData.itens ||
            [];


        const itensAtivos =
            todosItens.filter(
                item =>
                    itensComprados.includes(
                        item.id
                    )
            );


        try {

            sessionStorage.setItem(
                'itens_loja',
                JSON.stringify(
                    itensAtivos
                )
            );


            sessionStorage.setItem(
                'estrelas_total',
                String(
                    lojaData.estrelas ||
                    0
                )
            );


            sessionStorage.setItem(
                'loja_atualizada',
                Date.now().toString()
            );


            console.log(
                'Perfil atualizado com',
                itensAtivos.length,
                'itens'
            );

        } catch (erro) {

            console.error(
                'Erro no sessionStorage:',
                erro
            );
        }
    }


    // =================================================
    // NOTIFICAR PERFIL
    // =================================================

    function notificarPerfilAtualizado() {

        try {

            const channel =
                new BroadcastChannel(
                    'foag_loja'
                );


            channel.postMessage({
                type:
                    'LOJA_ATUALIZADA'
            });


            setTimeout(
                function () {

                    channel.close();

                },
                100
            );

        } catch (erro) {

            console.log(
                'BroadcastChannel não disponível.'
            );
        }


        atualizarPerfilComItens();
    }


    // =================================================
    // BOTÃO SECRETO
    // =================================================

    const botaoSecreto =
        document.getElementById(
            'botaoEstrelasSecretas'
        );

    const modalEstrelas =
        document.getElementById(
            'modal-estrelas'
        );

    const mensagemEstrelas =
        document.getElementById(
            'mensagemEstrelas'
        );

    const fecharEstrelas =
        document.getElementById(
            'fechar-estrelas'
        );


    let clicksSecretos = 0;

    let tempoUltimoClick = 0;


    if (botaoSecreto) {

        botaoSecreto.addEventListener(
            'click',
            function (evento) {

                evento.stopPropagation();


                const agora =
                    Date.now();


                if (
                    agora -
                    tempoUltimoClick >
                    2000
                ) {

                    clicksSecretos =
                        0;
                }


                clicksSecretos++;

                tempoUltimoClick =
                    agora;


                this.classList.add(
                    'ativo'
                );


                setTimeout(
                    () => {

                        this.classList.remove(
                            'ativo'
                        );

                    },
                    500
                );


                if (
                    clicksSecretos >=
                    5
                ) {

                    clicksSecretos =
                        0;


                    ganharEstrelas(
                        10
                    );
                }
            }
        );
    }


    // =================================================
    // GANHAR ESTRELAS
    // =================================================

    async function ganharEstrelas(
        quantidade
    ) {

        lojaData.estrelas =
            Number(
                lojaData.estrelas ||
                0
            ) +
            Number(
                quantidade
            );


        atualizarSaldo();

        renderizarItens(
            filtroAtual
        );


        if (mensagemEstrelas) {

            mensagemEstrelas.innerHTML = `
                Você ganhou
                <strong>
                    ${quantidade} estrelas
                </strong>!
                🌟

                <br>

                <small>
                    Total:
                    ${lojaData.estrelas}
                    estrelas
                </small>
            `;
        }


        if (modalEstrelas) {

            modalEstrelas.style.display =
                'flex';

            document.body.style.overflow =
                'hidden';
        }


        await salvarLoja();


        atualizarPerfilComItens();

        notificarPerfilAtualizado();
    }


    // =================================================
    // FECHAR MODAL ESTRELAS
    // =================================================

    fecharEstrelas?.addEventListener(
        'click',
        function () {

            if (modalEstrelas) {

                modalEstrelas.style.display =
                    'none';
            }


            document.body.style.overflow =
                '';
        }
    );


    modalEstrelas?.addEventListener(
        'click',
        function (evento) {

            if (
                evento.target ===
                modalEstrelas
            ) {

                modalEstrelas.style.display =
                    'none';


                document.body.style.overflow =
                    '';
            }
        }
    );


    // =================================================
    // FILTROS
    // =================================================

    filtrosBtns.forEach(
        function (btn) {

            btn.addEventListener(
                'click',
                function () {

                    filtrosBtns.forEach(
                        function (botao) {

                            botao.classList.remove(
                                'active'
                            );
                        }
                    );


                    this.classList.add(
                        'active'
                    );


                    filtroAtual =
                        this.dataset.filtro ||
                        'todos';


                    renderizarItens(
                        filtroAtual
                    );
                }
            );
        }
    );


    // =================================================
    // EVENTOS COMPRA
    // =================================================

    confirmarCompra?.addEventListener(
        'click',
        comprarItem
    );


    cancelarCompra?.addEventListener(
        'click',
        fecharModalCompra
    );


    modalCompra?.addEventListener(
        'click',
        function (evento) {

            if (
                evento.target ===
                modalCompra
            ) {

                fecharModalCompra();
            }
        }
    );


    // =================================================
    // EVENTOS SUCESSO
    // =================================================

    fecharSucesso?.addEventListener(
        'click',
        fecharSucessoModal
    );


    modalSucesso?.addEventListener(
        'click',
        function (evento) {

            if (
                evento.target ===
                modalSucesso
            ) {

                fecharSucessoModal();
            }
        }
    );


    // =================================================
    // TECLA ESC
    // =================================================

    document.addEventListener(
        'keydown',
        function (evento) {

            if (
                evento.key !==
                'Escape'
            ) {

                return;
            }


            fecharModalCompra();

            fecharSucessoModal();


            if (modalEstrelas) {

                modalEstrelas.style.display =
                    'none';
            }


            if (logoutModal) {

                logoutModal.style.display =
                    'none';
            }


            document.body.style.overflow =
                '';
        }
    );


    // =================================================
    // INICIAR LOJA
    // =================================================

    atualizarSaldo();

    renderizarItens(
        'todos'
    );

    atualizarPerfilComItens();


    console.log(
        'Loja pronta ✅'
    );
});


// =====================================================
// GANHAR ESTRELAS POR ESTUDO
// =====================================================

async function ganharEstrelasPorEstudo(
    minutos,
    disciplina
) {

    try {

        console.log(
            '⭐ Estudou',
            minutos,
            'minutos de',
            disciplina
        );


        const resposta =
            await fetch(
                'salvar_estrelas.php',
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json'
                    },

                    body:
                        JSON.stringify({
                            minutos:
                                minutos,

                            disciplina:
                                disciplina
                        })
                }
            );


        const texto =
            await resposta.text();


        console.log(
            '📥 Resposta estrelas:',
            texto
        );


        let dados;


        try {

            dados =
                JSON.parse(
                    texto
                );

        } catch (erro) {

            console.error(
                'Resposta não é JSON:',
                texto
            );

            return null;
        }


        if (
            dados.sucesso &&
            Number(
                dados.estrelas
            ) >
            0
        ) {

            lojaData.estrelas =
                Number(
                    dados.total_estrelas ||
                    lojaData.estrelas
                );


            atualizarSaldoGlobal();


            mostrarNotificacaoEstrelas(
                dados.estrelas,
                dados.total_estrelas
            );


            try {

                sessionStorage.setItem(
                    'estrelas_total',
                    String(
                        lojaData.estrelas
                    )
                );


                sessionStorage.setItem(
                    'loja_atualizada',
                    Date.now().toString()
                );

            } catch (erro) {
            }
        }


        return dados;

    } catch (erro) {

        console.error(
            '❌ Erro ao ganhar estrelas:',
            erro
        );


        return null;
    }
}


// =====================================================
// ATUALIZAR SALDO GLOBAL
// =====================================================

function atualizarSaldoGlobal() {

    const saldo =
        document.getElementById(
            'saldoEstrelas'
        );


    if (saldo) {

        saldo.textContent =
            lojaData.estrelas ||
            0;
    }
}


// =====================================================
// NOTIFICAÇÃO DE ESTRELAS
// =====================================================

function mostrarNotificacaoEstrelas(
    ganhas,
    total
) {

    const notificacao =
        document.createElement(
            'div'
        );


    notificacao.className =
        'notificacao-estrelas';


    notificacao.innerHTML = `
        <div class="notificacao-estrelas-conteudo">

            <span class="notificacao-icone">
                ⭐
            </span>

            <div>

                <div class="notificacao-titulo">
                    +${ganhas} estrelas!
                </div>

                <div class="notificacao-total">
                    Total:
                    ${total} ⭐
                </div>

            </div>

        </div>
    `;


    document.body.appendChild(
        notificacao
    );


    setTimeout(
        function () {

            notificacao.classList.add(
                'saindo'
            );


            setTimeout(
                function () {

                    notificacao.remove();

                },
                500
            );

        },
        3000
    );
}


// =====================================================
// CSS DA NOTIFICAÇÃO
// =====================================================

const styleNotif =
    document.createElement(
        'style'
    );


styleNotif.textContent = `

    @keyframes slideInRight {

        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }


    .notificacao-estrelas {

        position: fixed;

        top: 80px;

        right: 20px;

        z-index: 99999;

        background:
            linear-gradient(
                135deg,
                #ffd700,
                #f9a825
            );

        color: #1a2a3a;

        padding: 15px 25px;

        border-radius: 16px;

        box-shadow:
            0 8px 30px
            rgba(0, 0, 0, 0.3);

        font-family:
            'Poppins',
            sans-serif;

        font-weight: 600;

        font-size: 16px;

        animation:
            slideInRight
            0.5s ease;

        max-width: 320px;

        transition:
            opacity 0.5s ease;
    }


    .notificacao-estrelas.saindo {

        opacity: 0;
    }


    .notificacao-estrelas-conteudo {

        display: flex;

        align-items: center;

        gap: 12px;
    }


    .notificacao-icone {

        font-size: 32px;
    }


    .notificacao-titulo {

        font-size: 18px;
    }


    .notificacao-total {

        font-size: 13px;

        opacity: 0.8;
    }

`;


document.head.appendChild(
    styleNotif
);
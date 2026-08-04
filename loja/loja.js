// =====================================================
// loja.js — Loja de Estrelas FOAG
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Loja carregada ✅');

    // =================================================
    // CONFIGURAÇÕES
    // =================================================

    const LOJA_SAVE_URL = window.LOJA_SAVE_URL || 'salvar_loja.php';
    let lojaData = window.LOJA_DATA || { estrelas: 0, itens: [], itens_comprados: [] };
    let perfilData = window.PERFIL_DATA || {};

    // =================================================
    // ELEMENTOS
    // =================================================

    const lojaGrid = document.getElementById('lojaGrid');
    const saldoEstrelas = document.getElementById('saldoEstrelas');

    // Filtros
    const filtrosBtns = document.querySelectorAll('.filtro-btn');

    // Modal de compra
    const modalCompra = document.getElementById('modal-compra');
    const modalIcone = document.getElementById('modalIcone');
    const modalTitulo = document.getElementById('modalTitulo');
    const modalDescricao = document.getElementById('modalDescricao');
    const modalPreco = document.getElementById('modalPreco');
    const confirmarCompra = document.getElementById('confirmar-compra');
    const cancelarCompra = document.getElementById('cancelar-compra');

    // Modal de sucesso
    const modalSucesso = document.getElementById('modal-sucesso');
    const mensagemSucesso = document.getElementById('mensagemSucesso');
    const fecharSucesso = document.getElementById('fechar-sucesso');

    let itemSelecionado = null;
    let filtroAtual = 'todos';

    // =================================================
    // FUNÇÕES
    // =================================================

    function atualizarSaldo() {
        if (saldoEstrelas) {
            saldoEstrelas.textContent = lojaData.estrelas || 0;
        }
    }

    function verificarSeComprado(itemId) {
        return (lojaData.itens_comprados || []).includes(itemId);
    }

    function verificarSeAtivo(itemId) {
        return perfilData.item_ativo === itemId;
    }

    function getStatusItem(item) {
        const comprado = verificarSeComprado(item.id);
        const estrelas = lojaData.estrelas || 0;

        if (comprado) {
            return { status: 'comprado', texto: '✅ Comprado', classe: 'comprado' };
        }

        if (estrelas >= item.preco) {
            return { status: 'disponivel', texto: '🛒 Comprar', classe: 'disponivel' };
        }

        return { status: 'insuficiente', texto: '⭐ Insuficiente', classe: 'insuficiente' };
    }

    function renderizarItens(filtro = 'todos') {
        if (!lojaGrid) return;

        lojaGrid.innerHTML = '';

        const itens = lojaData.itens || [];

        let itensFiltrados = itens;

        if (filtro === 'comprados') {
            itensFiltrados = itens.filter(item => verificarSeComprado(item.id));
        } else if (filtro !== 'todos') {
            itensFiltrados = itens.filter(item => item.categoria === filtro);
        }

        if (itensFiltrados.length === 0) {
            lojaGrid.innerHTML = `
                <div class="sem-itens">
                    <i class="fa-regular fa-face-frown"></i>
                    <p>Nenhum item encontrado nesta categoria.</p>
                </div>
            `;
            return;
        }

        itensFiltrados.forEach(item => {
            const card = document.createElement('div');
            card.className = 'item-card';

            const status = getStatusItem(item);
            const comprado = verificarSeComprado(item.id);
            const ativo = verificarSeAtivo(item.id);

            card.innerHTML = `
                <div class="icone">
                    <i class="${item.icone || 'fa-solid fa-gift'}"></i>
                </div>
                <div class="nome">${item.nome}</div>
                <div class="descricao">${item.descricao}</div>
                <div class="preco">
                    <i class="fa-solid fa-star"></i>
                    ${item.preco}
                </div>
                <div class="status ${status.classe}">${status.texto}</div>
                ${comprado ? '<div class="badge-ativo">✔️ ATIVO</div>' : ''}
            `;

            // Se já comprado, ativar ao clicar
            if (comprado) {
                card.style.cursor = 'pointer';
                card.addEventListener('click', function() {
                    ativarItem(item.id);
                });
            } else if (status.status !== 'insuficiente') {
                card.addEventListener('click', function() {
                    abrirModalCompra(item);
                });
            }

            lojaGrid.appendChild(card);
        });
    }

    function abrirModalCompra(item) {
        itemSelecionado = item;

        if (modalIcone) {
            modalIcone.innerHTML = `<i class="${item.icone || 'fa-solid fa-gift'}"></i>`;
        }
        if (modalTitulo) modalTitulo.textContent = `Comprar ${item.nome}`;
        if (modalDescricao) modalDescricao.textContent = item.descricao;
        if (modalPreco) modalPreco.textContent = item.preco;

        if (modalCompra) {
            modalCompra.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function fecharModalCompra() {
        if (modalCompra) {
            modalCompra.style.display = 'none';
            document.body.style.overflow = '';
        }
        itemSelecionado = null;
    }

    function comprarItem() {
        if (!itemSelecionado) return;

        const estrelas = lojaData.estrelas || 0;
        const preco = itemSelecionado.preco;

        if (estrelas < preco) {
            alert('Você não tem estrelas suficientes!');
            return;
        }

        // Descontar estrelas
        lojaData.estrelas = estrelas - preco;

        // Adicionar aos itens comprados
        if (!lojaData.itens_comprados) {
            lojaData.itens_comprados = [];
        }
        lojaData.itens_comprados.push(itemSelecionado.id);

        // Salvar no servidor
        salvarLoja();

        // Fechar modal
        fecharModalCompra();

        // Mostrar sucesso
        mostrarSucesso(`🎉 Você comprou "${itemSelecionado.nome}"!`);

        // Atualizar interface
        atualizarSaldo();
        renderizarItens(filtroAtual);
        
        // Atualizar perfil
        setTimeout(atualizarPerfilComItens, 100);
        setTimeout(notificarPerfilAtualizado, 200);
    }

    function ativarItem(itemId) {
        // Salvar no perfil
        perfilData.item_ativo = itemId;

        // Salvar perfil
        salvarPerfil();

        // Mostrar feedback
        const item = (lojaData.itens || []).find(i => i.id === itemId);
        if (item) {
            mostrarSucesso(`✅ "${item.nome}" ativado no seu perfil!`);
        }

        renderizarItens(filtroAtual);
        
        // Atualizar perfil
        setTimeout(atualizarPerfilComItens, 100);
        setTimeout(notificarPerfilAtualizado, 200);
    }

    function mostrarSucesso(mensagem) {
        if (mensagemSucesso) {
            mensagemSucesso.textContent = mensagem;
        }
        if (modalSucesso) {
            modalSucesso.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function fecharSucessoModal() {
        if (modalSucesso) {
            modalSucesso.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // =================================================
    // SALVAR DADOS
    // =================================================

    async function salvarLoja() {
        try {
            const resposta = await fetch(LOJA_SAVE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(lojaData)
            });

            const textoResposta = await resposta.text();
            console.log('Loja salva:', resposta.status, textoResposta);
        } catch (erro) {
            console.error('Erro ao salvar loja:', erro);
        }
    }

    async function salvarPerfil() {
        try {
            const resposta = await fetch('../perfil/salvar_perfil.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(perfilData)
            });

            console.log('Perfil salvo:', resposta.status);
        } catch (erro) {
            console.error('Erro ao salvar perfil:', erro);
        }
    }

    // =================================================
    // ATUALIZAR PERFIL COM OS ITENS DA LOJA
    // =================================================

    function atualizarPerfilComItens() {
        const itensComprados = lojaData.itens_comprados || [];
        const todosItens = lojaData.itens || [];
        
        // Filtrar apenas os itens comprados
        const itensAtivos = todosItens.filter(item => itensComprados.includes(item.id));
        
        // Salvar no perfil (via sessionStorage)
        try {
            sessionStorage.setItem('itens_loja', JSON.stringify(itensAtivos));
            sessionStorage.setItem('estrelas_total', lojaData.estrelas);
            sessionStorage.setItem('loja_atualizada', Date.now().toString());
            console.log('Perfil atualizado com', itensAtivos.length, 'itens');
        } catch (e) {
            console.log('Não foi possível salvar no sessionStorage');
        }
    }

    // =================================================
    // NOTIFICAR PERFIL SOBRE ATUALIZAÇÕES
    // =================================================

    function notificarPerfilAtualizado() {
        // Usar BroadcastChannel para notificar o perfil
        try {
            const channel = new BroadcastChannel('foag_loja');
            channel.postMessage({ type: 'LOJA_ATUALIZADA' });
            setTimeout(() => channel.close(), 100);
            console.log('📢 Perfil notificado via BroadcastChannel');
        } catch (e) {
            console.log('BroadcastChannel não suportado, usando sessionStorage');
        }
        
        // Também salvar no sessionStorage
        try {
            const itensComprados = lojaData.itens_comprados || [];
            const todosItens = lojaData.itens || [];
            const itensAtivos = todosItens.filter(item => itensComprados.includes(item.id));
            
            sessionStorage.setItem('itens_loja', JSON.stringify(itensAtivos));
            sessionStorage.setItem('estrelas_total', lojaData.estrelas);
            sessionStorage.setItem('loja_atualizada', Date.now().toString());
        } catch (e) {}
    }

    // =================================================
    // BOTÃO SECRETO PARA GANHAR ESTRELAS (TESTE)
    // =================================================

    const botaoSecreto = document.getElementById('botaoEstrelasSecretas');
    const modalEstrelas = document.getElementById('modal-estrelas');
    const mensagemEstrelas = document.getElementById('mensagemEstrelas');
    const fecharEstrelas = document.getElementById('fechar-estrelas');

    let clicksSecretos = 0;
    let tempoUltimoClick = 0;

    console.log('🔮 Botão secreto encontrado:', botaoSecreto ? 'SIM' : 'NÃO');

    if (botaoSecreto) {
        botaoSecreto.addEventListener('click', function(evento) {
            evento.stopPropagation();
            const agora = Date.now();
            
            console.log('🔮 Clique secreto!', clicksSecretos + 1);
            
            // Reset se passar mais de 2 segundos
            if (agora - tempoUltimoClick > 2000) {
                clicksSecretos = 0;
                console.log('🔄 Resetando contador');
            }
            
            clicksSecretos++;
            tempoUltimoClick = agora;
            
            // Feedback visual
            this.classList.add('ativo');
            setTimeout(() => {
                this.classList.remove('ativo');
            }, 500);
            
            // Se clicou 5 vezes rapidamente
            if (clicksSecretos >= 5) {
                clicksSecretos = 0;
                console.log('🌟 GANHOU 10 ESTRELAS!');
                ganharEstrelas(10);
            }
        });
        
        console.log('✅ Botão secreto configurado!');
    } else {
        console.log('❌ Botão secreto NÃO encontrado!');
    }

    function ganharEstrelas(quantidade) {
        console.log('⭐ Ganhando', quantidade, 'estrelas');
        
        // Atualizar estrelas
        lojaData.estrelas = (lojaData.estrelas || 0) + quantidade;
        
        // Salvar no servidor
        salvarLoja();
        
        // Mostrar modal
        if (mensagemEstrelas) {
            mensagemEstrelas.innerHTML = `Você ganhou <strong>${quantidade} estrelas</strong>! 🌟<br><small>Total: ${lojaData.estrelas} estrelas</small>`;
        }
        
        if (modalEstrelas) {
            modalEstrelas.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            console.log('📢 Modal de estrelas aberto');
        }
        
        // Atualizar interface
        atualizarSaldo();
        renderizarItens(filtroAtual);
        
        // Atualizar perfil
        setTimeout(atualizarPerfilComItens, 100);
        setTimeout(notificarPerfilAtualizado, 200);
    }

    // Fechar modal de estrelas
    if (fecharEstrelas) {
        fecharEstrelas.addEventListener('click', function() {
            if (modalEstrelas) {
                modalEstrelas.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }

    if (modalEstrelas) {
        modalEstrelas.addEventListener('click', function(evento) {
            if (evento.target === this) {
                this.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }

    // =================================================
    // EVENTOS
    // =================================================

    // Filtros
    filtrosBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filtrosBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            filtroAtual = this.dataset.filtro;
            renderizarItens(filtroAtual);
        });
    });

    // Modal de compra
    confirmarCompra?.addEventListener('click', comprarItem);
    cancelarCompra?.addEventListener('click', fecharModalCompra);

    modalCompra?.addEventListener('click', function(evento) {
        if (evento.target === this) {
            fecharModalCompra();
        }
    });

    // Modal de sucesso
    fecharSucesso?.addEventListener('click', fecharSucessoModal);

    modalSucesso?.addEventListener('click', function(evento) {
        if (evento.target === this) {
            fecharSucessoModal();
        }
    });

    // =================================================
    // TECLA ESC
    // =================================================

    document.addEventListener('keydown', function(evento) {
        if (evento.key === 'Escape') {
            fecharModalCompra();
            fecharSucessoModal();
            if (modalEstrelas) {
                modalEstrelas.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
    });

    // =================================================
    // INICIALIZAÇÃO
    // =================================================

    atualizarSaldo();
    renderizarItens('todos');
    setTimeout(atualizarPerfilComItens, 500);

    console.log('Loja pronta ✅');
    console.log('🔮 Botão secreto ativado! Clique 5x rápido para ganhar estrelas!');
});
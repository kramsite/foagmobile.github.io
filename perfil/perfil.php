<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOAG - Perfil</title>
    <link rel="stylesheet" href="perfilfil.css?v=12">
    <link rel="stylesheet" href="../m.escuro/dark_basee.css?v=12">
    <link rel="stylesheet" href="../acessibilidade/acessibilidade.css?v=12">
    <link rel="stylesheet" href="dark-per.css?v=12">
    <script src="../acessibilidade/acessibilidade.js?v=4" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../m.escuro/dark-mode.js"></script>
</head>

<body>
    <header class="cabecalho">
        FOAG
        <div class="header-icons">
            <i id="icon-configuracoes" class="fa-solid fa-gear" title="Configurações"></i>
            <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
            <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
        </div>
    </header>

    <div class="container">
        <nav class="menu">
            <a href="../inicioo/inicio.php" class="<?= $current === "inicio.php" ? "active" : "" ?>">
                <i class="fa-solid fa-house"></i> Início
            </a>
            <a href="../calend/calendario.php" class="<?= $current === "calendario.php" ? "active" : "" ?>">
                <i class="fa-solid fa-calendar-days"></i> Calendário
            </a>
            <a href="../bloco/agenda.php" class="<?= $current === "agenda.php" ? "active" : "" ?>">
                <i class="fa-solid fa-book"></i> Agenda
            </a>
            <a href="../pomodoro/pomodoro.php" class="<?= $current === "pomodoro.php" ? "active" : "" ?>">
                <i class="fa-solid fa-stopwatch"></i> Pomodoro
            </a>
            <a href="../notas/notas.php" class="<?= $current === "notas.php" ? "active" : "" ?>">
                <i class="fa-solid fa-check-double"></i> Boletim
            </a>
            <a href="../loja/loja.php" class="<?= $current === "loja.php" ? "active" : "" ?>">
                <i class="fa-solid fa-store"></i> Loja
            </a>
            <a href="../rank/rank.php" class="<?= $current === "rank.php" ? "active" : "" ?>">
                <i class="fa-solid fa-trophy"></i> Ranking
            </a>
        </nav>

        <main class="conteudo perfil-conteudo">
            <div class="perfil-wrapper">
                <div class="titulo-pagina">
                    <div>
                        <span>Área do usuário</span>
                        <h1>Meu perfil</h1>
                    </div>
                    <a href="editar.php" class="btn-editar-topo">
                        <i class="fa-solid fa-pen"></i> Editar perfil
                    </a>
                </div>

                <!-- CARD DE DESTAQUE -->
                <section class="perfil-destaque">
                    <div class="perfil-identidade">
                        <div class="foto-container">
                            <div class="moldura-container">
                                <div class="moldura-borda" id="molduraPerfil">
                                    <img src="<?= escapar($caminho_foto) ?>" alt="Foto de perfil de <?= escapar($nome) ?>">
                                </div>
                                <span class="foto-status"></span>
                            </div>
                        </div>
                        <div class="perfil-texto">
                            <span class="etiqueta-perfil">Perfil do estudante</span>
                            <h2><?= escapar($nome) ?></h2>
                            <p class="email-perfil">
                                <i class="fa-regular fa-envelope"></i>
                                <?= escapar($email) ?>
                            </p>
                            <div class="perfil-resumo">
                                <span><i class="fa-solid fa-book-open"></i> <?= escapar($serie) ?></span>
                                <span><i class="fa-solid fa-school"></i> <?= escapar($escola) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="perfil-ilustracao">
                        <span class="circulo circulo-1"></span>
                        <span class="circulo circulo-2"></span>
                        <span class="circulo circulo-3"></span>
                        <div class="icone-estudante">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                    </div>
                </section>

                <!-- ===========================================
                     CARD PRINCIPAL - INFORMAÇÕES + INSÍGNIAS
                ============================================ -->
                <section class="dados-card">

                    <!-- CABEÇALHO INFORMAÇÕES -->
                    <div class="dados-cabecalho">
                        <div class="dados-icone">
                            <i class="fa-regular fa-address-card"></i>
                        </div>
                        <div>
                            <h3>Informações cadastradas</h3>
                            <p>Confira os dados salvos na sua conta.</p>
                        </div>
                    </div>

                    <!-- GRID DE DADOS -->
                    <div class="dados-grid">
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-regular fa-user"></i></div>
                            <div>
                                <span>Nome completo</span>
                                <strong><?= escapar($nome) ?></strong>
                            </div>
                        </div>
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-regular fa-envelope"></i></div>
                            <div>
                                <span>E-mail</span>
                                <strong><?= escapar($email) ?></strong>
                            </div>
                        </div>
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-regular fa-calendar"></i></div>
                            <div>
                                <span>Data de nascimento</span>
                                <strong><?= escapar($nascimento) ?></strong>
                            </div>
                        </div>
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-solid fa-phone"></i></div>
                            <div>
                                <span>Telefone</span>
                                <strong><?= escapar($telefone) ?></strong>
                            </div>
                        </div>
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-solid fa-book-open-reader"></i></div>
                            <div>
                                <span>Série ou ano</span>
                                <strong><?= escapar($serie) ?></strong>
                            </div>
                        </div>
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-solid fa-school"></i></div>
                            <div>
                                <span>Escola ou faculdade</span>
                                <strong><?= escapar($escola) ?></strong>
                            </div>
                        </div>
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-solid fa-location-dot"></i></div>
                            <div>
                                <span>Cidade</span>
                                <strong><?= escapar($localidade) ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- ===========================================
                         DIVISOR + INSÍGNIAS (DENTRO DO MESMO CARD)
                    ============================================ -->
                    <div class="insignias-divider"></div>

                    <div class="insignias-section">
                        <div class="insignias-cabecalho">
                            <div class="insignias-icone">
                                <i class="fa-solid fa-award"></i>
                            </div>
                            <div>
                                <h3>Minhas Insígnias</h3>
                                <p>Conquistas desbloqueadas durante sua jornada</p>
                            </div>
                            <span class="contador-insignias">
                                <?= count($insignias_usuario) ?> / <?= count($insignias_disponiveis) ?>
                            </span>
                        </div>

                        <?php if (empty($insignias_usuario)): ?>
                            <div class="sem-insignias">
                                <i class="fa-solid fa-trophy"></i>
                                <p>Você ainda não desbloqueou nenhuma insígnia</p>
                                <small>Continue estudando e conquistando!</small>
                            </div>
                        <?php else: ?>
                            <div class="insignias-grid">
                                <?php 
                                $categorias = [];
                                foreach ($insignias_usuario as $insignia) {
                                    $cat = $insignia['categoria'] ?? 'conquista';
                                    if (!isset($categorias[$cat])) {
                                        $categorias[$cat] = [];
                                    }
                                    $categorias[$cat][] = $insignia;
                                }
                                
                                foreach ($categorias as $categoria => $itens):
                                ?>
                                    <div class="insignia-categoria-grupo">
                                        <div class="insignia-categoria-titulo">
                                            <?php 
                                            $iconeCat = $categorias_insignias[$categoria]['icone'] ?? 'fa-solid fa-trophy';
                                            $nomeCat = $categorias_insignias[$categoria]['nome'] ?? ucfirst($categoria);
                                            ?>
                                            <i class="<?= $iconeCat ?>"></i>
                                            <?= $nomeCat ?>
                                            <span class="categoria-contador"><?= count($itens) ?></span>
                                        </div>
                                        <div class="insignias-grid-sub">
                                            <?php foreach ($itens as $insignia): ?>
                                                <div class="insignia-item" data-id="<?= escapar($insignia['id']) ?>" title="<?= escapar($insignia['descricao']) ?>">
                                                    <div class="insignia-imagem">
                                                        <?php if (file_exists(__DIR__ . '/../img/insignias/' . $insignia['imagem'])): ?>
                                                            <img src="../img/insignias/<?= escapar($insignia['imagem']) ?>" alt="<?= escapar($insignia['nome']) ?>">
                                                        <?php else: ?>
                                                            <i class="<?= escapar($insignia['icone']) ?>" style="color: <?= escapar($insignia['cor']) ?>;"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="insignia-info">
                                                        <span class="insignia-nome"><?= escapar($insignia['nome']) ?></span>
                                                        <span class="insignia-categoria"><?= escapar(ucfirst($insignia['categoria'])) ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </section>

                <!-- ===========================================
                     LOJA - ITENS COMPRADOS
                ============================================ -->
                <section class="loja-itens-card">
                    <div class="loja-itens-cabecalho">
                        <div class="loja-itens-icone">
                            <i class="fa-solid fa-store"></i>
                        </div>
                        <div>
                            <h3>Minhas Personalizações</h3>
                            <p>Itens comprados na Loja de Estrelas</p>
                        </div>
                        <a href="../loja/loja.php" class="btn-ir-loja">
                            <i class="fa-solid fa-cart-shopping"></i> Ir à Loja
                        </a>
                    </div>
                    <div class="loja-itens-grid" id="itensLojaPerfil">
                        <div class="carregando-itens">
                            <i class="fa-solid fa-spinner fa-spin"></i> Carregando itens...
                        </div>
                    </div>
                </section>

            </div>
        </main>
    </div>

    <!-- MODAIS -->
    <div id="logout-modal" class="modal">
        <div class="modal-content">
            <h3>Ah... já vai?</h3>
            <h4>Tem certeza que deseja sair?</h4>
            <div class="modal-buttons">
                <button id="confirm-logout">Sim</button>
                <button id="cancel-logout">Cancelar</button>
            </div>
        </div>
    </div>

    <div id="fogi-modal">
        <div class="fogi-container">
            <div class="fogi-header">
                <span>FOGi — Assistente de Estudos</span>
                <button id="fogi-close">Fechar</button>
            </div>
            <iframe id="fogi-iframe" src="about:blank"></iframe>
        </div>
    </div>

    <footer>&copy; 2025 FOAG. Todos os direitos reservados.</footer>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        console.log('Perfil carregado ✅');

        // ===========================================
        // FOGI
        // ===========================================
        const fogiBtn = document.getElementById("icon-fogi");
        const fogiModal = document.getElementById("fogi-modal");
        const fogiFrame = document.getElementById("fogi-iframe");
        const fogiClose = document.getElementById("fogi-close");

        if (fogiBtn && fogiModal && fogiFrame && fogiClose) {
            fogiBtn.addEventListener("click", function() {
                fogiFrame.src = "http://127.0.0.1:5000";
                fogiModal.style.display = "flex";
                document.body.style.overflow = "hidden";
            });

            fogiClose.addEventListener("click", function() {
                fogiModal.style.display = "none";
                fogiFrame.src = "about:blank";
                document.body.style.overflow = "";
            });

            window.addEventListener("message", function(evento) {
                if (evento.data && evento.data.type === "FOGI_CLOSE") {
                    fogiModal.style.display = "none";
                    fogiFrame.src = "about:blank";
                    document.body.style.overflow = "";
                }
            });
        }

        // ===========================================
        // LOGOUT
        // ===========================================
        const sairBtn = document.getElementById("icon-sair");
        const logoutModal = document.getElementById("logout-modal");
        const confirmarLogout = document.getElementById("confirm-logout");
        const cancelarLogout = document.getElementById("cancel-logout");

        if (sairBtn && logoutModal && confirmarLogout && cancelarLogout) {
            sairBtn.addEventListener("click", function() {
                logoutModal.style.display = "flex";
            });

            cancelarLogout.addEventListener("click", function() {
                logoutModal.style.display = "none";
            });

            confirmarLogout.addEventListener("click", function() {
                window.location.href = "../login/logout.php";
            });

            logoutModal.addEventListener("click", function(evento) {
                if (evento.target === logoutModal) {
                    logoutModal.style.display = "none";
                }
            });
        }

        // ===========================================
        // CARREGAR ITENS DA LOJA NO PERFIL
        // ===========================================

        function carregarItensLojaPerfil() {
            const container = document.getElementById('itensLojaPerfil');
            if (!container) {
                console.log('❌ Container itensLojaPerfil não encontrado');
                return;
            }

            console.log('🔄 Carregando itens da loja...');

            container.innerHTML = `
                <div class="carregando-itens">
                    <i class="fa-solid fa-spinner fa-spin"></i> Carregando itens...
                </div>
            `;

            fetch('../loja/loja_data.php')
                .then(function(resposta) {
                    if (!resposta.ok) {
                        throw new Error('Erro ao buscar dados');
                    }
                    return resposta.json();
                })
                .then(function(dados) {
                    console.log('📦 Dados da loja:', dados);
                    
                    if (dados && dados.estrelas !== undefined) {
                        atualizarEstrelasPerfil(dados.estrelas);
                    }
                    
                    if (dados && dados.itens_comprados && dados.itens) {
                        const itensComprados = dados.itens_comprados || [];
                        const todosItens = dados.itens || [];
                        const itensAtivos = todosItens.filter(function(item) {
                            return itensComprados.includes(item.id);
                        });
                        
                        try {
                            sessionStorage.setItem('itens_loja', JSON.stringify(itensAtivos));
                            sessionStorage.setItem('estrelas_total', dados.estrelas || 0);
                        } catch (e) {}
                        
                        renderizarItensPerfil(container, itensAtivos, dados.estrelas || 0);
                    } else {
                        container.innerHTML = `
                            <div class="sem-itens-loja">
                                <i class="fa-solid fa-store"></i>
                                <p>Você ainda não comprou nada na loja</p>
                                <small>⭐ ${dados?.estrelas || 0} estrelas disponíveis</small>
                            </div>
                        `;
                    }
                })
                .catch(function(erro) {
                    console.error('❌ Erro ao buscar itens da loja:', erro);
                    carregarDoSessionStorage(container);
                });
        }

        function carregarDoSessionStorage(container) {
            try {
                const itensSalvos = sessionStorage.getItem('itens_loja');
                const estrelasSalvas = sessionStorage.getItem('estrelas_total');
                
                if (itensSalvos) {
                    const itens = JSON.parse(itensSalvos);
                    const estrelas = parseInt(estrelasSalvas) || 0;
                    console.log('📦 Carregando do sessionStorage:', itens.length, 'itens');
                    renderizarItensPerfil(container, itens, estrelas);
                    return;
                }
            } catch (e) {}
            
            container.innerHTML = `
                <div class="sem-itens-loja">
                    <i class="fa-solid fa-store"></i>
                    <p>Erro ao carregar itens</p>
                    <small>Tente recarregar a página</small>
                </div>
            `;
        }

        function atualizarEstrelasPerfil(estrelas) {
            const semItens = document.querySelector('.sem-itens-loja small');
            if (semItens) {
                semItens.textContent = `⭐ ${estrelas} estrelas disponíveis`;
            }
        }

        function renderizarItensPerfil(container, itens, estrelas) {
            if (!itens || itens.length === 0) {
                container.innerHTML = `
                    <div class="sem-itens-loja">
                        <i class="fa-solid fa-store"></i>
                        <p>Você ainda não comprou nada na loja</p>
                        <small>⭐ ${estrelas || 0} estrelas disponíveis</small>
                    </div>
                `;
                return;
            }
            
            let html = '';
            const categoriasTraduzidas = {
                'temas': 'Tema',
                'insignias': 'Insígnia',
                'emojis': 'Emoji',
                'fundos': 'Fundo',
                'molduras': 'Moldura',
                'efeitos': 'Efeito'
            };
            
            itens.forEach(function(item) {
                const categoria = item.categoria || 'geral';
                const icone = item.icone || 'fa-solid fa-gift';
                const categoriaTraduzida = categoriasTraduzidas[categoria] || categoria;
                
                html += `
                    <div class="item-loja-perfil">
                        <div class="icone-item"><i class="${icone}"></i></div>
                        <div class="nome-item">${item.nome || 'Item'}</div>
                        <div class="categoria-item">${categoriaTraduzida}</div>
                    </div>
                `;
            });
            
            html += `
                <div class="item-loja-perfil total-estrelas">
                    <div class="icone-item"><i class="fa-solid fa-star" style="color: #ffd700;"></i></div>
                    <div class="nome-item">${estrelas || 0}</div>
                    <div class="categoria-item">Estrelas</div>
                </div>
            `;
            
            container.innerHTML = html;
            console.log('✅ Itens renderizados:', itens.length, 'itens + estrelas');
        }

        // ===========================================
        // INICIALIZAR
        // ===========================================

        setTimeout(carregarItensLojaPerfil, 500);

        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                console.log('👁️ Página visível novamente, recarregando...');
                setTimeout(carregarItensLojaPerfil, 300);
            }
        });

        try {
            const channel = new BroadcastChannel('foag_loja');
            channel.onmessage = function(evento) {
                if (evento.data && evento.data.type === 'LOJA_ATUALIZADA') {
                    console.log('📢 Loja atualizada! Recarregando perfil...');
                    setTimeout(carregarItensLojaPerfil, 300);
                }
            };
            console.log('📡 BroadcastChannel configurado');
        } catch (e) {
            console.log('⚠️ BroadcastChannel não suportado');
        }

        window.addEventListener('storage', function(evento) {
            if (evento.key === 'itens_loja' || evento.key === 'estrelas_total' || evento.key === 'loja_atualizada') {
                console.log('📦 Storage atualizado:', evento.key);
                setTimeout(carregarItensLojaPerfil, 300);
            }
        });

        console.log('✅ Perfil pronto!');
    });
    </script>
</body>
</html>
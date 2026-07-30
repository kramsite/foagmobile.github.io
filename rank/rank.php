<?php
session_start();

// ======================================
// VERIFICAR LOGIN
// ======================================

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$current = basename($_SERVER['PHP_SELF']);

// ======================================
// DADOS DO RANKING (PLACEHOLDER)
// ======================================

// Estrutura para substituir pelo banco de dados depois
$rankings = [
    'estrelas' => [
        'titulo' => '⭐ Mais Estrelas',
        'icone' => 'fa-solid fa-star',
        'cor' => '#ffd700',
        'jogadores' => [
            ['nome' => 'Ana Silva', 'valor' => 245, 'avatar' => '👩‍🎓', 'nivel' => 1],
            ['nome' => 'Carlos Mendes', 'valor' => 198, 'avatar' => '👨‍🎓', 'nivel' => 2],
            ['nome' => 'Mariana Santos', 'valor' => 167, 'avatar' => '👩‍💻', 'nivel' => 3],
            ['nome' => 'João Pereira', 'valor' => 143, 'avatar' => '👨‍💻', 'nivel' => 4],
            ['nome' => 'Juliana Costa', 'valor' => 128, 'avatar' => '👩‍🔬', 'nivel' => 5],
        ]
    ],
    'pomodoro' => [
        'titulo' => '⏱️ Mais Tempo no Pomodoro',
        'icone' => 'fa-solid fa-clock',
        'cor' => '#4caf50',
        'jogadores' => [
            ['nome' => 'Mariana Santos', 'valor' => '42h 30min', 'avatar' => '👩‍💻', 'nivel' => 1],
            ['nome' => 'Ana Silva', 'valor' => '38h 15min', 'avatar' => '👩‍🎓', 'nivel' => 2],
            ['nome' => 'Carlos Mendes', 'valor' => '35h 45min', 'avatar' => '👨‍🎓', 'nivel' => 3],
            ['nome' => 'João Pereira', 'valor' => '29h 20min', 'avatar' => '👨‍💻', 'nivel' => 4],
            ['nome' => 'Juliana Costa', 'valor' => '25h 50min', 'avatar' => '👩‍🔬', 'nivel' => 5],
        ]
    ],
    'faltas' => [
        'titulo' => '📊 Menos Faltas',
        'icone' => 'fa-solid fa-check-circle',
        'cor' => '#2196f3',
        'jogadores' => [
            ['nome' => 'João Pereira', 'valor' => '0 faltas', 'avatar' => '👨‍💻', 'nivel' => 1],
            ['nome' => 'Ana Silva', 'valor' => '1 falta', 'avatar' => '👩‍🎓', 'nivel' => 2],
            ['nome' => 'Carlos Mendes', 'valor' => '2 faltas', 'avatar' => '👨‍🎓', 'nivel' => 3],
            ['nome' => 'Mariana Santos', 'valor' => '3 faltas', 'avatar' => '👩‍💻', 'nivel' => 4],
            ['nome' => 'Juliana Costa', 'valor' => '4 faltas', 'avatar' => '👩‍🔬', 'nivel' => 5],

        ]
    ],
    'notas' => [
        'titulo' => '📚 Melhores Notas',
        'icone' => 'fa-solid fa-graduation-cap',
        'cor' => '#9c27b0',
        'jogadores' => [
            ['nome' => 'Juliana Costa', 'valor' => '9.8', 'avatar' => '👩‍🔬', 'nivel' => 1],
            ['nome' => 'Mariana Santos', 'valor' => '9.5', 'avatar' => '👩‍💻', 'nivel' => 2],
            ['nome' => 'Ana Silva', 'valor' => '9.2', 'avatar' => '👩‍🎓', 'nivel' => 3],
            ['nome' => 'Carlos Mendes', 'valor' => '8.9', 'avatar' => '👨‍🎓', 'nivel' => 4],
            ['nome' => 'João Pereira', 'valor' => '8.5', 'avatar' => '👨‍💻', 'nivel' => 5],
        ]
    ],
];

// ======================================
// USUÁRIO ATUAL (para destacar)
// ======================================

$usuarioAtual = 'Ana Silva'; // Placeholder - depois pegar do banco
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking — FOAG</title>

    <!-- CSS do Ranking -->
    <link rel="stylesheet" href="rank.css">

    <!-- Modo escuro -->
    <link rel="stylesheet" href="../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="dark_rank.css">

    <!-- Fontes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Modo escuro -->
    <script src="../m.escuro/dark-mode.js"></script>

    <style>
        /* Animações extras */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(255, 215, 0, 0.2); }
            50% { box-shadow: 0 0 40px rgba(255, 215, 0, 0.4); }
        }

        .rank-card {
            animation: slideUp 0.5s ease forwards;
            opacity: 0;
        }

        .rank-card:nth-child(1) { animation-delay: 0.1s; }
        .rank-card:nth-child(2) { animation-delay: 0.2s; }
        .rank-card:nth-child(3) { animation-delay: 0.3s; }
        .rank-card:nth-child(4) { animation-delay: 0.4s; }

        .rank-card .podium-1 {
            animation: glow 2s ease-in-out infinite;
        }

        .rank-card .podium-1 .rank-number {
            background: linear-gradient(135deg, #ffd700, #ffb300);
            color: #fff;
            font-size: 20px;
        }

        .rank-card .podium-2 .rank-number {
            background: linear-gradient(135deg, #c0c0c0, #a0a0a0);
            color: #fff;
        }

        .rank-card .podium-3 .rank-number {
            background: linear-gradient(135deg, #cd7f32, #b87333);
            color: #fff;
        }
    </style>
</head>

<body>

    <!-- ======================================
         CABEÇALHO
    ======================================= -->

    <header class="cabecalho">
        FOAG

        <div class="header-icons">
            <i id="themeToggle" class="fa-solid fa-moon" title="Modo Escuro"></i>
            <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
            <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
        </div>
    </header>

    <div class="container">

        <!-- ======================================
             MENU
        ======================================= -->

        <nav class="menu">
            <a href="../inicioo/inicio.php" class="<?= $current === 'inicio.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i> Início
            </a>
            <a href="../calend/calendario.php" class="<?= $current === 'calendario.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-days"></i> Calendário
            </a>
            <a href="../bloco/agenda.php" class="<?= $current === 'agenda.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-book"></i> Agenda
            </a>
            <a href="../pomodoro/pomodoro.php" class="<?= $current === 'pomodoro.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-stopwatch"></i> Pomodoro
            </a>
            <a href="../notas/notas.php" class="<?= $current === 'notas.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-check-double"></i> Boletim
            </a>
            <a href="../horario/horario.php" class="<?= $current === 'horario.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-clock"></i> Horário
            </a>
            <a href="../loja/loja.php" class="<?= $current === 'loja.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-store"></i> Loja
            </a>
            <a href="../rank/rank.php" class="<?= $current === 'rank.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-trophy"></i> Ranking
            </a>
        </nav>

        <!-- ======================================
             CONTEÚDO
        ======================================= -->

        <main class="main-content">

            <!-- ==================================
                 HEADER DO RANKING (SEM ESTATÍSTICAS)
            =================================== -->

            <div class="ranking-header">
                <div class="ranking-titulo">
                    <h1>
                        <i class="fa-solid fa-trophy"></i>
                        Ranking FOAG
                    </h1>
                    <p>Veja quem está se destacando em cada categoria!</p>
                </div>
            </div>

            <!-- ==================================
                 RANKINGS
            =================================== -->

            <div class="rankings-grid">

                <?php foreach ($rankings as $key => $ranking): ?>
                <div class="rank-card">
                    <div class="rank-header" style="border-color: <?= $ranking['cor'] ?>;">
                        <div class="rank-title">
                            <i class="<?= $ranking['icone'] ?>" style="color: <?= $ranking['cor'] ?>;"></i>
                            <h2><?= $ranking['titulo'] ?></h2>
                        </div>
                        <span class="rank-count">Top <?= count($ranking['jogadores']) ?></span>
                    </div>

                    <div class="rank-body">
                        <?php foreach ($ranking['jogadores'] as $index => $jogador): 
                            $posicao = $index + 1;
                            $isUsuario = $jogador['nome'] === $usuarioAtual;
                            $podiumClass = '';
                            if ($posicao === 1) $podiumClass = 'podium-1';
                            elseif ($posicao === 2) $podiumClass = 'podium-2';
                            elseif ($posicao === 3) $podiumClass = 'podium-3';
                        ?>
                        <div class="rank-item <?= $isUsuario ? 'usuario-destaque' : '' ?> <?= $podiumClass ?>">
                            <div class="rank-position">
                                <span class="rank-number">#<?= $posicao ?></span>
                            </div>
                            <div class="rank-avatar">
                                <?= $jogador['avatar'] ?>
                            </div>
                            <div class="rank-info">
                                <span class="rank-nome">
                                    <?= $jogador['nome'] ?>
                                    <?php if ($isUsuario): ?>
                                        <span class="badge-eu">Você</span>
                                    <?php endif; ?>
                                </span>
                                <span class="rank-valor" style="color: <?= $ranking['cor'] ?>;">
                                    <?= $jogador['valor'] ?>
                                </span>
                            </div>
                            <div class="rank-nivel">
                                <span class="nivel-badge" style="background: <?= $ranking['cor'] ?>;">
                                    Nível <?= $jogador['nivel'] ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="rank-footer">
                        <button class="btn-ver-todos" data-categoria="<?= $key ?>">
                            Ver todos <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>

        </main>
    </div>

    <!-- ======================================
         MODAL: LOGOUT
    ======================================= -->

    <div id="logout-modal" class="modal">
        <div class="modal-content">
            <h3>Ah... já vai?</h3>
            <h4>Tem certeza de que deseja sair?</h4>
            <div class="modal-buttons">
                <button id="confirm-logout">Sim</button>
                <button id="cancel-logout">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- ======================================
         MODAL: DETALHES DA CATEGORIA
    ======================================= -->

    <div id="modal-categoria" class="modal-categoria">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalCategoriaTitulo">Ranking</h2>
                <button id="fechar-modal-categoria" class="btn-fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body" id="modalCategoriaBody">
                <!-- Conteúdo dinâmico -->
            </div>
        </div>
    </div>

    <footer>
        &copy; 2025 FOAG. Todos os direitos reservados.
    </footer>

    <!-- ======================================
         JAVASCRIPT
    ======================================= -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ==========================
            // MODAL DE LOGOUT
            // ==========================

            const perfilIcon = document.getElementById('icon-perfil');
            const logoutModal = document.getElementById('logout-modal');
            const iconSair = document.getElementById('icon-sair');
            const confirmarLogout = document.getElementById('confirm-logout');
            const cancelarLogout = document.getElementById('cancel-logout');

            perfilIcon?.addEventListener('click', function() {
                window.location.href = '../perfil/perfil.php';
            });

            iconSair?.addEventListener('click', function() {
                if (logoutModal) {
                    logoutModal.style.display = 'flex';
                }
            });

            confirmarLogout?.addEventListener('click', function() {
                window.location.href = '../login/index.php';
            });

            cancelarLogout?.addEventListener('click', function() {
                if (logoutModal) {
                    logoutModal.style.display = 'none';
                }
            });

            logoutModal?.addEventListener('click', function(evento) {
                if (evento.target === logoutModal) {
                    logoutModal.style.display = 'none';
                }
            });

            // ==========================
            // MODAL DE CATEGORIA
            // ==========================

            const modalCategoria = document.getElementById('modal-categoria');
            const modalCategoriaTitulo = document.getElementById('modalCategoriaTitulo');
            const modalCategoriaBody = document.getElementById('modalCategoriaBody');
            const fecharModalCategoria = document.getElementById('fechar-modal-categoria');

            // Dados do ranking do PHP
            const rankingsData = <?= json_encode($rankings, JSON_UNESCAPED_UNICODE) ?>;

            document.querySelectorAll('.btn-ver-todos').forEach(btn => {
                btn.addEventListener('click', function() {
                    const categoria = this.dataset.categoria;
                    const dados = rankingsData[categoria];

                    if (dados) {
                        modalCategoriaTitulo.innerHTML = `<i class="${dados.icone}" style="color: ${dados.cor};"></i> ${dados.titulo}`;

                        let html = `
                            <div class="categoria-stats">
                                <span><i class="fa-solid fa-users"></i> ${dados.jogadores.length} jogadores</span>
                                <span><i class="fa-solid fa-trophy" style="color: ${dados.cor};"></i> Top 1: ${dados.jogadores[0]?.nome || '-'}</span>
                            </div>
                            <div class="categoria-lista">
                        `;

                        dados.jogadores.forEach((jogador, index) => {
                            const posicao = index + 1;
                            const isUsuario = jogador.nome === '<?= $usuarioAtual ?>';
                            let medalha = '';
                            if (posicao === 1) medalha = '🥇';
                            else if (posicao === 2) medalha = '🥈';
                            else if (posicao === 3) medalha = '🥉';

                            html += `
                                <div class="categoria-item ${isUsuario ? 'usuario-destaque' : ''}">
                                    <span class="categoria-pos">${medalha || '#' + posicao}</span>
                                    <span class="categoria-avatar">${jogador.avatar}</span>
                                    <span class="categoria-nome">${jogador.nome} ${isUsuario ? '<span class="badge-eu">Você</span>' : ''}</span>
                                    <span class="categoria-valor" style="color: ${dados.cor};">${jogador.valor}</span>
                                    <span class="categoria-nivel">Nível ${jogador.nivel}</span>
                                </div>
                            `;
                        });

                        html += `</div>`;

                        modalCategoriaBody.innerHTML = html;
                        modalCategoria.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    }
                });
            });

            fecharModalCategoria?.addEventListener('click', function() {
                modalCategoria.style.display = 'none';
                document.body.style.overflow = '';
            });

            modalCategoria?.addEventListener('click', function(evento) {
                if (evento.target === this) {
                    modalCategoria.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });

            // ==========================
            // TECLA ESC
            // ==========================

            document.addEventListener('keydown', function(evento) {
                if (evento.key === 'Escape') {
                    if (modalCategoria?.style.display === 'flex') {
                        modalCategoria.style.display = 'none';
                        document.body.style.overflow = '';
                    }
                    if (logoutModal?.style.display === 'flex') {
                        logoutModal.style.display = 'none';
                    }
                }
            });
        });
    </script>

</body>

</html>
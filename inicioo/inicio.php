<?php
session_start();

$userId = $_SESSION['user_id'] ?? null;

// --------- VALORES PADRÃO ----------
$diasConsecutivos    = 0;
$tarefasPendentes    = 0;
$percentualGeral     = 0;
$totalPresencas      = 0;
$totalFaltas         = 0;
$percentualPresenca  = 0;
$notasImportantes    = [];

try {
    // ========= FREQUÊNCIA (CALENDÁRIO) =========
    $anoMes = date('Y-m');

    // AJUSTA SE PRECISAR (por usuário, etc.)
    $arquivoFreq = __DIR__ . "/../json/frequencia_{$anoMes}.json";

    if (file_exists($arquivoFreq)) {
        $dadosFreq = json_decode(file_get_contents($arquivoFreq), true);

        if (is_array($dadosFreq)) {
            foreach ($dadosFreq as $dia => $info) {
                $status = null;
                if (is_array($info)) {
                    $status = $info['status'] ?? $info['cor'] ?? null;
                }

                if (in_array($status, ['presenca', 'verde'], true)) {
                    $totalPresencas++;
                } elseif (in_array($status, ['falta', 'vermelho'], true)) {
                    $totalFaltas++;
                }
            }

            $aulasPossiveis = $totalPresencas + $totalFaltas;
            if ($aulasPossiveis > 0) {
                $percentualPresenca = round(($totalPresencas / $aulasPossiveis) * 100);
            }
        }
    }

    // ========= AGENDA / TAREFAS / NOTAS IMPORTANTES =========
    $hoje = date('Y-m-d');
    $diasProdutivosMapa = [];

    // AJUSTA SE TUA AGENDA FOR POR USUÁRIO
    $arquivoAgenda = __DIR__ . "/../json/agenda.json";

    if (file_exists($arquivoAgenda)) {
        $dadosAgenda = json_decode(file_get_contents($arquivoAgenda), true);

        if (is_array($dadosAgenda)) {
            foreach ($dadosAgenda as $tarefa) {
                if (!is_array($tarefa)) continue;

                $data       = $tarefa['data']       ?? null;
                $concluida  = $tarefa['concluida']  ?? false;
                $texto      = $tarefa['texto']      ?? ($tarefa['titulo'] ?? null);
                $importante = $tarefa['importante'] ?? false;

                // tarefas de hoje pendentes
                if ($data === $hoje && empty($concluida)) {
                    $tarefasPendentes++;
                }

                // dias com tarefa concluída (pra dias produtivos)
                if (!empty($concluida) && $data) {
                    $diasProdutivosMapa[$data] = true;
                }

                // anotações importantes (pra card da direita)
                if (!empty($importante) && !empty($texto)) {
                    $notasImportantes[] = [
                        'texto' => $texto,
                        'data'  => $data,
                    ];
                }
            }

            // ordenar notas: mais recentes primeiro
            usort($notasImportantes, function ($a, $b) {
                return strcmp($b['data'] ?? '', $a['data'] ?? '');
            });

            // limita a 5 no card
            $notasImportantes = array_slice($notasImportantes, 0, 5);

            // calcular dias consecutivos produtivos
            $diasConsecutivos = 0;
            $dataCursor = new DateTime($hoje);

            while (true) {
                $dataStr = $dataCursor->format('Y-m-d');
                if (!isset($diasProdutivosMapa[$dataStr])) {
                    break;
                }
                $diasConsecutivos++;
                $dataCursor->modify('-1 day');
            }
        }
    }

    // ========= TAXA GERAL DE PRODUTIVIDADE =========
    $pesoFreq   = 0.6;
    $pesoAgenda = 0.4;

    $indiceAgenda = 0;
    if ($diasConsecutivos > 0) {
        $indiceAgenda = min($diasConsecutivos, 10) * 10; // cada dia = 10%
    }

    $percentualGeraldouble = ($percentualPresenca * $pesoFreq) + ($indiceAgenda * $pesoAgenda);
    $percentualGeral = (int) round(min($percentualGeraldouble, 100));

} catch (Throwable $e) {
    // se quiser debugar:
    // echo "Erro: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOAG - Início</title>
    <link rel="stylesheet" href="inicioo.css">
    <link rel="stylesheet" href="dark_ini.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="cabecalho">
        <div class="header-left">
            <div class="logo">FOAG</div>
            <nav class="top-menu">
                <a href="../inicio/inicio.php" class="active">
                    <i class="fa-solid fa-house"></i> Início
                </a>
                <a href="../calend/calendario.php">
                    <i class="fa-solid fa-calendar-days"></i> Calendário
                </a>
                <a href="../bloco/agenda.php">
                    <i class="fa-solid fa-book"></i> Agenda
                </a>
                <a href="../pomodoro/pomodoro.php">
                    <i class="fa-solid fa-stopwatch"></i> Pomodoro
                </a>
                <a href="../notas/notas.php">
                    <i class="fa-solid fa-check-double"></i> Boletim
                </a>
                <a href="../horario/horario.php">
                    <i class="fa-solid fa-clock"></i> Horário
                </a>
            </nav>
        </div>
        <div class="header-icons">
            <i id="themeToggle" class="fa-solid fa-moon" title="Modo Escuro"></i>
            <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
            <i id="icon-fogi" class="fa-solid fa-robot" title="Assistente FOAG — FOGi"></i>
            <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
        </div>
    </header>

    <main class="main-content">
        <div class="welcome-container">
            <div class="left-panel">
                <div class="welcome-header">
                    <h1>Bem-vindo de volta! 👋</h1>
                    <p class="subtitle">Seu organizador pessoal FOAG está aqui para te ajudar a ser mais produtivo</p>
                </div>

                <div class="stats-grid-large">
                    <div class="stat-card-large">
                        <div class="stat-icon-large">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div class="stat-info-large">
                            <span class="stat-number-large" id="dias-consecutivos">
                                <?php echo $diasConsecutivos; ?>
                            </span>
                            <span class="stat-label-large">Dias produtivos consecutivos</span>
                        </div>
                    </div>

                    <div class="stat-card-large">
                        <div class="stat-icon-large">
                            <i class="fa-solid fa-tasks"></i>
                        </div>
                        <div class="stat-info-large">
                            <span class="stat-number-large" id="tarefas-pendentes">
                                <?php echo $tarefasPendentes; ?>
                            </span>
                            <span class="stat-label-large">Tarefas para hoje</span>
                        </div>
                    </div>

                    <div class="stat-card-large">
                        <div class="stat-icon-large">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div class="stat-info-large">
                            <span class="stat-number-large" id="percentual-geral">
                                <?php echo $percentualGeral; ?>%
                            </span>
                            <span class="stat-label-large">Taxa de produtividade</span>
                        </div>
                    </div>
                </div>

                <div class="motivational-section">
                    <div class="motivational-quote-large">
                        <i class="fa-solid fa-quote-left"></i>
                        <p id="quote-text">
                            Organizar é o primeiro passo para o sucesso! Comece seu dia planejando suas atividades.
                        </p>
                        <i class="fa-solid fa-quote-right"></i>
                    </div>

                    <div class="quick-tips">
                        <h3>💡 Dicas Rápidas</h3>
                        <ul>
                            <li>Revise suas tarefas pela manhã</li>
                            <li>Estabeleça metas realistas para o dia</li>
                            <li>Faça pausas regulares</li>
                            <li>Celebre suas conquistas</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="right-panel">
                <div class="info-card">
                    <div class="card-header">
                        <h2><i class="fa-solid fa-chart-line"></i> Resumo do Mês</h2>
                    </div>
                    <div class="card-content">
                        <div class="metric-row">
                            <div class="metric">
                                <span class="metric-value" id="total-presencas">
                                    <?php echo $totalPresencas; ?>
                                </span>
                                <span class="metric-label">Presenças</span>
                            </div>
                            <div class="metric">
                                <span class="metric-value faltas" id="total-faltas">
                                    <?php echo $totalFaltas; ?>
                                </span>
                                <span class="metric-label">Faltas</span>
                            </div>
                            <div class="metric">
                                <span class="metric-value" id="percentual-presenca">
                                    <?php echo $percentualPresenca; ?>%
                                </span>
                                <span class="metric-label">Frequência</span>
                            </div>
                        </div>

                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $percentualPresenca; ?>%"></div>
                            </div>
                            <span class="progress-text">Meta: 85% de frequência</span>
                        </div>
                    </div>
                </div>

                <!-- Anotações Importantes -->
                <div class="info-card">
                    <div class="card-header">
                        <h2><i class="fa-solid fa-star"></i> Anotações Importantes</h2>
                        <button class="btn-add" id="add-note">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <div class="card-content">
                        <div class="notes-list" id="notes-list">
                            <?php if (!empty($notasImportantes)): ?>
                                <?php foreach ($notasImportantes as $nota): ?>
                                    <div class="note-item">
                                        <p class="note-text">
                                            <?= htmlspecialchars($nota['texto']); ?>
                                        </p>
                                        <?php if (!empty($nota['data'])): ?>
                                            <span class="note-date">
                                                <?= date('d/m/Y', strtotime($nota['data'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="empty-notes" id="empty-notes" style="<?= !empty($notasImportantes) ? 'display:none;' : '' ?>">
                            <i class="fa-solid fa-clipboard"></i>
                            <p>Nenhuma anotação importante</p>
                            <button class="btn-primary" id="create-first-note">
                                <i class="fa-solid fa-plus"></i>
                                Criar primeira anotação
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lembretes -->
                <div class="info-card">
                    <div class="card-header">
                        <h2><i class="fa-solid fa-bell"></i> Próximos Lembretes</h2>
                    </div>
                    <div class="card-content">
                        <div class="reminders-list" id="reminders-list"></div>
                        <div class="empty-reminders" id="empty-reminders">
                            <i class="fa-solid fa-bell-slash"></i>
                            <p>Nenhum lembrete próximo</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Modal para nova anotação -->
    <div id="note-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nova Anotação Importante</h3>
                <button class="modal-close" id="close-note-modal">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <textarea id="note-text" placeholder="Digite sua anotação importante aqui..." maxlength="200"></textarea>
                <div class="modal-footer">
                    <button class="btn-secondary" id="cancel-note">Cancelar</button>
                    <button class="btn-primary" id="save-note">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação -->
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

    <!-- Modal da FOGi -->
    <div id="fogi-modal">
        <div class="fogi-container">
            <div class="fogi-header">
                <span>FOGi — Assistente de Estudos</span>
                <button id="fogi-close">Fechar</button>
            </div>
            <iframe id="fogi-iframe" src="about:blank"></iframe>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <p>&copy; 2025 FOAG. Todos os direitos reservados.</p>
            <p>Organize seu tempo, conquiste seus objetivos</p>
        </div>
    </footer>

    <script src="inicio.js"></script>
</body>
</html>

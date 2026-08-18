<?php
session_start();

// ======================================
// VERIFICAR LOGIN
// ======================================

if (empty($_SESSION['codigo_usuario'])) {
    header("Location: ../../login/index.php");
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];

// ======================================
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir = __DIR__ . '/../../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    exit("Pasta do usuário não encontrada.");
}

// ======================================
// ARQUIVO DO POMODORO
// ======================================

$arquivoPomodoro = $pastaUsuario . '/pomodoro.json';

if (!file_exists($arquivoPomodoro)) {
    $estadoInicial = [
        'disciplines' => ['Geral'],
        'sessions' => [],
        'goals' => new stdClass()
    ];

    file_put_contents(
        $arquivoPomodoro,
        json_encode(
            $estadoInicial,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ),
        LOCK_EX
    );
}

// ======================================
// CARREGAR POMODORO
// ======================================

$pomodoroData = json_decode(
    file_get_contents($arquivoPomodoro),
    true
);

if (!is_array($pomodoroData)) {
    $pomodoroData = [];
}

if (
    !isset($pomodoroData['sessions']) ||
    !is_array($pomodoroData['sessions'])
) {
    $pomodoroData['sessions'] = [];
}

if (
    !isset($pomodoroData['goals']) ||
    !is_array($pomodoroData['goals'])
) {
    $pomodoroData['goals'] = [];
}

// ======================================
// CARREGAR MATÉRIAS DO USUÁRIO
// ======================================

$arquivoMaterias = $pastaUsuario . '/materias.json';

$materiasData = [
    'materias' => []
];

if (file_exists($arquivoMaterias)) {

    $conteudoMaterias = json_decode(
        file_get_contents($arquivoMaterias),
        true
    );

    if (
        is_array($conteudoMaterias) &&
        isset($conteudoMaterias['materias']) &&
        is_array($conteudoMaterias['materias'])
    ) {
        $materiasData = $conteudoMaterias;
    }
}

$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>FOAG – Pomodoro</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="pomodoro.css">
  <link rel="stylesheet" href="../../m.escuro/dark_basee.css">
  <link rel="stylesheet" href="dark_pomo.css">

  <script src="../../m.escuro/dark-mode.js"></script>
  <!-- Chart.js para os gráficos -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <!-- Passa o estado inicial do Pomodoro para o JS -->
  <script>
  window.POMODORO_DATA = <?= json_encode(
      $pomodoroData,
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
  ); ?>;

  window.MATERIAS_DATA = <?= json_encode(
      $materiasData,
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
  ); ?>;

  window.POMODORO_SAVE_URL = "salvar_pomodoro.php";
</script>
</head>
<body>
  <!-- Cabeçalho -->
  <header class="cabecalho">
    FOAG
    <div class="header-icons">
  <i id="icon-configuracoes" class="fa-solid fa-gear" title="Configurações"></i>
      <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
      <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
    </div>
  </header>

  <div class="container">
    <!-- Menu lateral -->
    <nav class="menu">

    <a href="../../inicioo/inicio.php">
      <i class="fa-solid fa-house"></i>
      Início
    </a>

    <a href="../../calend/calendario.php">
      <i class="fa-solid fa-calendar-days"></i>
      Calendário
    </a>

    <a href="../../bloco/agenda.php">
      <i class="fa-solid fa-book"></i>
      Agenda
    </a>

    <a href="../estudos.php" class="active">
      <i class="fa-solid fa-graduation-cap"></i>
      Estudos
    </a>

    <a href="../../notas/notas.php">
      <i class="fa-solid fa-check-double"></i>
      Boletim
    </a>

    <a href="../../loja/loja.php">
      <i class="fa-solid fa-store"></i>
      Loja
    </a>

    <a href="../../rank/rank.php">
      <i class="fa-solid fa-trophy"></i>
      Ranking
    </a>

  </nav>

    <!-- Conteúdo -->
    <main class="conteudo">
      <section class="estudos-wrapper">
        <div class="pomodoro-nav">
        <a href="../estudos.php" class="back-studies">
          <i class="fa-solid fa-arrow-left"></i>
          Voltar para Estudos
        </a>
      </div>
        <!-- TIMER + CRONÔMETRO (Abas) -->
        <section class="card half">
          <h2>⏱️ Tempo de Estudo</h2>
          <p class="sub">Use o <strong>Timer (Pomodoro)</strong> para sessões cronometradas ou o <strong>Cronômetro</strong> para contar livre.</p>

          <div class="tabs">
            <button class="tab-btn active" data-tab="pomodoro">Timer (Pomodoro)</button>
            <button class="tab-btn" data-tab="stopwatch">Cronômetro</button>
          </div>

          <!-- Painel: Pomodoro -->
          <div id="tab-pomodoro" class="tab-panel active">
            <div class="grid-2">
              <label class="row"><span class="lbl">Foco (min)</span>
                <input id="focusM" class="input" type="number" min="5" max="120" value="25" />
              </label>
              <label class="row"><span class="lbl">Pausa curta (min)</span>
                <input id="shortM" class="input" type="number" min="3" max="30" value="5" />
              </label>
              <label class="row"><span class="lbl">Pausa longa (min)</span>
                <input id="longM" class="input" type="number" min="5" max="60" value="15" />
              </label>
              <label class="row"><span class="lbl">A cada (ciclos)</span>
                <input id="everyCycles" class="input" type="number" min="2" max="8" value="4" />
              </label>
            </div>

            <div class="field-group mt">
              <label class="lbl" for="discipline">
                Matéria
              </label>

              <select id="discipline" class="select">
                <option value="Geral">Geral</option>
              </select>
            </div>

            <div class="timer" id="timer">25:00</div>
            <div class="row center gap">
              <button class="btn" id="startBtn"><i class="fa-solid fa-play"></i> Iniciar</button>
              <button class="btn secondary" id="pauseBtn"><i class="fa-solid fa-pause"></i> Pausar</button>
              <button class="btn ghost" id="resetBtn"><i class="fa-solid fa-rotate-left"></i> Reset</button>
            </div>
            <div class="row center gap mt">
              <span class="pill" id="modePill"><i class="fa-solid fa-hourglass-half"></i> Foco</span>
              <span class="pill" id="cyclePill"><i class="fa-solid fa-repeat"></i> Ciclo 1</span>
            </div>
            <div class="progress mt"><span id="timerProgress"></span></div>
            <audio id="ding" preload="auto">
              <source src="https://cdn.pixabay.com/download/audio/2022/03/15/audio_6f4caa1a68.mp3?filename=ui-interface-sfx-confirmation-95384.mp3" type="audio/mpeg" />
            </audio>
          </div>

          <!-- Painel: Cronômetro -->
          <div id="tab-stopwatch" class="tab-panel">
            <div class="row mt">
              <select id="stopwatchDiscipline" class="select"></select>
              <button class="btn secondary" id="swSaveSession" title="Salvar a sessão atual como estudo"><i class="fa-solid fa-floppy-disk"></i> Salvar sessão</button>
            </div>
            <div class="timer" id="stopwatchDisplay">00:00:00</div>
            <div class="row center gap">
              <button class="btn" id="swStart"><i class="fa-solid fa-play"></i> Iniciar</button>
              <button class="btn secondary" id="swPause"><i class="fa-solid fa-pause"></i> Pausar</button>
              <button class="btn ghost" id="swReset"><i class="fa-solid fa-rotate-left"></i> Zerar</button>
              <button class="btn" id="swLap"><i class="fa-solid fa-flag-checkered"></i> Volta</button>
            </div>
            <div class="list mt" id="lapsList"></div>
          </div>
        </section>

        <!-- METAS SEMANAIS -->
        <section class="card half">
          <h2>🎯 Metas Semanais</h2> 
            <p class="sub">Defina horas por matéria e acompanhe o progresso semanal. </p>          
          <div class="row">
            <select id="goalDiscipline" class="select"></select>
            <input id="goalHours" class="input" type="number" min="1" max="60" placeholder="Horas/semana" />
            <button class="btn" id="saveGoal">Salvar meta</button>
          </div>
          <div id="goalsList" class="list mt"></div>
          <div class="recent-studies">
          <div class="recent-studies-header">
            <div>
              <h3>
                <i class="fa-solid fa-clock-rotate-left"></i>
                Estudadas recentemente
              </h3>

              <p>Suas últimas matérias estudadas.</p>
            </div>
          </div>

          <div id="recentStudiesList" class="recent-studies-list">
            <div class="recent-empty">
              <i class="fa-regular fa-clock"></i>

              <span>
                Suas matérias estudadas recentemente aparecerão aqui.
              </span>
            </div>
          </div>
        </div>
        </section>

        <!-- ESTATÍSTICAS -->
        <section class="card full">
          <h2>📊 Estatísticas</h2>
          <p class="sub"> Horas estudadas nos últimos 14 dias e distribuição por matéria. </p>
          <div class="grid-2">
            <div><canvas id="lineChart"></canvas></div>
            <div><canvas id="pieChart"></canvas></div>
          </div>
        </section>

        <!-- HISTÓRICO / EXPORT -->
        <section class="card full">
          <div class="row between">
            <h2>🗂️ Histórico de Sessões</h2>
            <div class="row gap">
              <button class="btn secondary" id="clearHistory"><i class="fa-solid fa-trash"></i> Limpar histórico</button>
              <button class="btn" id="exportCsv"><i class="fa-solid fa-file-arrow-down"></i> Exportar CSV</button>
            </div>
          </div>
          <table class="table" id="historyTable">
            <thead>
              <tr><th>Data</th><th>Matéria</th><th>Modo</th><th>Duração (min)</th></tr>
            </thead>
            <tbody></tbody>
          </table>
        </section>
      </section>
    </main>
  </div>

  <!-- Modal de Sair -->
  <div id="logout-modal" class="modal">
    <div class="modal-content">
      <h3>Ah... já vai?</h3>
      <h4>Tem certeza que deseja sair?</h4>
      <div class="modal-buttons">
        <button id="confirm-logout" class="btn">Sim</button>
        <button id="cancel-logout" class="btn secondary">Cancelar</button>
      </div>
    </div>
  </div>

  <footer>&copy; 2025 FOAG. Todos os direitos reservados.</footer>

  <!-- Lógica do módulo -->
  <script defer src="pomodoro.js?v=<?= time() ?>"></script>
</body>
</html>

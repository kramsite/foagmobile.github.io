<?php
session_start();

// qual página está ativa
$current = basename($_SERVER['PHP_SELF']); // ex: pomodoro.php, calendario.php

// Carrega os feriados do JSON
$feriados = json_decode(file_get_contents(__DIR__ . '/../json/feriados.json'), true);

// ====== CARREGAR AGENDA DO USUÁRIO (MESMO FORMATO DA AGENDA) ======
$userId = $_SESSION['user_id'] ?? null;

// se quiser obrigar login aqui, descomenta:
/*
if (!$userId) {
  header("Location: ../login/index.php");
  exit;
}
*/

$baseJsonDir  = __DIR__ . '/../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $userId;

if (!is_dir($pastaUsuario)) {
  mkdir($pastaUsuario, 0755, true);
}

$arquivoAgenda = $pastaUsuario . '/agenda.json';
if (!file_exists($arquivoAgenda)) {
  $estruturaInicial = [
    'notas'        => [],
    'tarefas'      => [],
    'nao_esquecer' => []
  ];
  file_put_contents(
    $arquivoAgenda,
    json_encode($estruturaInicial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
  );
}

$agendaData = json_decode(file_get_contents($arquivoAgenda), true) ?? [
  'notas'        => [],
  'tarefas'      => [],
  'nao_esquecer' => []
];


// ================== CALENDÁRIO DO USUÁRIO ==================
$arquivoCalend = $pastaUsuario . '/calendario.json';

if (!file_exists($arquivoCalend)) {
  $calendarioInicial = [
    'dias'  => [], // 'YYYY-MM-DD' => 'vermelho' | 'amarelo' | 'sem-aula' | 'roxo'
    'metas' => []  // 'ANO-MES' => 80, 90...
  ];
  file_put_contents(
    $arquivoCalend,
    json_encode($calendarioInicial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
  );
}

$calendData = json_decode(file_get_contents($arquivoCalend), true);
if (!is_array($calendData)) {
  $calendData = ['dias' => [], 'metas' => []];
}


// ================== FUNÇÕES DO CALENDÁRIO ==================

// Função para gerar os dias de cada mês
function obterDiasDoMes($mes, $ano) {
    $meses = [
        'Janeiro' => 1, 'Fevereiro' => 2, 'Março' => 3, 'Abril' => 4,
        'Maio' => 5, 'Junho' => 6, 'Julho' => 7, 'Agosto' => 8,
        'Setembro' => 9, 'Outubro' => 10, 'Novembro' => 11, 'Dezembro' => 12
    ];
    $numeroMes = $meses[$mes];
    $diasNoMes = cal_days_in_month(CAL_GREGORIAN, $numeroMes, $ano);
    $primeiroDiaSemana = date('w', strtotime("$ano-$numeroMes-01"));

    $dias = [];
    for ($i = 0; $i < $primeiroDiaSemana; $i++) $dias[] = '';
    for ($i = 1; $i <= $diasNoMes; $i++) $dias[] = $i;
    return [$dias, $numeroMes];
}

// Gera o calendário completo (todos os meses)
function gerarCalendario() {
    global $feriados;
    // Histórico por ano via ?ano=YYYY
    $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

    $meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    $diasSemana = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];

    foreach ($meses as $mes) {
        list($dias, $numeroMes) = obterDiasDoMes($mes, $ano);

        echo "<div class='mes' data-ano='$ano' data-mes='$numeroMes'>";
        echo "  <div class='calendario-mes'>";
        echo "    <div class='header-mes'>$mes</div>";
        echo "    <div class='dias'>";

        foreach ($diasSemana as $dia) {
            echo "<div class='dia header-dia'><strong>$dia</strong></div>";
        }

        foreach ($dias as $d) {
            if ($d) {
                $dataAtual = sprintf('%04d-%02d-%02d', $ano, $numeroMes, $d);
                $classeExtra = '';
                $attrExtra = '';
                if (isset($feriados[$dataAtual])) {
                    // marca feriado + nome no data-attribute (tooltip)
                    $classeExtra = 'feriado';
                    $nomeFeriado = htmlspecialchars($feriados[$dataAtual], ENT_QUOTES, 'UTF-8');
                    $attrExtra = " data-feriado=\"$nomeFeriado\"";
                }
                echo "<div class='dia $classeExtra'$attrExtra data-date='$dataAtual'>
                        <span class='num-dia'>$d</span>
                        <div class='dots'></div>
                      </div>";
            } else {
                echo "<div class='dia'></div>";
            }
        }

        echo "    </div>"; // .dias
        echo "  </div>";   // .calendario-mes

        // TUDO DENTRO DO MINI CALENDÁRIO (por mês)
        echo "  <div class='info-mes'>";
        echo "    <div class='toolbar-cal'>";
        echo "      <div class='lado-a'>";
        echo "        <label>Ano:</label>";
        echo "        <select class='anoSelect'></select>";
        echo "      </div>";
        echo "      <div class='lado-b'>";
        echo "        <button class='btn-exportar-png' title='Exportar PNG'>Exportar PNG</button>";
        echo "        <button class='btn-imprimir' title='Imprimir mês'>Imprimir</button>";
        echo "      </div>";
        echo "    </div>";

        echo "    <div class='bloco-cores'>";
        echo "      <p class='texto-cores'>Selecione a cor e depois clique no dia:</p>";
        echo "      <div class='botoes-cores'>";
        echo "        <div class='cor-item'><button class='btn-cor' data-cor='vermelho' style='background:#e74c3c'></button><span>Faltou</span></div>";
        echo "        <div class='cor-item'><button class='btn-cor' data-cor='amarelo' style='background:#f1c40f'></button><span>Atestado</span></div>";
        echo "        <div class='cor-item'><button class='btn-cor' data-cor='sem-aula' style='background:#f39c12'></button><span>Sem aula</span></div>";
        echo "        <div class='cor-item'><button class='btn-cor' data-cor='roxo' style='background:#8e44ad'></button><span>Prova</span></div>";
        echo "        <div class='cor-item'><button class='btn-cor limpar' data-cor='limpar' style='background:#bdc3c7'></button><span>Limpar</span></div>";
        echo "      </div>";
        echo "    </div>";

        echo "    <div class='painel-metas'>";
        echo "      <div class='linha'>";
        echo "        <label>Meta de presença (%):</label>";
        echo "        <input class='meta-presenca' type='number' min='0' max='100' value='80'>";
        echo "      </div>";
        echo "      <div class='linha linha-progress'>";
        echo "        <div class='progress-wrap'><div class='progress-bar'></div></div>";
        echo "        <span class='label-presenca'>0%</span>";
        echo "      </div>";
        echo "      <div class='resumos'>";
        echo "        <span><b>Presenças</b>: <span class='count-presenca'>0</span></span>";
        echo "        <span><b>Faltas</b>: <span class='count-falta'>0</span></span>";
        echo "        <span><b>Atestados</b>: <span class='count-atestado'>0</span></span>";
        echo "        <span><b>Sem aula</b>: <span class='count-semaula'>0</span></span>";
        echo "        <span><b>Provas</b>: <span class='count-prova'>0</span></span>";
        echo "      </div>";
        echo "    </div>";

        // Mini-agenda integrada com Agenda
               echo "    <div class='mini-agenda'>";
        echo "      <div class='agenda-header'>";
        echo "        <strong class='agenda-data'></strong>";
        echo "        <button class='agenda-fechar'>×</button>";
        echo "      </div>";

        echo "      <div class='agenda-opcoes'>";
        echo "        <button class='btn-ver-tarefas'>Ver tarefas do dia</button>";
        echo "        <button class='btn-nova-tarefa'>Agendar nova tarefa</button>";
        echo "        <button class='btn-ver-horarios'>Ver horários</button>";
        echo "      </div>";

        echo "      <div class='agenda-resumo'></div>";

        echo "      <div class='agenda-editor'>";
        echo "        <textarea class='agenda-notas' placeholder='Anote tarefas, horários, links...'></textarea>";
        echo "        <button class='agenda-salvar'>Salvar</button>";
        echo "      </div>";

        echo "    </div>";


        echo "  </div>"; // .info-mes
        echo "</div>";   // .mes
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calendário</title>
  <link rel="stylesheet" href="calendario.css">
  <link rel="stylesheet" href="../m.escuro/dark_basee.css">
  <link rel="stylesheet" href="dark_calendario.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <script src="../m.escuro/dark-mode.js"></script>
  <!-- Export PNG -->
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

      <script>
    window.CAL_AGENDA_DATA      = <?= json_encode($agendaData,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    window.CAL_AGENDA_SAVE_URL  = "../bloco/salvar_agenda.php";

    window.CAL_HORARIO_URL      = "../horario/horario_api.php";

    // NOVO: dados do calendário salvo
    window.CAL_CALEND_DATA      = <?= json_encode($calendData,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    window.CAL_CALEND_SAVE_URL  = "salvar_calendario.php";
  </script>


</head>

<body>
  <!-- Backdrop para bloquear interação no fundo quando um mês estiver expandido -->
  <div id="cal-backdrop" aria-hidden="true"></div>

  <header class="cabecalho">
    FOAG
    <div class="header-icons">
      <i id="themeToggle" class="fa-solid fa-moon" title="Modo Escuro"></i>
      <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>

      <!-- ÍCONE DA IA -->
      <i id="icon-fogi" class="fa-solid fa-robot" title="Assistente FOAG — FOGi"></i>

      <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
    </div>
  </header>

  <div class="container">
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
    </nav>

    <div class="conteudo">
      <div class="calendario-container">
        <div class="calendario">
          <?php gerarCalendario(); ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Confirmação -->
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

  <footer>&copy; 2025 FOAG. Todos os direitos reservados.</footer>

  <!-- Usa o JS organizado do calendário -->
  <script src="calend.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const fogiBtn   = document.getElementById("icon-fogi");
      const fogiModal = document.getElementById("fogi-modal");
      const fogiFrame = document.getElementById("fogi-iframe");
      const fogiClose = document.getElementById("fogi-close");

      // Se faltar algum elemento, não tenta fazer nada
      if (!fogiBtn || !fogiModal || !fogiFrame || !fogiClose) {
        console.warn("FOGi: elemento não encontrado na página.");
        return;
      }

      // abre IA
      fogiBtn.addEventListener("click", () => {
        fogiFrame.src = "http://127.0.0.1:5000";  // Flask/Ollama rodando
        fogiModal.style.display = "flex";
        document.body.style.overflow = "hidden";
      });

      // fecha IA pelo botão "Fechar" do modal
      fogiClose.addEventListener("click", () => {
        fogiModal.style.display = "none";
        fogiFrame.src = "about:blank"; // limpa sessão
        document.body.style.overflow = "";
      });

      // sair da IA via postMessage (botão X dentro do FOGi.html)
      window.addEventListener("message", (ev) => {
        if (ev.data && ev.data.type === "FOGI_CLOSE") {
          fogiModal.style.display = "none";
          fogiFrame.src = "about:blank";
          document.body.style.overflow = "";
        }
      });
    });
  </script>
</body>
</html>

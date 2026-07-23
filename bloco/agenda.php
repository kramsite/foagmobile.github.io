<?php
session_start();

// 1) Garantir que o usuário está logado
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login/index.php");
  exit;
}

$userId = $_SESSION['user_id'];

// 2) Caminho da pasta e arquivo de agenda desse usuário
$baseJsonDir   = __DIR__ . '/../json/usuarios';
$pastaUsuario  = $baseJsonDir . '/' . $userId;
$arquivoAgenda = $pastaUsuario . '/agenda.json';

// Garante que a pasta exista (caso algo tenha falhado no cadastro)
if (!is_dir($pastaUsuario)) {
  mkdir($pastaUsuario, 0755, true);
}

// 3) Se não existir agenda.json, cria com estrutura básica
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

// 4) Carrega os dados da agenda
$agendaData = json_decode(file_get_contents($arquivoAgenda), true);

// Estrutura padrão
$estruturaPadrao = [
    'notas'        => [],
    'tarefas'      => [],
    'nao_esquecer' => []
];

// Se o arquivo tiver [] ou null → força estrutura padrão
if (!is_array($agendaData)) {
    $agendaData = $estruturaPadrao;
} else {
    $chaves = array_keys($agendaData);
    $ehListaNumerica = $chaves === range(0, count($chaves) - 1);

    // Se vier lista numérica ou faltar alguma chave → corrige
    if ($ehListaNumerica ||
        !isset($agendaData['notas']) ||
        !isset($agendaData['tarefas']) ||
        !isset($agendaData['nao_esquecer'])
    ) {
        $agendaData = $estruturaPadrao;
    }
}


$current = basename($_SERVER['PHP_SELF']); // ex: pomodoro.php, calendario.php
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Organizador</title>
  <link rel="stylesheet" href="bloco.css" />
  <link rel="stylesheet" href="../m.escuro/dark_basee.css">
  <link rel="stylesheet" href="dark_agend.css">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <script src="../m.escuro/dark-mode.js"></script>

  <!-- Passando dados do PHP para o JS -->
  <script>
    // Dados iniciais da agenda do usuário logado
    window.AGENDA_DATA = <?= json_encode($agendaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    // Endpoint para salvar as alterações
    window.AGENDA_SAVE_URL = "salvar_agenda.php";
  </script>

  <!-- Estilos básicos do modal da FOGi -->
  <style>
    #icon-fogi {
      cursor: pointer;
      transition: 0.2s;
    }
    #icon-fogi:hover {
      color: #38a5ff;
      transform: scale(1.1);
    }

    /* Modal full-screen da FOGi */
    #fogi-modal {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 9999;
      background: rgba(0,0,0,0.5);
      backdrop-filter: blur(4px);
      align-items: center;
      justify-content: center;
    }

    #fogi-modal .fogi-container {
      background: #ffffff;
      width: 90%;
      max-width: 1100px;
      height: 80vh;
      border-radius: 12px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: 0 10px 35px rgba(0,0,0,0.2);
    }

    #fogi-modal .fogi-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #38a5ff;
      color: #fff;
      padding: 8px 14px;
      font-weight: 600;
      font-size: 0.95rem;
    }

    #fogi-close {
      border: none;
      background: #ffffff;
      color: #333;
      padding: 4px 10px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.85rem;
    }

    #fogi-close:hover {
      background: #f1f1f1;
    }

    #fogi-iframe {
      flex: 1;
      border: none;
      width: 100%;
      height: 100%;
    }
  </style>
</head>

<body>
  <header class="cabecalho">
    FOAG
    <div class="header-icons">
      <i id="themeToggle" class="fa-solid fa-moon" title="Modo Escuro"></i>
      <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
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

    <main class="main-content">
      <div id="container-notas">
        <!-- Notas -->
        <div id="notas">
          <textarea placeholder="Escreva suas notas aqui..." wrap="soft"></textarea>
          <button id="btn-salvar-nota">Salvar Nota</button>
          <div id="saved-notes">
            <h2>Notas Salvas</h2>
            <div class="notas-container" id="noteList">
              <!-- As notas salvas serão inseridas aqui -->
            </div>
          </div>
        </div>

        <!-- Tarefas e Não Esquecer -->
        <div id="tarefas">
          <div class="titulo-tabela">TAREFAS</div>
          <table id="tabela-tarefas">
            <thead>
              <tr><th>#</th><th>Tarefa</th><th>Data</th><th>Ações</th></tr>
            </thead>
            <tbody id="lista-tarefas"></tbody>
          </table>
          <button id="add-tarefa">Adicionar Tarefa</button>

          <div class="titulo-tabela">NÃO ESQUECER</div>
          <table id="tabela-nao-esquecer">
            <thead>
              <tr><th>#</th><th>Item</th><th>Data</th><th>Ações</th></tr>
            </thead>
            <tbody id="lista-nao-esquecer"></tbody>
          </table>
          <button id="add-nao-esquecer">Adicionar Item</button>
        </div>
      </div>
    </main>
  </div>

  <!-- Modal para nomear a nota -->
  <div id="modal-nomear-nota" class="modal-nomear-nota">
    <div class="modal-content">
      <h3>Dê um nome para sua nota</h3>
      <input type="text" id="nome-nota" placeholder="Digite um título para sua nota..." maxlength="50">
      <div class="modal-buttons">
        <button id="confirmar-nome-nota">Salvar</button>
        <button id="cancelar-nome-nota">Cancelar</button>
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

  <div id="modal-excluir" class="modal-excluir">
    <div class="modal-content">
        <div class="excluir-icon">
            <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 id="excluir-titulo">Excluir Item</h3>
        <p id="excluir-mensagem">Tem certeza que deseja excluir este item?</p>
        <div class="modal-buttons">
            <button id="confirmar-exclusao" class="btn-excluir-confirmar">Excluir</button>
            <button id="cancelar-exclusao" class="btn-cancelar">Cancelar</button>
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

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="./agendar.js?v=<?=time()?>"></script>
  <script>
    const fogiBtn = document.getElementById("icon-fogi");
    const fogiModal = document.getElementById("fogi-modal");
    const fogiFrame = document.getElementById("fogi-iframe");
    const fogiClose = document.getElementById("fogi-close");

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
  </script>

</body>
</html>

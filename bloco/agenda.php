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

if (!is_dir($pastaUsuario)) {
  mkdir($pastaUsuario, 0755, true);
}

if (!file_exists($arquivoAgenda)) {
  $estruturaInicial = ['notas' => [], 'tarefas' => [], 'nao_esquecer' => []];
  file_put_contents($arquivoAgenda, json_encode($estruturaInicial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$agendaData = json_decode(file_get_contents($arquivoAgenda), true);
$estruturaPadrao = ['notas' => [], 'tarefas' => [], 'nao_esquecer' => []];

if (!is_array($agendaData)) {
    $agendaData = $estruturaPadrao;
} else {
    $chaves = array_keys($agendaData);
    $ehListaNumerica = $chaves === range(0, count($chaves) - 1);
    if ($ehListaNumerica || !isset($agendaData['notas']) || !isset($agendaData['tarefas']) || !isset($agendaData['nao_esquecer'])) {
        $agendaData = $estruturaPadrao;
    }
}

$current = basename($_SERVER['PHP_SELF']);
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

  <script>
    window.AGENDA_DATA = <?= json_encode($agendaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    window.AGENDA_SAVE_URL = "salvar_agenda.php";
  </script>

  <style>
    /* --- ESTRUTURA PARA CONTEÚDO EXPANSÍVEL --- */
    :root {
      --sidebar-width: 250px;
      --sidebar-collapsed-width: 70px;
    }

    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      margin: 0;
    }

    .container {
      display: flex;
      flex: 1; /* Faz o container ocupar o espaço entre header e footer */
      width: 100%;
      overflow: hidden; /* Evita scroll duplo */
    }

    .menu {
      width: var(--sidebar-width);
      min-width: var(--sidebar-width);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      flex-direction: column;
      background-color: #fff; 
      border-right: 1px solid rgba(0,0,0,0.1);
      overflow-x: hidden;
      z-index: 100;
    }

    .menu.fechado {
      width: var(--sidebar-collapsed-width);
      min-width: var(--sidebar-collapsed-width);
    }

    /* O SEGREDO DO CONTEÚDO EXPANSÍVEL */
    .main-content {
      flex: 1; /* Ocupa 100% do espaço restante */
      padding: 20px;
      transition: all 0.3s ease;
      overflow-y: auto; /* Scroll apenas no conteúdo */
      min-width: 0; /* Permite que o flex-item encolha abaixo do tamanho do conteúdo se necessário */
    }

    .menu a {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      text-decoration: none;
      color: inherit;
      white-space: nowrap;
    }

    .menu a i {
      min-width: 30px;
      font-size: 1.2rem;
    }

    .txt-link {
      margin-left: 10px;
      transition: opacity 0.2s;
      opacity: 1;
    }

    .menu.fechado .txt-link {
      opacity: 0;
      pointer-events: none;
    }

    .cabecalho {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      height: 60px;
      flex-shrink: 0;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    #btn-expandir {
      cursor: pointer;
      font-size: 1.4rem;
      transition: 0.2s;
    }

    #btn-expandir:hover { color: #38a5ff; }

    /* Ajuste para as notas e tabelas não quebrarem o layout */
    #container-notas {
      display: flex;
      flex-direction: column;
      gap: 20px;
      width: 100%;
    }

    /* Estilos IA (Mantidos) */
    #icon-fogi { cursor: pointer; transition: 0.2s; }
    #fogi-modal {
      display: none; position: fixed; inset: 0; z-index: 9999;
      background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
      align-items: center; justify-content: center;
    }
    #fogi-modal .fogi-container {
      background: #ffffff; width: 90%; max-width: 1100px; height: 80vh;
      border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;
    }
    #fogi-close { border: none; background: #fff; padding: 4px 10px; border-radius: 6px; cursor: pointer; }
    #fogi-iframe { flex: 1; border: none; width: 100%; height: 100%; }
  </style>
</head>

<body>
  <header class="cabecalho">
    <div class="header-left">
      <i id="btn-expandir" class="fa-solid fa-bars"></i>
      <strong>FOAG</strong>
    </div>
    
    <div class="header-icons">
      <i id="themeToggle" class="fa-solid fa-moon" title="Modo Escuro"></i>
      <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
      <i id="icon-fogi" class="fa-solid fa-robot" title="Assistente FOAG — FOGi"></i>
      <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
    </div>
  </header>

  <div class="container">
    <nav class="menu" id="sidebar">
      <a href="../inicioo/inicio.php" class="<?= $current === 'inicio.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-house"></i> <span class="txt-link">Início</span>
      </a>
      <a href="../calend/calendario.php" class="<?= $current === 'calendario.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-calendar-days"></i> <span class="txt-link">Calendário</span>
      </a>
      <a href="../bloco/agenda.php" class="<?= $current === 'agenda.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-book"></i> <span class="txt-link">Agenda</span>
      </a>
      <a href="../pomodoro/pomodoro.php" class="<?= $current === 'pomodoro.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-stopwatch"></i> <span class="txt-link">Pomodoro</span>
      </a>
      <a href="../notas/notas.php" class="<?= $current === 'notas.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-check-double"></i> <span class="txt-link">Boletim</span>
      </a>
      <a href="../horario/horario.php" class="<?= $current === 'horario.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-clock"></i> <span class="txt-link">Horário</span>
      </a>
    </nav>

    <main class="main-content">
      <div id="container-notas">
        <div id="notas">
          <textarea placeholder="Escreva suas notas aqui..." wrap="soft" style="width: 100%; min-height: 150px;"></textarea>
          <button id="btn-salvar-nota" style="margin-top: 10px;">Salvar Nota</button>
          <div id="saved-notes">
            <h2>Notas Salvas</h2>
            <div class="notas-container" id="noteList"></div>
          </div>
        </div>

        <div id="tarefas">
          <div class="titulo-tabela">TAREFAS</div>
          <div style="overflow-x: auto;"> <table id="tabela-tarefas" style="width: 100%;">
              <thead>
                <tr><th>#</th><th>Tarefa</th><th>Data</th><th>Ações</th></tr>
              </thead>
              <tbody id="lista-tarefas"></tbody>
            </table>
          </div>
          <button id="add-tarefa">Adicionar Tarefa</button>

          <div class="titulo-tabela" style="margin-top: 30px;">NÃO ESQUECER</div>
          <div style="overflow-x: auto;">
            <table id="tabela-nao-esquecer" style="width: 100%;">
              <thead>
                <tr><th>#</th><th>Item</th><th>Data</th><th>Ações</th></tr>
              </thead>
              <tbody id="lista-nao-esquecer"></tbody>
            </table>
          </div>
          <button id="add-nao-esquecer">Adicionar Item</button>
        </div>
      </div>
    </main>
  </div>

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
        <div class="excluir-icon"><i class="fa-solid fa-trash-can"></i></div>
        <h3 id="excluir-titulo">Excluir Item</h3>
        <p id="excluir-mensagem">Tem certeza que deseja excluir este item?</p>
        <div class="modal-buttons">
            <button id="confirmar-exclusao" class="btn-excluir-confirmar">Excluir</button>
            <button id="cancelar-exclusao" class="btn-cancelar">Cancelar</button>
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

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="./agendar.js?v=<?=time()?>"></script>

  <script>
    const btnExpandir = document.getElementById('btn-expandir');
    const sidebar = document.getElementById('sidebar');

    if (localStorage.getItem('menuFechado') === 'true') {
      sidebar.classList.add('fechado');
    }

    btnExpandir.addEventListener('click', () => {
      sidebar.classList.toggle('fechado');
      localStorage.setItem('menuFechado', sidebar.classList.contains('fechado'));
    });

    const fogiBtn = document.getElementById("icon-fogi");
    const fogiModal = document.getElementById("fogi-modal");
    const fogiFrame = document.getElementById("fogi-iframe");
    const fogiClose = document.getElementById("fogi-close");

    fogiBtn.addEventListener("click", () => {
      fogiFrame.src = "http://127.0.0.1:5000";
      fogiModal.style.display = "flex";
      document.body.style.overflow = "hidden";
    });

    fogiClose.addEventListener("click", () => {
      fogiModal.style.display = "none";
      fogiFrame.src = "about:blank";
      document.body.style.overflow = "";
    });

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
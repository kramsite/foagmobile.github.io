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
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes"/>
  <title>Organizador - FOAG</title>
  <link rel="stylesheet" href="bloco.css" />
  <link rel="stylesheet" href="../m.escuro/dark_basee.css">
  <link rel="stylesheet" href="dark_agend.css">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
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
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* Header responsivo */
    .cabecalho {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      position: sticky;
      top: 0;
      z-index: 1000;
      flex-wrap: wrap;
      gap: 0.5rem;
    }

    .cabecalho h1 {
      font-size: 1.5rem;
    }

    .header-icons {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .header-icons i {
      font-size: 1.3rem;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 50%;
      transition: all 0.3s;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .header-icons i:hover {
      background: rgba(255,255,255,0.2);
      transform: scale(1.1);
    }

    /* Container principal responsivo */
    .container {
      display: flex;
      flex: 1;
      min-height: calc(100vh - 140px);
    }

    /* Menu lateral responsivo */
    .menu {
      width: 250px;
      background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
      padding: 1rem 0;
      transition: all 0.3s;
      overflow-y: auto;
    }

    .menu a {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem 1.5rem;
      color: white;
      text-decoration: none;
      transition: all 0.3s;
      font-weight: 500;
      white-space: nowrap;
    }

    .menu a i {
      width: 24px;
      text-align: center;
      font-size: 1.2rem;
    }

    .menu a:hover, .menu a.active {
      background: rgba(255,255,255,0.2);
      padding-left: 2rem;
    }

    /* Conteúdo principal responsivo */
    .main-content {
      flex: 1;
      padding: 1rem;
      background: #f5f5f5;
      overflow-y: auto;
    }

    #container-notas {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    /* Seção de notas responsiva */
    #notas {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    #notas textarea {
      width: 100%;
      min-height: 150px;
      padding: 1rem;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      resize: vertical;
      margin-bottom: 1rem;
      transition: border-color 0.3s;
    }

    #notas textarea:focus {
      outline: none;
      border-color: #667eea;
    }

    #btn-salvar-nota {
      width: 100%;
      padding: 0.8rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.3s;
      margin-bottom: 1.5rem;
    }

    #btn-salvar-nota:hover {
      transform: translateY(-2px);
    }

    #saved-notes h2 {
      margin-bottom: 1rem;
      color: #333;
      font-size: 1.3rem;
    }

    .notas-container {
      max-height: 400px;
      overflow-y: auto;
      padding-right: 0.5rem;
    }

    .nota-item {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
      border-left: 4px solid #667eea;
      animation: slideIn 0.3s;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .nota-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
      padding-bottom: 0.5rem;
      border-bottom: 1px solid #dee2e6;
    }

    .nota-titulo {
      font-weight: 600;
      color: #495057;
      word-break: break-word;
      max-width: 70%;
    }

    .nota-acoes {
      display: flex;
      gap: 0.5rem;
    }

    .nota-acoes button {
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1rem;
      padding: 0.3rem;
      border-radius: 4px;
      transition: all 0.3s;
    }

    .btn-editar {
      color: #667eea;
    }

    .btn-excluir {
      color: #dc3545;
    }

    .nota-acoes button:hover {
      background: rgba(0,0,0,0.1);
    }

    .nota-conteudo {
      color: #6c757d;
      line-height: 1.6;
      word-wrap: break-word;
      white-space: pre-wrap;
    }

    /* Seção de tarefas responsiva */
    #tarefas {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .titulo-tabela {
      font-size: 1.3rem;
      font-weight: 600;
      color: #333;
      margin: 1.5rem 0 1rem;
    }

    .titulo-tabela:first-of-type {
      margin-top: 0;
    }

    /* Tabelas responsivas */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1rem;
      background: white;
      border-radius: 8px;
      overflow: hidden;
    }

    th {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 0.8rem;
      font-weight: 500;
      text-align: left;
    }

    td {
      padding: 0.8rem;
      border-bottom: 1px solid #dee2e6;
      color: #495057;
    }

    tr:last-child td {
      border-bottom: none;
    }

    /* Cards para mobile */
    .mobile-card {
      display: none;
      background: #f8f9fa;
      border-radius: 10px;
      padding: 1rem;
      margin-bottom: 1rem;
      border: 1px solid #e0e0e0;
    }

    .mobile-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
      padding-bottom: 0.5rem;
      border-bottom: 1px solid #dee2e6;
    }

    .mobile-card-title {
      font-weight: 600;
      color: #333;
      word-break: break-word;
    }

    .mobile-card-badge {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 0.3rem 0.6rem;
      border-radius: 20px;
      font-size: 0.8rem;
    }

    .mobile-card-content {
      margin-bottom: 0.5rem;
    }

    .mobile-card-content p {
      margin: 0.3rem 0;
      color: #6c757d;
      word-break: break-word;
    }

    .mobile-card-actions {
      display: flex;
      gap: 0.5rem;
      justify-content: flex-end;
      margin-top: 0.5rem;
      padding-top: 0.5rem;
      border-top: 1px solid #dee2e6;
    }

    .mobile-card-actions button {
      padding: 0.4rem 0.8rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 0.3rem;
      transition: all 0.3s;
    }

    .btn-edit-mobile {
      background: #667eea;
      color: white;
    }

    .btn-delete-mobile {
      background: #dc3545;
      color: white;
    }

    /* Botões de adicionar */
    #add-tarefa, #add-nao-esquecer {
      width: 100%;
      padding: 0.8rem;
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.3s;
      margin-bottom: 2rem;
    }

    #add-tarefa:hover, #add-nao-esquecer:hover {
      transform: translateY(-2px);
    }

    /* Modais responsivos */
    .modal, .modal-excluir, .modal-nomear-nota {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      backdrop-filter: blur(4px);
      align-items: center;
      justify-content: center;
      z-index: 2000;
      padding: 1rem;
    }

    .modal-content {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      width: 90%;
      max-width: 400px;
      max-height: 90vh;
      overflow-y: auto;
    }

    .modal-content h3 {
      margin-bottom: 1rem;
      color: #333;
    }

    .modal-content input {
      width: 100%;
      padding: 0.8rem;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      margin: 1rem 0;
    }

    .modal-buttons {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
    }

    .modal-buttons button {
      padding: 0.6rem 1.2rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s;
    }

    #confirmar-exclusao, #confirmar-nome-nota, #confirm-logout {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    #cancelar-exclusao, #cancelar-nome-nota, #cancel-logout {
      background: #6c757d;
      color: white;
    }

    /* Modal da FOGi responsivo */
    #fogi-modal .fogi-container {
      width: 95%;
      height: 90vh;
      max-width: 1200px;
    }

    .fogi-header {
      padding: 0.8rem;
    }

    /* Footer */
    footer {
      text-align: center;
      padding: 1rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      font-size: 0.9rem;
    }

    /* Media Queries para responsividade */
    @media screen and (max-width: 1024px) {
      #container-notas {
        grid-template-columns: 1fr;
      }
    }

    @media screen and (max-width: 768px) {
      .cabecalho {
        padding: 0.8rem;
      }

      .cabecalho h1 {
        font-size: 1.2rem;
      }

      .header-icons {
        gap: 0.5rem;
      }

      .header-icons i {
        width: 35px;
        height: 35px;
        font-size: 1.1rem;
      }

      .container {
        flex-direction: column;
      }

      .menu {
        width: 100%;
        padding: 0.5rem;
        display: flex;
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
      }

      .menu::-webkit-scrollbar {
        display: none;
      }

      .menu a {
        padding: 0.8rem 1.2rem;
        font-size: 0.9rem;
        display: inline-flex;
      }

      .menu a i {
        margin-right: 0.3rem;
      }

      .main-content {
        padding: 0.8rem;
      }

      #notas, #tarefas {
        padding: 1rem;
      }

      /* Esconde tabelas em mobile */
      table {
        display: none;
      }

      /* Mostra cards em mobile */
      .mobile-card {
        display: block;
      }

      .titulo-tabela {
        font-size: 1.1rem;
        margin: 1rem 0 0.5rem;
      }

      #add-tarefa, #add-nao-esquecer {
        padding: 0.7rem;
        font-size: 0.95rem;
      }

      .modal-content {
        padding: 1.5rem;
        width: 95%;
      }

      .modal-buttons {
        flex-direction: column;
        gap: 0.5rem;
      }

      .modal-buttons button {
        width: 100%;
        padding: 0.8rem;
      }

      footer {
        padding: 0.8rem;
        font-size: 0.8rem;
      }
    }

    @media screen and (max-width: 480px) {
      .cabecalho {
        flex-direction: column;
        text-align: center;
      }

      .header-icons {
        justify-content: center;
      }

      .menu a {
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
      }

      .nota-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
      }

      .nota-titulo {
        max-width: 100%;
      }

      .nota-acoes {
        width: 100%;
        justify-content: flex-end;
      }

      .mobile-card-actions {
        flex-direction: column;
      }

      .mobile-card-actions button {
        width: 100%;
        justify-content: center;
      }
    }

    /* Suporte para orientação paisagem em mobile */
    @media screen and (max-height: 500px) and (orientation: landscape) {
      .modal-content {
        max-height: 80vh;
      }

      #fogi-modal .fogi-container {
        height: 85vh;
      }
    }

    #icon-fogi {
      cursor: pointer;
      transition: 0.2s;
    }
    #icon-fogi:hover {
      color: #38a5ff;
      transform: scale(1.1);
    }
  </style>
</head>

<body>
  <header class="cabecalho">
    <h1>FOAG</h1>
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
          <!-- Tabela para desktop -->
          <table id="tabela-tarefas">
            <thead>
              <tr><th>#</th><th>Tarefa</th><th>Data</th><th>Ações</th></tr>
            </thead>
            <tbody id="lista-tarefas"></tbody>
          </table>
          <!-- Container para cards mobile -->
          <div id="lista-tarefas-mobile" class="mobile-cards-container"></div>
          <button id="add-tarefa">Adicionar Tarefa</button>

          <div class="titulo-tabela">NÃO ESQUECER</div>
          <!-- Tabela para desktop -->
          <table id="tabela-nao-esquecer">
            <thead>
              <tr><th>#</th><th>Item</th><th>Data</th><th>Ações</th></tr>
            </thead>
            <tbody id="lista-nao-esquecer"></tbody>
          </table>
          <!-- Container para cards mobile -->
          <div id="lista-nao-esquecer-mobile" class="mobile-cards-container"></div>
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

  <!-- Modal de Confirmação de Logout -->
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

  <!-- Modal de Exclusão -->
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

    // Função para detectar se é mobile e ajustar a exibição
    function isMobile() {
      return window.innerWidth <= 768;
    }

    // Ajustar exibição baseado no tamanho da tela
    function adjustForMobile() {
      const tables = document.querySelectorAll('table');
      const mobileContainers = document.querySelectorAll('.mobile-cards-container');
      
      if (isMobile()) {
        tables.forEach(table => table.style.display = 'none');
        mobileContainers.forEach(container => container.style.display = 'block');
      } else {
        tables.forEach(table => table.style.display = 'table');
        mobileContainers.forEach(container => container.style.display = 'none');
      }
    }

    // Executar ao carregar e ao redimensionar
    window.addEventListener('load', adjustForMobile);
    window.addEventListener('resize', adjustForMobile);
  </script>

</body>
</html>
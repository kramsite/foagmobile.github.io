<?php
session_start();

if (empty($_SESSION['codigo_usuario'])) {
    header('Location: ../login/index.php');
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];
$current = basename($_SERVER['PHP_SELF']);

// ==============================
// PASTA DO USUÁRIO
// ==============================

$baseJsonDir = __DIR__ . '/../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    exit('Pasta do usuário não encontrada.');
}

// ==============================
// ARQUIVO DE MATÉRIAS
// ==============================

$arquivoMaterias = $pastaUsuario . '/materias.json';

// Cria o arquivo caso ainda não exista
if (!file_exists($arquivoMaterias)) {
    $estadoInicial = [
        'materias' => []
    ];

    file_put_contents(
        $arquivoMaterias,
        json_encode(
            $estadoInicial,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ),
        LOCK_EX
    );
}

// ==============================
// CARREGAR MATÉRIAS
// ==============================

$materiasData = json_decode(
    file_get_contents($arquivoMaterias),
    true
);

if (!is_array($materiasData)) {
    $materiasData = [
        'materias' => []
    ];
}

if (
    !isset($materiasData['materias']) ||
    !is_array($materiasData['materias'])
) {
    $materiasData['materias'] = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FOAG – Estudos</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="estudos.css">
  <link rel="stylesheet" href="../m.escuro/dark_basee.css">
  <script src="../m.escuro/dark-mode.js"></script>

  <script>
  window.MATERIAS_DATA = <?= json_encode(
      $materiasData,
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
  ); ?>;

  window.MATERIAS_SAVE_URL = 'salvar_materia.php';
</script>
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
      <a href="../inicioo/inicio.php">
        <i class="fa-solid fa-house"></i> Início
      </a>

      <a href="../calend/calendario.php">
        <i class="fa-solid fa-calendar-days"></i> Calendário
      </a>

      <a href="../bloco/agenda.php">
        <i class="fa-solid fa-book"></i> Agenda
      </a>

      <a href="estudos.php" class="<?= $current === 'estudos.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-graduation-cap"></i> Estudos
      </a>

      <a href="../notas/notas.php">
        <i class="fa-solid fa-check-double"></i> Boletim
      </a>

      <a href="../loja/loja.php">
        <i class="fa-solid fa-store"></i> Loja
      </a>

      <a href="../rank/rank.php">
        <i class="fa-solid fa-trophy"></i> Ranking
      </a>
    </nav>

    <main class="conteudo">
      <section class="page-header">
        <div>
          <span class="page-eyebrow">Área de estudos</span>
          <h1>Estudos</h1>
          <p>Organize suas matérias, escolha como estudar e acompanhe seu progresso.</p>
        </div>
        <button class="btn" id="open-subject-modal">
          <i class="fa-solid fa-plus"></i> Nova matéria
        </button>
      </section>

      <section class="stats-grid" aria-label="Estatísticas gerais">
        <article class="stat-card">
          <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
          <div>
            <span class="stat-label">Tempo estudado</span>
            <strong id="stat-study-time">0h 00min</strong>
            <small>Total acumulado</small>
          </div>
        </article>

        <article class="stat-card">
          <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
          <div>
            <span class="stat-label">Sessões</span>
            <strong id="stat-sessions">0</strong>
            <small>Sessões concluídas</small>
          </div>
        </article>

        <article class="stat-card">
          <div class="stat-icon"><i class="fa-solid fa-book-open"></i></div>
          <div>
            <span class="stat-label">Matérias</span>
            <strong id="stat-subjects">0</strong>
            <small>Matérias cadastradas</small>
          </div>
        </article>

        <article class="stat-card">
          <div class="stat-icon"><i class="fa-solid fa-fire"></i></div>
          <div>
            <span class="stat-label">Sequência</span>
            <strong id="stat-streak">0 dias</strong>
            <small>Continue estudando</small>
          </div>
        </article>
      </section>

      <section class="content-section">
        <div class="section-heading">
          <div>
            <h2>Métodos de estudo</h2>
            <p>Escolha a ferramenta que combina com o que você quer estudar agora.</p>
          </div>
        </div>

        <div class="methods-grid">
          <a class="method-card" href="#" data-coming-soon="Flashcards">
            <div class="method-icon"><i class="fa-solid fa-layer-group"></i></div>
            <div class="method-info">
              <h3>Flashcards</h3>
              <p>Crie cartões de perguntas e respostas para revisar conteúdos.</p>
              <span class="method-link">Em breve <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </a>

          <a class="method-card" href="pomodoro/pomodoro.php">
            <div class="method-icon"><i class="fa-solid fa-stopwatch"></i></div>
            <div class="method-info">
              <h3>Pomodoro</h3>
              <p>Organize períodos de foco, pausas e acompanhe suas sessões.</p>
              <span class="method-link">Abrir <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </a>

          <a class="method-card" href="#" data-coming-soon="Quiz">
            <div class="method-icon"><i class="fa-solid fa-circle-question"></i></div>
            <div class="method-info">
              <h3>Quiz</h3>
              <p>Teste seus conhecimentos com perguntas sobre suas matérias.</p>
              <span class="method-link">Em breve <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </a>

          <a class="method-card" href="#" data-coming-soon="Revisão">
            <div class="method-icon"><i class="fa-solid fa-arrows-rotate"></i></div>
            <div class="method-info">
              <h3>Revisão</h3>
              <p>Centralize conteúdos que precisam ser retomados e revisados.</p>
              <span class="method-link">Em breve <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </a>
        </div>
      </section>

      <section class="content-section subjects-section">
        <div class="section-heading subjects-heading">
          <div>
            <h2>Minhas matérias</h2>
            <p>Cadastre as matérias que você está estudando para organizar seus métodos e estatísticas.</p>
          </div>
          <button class="btn secondary-outline" id="open-subject-modal-secondary">
            <i class="fa-solid fa-plus"></i> Adicionar matéria
          </button>
        </div>

        <div id="subjects-empty" class="empty-state">
          <div class="empty-icon"><i class="fa-solid fa-book-open-reader"></i></div>
          <h3>Nenhuma matéria cadastrada ainda</h3>
          <p>Adicione sua primeira matéria para começar a organizar seus estudos.</p>
          <button class="btn" id="open-subject-modal-empty">
            <i class="fa-solid fa-plus"></i> Adicionar primeira matéria
          </button>
        </div>

        <div id="subjects-grid" class="subjects-grid" hidden></div>
      </section>
    </main>
  </div>

  <div id="subject-modal" class="modal" aria-hidden="true">
    <div class="modal-content subject-modal-content">
      <button class="modal-close" id="close-subject-modal" aria-label="Fechar">
        <i class="fa-solid fa-xmark"></i>
      </button>

      <div class="modal-title">
        <div class="modal-title-icon"><i class="fa-solid fa-book"></i></div>
        <div>
          <h3>Nova matéria</h3>
          <p>Escolha um nome, uma cor e um ícone para identificar a matéria.</p>
        </div>
      </div>

      <form id="subject-form">
        <label class="field-group">
          <span class="lbl">Nome da matéria</span>
          <input class="input" id="subject-name" type="text" maxlength="50" placeholder="Ex.: Matemática" required>
        </label>

        <div class="form-group">
  <label for="subject-color">Cor da matéria</label>

  <div class="color-picker-area">
        <input
        type="color"
        id="subject-color"
        name="subject-color"
        value="#38a5ff"
        class="color-picker"
        >

        <span>Escolha uma cor</span>
    </div>
    </div>

        <label class="field-group">
          <span class="lbl">Ícone</span>
          <select class="select" id="subject-icon">
            <option value="fa-book">Livro</option>
            <option value="fa-calculator">Matemática</option>
            <option value="fa-flask">Ciências / Química</option>
            <option value="fa-dna">Biologia</option>
            <option value="fa-globe">Geografia</option>
            <option value="fa-landmark">História</option>
            <option value="fa-language">Idiomas</option>
            <option value="fa-laptop-code">Tecnologia</option>
          </select>
        </label>

        <div class="modal-buttons subject-modal-buttons">
          <button type="button" class="btn secondary" id="cancel-subject-modal">Cancelar</button>
          <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Adicionar</button>
        </div>
      </form>
    </div>
  </div>

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

  <div id="toast" class="toast" role="status" aria-live="polite"></div>

  <footer>&copy; 2025 FOAG. Todos os direitos reservados.</footer>

  <script defer src="estudos.js?v=<?= time() ?>"></script>
</body>
</html>
<?php
session_start();

if (empty($_SESSION['codigo_usuario'])) {
    header('Location: ../../login/index.php');
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];
$current = basename($_SERVER['PHP_SELF']);

$baseJsonDir = __DIR__ . '/../../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    exit('Pasta do usuário não encontrada.');
}

function carregarJsonQuiz($arquivo, $padrao) {
    if (!file_exists($arquivo)) {
        return $padrao;
    }

    $dados = json_decode(file_get_contents($arquivo), true);
    return is_array($dados) ? $dados : $padrao;
}

$materiasData = carregarJsonQuiz(
    $pastaUsuario . '/materias.json',
    ['materias' => []]
);

$flashcardsData = carregarJsonQuiz(
    $pastaUsuario . '/flashcards.json',
    ['baralhos' => []]
);

$inicioData = carregarJsonQuiz(
    $pastaUsuario . '/inicio.json',
    ['anotacoes_importantes' => []]
);

if (!isset($materiasData['materias']) || !is_array($materiasData['materias'])) {
    $materiasData['materias'] = [];
}

if (!isset($flashcardsData['baralhos']) || !is_array($flashcardsData['baralhos'])) {
    $flashcardsData['baralhos'] = [];
}

if (!isset($inicioData['anotacoes_importantes']) || !is_array($inicioData['anotacoes_importantes'])) {
    $inicioData['anotacoes_importantes'] = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FOAG – Quiz</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="quiz.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="../../global/css/cursor.css">
    <link rel="stylesheet" href="dark_quiz.css?v=<?= time() ?>">

    <script src="../../m.escuro/dark-mode.js"></script>

    <script>
        window.MATERIAS_DATA = <?= json_encode(
            $materiasData,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ); ?>;

        window.FLASHCARDS_DATA = <?= json_encode(
            $flashcardsData,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ); ?>;

        window.ANOTACOES_DATA = <?= json_encode(
            $inicioData['anotacoes_importantes'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ); ?>;

        window.QUIZ_CONFIG = {
            saveReviewUrl: 'salvar_flashcards_revisao.php',
            aiEndpoint: 'gerar_quiz.php'
        };
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
    <nav class="menu" aria-label="Menu principal">
        <a href="../../inicioo/inicio.php">
            <i class="fa-solid fa-house"></i> Início
        </a>

        <a href="../estudos.php" class="active">
            <i class="fa-solid fa-graduation-cap"></i> Estudos
        </a>

        <a href="../../bloco/agenda.php">
            <i class="fa-solid fa-book"></i> Agenda
        </a>

        <a href="../../calend/calendario.php">
            <i class="fa-solid fa-calendar-days"></i> Calendário
        </a>

        <a href="../../notas/notas.php">
            <i class="fa-solid fa-check-double"></i> Boletim
        </a>

        <a href="../../comunidade/comunidade.php">
            <i class="fa-solid fa-comments"></i> Comunidade
        </a>

        <a href="../../rank/rank.php">
            <i class="fa-solid fa-trophy"></i> Ranking
        </a>

        <a href="../../loja/loja.php">
            <i class="fa-solid fa-store"></i> Loja
        </a>
    </nav>

    <div class="page-area">
    <main class="conteudo">
        <div class="quiz-shell">
            <div class="back-row">
                <a href="../estudos.php" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    Voltar para Estudos
                </a>
            </div>

            <section class="quiz-heading">
                <div class="heading-icon">
                    <i class="fa-solid fa-circle-question"></i>
                </div>

                <div>
                    <span class="eyebrow">Revisão inteligente</span>
                    <h1>Quiz</h1>
                    <p>
                        Revise o que você estudou usando suas anotações e flashcards.
                    </p>
                </div>
            </section>

            <!-- CONFIGURAÇÃO -->
            <section id="screen-setup" class="screen active">
                <div class="setup-grid setup-grid-content">
                    <section class="panel setup-panel">
                        <div class="panel-heading">
                            <div>
                                <span class="step-pill">1</span>
                                <h2>Escolha o que quer revisar</h2>
                            </div>
                            <p>Selecione anotações e flashcards específicos. A matéria serve apenas como filtro.</p>
                        </div>

                        <div class="content-toolbar">
                            <div class="field compact-field">
                                <label for="content-search">Buscar conteúdo</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input id="content-search" type="search" placeholder="Buscar em anotações e flashcards...">
                                </div>
                            </div>

                            <div class="field compact-field">
                                <label for="quiz-subject">Filtrar por matéria <span class="optional-label">opcional</span></label>
                                <div class="select-wrap">
                                    <i class="fa-solid fa-filter"></i>
                                    <select id="quiz-subject">
                                        <option value="">Todas as matérias</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="selection-summary" id="selection-summary">
                            <div>
                                <span class="selection-icon"><i class="fa-solid fa-check"></i></span>
                                <div>
                                    <strong id="selected-content-count">0 conteúdos selecionados</strong>
                                    <small id="selected-content-detail">Escolha pelo menos um item abaixo.</small>
                                </div>
                            </div>
                            <button id="clear-content-selection" class="text-button" type="button" disabled>Limpar seleção</button>
                        </div>

                        <div class="content-source-grid">
                            <section class="content-source-card">
                                <div class="content-source-head">
                                    <div>
                                        <span class="choice-icon notes-icon"><i class="fa-regular fa-note-sticky"></i></span>
                                        <div>
                                            <h3>Anotações</h3>
                                            <p>Marque somente as anotações que entram neste quiz.</p>
                                        </div>
                                    </div>
                                    <button id="select-all-notes" class="mini-action" type="button">Selecionar visíveis</button>
                                </div>
                                <div id="notes-content-list" class="content-item-list"></div>
                                <div id="notes-empty" class="content-empty" hidden>
                                    <i class="fa-regular fa-note-sticky"></i>
                                    <span>Nenhuma anotação encontrada.</span>
                                </div>
                            </section>

                            <section class="content-source-card">
                                <div class="content-source-head">
                                    <div>
                                        <span class="choice-icon flash-icon"><i class="fa-solid fa-layer-group"></i></span>
                                        <div>
                                            <h3>Flashcards</h3>
                                            <p>Você pode escolher cartões individuais, mesmo de baralhos diferentes.</p>
                                        </div>
                                    </div>
                                    <button id="select-all-flashcards-content" class="mini-action" type="button">Selecionar visíveis</button>
                                </div>
                                <div id="flashcards-content-list" class="content-item-list"></div>
                                <div id="flashcards-empty" class="content-empty" hidden>
                                    <i class="fa-solid fa-layer-group"></i>
                                    <span>Nenhum flashcard encontrado.</span>
                                </div>
                            </section>
                        </div>

                        <div class="quiz-options-divider">
                            <span>Configurações do quiz</span>
                        </div>

                        <div class="settings-row">
                            <div class="field">
                                <label for="quiz-count">Quantidade</label>
                                <div class="select-wrap">
                                    <i class="fa-solid fa-list-ol"></i>
                                    <select id="quiz-count">
                                        <option value="5">5 questões</option>
                                        <option value="10" selected>10 questões</option>
                                        <option value="15">15 questões</option>
                                        <option value="20">20 questões</option>
                                    </select>
                                </div>
                            </div>

                            <div class="field">
                                <label for="quiz-difficulty">Dificuldade</label>
                                <div class="select-wrap">
                                    <i class="fa-solid fa-signal"></i>
                                    <select id="quiz-difficulty">
                                        <option value="easy">Fácil</option>
                                        <option value="medium" selected>Média</option>
                                        <option value="hard">Difícil</option>
                                        <option value="adaptive">Adaptativa</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <span class="field-label">Tipo de questão</span>

                            <div class="type-grid">
                                <label class="type-option">
                                    <input type="radio" name="quiz-type" value="multiple" checked>
                                    <span><i class="fa-solid fa-list-check"></i>Múltipla escolha</span>
                                </label>

                                <label class="type-option">
                                    <input type="radio" name="quiz-type" value="truefalse">
                                    <span><i class="fa-solid fa-toggle-on"></i>Verdadeiro ou falso</span>
                                </label>

                                <label class="type-option">
                                    <input type="radio" name="quiz-type" value="mixed">
                                    <span><i class="fa-solid fa-shuffle"></i>Misturado</span>
                                </label>
                            </div>
                        </div>

                        <div id="setup-warning" class="inline-alert" hidden></div>

                        <button id="generate-quiz" class="btn-primary btn-large" type="button">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            Gerar quiz com os conteúdos selecionados
                        </button>
                    </section>

                    <aside class="panel preview-panel compact-preview-panel">
                        <div class="preview-visual">
                            <div class="bubble bubble-1"></div>
                            <div class="bubble bubble-2"></div>

                            <div class="preview-card">
                                <span class="preview-tag">Você escolhe</span>
                                <div class="preview-question-mark"><i class="fa-solid fa-crosshairs"></i></div>
                                <h3>Revisão focada no conteúdo certo</h3>
                                <p>Em vez de revisar uma matéria inteira, monte o quiz só com os pontos que interessam agora.</p>

                                <div class="preview-benefits">
                                    <span><i class="fa-solid fa-check"></i> Anotações específicas</span>
                                    <span><i class="fa-solid fa-check"></i> Flashcards individuais</span>
                                    <span><i class="fa-solid fa-check"></i> Pode misturar conteúdos</span>
                                </div>
                            </div>
                        </div>

                        <div class="tip-card">
                            <div class="tip-icon"><i class="fa-regular fa-lightbulb"></i></div>
                            <div>
                                <strong>Dica FOAG</strong>
                                <p>Use o filtro de matéria só para encontrar o conteúdo mais rápido. Você decide item por item o que entra no quiz.</p>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

            <!-- QUIZ EM ANDAMENTO -->
            <section id="screen-quiz" class="screen">
                <div class="quiz-topbar">
                    <div>
                        <span id="quiz-subject-badge" class="subject-badge">Conteúdo selecionado</span>
                        <h2 id="quiz-title">Quiz de revisão</h2>
                    </div>

                    <button id="exit-quiz" class="btn-ghost" type="button">
                        <i class="fa-solid fa-xmark"></i>
                        Sair
                    </button>
                </div>

                <div class="progress-panel">
                    <div class="progress-meta">
                        <span id="question-counter">Questão 1 de 10</span>
                        <strong id="progress-percent">10%</strong>
                    </div>
                    <div class="progress-track">
                        <div id="progress-bar" class="progress-bar"></div>
                    </div>
                </div>

                <section class="question-card">
                    <div class="question-header">
                        <span id="difficulty-badge" class="difficulty-badge">Média</span>
                        <span id="question-type-label" class="question-type-label">
                            <i class="fa-solid fa-list-check"></i>
                            Múltipla escolha
                        </span>
                    </div>

                    <h2 id="question-text">Pergunta</h2>
                    <div id="answers-list" class="answers-list"></div>

                    <div id="answer-feedback" class="answer-feedback" hidden>
                        <div id="feedback-icon" class="feedback-icon"></div>
                        <div>
                            <strong id="feedback-title"></strong>
                            <p id="feedback-text"></p>
                        </div>
                    </div>

                    <div class="question-actions">
                        <button id="next-question" class="btn-primary" type="button" disabled>
                            Próxima questão
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </section>
            </section>

            <!-- RESULTADO -->
            <section id="screen-result" class="screen">
                <div class="result-grid">
                    <section class="panel result-main">
                        <div id="result-ring" class="result-ring" style="--score: 0">
                            <div>
                                <strong id="result-percent">0%</strong>
                                <span>aproveitamento</span>
                            </div>
                        </div>

                        <div class="result-copy">
                            <span class="eyebrow">Quiz concluído</span>
                            <h2 id="result-message">Boa revisão!</h2>
                            <p id="result-description">
                                Veja seu desempenho e transforme seus erros em uma nova revisão.
                            </p>
                        </div>

                        <div class="result-stats">
                            <article>
                                <span class="result-stat-icon correct">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                                <div>
                                    <strong id="correct-count">0</strong>
                                    <small>Acertos</small>
                                </div>
                            </article>

                            <article>
                                <span class="result-stat-icon wrong">
                                    <i class="fa-solid fa-xmark"></i>
                                </span>
                                <div>
                                    <strong id="wrong-count">0</strong>
                                    <small>Erros</small>
                                </div>
                            </article>

                            <article>
                                <span class="result-stat-icon total">
                                    <i class="fa-solid fa-list"></i>
                                </span>
                                <div>
                                    <strong id="total-count">0</strong>
                                    <small>Questões</small>
                                </div>
                            </article>
                        </div>
                    </section>

                    <aside class="panel review-summary">
                        <div class="panel-heading compact">
                            <div>
                                <span class="step-pill">
                                    <i class="fa-solid fa-bullseye"></i>
                                </span>
                                <h2>Revisar depois</h2>
                            </div>
                        </div>

                        <p id="review-summary-text">
                            Seus erros vão aparecer aqui.
                        </p>

                        <div id="wrong-topics" class="wrong-topics"></div>
                    </aside>
                </div>

                <section id="review-cta" class="review-cta">
                    <div class="review-cta-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>

                    <div class="review-cta-copy">
                        <span class="eyebrow">Revisão personalizada</span>
                        <h2>Transforme seus erros em flashcards</h2>
                        <p>
                            O FOAG cria cartões usando somente as questões que você errou,
                            para você revisar exatamente o que precisa.
                        </p>
                    </div>

                    <button id="create-review-flashcards" class="btn-primary" type="button">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        Criar flashcards de revisão
                    </button>
                </section>

                <div class="result-actions">
                    <button id="retry-wrong" class="btn-secondary" type="button">
                        <i class="fa-solid fa-rotate-right"></i>
                        Refazer questões erradas
                    </button>

                    <button id="new-quiz" class="btn-primary" type="button">
                        <i class="fa-solid fa-plus"></i>
                        Gerar novo quiz
                    </button>
                </div>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <span class="footer-brand">FOAG</span>
                <nav class="footer-links">
                    <a href="../../sobre/sobre.php">Sobre</a>
                    <a href="../../contato/contato.php">Contato</a>
                    <a href="../../privacidade/privacidade.php">Privacidade</a>
                </nav>
            </div>
            <span class="footer-copy">© <?= date('Y') ?> FOAG</span>
        </div>
    </footer>

    </div>
</div>

<!-- MODAL FLASHCARDS -->
<div id="flashcard-modal" class="modal" aria-hidden="true">
    <div class="modal-card flashcard-modal-card">
        <button id="close-flashcard-modal" class="modal-close" type="button" aria-label="Fechar">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="modal-heading">
            <span class="modal-icon">
                <i class="fa-solid fa-layer-group"></i>
            </span>
            <div>
                <span class="eyebrow">Antes de salvar</span>
                <h2>Flashcards de revisão</h2>
                <p>Escolha quais cartões você quer adicionar ao FOAG.</p>
            </div>
        </div>

        <div class="review-save-subject field">
            <label for="review-subject">Salvar este baralho em qual matéria?</label>
            <div class="select-wrap">
                <i class="fa-solid fa-book-open"></i>
                <select id="review-subject">
                    <option value="">Selecione a matéria</option>
                </select>
            </div>
            <small>Como o quiz pode misturar conteúdos, você escolhe onde guardar os flashcards de revisão.</small>
        </div>

        <div class="select-all-row">
            <label>
                <input id="select-all-flashcards" type="checkbox" checked>
                Selecionar todos
            </label>
            <span id="flashcard-selection-count">0 selecionados</span>
        </div>

        <div id="flashcard-preview-list" class="flashcard-preview-list"></div>

        <div id="flashcard-save-feedback" class="inline-alert" hidden></div>

        <div class="modal-actions">
            <button id="cancel-flashcards" class="btn-secondary" type="button">
                Cancelar
            </button>
            <button id="save-review-flashcards" class="btn-primary" type="button">
                <i class="fa-solid fa-floppy-disk"></i>
                Salvar no Flashcards
            </button>
        </div>
    </div>
</div>


<!-- MODAL LOGOUT -->
<div id="logout-modal" class="modal" aria-hidden="true">
    <div class="modal-card logout-card">
        <div class="logout-icon"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
        <h3>Ah... já vai?</h3>
        <p>Tem certeza que deseja sair?</p>
        <div class="modal-actions">
            <button id="cancel-logout" class="btn-secondary" type="button">Cancelar</button>
            <button id="confirm-logout" class="btn-primary" type="button">Sim, sair</button>
        </div>
    </div>
</div>

<div id="toast" class="toast" role="status" aria-live="polite"></div>


<script src="../../global/js/cursor.js?v=<?= time() ?>"></script>
<script src="quiz.js?v=<?= time() ?>" defer></script>
<script src="../../configuracoes/aparencia.js?v=6"></script>
<script src="../../configuracoes/acessibilidade.js?v=26" defer></script>
</body>
</html>

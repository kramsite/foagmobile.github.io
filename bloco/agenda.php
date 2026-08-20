<?php
session_start();

// ======================================
// VERIFICAR LOGIN
// ======================================

if (empty($_SESSION['codigo_usuario'])) {
    header("Location: ../login/index.php");
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];

$current = basename($_SERVER['PHP_SELF']);

// ======================================
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir = __DIR__ . '/../json/usuarios';

$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

// A pasta já deve ter sido criada no cadastro.
// Se não existir, alguma coisa está errada.
if (!is_dir($pastaUsuario)) {
    exit("Pasta do usuário não encontrada.");
}

// ======================================
// DADOS DA AGENDA
// ======================================

$arquivoAgenda = $pastaUsuario . '/agenda.json';

$estruturaAgendaPadrao = [
    'notas' => [],
    'tarefas' => [],
    'nao_esquecer' => []
];

if (!file_exists($arquivoAgenda)) {
    file_put_contents(
        $arquivoAgenda,
        json_encode(
            $estruturaAgendaPadrao,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        )
    );
}

$agendaData = json_decode(
    file_get_contents($arquivoAgenda),
    true
);

if (!is_array($agendaData)) {
    $agendaData = $estruturaAgendaPadrao;
} else {
    $chaves = array_keys($agendaData);

    $ehListaNumerica =
        count($chaves) > 0 &&
        $chaves === range(0, count($chaves) - 1);

    if ($ehListaNumerica) {
        $agendaData = $estruturaAgendaPadrao;
    } else {
        foreach ($estruturaAgendaPadrao as $chave => $valor) {
            if (
                !isset($agendaData[$chave]) ||
                !is_array($agendaData[$chave])
            ) {
                $agendaData[$chave] = $valor;
            }
        }
    }
}

// ======================================
// DADOS DO HORÁRIO
// ======================================

$arquivoHorario = $pastaUsuario . '/horario.json';

$estruturaHorarioPadrao = [
    'html' => ''
];

if (!file_exists($arquivoHorario)) {
    file_put_contents(
        $arquivoHorario,
        json_encode(
            $estruturaHorarioPadrao,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        )
    );
}

$horarioData = json_decode(
    file_get_contents($arquivoHorario),
    true
);

if (!is_array($horarioData)) {
    $horarioData = $estruturaHorarioPadrao;
}

if (!isset($horarioData['html'])) {
    $horarioData['html'] = '';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Agenda e Horário - FOAG</title>

    <link rel="stylesheet" href="bloco.css">
    <link rel="stylesheet" href="../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="dark_agend.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ACESSIBILIDADE GLOBAL -->
    <link rel="stylesheet" href="../acessibilidade/acessibilidade.css">
    <script src="../acessibilidade/acessibilidade.js" defer></script>

    <?php
require_once $_SERVER['DOCUMENT_ROOT']
    . '/foagmobile.github.io/acessibilidade/carregar_acessibilidade.php';
?>

    <script src="../m.escuro/dark-mode.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>

    <script>
        window.AGENDA_DATA = <?= json_encode(
            $agendaData,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;

        window.AGENDA_SAVE_URL =
            "salvar_agenda.php";

        window.HORARIO_HTML = <?= json_encode(
            $horarioData['html'] ?? '',
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;

        window.HORARIO_SAVE_URL =
            "../horario/salvar_horario.php";
    </script>

    <style>
        /* ======================================
           ORGANIZAÇÃO DA PÁGINA
        ====================================== */

        .main-content {
            flex: 1;
            min-width: 0;
            width: 100%;
            padding: 25px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
            gap: 25px;
        }

        /* ======================================
           CARD DO HORÁRIO
        ====================================== */

        .horario-agenda-card {
            width: 100%;
            background: #ffffff;
            border: 1px solid #e3e8ef;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .horario-agenda-header {
            margin-bottom: 20px;
        }

        .horario-agenda-header h2 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
            color: #222222;
            font-size: 22px;
        }

        .horario-agenda-header p {
            color: #64748b;
            font-size: 14px;
        }

        .horario-table-wrapper {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #dbe3ee;
            border-radius: 12px;
        }

        #scheduleTable {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
            background: #ffffff;
        }

        #scheduleTable th,
        #scheduleTable td {
            padding: 13px 12px;
            border: 1px solid #dbe3ee;
            text-align: center;
            font-size: 14px;
        }

        #scheduleTable th {
            background: #38a5ff;
            color: #ffffff;
            font-weight: 600;
        }

        #scheduleTable td {
            min-width: 130px;
            background: #f9fafb;
            color: #222222;
            outline: none;
        }

        #scheduleTable td:first-child {
            min-width: 100px;
            font-weight: 600;
        }

        #scheduleTable td:focus {
            background: #ffffff;
            box-shadow: inset 0 0 0 2px #38a5ff;
        }

        /* ======================================
           BOTÕES DO HORÁRIO
        ====================================== */

        .horario-buttons {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }

        .horario-buttons button {
            min-height: 42px;
            padding: 10px 16px;
            border: none;
            border-radius: 9px;
            background: #38a5ff;
            color: #ffffff;
            font-family: "Poppins", sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition:
                background-color 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .horario-buttons button:hover {
            background: #168fe8;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(56, 165, 255, 0.25);
        }

        /* ======================================
           MODO ESCURO DO HORÁRIO
        ====================================== */

        body.dark-mode .horario-agenda-card {
            background: #1e293b !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3) !important;
        }

        body.dark-mode .horario-agenda-header h2 {
            color: #e2e8f0 !important;
        }

        body.dark-mode .horario-agenda-header p {
            color: #94a3b8 !important;
        }

        body.dark-mode .horario-table-wrapper {
            border-color: #334155 !important;
        }

        body.dark-mode #scheduleTable {
            background: #1e293b !important;
        }

        body.dark-mode #scheduleTable th {
            background: #669ada !important;
            color: #ffffff !important;
            border-color: #334155 !important;
        }

        body.dark-mode #scheduleTable td {
            background: #0f172a !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
        }

        body.dark-mode #scheduleTable td:focus {
            background: #1e293b !important;
            box-shadow: inset 0 0 0 2px #669ada !important;
        }

        body.dark-mode .horario-buttons button {
            background: #669ada !important;
            color: #ffffff !important;
            box-shadow: 0 5px 14px rgba(102, 154, 218, 0.25) !important;
        }

        body.dark-mode .horario-buttons button:hover {
            background: #7aa9df !important;
        }

        /* ======================================
           MODAL DA FOGi
        ====================================== */

        #icon-fogi {
            cursor: pointer;
            transition: 0.2s;
        }

        #icon-fogi:hover {
            transform: scale(1.1);
        }

        #fogi-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.5);
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
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.2);
        }

        #fogi-modal .fogi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #38a5ff;
            color: #ffffff;
            padding: 8px 14px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        #fogi-close {
            border: none;
            background: #ffffff;
            color: #333333;
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

        body.dark-mode #fogi-modal .fogi-container {
            background: #1e293b !important;
            border: 1px solid #334155 !important;
        }

        body.dark-mode #fogi-modal .fogi-header {
            background: #669ada !important;
        }

        body.dark-mode #fogi-close {
            background: #374151 !important;
            color: #e2e8f0 !important;
        }

        /* ======================================
           RESPONSIVIDADE
        ====================================== */

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .horario-agenda-card {
                padding: 18px;
            }

            .horario-buttons {
                flex-direction: column;
            }

            .horario-buttons button {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<?php
require_once __DIR__ . '/../acessibilidade/menu_acessibilidade.php';
?>


    <!-- ======================================
         CABEÇALHO
    ======================================= -->

    <header class="cabecalho">
        FOAG

        <div class="header-icons">

  <i id="icon-configuracoes" class="fa-solid fa-gear" title="Configurações"></i>

            <i
                id="icon-perfil"
                class="fa-regular fa-user"
                title="Perfil">
            </i>

            <i
                id="icon-sair"
                class="fa-solid fa-right-from-bracket"
                title="Sair">
            </i>
        </div>
    </header>

    <div class="container">

        <!-- ======================================
             MENU
        ======================================= -->

<<<<<<< HEAD
        <nav class="menu" aria-label="Menu principal">
            <a
                href="../inicioo/inicio.php"
                class="<?= $current === 'inicio.php' ? 'active' : '' ?>">

                <i class="fa-solid fa-house" aria-hidden="true"></i>
                Início
            </a>

            <a
                href="../calend/calendario.php"
                class="<?= $current === 'calendario.php' ? 'active' : '' ?>">

                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                Calendário
            </a>

            <a
                href="../bloco/agenda.php"
                class="<?= $current === 'agenda.php' ? 'active' : '' ?>">

                <i class="fa-solid fa-book" aria-hidden="true"></i>
                Agenda
            </a>

            <a
                href="../pomodoro/pomodoro.php"
                class="<?= $current === 'pomodoro.php' ? 'active' : '' ?>">

                <i class="fa-solid fa-stopwatch" aria-hidden="true"></i>
                Pomodoro
=======
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

            <a href="../estudos/estudos.php" class="<?= $current === 'estudos.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-graduation-cap"></i> Estudos
>>>>>>> f99e21492b363db6abaac8aeef80d98d385d71cd
            </a>

            <a href="../notas/notas.php" class="<?= $current === 'notas.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-check-double"></i> Boletim 
            </a>

<<<<<<< HEAD
                <i class="fa-solid fa-check-double" aria-hidden="true"></i>
                Boletim
            </a>

            <a href="../loja/loja.php" class="<?= $current === 'loja.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-store" aria-hidden="true"></i> Loja
=======
            <a href="../loja/loja.php" class="<?= $current === 'loja.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-store"></i> Loja 
>>>>>>> f99e21492b363db6abaac8aeef80d98d385d71cd
            </a>

            <a href="../rank/rank.php" class="<?= $current === 'rank.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-trophy" aria-hidden="true"></i> Ranking
            </a>

        </nav>

        <!-- ======================================
             CONTEÚDO
        ======================================= -->

        <main class="main-content" id="conteudo-principal" tabindex="-1">

            <!-- ==================================
                 HORÁRIO NO TOPO
            =================================== -->

            <section class="horario-agenda-card">

                <div class="horario-agenda-header">
                    <h2>
                        <i class="fa-solid fa-clock"></i>
                        Horário semanal
                    </h2>

                    <p>
                        Organize seus horários e consulte suas aulas
                        diretamente pela Agenda.
                    </p>
                </div>

                <div class="horario-table-wrapper">
                    <table id="scheduleTable">
                        <thead>
                            <tr>
                                <th>Horário</th>
                                <th>Segunda-feira</th>
                                <th>Terça-feira</th>
                                <th>Quarta-feira</th>
                                <th>Quinta-feira</th>
                                <th>Sexta-feira</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                            </tr>

                            <tr>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                            </tr>

                            <tr>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                                <td contenteditable="true"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="horario-buttons">
                    <button
                        type="button"
                        onclick="salvarEdicoes()">

                        Salvar edições
                    </button>

                    <button
                        type="button"
                        onclick="adicionarLinha()">

                        Adicionar linha
                    </button>

                    <button
                        type="button"
                        onclick="removerLinha()">

                        Remover linha
                    </button>

                    <button
                        type="button"
                        onclick="adicionarIntervalo()">

                        Adicionar intervalo
                    </button>

                    <button
                        type="button"
                        onclick="salvarComoPDF()">

                        Salvar como PDF
                    </button>
                </div>
            </section>

            <!-- ==================================
                 AGENDA
            =================================== -->

            <div id="container-notas">

                <!-- Notas -->
                <div id="notas">
                    <label for="nota-texto" class="sr-only">
                        Escreva sua nota
                    </label>

                    <textarea
                        id="nota-texto"
                        placeholder="Escreva suas notas aqui..."
                        wrap="soft">
                    </textarea>

                    <button
                        type="button"
                        id="btn-salvar-nota">

                        Salvar Nota
                    </button>

                    <div id="saved-notes">
                        <h2>Notas Salvas</h2>

                        <div
                            class="notas-container"
                            id="noteList">

                            <!-- Notas inseridas pelo JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Tarefas -->
                <div id="tarefas">

                    <div class="titulo-tabela">
                        TAREFAS
                    </div>

                    <table id="tabela-tarefas">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tarefa</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody id="lista-tarefas"></tbody>
                    </table>

                    <button
                        type="button"
                        id="add-tarefa">

                        Adicionar Tarefa
                    </button>

                    <div class="titulo-tabela">
                        NÃO ESQUECER
                    </div>

                    <table id="tabela-nao-esquecer">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody id="lista-nao-esquecer"></tbody>
                    </table>

                    <button
                        type="button"
                        id="add-nao-esquecer">

                        Adicionar Item
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- ======================================
         MODAL: NOMEAR NOTA
    ======================================= -->

    <div
        id="modal-nomear-nota"
        class="modal-nomear-nota"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-modal-nomear">

        <div class="modal-content">
            <h3 id="titulo-modal-nomear">Dê um nome para sua nota</h3>

            <input
                type="text"
                id="nome-nota"
                placeholder="Digite um título para sua nota..."
                maxlength="50"
            >

            <div class="modal-buttons">
                <button id="confirmar-nome-nota">
                    Salvar
                </button>

                <button id="cancelar-nome-nota">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================
         MODAL: LOGOUT
    ======================================= -->

    <div
        id="logout-modal"
        class="modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-logout">

        <div class="modal-content">
            <h3 id="titulo-logout">Ah... já vai?</h3>

            <h4>
                Tem certeza de que deseja sair?
            </h4>

            <div class="modal-buttons">
                <button id="confirm-logout">
                    Sim
                </button>

                <button id="cancel-logout">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================
         MODAL: EXCLUSÃO
    ======================================= -->

    <div
        id="modal-excluir"
        class="modal-excluir"
        role="dialog"
        aria-modal="true"
        aria-labelledby="excluir-titulo"
        aria-describedby="excluir-mensagem">

        <div class="modal-content">

            <div class="excluir-icon">
                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
            </div>

            <h3 id="excluir-titulo">
                Excluir Item
            </h3>

            <p id="excluir-mensagem">
                Tem certeza que deseja excluir este item?
            </p>

            <div class="modal-buttons">
                <button
                    id="confirmar-exclusao"
                    class="btn-excluir-confirmar">

                    Excluir
                </button>

                <button
                    id="cancelar-exclusao"
                    class="btn-cancelar">

                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================
         MODAL: HORÁRIO SALVO
    ======================================= -->

    <div
        id="modal-sucesso"
        class="modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-sucesso">

        <div class="modal-content modal-sucesso-content">
            <h3 id="titulo-sucesso">Horário salvo!</h3>

            <p>
                Suas alterações foram registradas com sucesso.
            </p>

            <button
                id="fechar-modal"
                class="btn-modal">

                OK
            </button>
        </div>
    </div>

    <!-- ======================================
         MODAL: FOGi
    ======================================= -->

    <div id="fogi-modal" role="dialog" aria-modal="true" aria-labelledby="titulo-fogi">
        <div class="fogi-container">

            <div class="fogi-header">
                <span id="titulo-fogi">
                    FOGi — Assistente de Estudos
                </span>

                <button id="fogi-close">
                    Fechar
                </button>
            </div>

            <iframe
                id="fogi-iframe"
                src="about:blank"
                title="FOGi - Assistente de Estudos">
            </iframe>
        </div>
    </div>

    <footer>
        &copy; 2025 FOAG. Todos os direitos reservados.
    </footer>

    <!-- ======================================
         FUNÇÕES GERAIS
    ======================================= -->

    <script>
        // Modal de sucesso do horário
        window.abrirModalSucesso = function () {
            const modal =
                document.getElementById("modal-sucesso");

            if (modal) {
                modal.style.display = "flex";
            }
        };
    </script>

    <script src="./agendar.js?v=<?= time() ?>"></script>

    <script>
        document.addEventListener(
            "DOMContentLoaded",
            function () {

                // ==========================
                // FOGi
                // ==========================

                const fogiBtn =
                    document.getElementById("icon-fogi");

                const fogiModal =
                    document.getElementById("fogi-modal");

                const fogiFrame =
                    document.getElementById("fogi-iframe");

                const fogiClose =
                    document.getElementById("fogi-close");

                if (
                    fogiBtn &&
                    fogiModal &&
                    fogiFrame
                ) {
                    fogiBtn.addEventListener(
                        "click",
                        function () {
                            fogiFrame.src =
                                "http://127.0.0.1:5000";

                            fogiModal.style.display =
                                "flex";

                            document.body.style.overflow =
                                "hidden";
                        }
                    );
                }

                if (
                    fogiClose &&
                    fogiModal &&
                    fogiFrame
                ) {
                    fogiClose.addEventListener(
                        "click",
                        function () {
                            fogiModal.style.display =
                                "none";

                            fogiFrame.src =
                                "about:blank";

                            document.body.style.overflow =
                                "";
                        }
                    );
                }

                window.addEventListener(
                    "message",
                    function (event) {
                        if (
                            event.data &&
                            event.data.type === "FOGI_CLOSE"
                        ) {
                            fogiModal.style.display =
                                "none";

                            fogiFrame.src =
                                "about:blank";

                            document.body.style.overflow =
                                "";
                        }
                    }
                );

                // ==========================
                // MODAL DE SUCESSO
                // ==========================

                const modalSucesso =
                    document.getElementById(
                        "modal-sucesso"
                    );

                const btnFecharModal =
                    document.getElementById(
                        "fechar-modal"
                    );

                if (
                    btnFecharModal &&
                    modalSucesso
                ) {
                    btnFecharModal.addEventListener(
                        "click",
                        function () {
                            modalSucesso.style.display =
                                "none";
                        }
                    );

                    modalSucesso.addEventListener(
                        "click",
                        function (event) {
                            if (
                                event.target ===
                                modalSucesso
                            ) {
                                modalSucesso.style.display =
                                    "none";
                            }
                        }
                    );
                }
            }
        );
    </script>
</body>

</html>
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

// ==========================================
// DADOS DA AGENDA
// ==========================================

$tarefas =
    isset($agendaData['tarefas']) &&
    is_array($agendaData['tarefas'])
        ? array_values($agendaData['tarefas'])
        : [];

$lembretes =
    isset($agendaData['nao_esquecer']) &&
    is_array($agendaData['nao_esquecer'])
        ? array_values($agendaData['nao_esquecer'])
        : [];

$notasAgenda =
    isset($agendaData['notas']) &&
    is_array($agendaData['notas'])
        ? array_values($agendaData['notas'])
        : [];
        
// ======================================
// RESUMO DA AGENDA
// ======================================

$totalTarefas = count($agendaData['tarefas'] ?? []);
$totalLembretes = count($agendaData['nao_esquecer'] ?? []);
$totalNotas = count($agendaData['notas'] ?? []);

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

// ======================================
// MATÉRIAS DO USUÁRIO
// ======================================

$arquivoMaterias =
    $pastaUsuario . '/materias.json';

$materiasData = [];

if (file_exists($arquivoMaterias)) {

    $conteudoMaterias =
        file_get_contents($arquivoMaterias);

    $materiasJson =
        json_decode(
            $conteudoMaterias,
            true
        );

    if (is_array($materiasJson)) {

        // Caso materias.json seja:
        // [ {...}, {...} ]

        if (
            array_keys($materiasJson) ===
            range(0, count($materiasJson) - 1)
        ) {
            $materiasData =
                $materiasJson;
        }

        // Caso materias.json seja:
        // { "materias": [ {...}, {...} ] }

        elseif (
            isset($materiasJson['materias']) &&
            is_array($materiasJson['materias'])
        ) {
            $materiasData =
                $materiasJson['materias'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Agenda e Horário - FOAG</title>

    <link rel="stylesheet" href="bloco.css">
    <link rel="stylesheet" href="agenda.css">
    <link rel="stylesheet" href="../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="dark_agend.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ACESSIBILIDADE GLOBAL -->
    <link rel="stylesheet" href="../acessibilidade/acessibilidade.css">
    <script src="../acessibilidade/acessibilidade.js?v=4" defer></script>
      <?php include '../configuracoes/geral.php'; ?>
<script src="<?= get_aparencia_path() ?>"></script>
 <script src="../configuracoes/aparencia.js?v=1"></script>


    <script src="../m.escuro/dark-mode.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>

    <script>
        window.AGENDA_DATA = <?= json_encode(
            $agendaData,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;

        window.MATERIAS_DATA = <?= json_encode(
            $materiasData,
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
            "salvar_agenda.php";
    </script>


</head>

<body>




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
            </a>

            <a href="../notas/notas.php" class="<?= $current === 'notas.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-check-double"></i> Boletim 
            </a>

            <a href="../loja/loja.php" class="<?= $current === 'loja.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-store"></i> Loja 
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
                 CABEÇALHO DA AGENDA
            =================================== -->

            <div class="agenda-page-header">
                <div class="agenda-page-title">
                    <h1>Agenda</h1>
                    <p>Organize suas tarefas, lembretes, notas e horário semanal.</p>
                </div>

                <div class="agenda-auto-save" id="status-salvamento" data-status="salvo">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Salvo</span>
                </div>
            </div>

            <!-- ==================================
                 HORÁRIO RECOLHÍVEL
            =================================== -->

            <section class="horario-agenda-card">

                <div class="horario-agenda-header">
                    <button
                        type="button"
                        class="horario-toggle"
                        id="toggle-horario"
                        aria-expanded="false"
                        aria-controls="horario-conteudo">

                        <span class="horario-toggle-texto">
                            <span class="horario-toggle-icone">
                                <i class="fa-solid fa-clock"></i>
                            </span>

                            <span class="horario-toggle-copy">
                                <strong>Horário semanal</strong>
                                <span>Clique para visualizar ou editar sua grade.</span>
                            </span>
                        </span>

                        <i class="fa-solid fa-chevron-down horario-toggle-seta"></i>
                    </button>
                </div>

                <div id="horario-conteudo" class="horario-conteudo" hidden>
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
                                    <td class="horario-hora">
                                        <div class="horario-faixa">
                                            <input type="time" class="input-horario input-horario-inicio" step="300" aria-label="Horário de início da aula">
                                            <span class="horario-separador">às</span>
                                            <input type="time" class="input-horario input-horario-fim" step="300" aria-label="Horário de término da aula">
                                        </div>
                                    </td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                </tr>

                                <tr>
                                    <td class="horario-hora">
                                        <div class="horario-faixa">
                                            <input type="time" class="input-horario input-horario-inicio" step="300" aria-label="Horário de início da aula">
                                            <span class="horario-separador">às</span>
                                            <input type="time" class="input-horario input-horario-fim" step="300" aria-label="Horário de término da aula">
                                        </div>
                                    </td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                </tr>

                                <tr>
                                    <td class="horario-hora">
                                        <div class="horario-faixa">
                                            <input type="time" class="input-horario input-horario-inicio" step="300" aria-label="Horário de início da aula">
                                            <span class="horario-separador">às</span>
                                            <input type="time" class="input-horario input-horario-fim" step="300" aria-label="Horário de término da aula">
                                        </div>
                                    </td>
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
                        <button type="button" onclick="salvarEdicoes()">
                            Salvar agora
                        </button>

                        <button type="button" onclick="adicionarLinha()">
                            Adicionar linha
                        </button>

                        <button type="button" onclick="removerLinha()">
                            Remover linha
                        </button>

                        <button type="button" onclick="adicionarIntervalo()">
                            Adicionar intervalo
                        </button>

                        <button type="button" onclick="salvarComoPDF()">
                            Salvar como PDF
                        </button>
                    </div>
                </div>
            </section>

            <!-- ==================================
                 TAREFAS + LEMBRETES
            =================================== -->

            <div class="agenda-paineis">

                <section class="agenda-painel" id="tarefas">
                    <div class="agenda-painel-header">
                        <div class="agenda-painel-titulo">
                            <i class="fa-solid fa-list-check"></i>
                            Tarefas
                        </div>

                        <span class="agenda-painel-contador" id="contador-tarefas">
                            <?= $totalTarefas ?>
                        </span>
                    </div>

                    <div class="agenda-tabela-wrapper">
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
                    </div>

                    <button type="button" id="add-tarefa">
                        <i class="fa-solid fa-plus"></i>
                        Adicionar tarefa
                    </button>
                </section>

                <section class="agenda-painel" id="lembretes">
                    <div class="agenda-painel-header">
                        <div class="agenda-painel-titulo">
                            <i class="fa-solid fa-bell"></i>
                            Lembretes
                        </div>

                        <span class="agenda-painel-contador" id="contador-lembretes">
                            <?= $totalLembretes ?>
                        </span>
                    </div>

                    <div class="agenda-tabela-wrapper">
                        <table id="tabela-nao-esquecer">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Lembrete</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody id="lista-nao-esquecer"></tbody>
                        </table>
                    </div>

                    <button type="button" id="add-nao-esquecer">
                        <i class="fa-solid fa-plus"></i>
                        Adicionar lembrete
                    </button>
                </section>
            </div>

            <!-- ==================================
                 NOTAS
            =================================== -->

            <section id="notas" class="agenda-notas-card">
                <div class="agenda-notas-header">
                    <h2>
                        <i class="fa-solid fa-note-sticky"></i>
                        Minhas notas
                    </h2>

                    <span class="agenda-painel-contador" id="contador-notas">
                        <?= $totalNotas ?>
                    </span>
                </div>

                <label for="nota-texto" class="sr-only">
                    Escreva sua nota
                </label>

                <textarea
                    id="nota-texto"
                    placeholder="Escreva suas notas aqui..."
                    wrap="soft"></textarea>

                <button type="button" id="btn-salvar-nota">
                    Salvar Nota
                </button>

                <div id="saved-notes">
                    <h2>Notas Salvas</h2>

                    <div class="notas-container" id="noteList">
                        <!-- Notas inseridas pelo JavaScript -->
                    </div>
                </div>
            </section>
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


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleHorario = document.getElementById('toggle-horario');
            const horarioConteudo = document.getElementById('horario-conteudo');

            if (toggleHorario && horarioConteudo) {
                toggleHorario.addEventListener('click', function () {
                    const aberto = toggleHorario.getAttribute('aria-expanded') === 'true';

                    toggleHorario.setAttribute('aria-expanded', aberto ? 'false' : 'true');
                    horarioConteudo.hidden = aberto;
                });
            }

            const listaTarefas = document.getElementById('lista-tarefas');
            const listaLembretes = document.getElementById('lista-nao-esquecer');
            const noteList = document.getElementById('noteList');


            const contadorTarefas = document.getElementById('contador-tarefas');
            const contadorLembretes = document.getElementById('contador-lembretes');
            const contadorNotas = document.getElementById('contador-notas');

            function atualizarResumoAgenda() {
                const tarefas = listaTarefas ? listaTarefas.rows.length : 0;
                const lembretes = listaLembretes ? listaLembretes.rows.length : 0;
                const notas = noteList ? noteList.querySelectorAll('.nota-item').length : 0;


                if (contadorTarefas) contadorTarefas.textContent = tarefas;
                if (contadorLembretes) contadorLembretes.textContent = lembretes;
                if (contadorNotas) contadorNotas.textContent = notas;
            }

            const observer = new MutationObserver(atualizarResumoAgenda);

            if (listaTarefas) observer.observe(listaTarefas, { childList: true });
            if (listaLembretes) observer.observe(listaLembretes, { childList: true });
            if (noteList) observer.observe(noteList, { childList: true, subtree: true });

            atualizarResumoAgenda();
        });
    </script>

</body>

</html>
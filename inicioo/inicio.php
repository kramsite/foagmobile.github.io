<?php
session_start();

$userId = $_SESSION['user_id'] ?? null;
$current = basename($_SERVER['PHP_SELF']);

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

    $arquivoFreq = __DIR__ . "/../json/frequencia_{$anoMes}.json";

    if (file_exists($arquivoFreq)) {

        $dadosFreq = json_decode(
            file_get_contents($arquivoFreq),
            true
        );

        if (is_array($dadosFreq)) {

            foreach ($dadosFreq as $dia => $info) {

                $status = null;

                if (is_array($info)) {
                    $status =
                        $info['status']
                        ?? $info['cor']
                        ?? null;
                }

                if (
                    in_array(
                        $status,
                        ['presenca', 'verde'],
                        true
                    )
                ) {

                    $totalPresencas++;

                } elseif (
                    in_array(
                        $status,
                        ['falta', 'vermelho'],
                        true
                    )
                ) {

                    $totalFaltas++;
                }
            }

            $aulasPossiveis =
                $totalPresencas + $totalFaltas;

            if ($aulasPossiveis > 0) {

                $percentualPresenca = round(
                    ($totalPresencas / $aulasPossiveis)
                    * 100
                );
            }
        }
    }


    // ========= AGENDA / TAREFAS =========
    $hoje = date('Y-m-d');

    $diasProdutivosMapa = [];

    $arquivoAgenda =
        __DIR__ . "/../json/agenda.json";

    if (file_exists($arquivoAgenda)) {

        $dadosAgenda = json_decode(
            file_get_contents($arquivoAgenda),
            true
        );

        if (is_array($dadosAgenda)) {

            foreach ($dadosAgenda as $tarefa) {

                if (!is_array($tarefa)) {
                    continue;
                }

                $data =
                    $tarefa['data']
                    ?? null;

                $concluida =
                    $tarefa['concluida']
                    ?? false;

                $texto =
                    $tarefa['texto']
                    ?? ($tarefa['titulo'] ?? null);

                $importante =
                    $tarefa['importante']
                    ?? false;


                // tarefas de hoje
                if (
                    $data === $hoje
                    && empty($concluida)
                ) {

                    $tarefasPendentes++;
                }


                // dias produtivos
                if (
                    !empty($concluida)
                    && $data
                ) {

                    $diasProdutivosMapa[$data] = true;
                }


                // notas importantes
                if (
                    !empty($importante)
                    && !empty($texto)
                ) {

                    $notasImportantes[] = [
                        'texto' => $texto,
                        'data'  => $data
                    ];
                }
            }


            // mais recentes primeiro
            usort(
                $notasImportantes,
                function ($a, $b) {

                    return strcmp(
                        $b['data'] ?? '',
                        $a['data'] ?? ''
                    );
                }
            );


            // máximo 5
            $notasImportantes =
                array_slice(
                    $notasImportantes,
                    0,
                    5
                );


            // dias consecutivos
            $diasConsecutivos = 0;

            $dataCursor =
                new DateTime($hoje);

            while (true) {

                $dataStr =
                    $dataCursor->format(
                        'Y-m-d'
                    );

                if (
                    !isset(
                        $diasProdutivosMapa[$dataStr]
                    )
                ) {
                    break;
                }

                $diasConsecutivos++;

                $dataCursor->modify('-1 day');
            }
        }
    }


    // ========= TAXA GERAL =========

    $pesoFreq   = 0.6;
    $pesoAgenda = 0.4;

    $indiceAgenda = 0;

    if ($diasConsecutivos > 0) {

        $indiceAgenda =
            min(
                $diasConsecutivos,
                10
            ) * 10;
    }

    $percentualGeraldouble =
        ($percentualPresenca * $pesoFreq)
        +
        ($indiceAgenda * $pesoAgenda);

    $percentualGeral =
        (int) round(
            min(
                $percentualGeraldouble,
                100
            )
        );

} catch (Throwable $e) {

    // echo "Erro: " . $e->getMessage();

}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOAG - Início</title>
    <link rel="stylesheet" href="inicioo.css">
    <link rel="stylesheet" href="dark_ini.css">
    <link rel="stylesheet" href="../acessibilidade/acessibilidade.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../acessibilidade/acessibilidade.js" defer></script>
</head>
<body>


<?php
include __DIR__
    . '/../acessibilidade/menu_acessibilidade.php';
?>


<!-- ===========================
     CABEÇALHO
=========================== -->

<header class="cabecalho">

    <div class="header-left">

        <div class="logo">
            FOAG
        </div>


        <nav
            class="top-menu"
            aria-label="Menu principal"
        >

            <a
                href="../inicioo/inicio.php"
                class="active"
                aria-current="page"
            >
                <i
                    class="fa-solid fa-house"
                    aria-hidden="true"
                ></i>

                Início
            </a>


            <a href="../calend/calendario.php">

                <i
                    class="fa-solid fa-calendar-days"
                    aria-hidden="true"
                ></i>

                Calendário
            </a>


            <a href="../bloco/agenda.php">

                <i
                    class="fa-solid fa-book"
                    aria-hidden="true"
                ></i>

                Agenda
            </a>


            <a href="../estudos/estudos.php">

                <i
                    class="fa-solid fa-graduation-cap"
                    aria-hidden="true"
                ></i>

                Estudos
            </a>


            <a href="../notas/notas.php">

                <i
                    class="fa-solid fa-check-double"
                    aria-hidden="true"
                ></i>

                Boletim
            </a>


            <a
                href="../loja/loja.php"
                class="<?= $current === 'loja.php'
                    ? 'active'
                    : '' ?>"
            >

                <i
                    class="fa-solid fa-store"
                    aria-hidden="true"
                ></i>

                Loja
            </a>


            <a
                href="../rank/rank.php"
                class="<?= $current === 'rank.php'
                    ? 'active'
                    : '' ?>"
            >

                <i
                    class="fa-solid fa-trophy"
                    aria-hidden="true"
                ></i>

                Ranking
            </a>

        </nav>

    </div>


    <!-- ÍCONES DO CABEÇALHO -->

    <div class="header-icons">


        <a
            href="../configuracoes/configuracoes.php"
            class="link-configuracoes"
            title="Configurações"
            aria-label="Abrir configurações"
        >

            <i
                class="fa-solid fa-gear"
                aria-hidden="true"
            ></i>

        </a>


        <button
            type="button"
            id="icon-perfil"
            class="header-icon-btn"
            title="Perfil"
            aria-label="Abrir perfil"
        >

            <i
                class="fa-regular fa-user"
                aria-hidden="true"
            ></i>

        </button>


        <button
            type="button"
            id="icon-sair"
            class="header-icon-btn"
            title="Sair"
            aria-label="Sair da conta"
        >

            <i
                class="fa-solid fa-right-from-bracket"
                aria-hidden="true"
            ></i>

        </button>

    </div>

</header>



<!-- ===========================
     CONTEÚDO PRINCIPAL
=========================== -->

<main
    class="main-content"
    id="conteudo-principal"
    tabindex="-1"
>

    <div class="welcome-container">


        <!-- LADO ESQUERDO -->

        <div class="left-panel">


            <div class="welcome-header">

                <h1>
                    Bem-vindo de volta! 👋
                </h1>

                <p class="subtitle">
                    Seu organizador pessoal FOAG está aqui
                    para te ajudar a ser mais produtivo
                </p>

            </div>



            <!-- ESTATÍSTICAS -->

            <div class="stats-grid-large">


                <div class="stat-card-large">

                    <div
                        class="stat-icon-large"
                        aria-hidden="true"
                    >

                        <i
                            class="fa-solid fa-calendar-check"
                        ></i>

                    </div>


                    <div class="stat-info-large">

                        <span
                            class="stat-number-large"
                            id="dias-consecutivos"
                        >
                            <?= $diasConsecutivos ?>
                        </span>

                        <span
                            class="stat-label-large"
                        >
                            Dias produtivos consecutivos
                        </span>

                    </div>

                </div>



                <div class="stat-card-large">

                    <div
                        class="stat-icon-large"
                        aria-hidden="true"
                    >

                        <i
                            class="fa-solid fa-tasks"
                        ></i>

                    </div>


                    <div class="stat-info-large">

                        <span
                            class="stat-number-large"
                            id="tarefas-pendentes"
                        >
                            <?= $tarefasPendentes ?>
                        </span>

                        <span
                            class="stat-label-large"
                        >
                            Tarefas para hoje
                        </span>

                    </div>

                </div>



                <div class="stat-card-large">

                    <div
                        class="stat-icon-large"
                        aria-hidden="true"
                    >

                        <i
                            class="fa-solid fa-chart-line"
                        ></i>

                    </div>


                    <div class="stat-info-large">

                        <span
                            class="stat-number-large"
                            id="percentual-geral"
                        >
                            <?= $percentualGeral ?>%
                        </span>

                        <span
                            class="stat-label-large"
                        >
                            Taxa de produtividade
                        </span>

                    </div>

                </div>

            </div>



            <!-- MOTIVAÇÃO -->

            <div class="motivational-section">


                <div class="motivational-quote-large">

                    <i
                        class="fa-solid fa-quote-left"
                        aria-hidden="true"
                    ></i>


                    <p id="quote-text">

                        Organizar é o primeiro passo
                        para o sucesso! Comece seu dia
                        planejando suas atividades.

                    </p>


                    <i
                        class="fa-solid fa-quote-right"
                        aria-hidden="true"
                    ></i>

                </div>



                <div class="quick-tips">

                    <h3>
                        💡 Dicas Rápidas
                    </h3>

                    <ul>

                        <li>
                            Revise suas tarefas pela manhã
                        </li>

                        <li>
                            Estabeleça metas realistas
                            para o dia
                        </li>

                        <li>
                            Faça pausas regulares
                        </li>

                        <li>
                            Celebre suas conquistas
                        </li>

                    </ul>

                </div>

            </div>

        </div>



        <!-- LADO DIREITO -->

        <div class="right-panel">


            <!-- RESUMO DO MÊS -->

            <section
                class="info-card"
                aria-labelledby="titulo-resumo-mes"
            >

                <div class="card-header">

                    <h2 id="titulo-resumo-mes">

                        <i
                            class="fa-solid fa-chart-line"
                            aria-hidden="true"
                        ></i>

                        Resumo do Mês

                    </h2>

                </div>


                <div class="card-content">


                    <div class="metric-row">


                        <div class="metric">

                            <span
                                class="metric-value"
                                id="total-presencas"
                            >
                                <?= $totalPresencas ?>
                            </span>

                            <span class="metric-label">
                                Presenças
                            </span>

                        </div>



                        <div class="metric">

                            <span
                                class="metric-value faltas"
                                id="total-faltas"
                            >
                                <?= $totalFaltas ?>
                            </span>

                            <span class="metric-label">
                                Faltas
                            </span>

                        </div>



                        <div class="metric">

                            <span
                                class="metric-value"
                                id="percentual-presenca"
                            >
                                <?= $percentualPresenca ?>%
                            </span>

                            <span class="metric-label">
                                Frequência
                            </span>

                        </div>

                    </div>



                    <!-- BARRA DE FREQUÊNCIA -->

                    <div class="progress-container">


                        <div
                            class="progress-bar"
                            role="progressbar"
                            aria-label="Frequência mensal"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="<?= $percentualPresenca ?>"
                        >

                            <div
                                class="progress-fill"
                                style="width: <?= $percentualPresenca ?>%"
                            ></div>

                        </div>


                        <span class="progress-text">
                            Meta: 85% de frequência
                        </span>

                    </div>

                </div>

            </section>



            <!-- ANOTAÇÕES IMPORTANTES -->

            <section
                class="info-card"
                aria-labelledby="titulo-anotacoes"
            >

                <div class="card-header">


                    <h2 id="titulo-anotacoes">

                        <i
                            class="fa-solid fa-star"
                            aria-hidden="true"
                        ></i>

                        Anotações Importantes

                    </h2>


                    <button
                        type="button"
                        class="btn-add"
                        id="add-note"
                        aria-label="Criar nova anotação importante"
                        title="Criar nova anotação"
                    >

                        <i
                            class="fa-solid fa-plus"
                            aria-hidden="true"
                        ></i>

                    </button>

                </div>


                <div class="card-content">


                    <div
                        class="notes-list"
                        id="notes-list"
                    >


                        <?php if (
                            !empty($notasImportantes)
                        ): ?>


                            <?php foreach (
                                $notasImportantes
                                as $nota
                            ): ?>


                                <div class="note-item">


                                    <p class="note-text">

                                        <?= htmlspecialchars(
                                            $nota['texto']
                                        ) ?>

                                    </p>


                                    <?php if (
                                        !empty($nota['data'])
                                    ): ?>


                                        <span class="note-date">

                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $nota['data']
                                                )
                                            ) ?>

                                        </span>


                                    <?php endif; ?>


                                </div>


                            <?php endforeach; ?>


                        <?php endif; ?>


                    </div>



                    <div
                        class="empty-notes"
                        id="empty-notes"
                        style="<?= !empty($notasImportantes)
                            ? 'display:none;'
                            : '' ?>"
                    >

                        <i
                            class="fa-solid fa-clipboard"
                            aria-hidden="true"
                        ></i>


                        <p>
                            Nenhuma anotação importante
                        </p>


                        <button
                            type="button"
                            class="btn-primary"
                            id="create-first-note"
                        >

                            <i
                                class="fa-solid fa-plus"
                                aria-hidden="true"
                            ></i>

                            Criar primeira anotação

                        </button>

                    </div>

                </div>

            </section>



            <!-- LEMBRETES -->

            <section
                class="info-card"
                aria-labelledby="titulo-lembretes"
            >

                <div class="card-header">

                    <h2 id="titulo-lembretes">

                        <i
                            class="fa-solid fa-bell"
                            aria-hidden="true"
                        ></i>

                        Próximos Lembretes

                    </h2>

                </div>


                <div class="card-content">


                    <div
                        class="reminders-list"
                        id="reminders-list"
                    ></div>


                    <div
                        class="empty-reminders"
                        id="empty-reminders"
                    >

                        <i
                            class="fa-solid fa-bell-slash"
                            aria-hidden="true"
                        ></i>


                        <p>
                            Nenhum lembrete próximo
                        </p>

                    </div>

                </div>

            </section>

        </div>

    </div>

</main>



<!-- ===========================
     MODAL NOVA ANOTAÇÃO
=========================== -->

<div
    id="note-modal"
    class="modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="titulo-note-modal"
>

    <div class="modal-content">


        <div class="modal-header">


            <h3 id="titulo-note-modal">
                Nova Anotação Importante
            </h3>


            <button
                type="button"
                class="modal-close"
                id="close-note-modal"
                aria-label="Fechar janela de nova anotação"
            >

                <i
                    class="fa-solid fa-times"
                    aria-hidden="true"
                ></i>

            </button>

        </div>



        <div class="modal-body">


            <label
                for="note-text"
                class="sr-only"
            >
                Anotação importante
            </label>


            <textarea
                id="note-text"
                placeholder="Digite sua anotação importante aqui..."
                maxlength="200"
                aria-describedby="note-limite"
            ></textarea>


            <span
                id="note-limite"
                class="sr-only"
            >
                Máximo de 200 caracteres
            </span>


            <div class="modal-footer">


                <button
                    type="button"
                    class="btn-secondary"
                    id="cancel-note"
                >
                    Cancelar
                </button>


                <button
                    type="button"
                    class="btn-primary"
                    id="save-note"
                >
                    Salvar
                </button>


            </div>

        </div>

    </div>

</div>



<!-- ===========================
     MODAL LOGOUT
=========================== -->

<div
    id="logout-modal"
    class="modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="titulo-logout"
>

    <div class="modal-content">


        <h3 id="titulo-logout">
            Ah... já vai?
        </h3>


        <h4>
            Tem certeza de que deseja sair?
        </h4>


        <div class="modal-buttons">


            <button
                type="button"
                id="confirm-logout"
            >
                Sim
            </button>


            <button
                type="button"
                id="cancel-logout"
            >
                Cancelar
            </button>


        </div>

    </div>

</div>



<!-- ===========================
     MODAL FOGi
=========================== -->

<div
    id="fogi-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="titulo-fogi"
>

    <div class="fogi-container">


        <div class="fogi-header">


            <span id="titulo-fogi">
                FOGi — Assistente de Estudos
            </span>


            <button
                type="button"
                id="fogi-close"
            >
                Fechar
            </button>


        </div>


        <iframe
            id="fogi-iframe"
            src="about:blank"
            title="FOGi - Assistente de Estudos"
        ></iframe>


    </div>

</div>



<!-- ===========================
     RODAPÉ
=========================== -->

<footer>

    <div class="footer-content">

        <p>
            &copy; 2025 FOAG.
            Todos os direitos reservados.
        </p>

        <p>
            Organize seu tempo,
            conquiste seus objetivos
        </p>

    </div>

</footer>



<script src="inicio.js"></script>


</body>

</html>
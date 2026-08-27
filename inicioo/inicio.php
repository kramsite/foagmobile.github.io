<?php

session_start();

if (empty($_SESSION['codigo_usuario'])) {
    header('Location: ../login/index.php');
    exit;
}

$codigoUsuario =
    $_SESSION['codigo_usuario'];

$current =
    basename($_SERVER['PHP_SELF']);

$pastaUsuario =
    __DIR__ .
    '/../json/usuarios/' .
    $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    exit('Pasta do usuário não encontrada.');
}


// ==========================================
// FUNÇÕES AUXILIARES
// ==========================================

function carregarJsonInicio(
    $arquivo,
    $padrao = []
) {
    if (!file_exists($arquivo)) {
        return $padrao;
    }

    $conteudo =
        file_get_contents($arquivo);

    if ($conteudo === false) {
        return $padrao;
    }

    $dados =
        json_decode(
            $conteudo,
            true
        );

    return is_array($dados)
        ? $dados
        : $padrao;
}


function tarefaConcluida($tarefa)
{
    return !empty(
        $tarefa['concluida'] ??
        $tarefa['completed'] ??
        $tarefa['feito'] ??
        false
    );
}


function tarefaTexto($tarefa)
{
    return trim(
        (string)(
            $tarefa['texto'] ??
            $tarefa['titulo'] ??
            $tarefa['title'] ??
            $tarefa['descricao'] ??
            ''
        )
    );
}


function tarefaData($tarefa)
{
    return trim(
        (string)(
            $tarefa['data'] ??
            $tarefa['date'] ??
            $tarefa['prazo'] ??
            ''
        )
    );
}


function calcularMediaLinhaInicio(
    $notas,
    $pesos
) {
    if (!is_array($notas)) {
        return null;
    }

    $soma = 0;
    $somaPesos = 0;

    for ($i = 1; $i <= 4; $i++) {

        $nota =
            $notas[$i] ??
            $notas[(string)$i] ??
            null;

        $peso =
            $pesos[$i] ??
            $pesos[(string)$i] ??
            1;

        if (
            $nota === null ||
            $nota === '' ||
            (float)$peso <= 0
        ) {
            continue;
        }

        $soma +=
            (float)$nota *
            (float)$peso;

        $somaPesos +=
            (float)$peso;
    }

    return $somaPesos > 0
        ? $soma / $somaPesos
        : null;
}


function coletarStatusFrequencia(
    $dados,
    &$presencas,
    &$faltas
) {
    if (!is_array($dados)) {
        return;
    }

    $status =
        $dados['status'] ??
        $dados['cor'] ??
        null;

    if ($status !== null) {

        if (
            in_array(
                $status,
                [
                    'presenca',
                    'presença',
                    'verde'
                ],
                true
            )
        ) {
            $presencas++;

        } elseif (
            in_array(
                $status,
                [
                    'falta',
                    'vermelho'
                ],
                true
            )
        ) {
            $faltas++;
        }
    }

    foreach ($dados as $valor) {

        if (is_array($valor)) {

            coletarStatusFrequencia(
                $valor,
                $presencas,
                $faltas
            );
        }
    }
}


function formatarDataLembreteInicio(
    $data
) {
    $data =
        trim(
            (string)$data
        );

    if ($data === '') {
        return 'Sem data';
    }

    $objetoData =
        DateTime::createFromFormat(
            'Y-m-d',
            $data
        );

    if (!$objetoData) {
        return $data;
    }

    return $objetoData->format(
        'd/m'
    );
}


// ==========================================
// ARQUIVOS
// ==========================================

$arquivoAgenda =
    $pastaUsuario .
    '/agenda.json';

$arquivoMaterias =
    $pastaUsuario .
    '/materias.json';

$arquivoPomodoro =
    $pastaUsuario .
    '/pomodoro.json';

$arquivoNotas =
    $pastaUsuario .
    '/notas.json';

$arquivoCalendario =
    $pastaUsuario .
    '/calendario.json';

$arquivoFreqMes =
    $pastaUsuario .
    '/frequencia_' .
    date('Y-m') .
    '.json';


// ==========================================
// CARREGAR DADOS
// ==========================================

$agendaData =
    carregarJsonInicio(
        $arquivoAgenda,
        [
            'notas' => [],
            'tarefas' => [],
            'nao_esquecer' => []
        ]
    );


$materiasData =
    carregarJsonInicio(
        $arquivoMaterias,
        []
    );


$pomodoroData =
    carregarJsonInicio(
        $arquivoPomodoro,
        [
            'sessions' => []
        ]
    );


$notasData =
    carregarJsonInicio(
        $arquivoNotas,
        []
    );


$calendarioData =
    carregarJsonInicio(
        $arquivoCalendario,
        []
    );


// ==========================================
// AGENDA
// ==========================================

$tarefas =
    isset($agendaData['tarefas']) &&
    is_array($agendaData['tarefas'])
        ? array_values(
            $agendaData['tarefas']
        )
        : [];


$lembretes =
    isset($agendaData['nao_esquecer']) &&
    is_array($agendaData['nao_esquecer'])
        ? array_values(
            $agendaData['nao_esquecer']
        )
        : [];


$notasAgenda =
    isset($agendaData['notas']) &&
    is_array($agendaData['notas'])
        ? array_values(
            $agendaData['notas']
        )
        : [];


// ==========================================
// MATÉRIAS
// aceita os dois formatos de materias.json
// ==========================================

$materias = [];

if (
    isset($materiasData['materias']) &&
    is_array($materiasData['materias'])
) {

    $materias =
        array_values(
            $materiasData['materias']
        );

} elseif (
    is_array($materiasData) &&
    array_keys($materiasData) ===
    range(
        0,
        max(
            count($materiasData) - 1,
            0
        )
    )
) {

    $materias =
        array_values(
            $materiasData
        );
}


// ==========================================
// POMODORO
// ==========================================

$sessoes =
    isset($pomodoroData['sessions']) &&
    is_array($pomodoroData['sessions'])
        ? $pomodoroData['sessions']
        : [];


// ==========================================
// TAREFAS / PRODUTIVIDADE
// ==========================================

$hoje =
    date('Y-m-d');

$tarefasPendentes =
    0;

$diasProdutivosMapa =
    [];


// ==========================================
// TAREFAS
// ==========================================

foreach ($tarefas as $tarefa) {

    if (!is_array($tarefa)) {
        continue;
    }

    $texto =
        tarefaTexto($tarefa);

    $data =
        tarefaData($tarefa);

    $concluida =
        tarefaConcluida($tarefa);

    if ($texto === '') {
        continue;
    }


    /*
     * Mostra como pendente:
     *
     * - sem data
     * - de hoje
     * - atrasada
     *
     * Tarefas futuras não entram.
     */
    if (
        !$concluida &&
        (
            $data === '' ||
            $data <= $hoje
        )
    ) {
        $tarefasPendentes++;
    }


    if (
        $concluida &&
        $data !== ''
    ) {

        $diasProdutivosMapa[
            $data
        ] = true;
    }
}


// ==========================================
// DIAS PRODUTIVOS CONSECUTIVOS
// ==========================================

$diasConsecutivos =
    0;

$dataCursor =
    new DateTime($hoje);


while (true) {

    $dataStr =
        $dataCursor->format(
            'Y-m-d'
        );

    if (
        !isset(
            $diasProdutivosMapa[
                $dataStr
            ]
        )
    ) {
        break;
    }

    $diasConsecutivos++;

    $dataCursor->modify(
        '-1 day'
    );
}


// ==========================================
// LEMBRETES
// ==========================================

$proximosLembretes =
    [];


foreach ($lembretes as $lembrete) {

    if (!is_array($lembrete)) {
        continue;
    }

    $texto =
        trim(
            (string)(
                $lembrete['texto'] ??
                $lembrete['titulo'] ??
                ''
            )
        );

    $data =
        trim(
            (string)(
                $lembrete['data'] ??
                $lembrete['date'] ??
                ''
            )
        );

    $hora =
        trim(
            (string)(
                $lembrete['hora'] ??
                $lembrete['time'] ??
                ''
            )
        );


    if ($texto === '') {
        continue;
    }


    /*
     * Lembrete antigo com data
     * não aparece mais.
     */
    if (
        $data !== '' &&
        $data < $hoje
    ) {
        continue;
    }


    $proximosLembretes[] = [
        'texto' =>
            $texto,

        'data' =>
            $data,

        'hora' =>
            $hora
    ];
}


// ==========================================
// ORDENAR LEMBRETES
// ==========================================

usort(
    $proximosLembretes,

    function ($a, $b) {

        $dataA =
            $a['data'] !== ''
                ? $a['data']
                : '9999-12-31';

        $dataB =
            $b['data'] !== ''
                ? $b['data']
                : '9999-12-31';


        $comparacao =
            strcmp(
                $dataA,
                $dataB
            );


        if ($comparacao !== 0) {
            return $comparacao;
        }


        return strcmp(
            $a['hora'] ?? '',
            $b['hora'] ?? ''
        );
    }
);


$proximosLembretes =
    array_slice(
        $proximosLembretes,
        0,
        4
    );


// ==========================================
// ANOTAÇÕES IMPORTANTES
// DIRETO DO agenda.json
// ==========================================

$anotacoesImportantes =
    [];


foreach ($notasAgenda as $nota) {

    if (!is_array($nota)) {
        continue;
    }

    $id =
        $nota['id'] ??
        0;

    $titulo =
        trim(
            (string)(
                $nota['titulo'] ??
                ''
            )
        );

    $texto =
        trim(
            (string)(
                $nota['texto'] ??
                $nota['text'] ??
                ''
            )
        );

    $data =
        trim(
            (string)(
                $nota['data'] ??
                $nota['date'] ??
                ''
            )
        );


    if (
        $titulo === '' &&
        $texto === ''
    ) {
        continue;
    }


    $anotacoesImportantes[] = [
        'id' =>
            $id,

        'titulo' =>
            $titulo,

        'text' =>
            $texto,

        'date' =>
            $data
    ];
}


// Mais recentes primeiro

usort(
    $anotacoesImportantes,

    function ($a, $b) {

        return
            (int)(
                $b['id'] ?? 0
            )
            <=>
            (int)(
                $a['id'] ?? 0
            );
    }
);


$anotacoesImportantes =
    array_slice(
        $anotacoesImportantes,
        0,
        5
    );


// ==========================================
// ESTUDOS / POMODORO
// ==========================================

$totalMinutosEstudados =
    0;

$totalSessoes =
    0;


foreach ($sessoes as $sessao) {

    if (!is_array($sessao)) {
        continue;
    }

    $modo =
        $sessao['mode'] ??
        $sessao['modo'] ??
        'focus';


    if ($modo !== 'focus') {
        continue;
    }


    $minutos =
        (int)(
            $sessao['minutes'] ??
            $sessao['minutos'] ??
            0
        );


    if ($minutos > 0) {

        $totalMinutosEstudados +=
            $minutos;

        $totalSessoes++;
    }
}


$horasEstudadas =
    intdiv(
        $totalMinutosEstudados,
        60
    );


$minutosRestantes =
    $totalMinutosEstudados %
    60;


$tempoEstudadoFormatado =
    $horasEstudadas > 0
        ? $horasEstudadas .
          'h ' .
          str_pad(
              (string)$minutosRestantes,
              2,
              '0',
              STR_PAD_LEFT
          ) .
          'min'
        : $minutosRestantes .
          'min';


// ==========================================
// BOLETIM
// ==========================================

$notaMaxima =
    (float)(
        $notasData['nota_maxima'] ??
        10
    );


$pesos =
    isset($notasData['pesos']) &&
    is_array($notasData['pesos'])
        ? $notasData['pesos']
        : [
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 1
        ];


$periodos =
    isset($notasData['periodos']) &&
    is_array($notasData['periodos'])
        ? $notasData['periodos']
        : [];


$periodoAtual =
    (string)(
        $notasData['periodo_atual'] ??
        ''
    );


$dadosPeriodo =
    isset(
        $periodos[
            $periodoAtual
        ]
    ) &&
    is_array(
        $periodos[
            $periodoAtual
        ]
    )
        ? $periodos[
            $periodoAtual
        ]
        : [];


$nomesPeriodo =
    isset($dadosPeriodo['materias']) &&
    is_array($dadosPeriodo['materias'])
        ? $dadosPeriodo['materias']
        : [];


$notasPeriodo =
    isset($dadosPeriodo['notas']) &&
    is_array($dadosPeriodo['notas'])
        ? $dadosPeriodo['notas']
        : [];


$somaMedias =
    0;

$totalMedias =
    0;

$materiaAtencao =
    null;


foreach (
    $nomesPeriodo
    as $indice =>
       $nomeMateria
) {

    $media =
        calcularMediaLinhaInicio(
            $notasPeriodo[$indice] ??
            [],
            $pesos
        );


    if ($media === null) {
        continue;
    }


    $somaMedias +=
        $media;

    $totalMedias++;


    if (
        $materiaAtencao ===
            null ||
        $media <
            $materiaAtencao[
                'media'
            ]
    ) {

        $materiaAtencao = [
            'nome' =>
                (string)$nomeMateria,

            'media' =>
                $media
        ];
    }
}


$mediaGeral =
    $totalMedias > 0
        ? $somaMedias /
          $totalMedias
        : 0;


$percentualMedia =
    $notaMaxima > 0
        ? (int)round(
            min(
                100,

                max(
                    0,

                    (
                        $mediaGeral /
                        $notaMaxima
                    ) *
                    100
                )
            )
        )
        : 0;


// ==========================================
// FREQUÊNCIA
// ==========================================

$totalPresencas =
    0;

$totalFaltas =
    0;


if (!empty($calendarioData)) {

    coletarStatusFrequencia(
        $calendarioData,
        $totalPresencas,
        $totalFaltas
    );

} elseif (
    file_exists(
        $arquivoFreqMes
    )
) {

    $dadosFreqMes =
        carregarJsonInicio(
            $arquivoFreqMes,
            []
        );

    coletarStatusFrequencia(
        $dadosFreqMes,
        $totalPresencas,
        $totalFaltas
    );
}


$totalAulas =
    $totalPresencas +
    $totalFaltas;


$percentualPresenca =
    $totalAulas > 0
        ? (int)round(
            (
                $totalPresencas /
                $totalAulas
            ) *
            100
        )
        : 0;

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


<div
    id="mensagem-acessibilidade"
    class="sr-only"
    aria-live="polite"
></div>


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


            <a href="../loja/loja.php">

                <i
                    class="fa-solid fa-store"
                    aria-hidden="true"
                ></i>

                Loja

            </a>


            <a href="../rank/rank.php">

                <i
                    class="fa-solid fa-trophy"
                    aria-hidden="true"
                ></i>

                Ranking

            </a>

        </nav>

    </div>


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
     CONTEÚDO
=========================== -->

<main
    class="main-content"
    id="conteudo-principal"
    tabindex="-1"
>

    <div class="welcome-container">


        <!-- ESQUERDA -->

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

                    <div class="stat-icon-large">

                        <i
                            class="fa-solid fa-calendar-check"
                            aria-hidden="true"
                        ></i>

                    </div>

                    <div class="stat-info-large">

                        <span
                            class="stat-number-large"
                            id="dias-consecutivos"
                        >
                            <?= $diasConsecutivos ?>
                        </span>

                        <span class="stat-label-large">
                            Dias produtivos consecutivos
                        </span>

                    </div>

                </div>


                <div class="stat-card-large">

                    <div class="stat-icon-large">

                        <i
                            class="fa-solid fa-list-check"
                            aria-hidden="true"
                        ></i>

                    </div>

                    <div class="stat-info-large">

                        <span
                            class="stat-number-large"
                            id="tarefas-pendentes"
                        >
                            <?= $tarefasPendentes ?>
                        </span>

                        <span class="stat-label-large">
                            Tarefas para hoje
                        </span>

                    </div>

                </div>


                <div class="stat-card-large">

                    <div class="stat-icon-large">

                        <i
                            class="fa-solid fa-stopwatch"
                            aria-hidden="true"
                        ></i>

                    </div>

                    <div class="stat-info-large">

                        <span
                            class="stat-number-large"
                            id="tempo-estudado"
                        >
                            <?= htmlspecialchars(
                                $tempoEstudadoFormatado,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>

                        <span class="stat-label-large">
                            Tempo estudado
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
                        para o sucesso!
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
                            Estabeleça metas realistas para o dia
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


        <!-- DIREITA -->

        <div class="right-panel">


            <!-- RESUMO -->

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

                        Resumo dos Estudos

                    </h2>

                </div>


                <div class="card-content">

                    <div class="metric-row">


                        <div class="metric">

                            <span class="metric-value">
                                <?= count($materias) ?>
                            </span>

                            <span class="metric-label">
                                Matérias
                            </span>

                        </div>


                        <div class="metric">

                            <span class="metric-value">
                                <?= $totalSessoes ?>
                            </span>

                            <span class="metric-label">
                                Sessões
                            </span>

                        </div>


                        <div class="metric">

                            <span class="metric-value">

                                <?= number_format(
                                    $mediaGeral,
                                    2,
                                    ',',
                                    '.'
                                ) ?>

                            </span>

                            <span class="metric-label">
                                Média geral
                            </span>

                        </div>

                    </div>


                    <div class="progress-container">

                        <div
                            class="progress-bar"
                            role="progressbar"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="<?= $percentualMedia ?>"
                        >

                            <div
                                class="progress-fill"
                                style="width: <?= $percentualMedia ?>%"
                            ></div>

                        </div>


                        <span class="progress-text">

                            <?php if ($totalMedias > 0): ?>

                                Desempenho atual:
                                <?= $percentualMedia ?>%

                            <?php else: ?>

                                Adicione notas no Boletim
                                para acompanhar sua média.

                            <?php endif; ?>

                        </span>


                        <?php if ($materiaAtencao): ?>

                            <p
                                class="progress-text"
                                style="margin-top:8px;"
                            >

                                <strong>
                                    Precisa de atenção:
                                </strong>

                                <?= htmlspecialchars(
                                    $materiaAtencao['nome'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                (
                                <?= number_format(
                                    $materiaAtencao['media'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                                )

                            </p>

                        <?php endif; ?>

                    </div>

                </div>

            </section>


            <!-- ===========================
                 ANOTAÇÕES IMPORTANTES
            ============================ -->

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
                        style="<?= empty($anotacoesImportantes)
                            ? 'display:none;'
                            : 'display:flex;' ?>"
                    >

                        <?php foreach (
                            $anotacoesImportantes
                            as $nota
                        ): ?>


                            <div class="note-item">


                                <?php if (
                                    !empty(
                                        $nota['titulo']
                                    )
                                ): ?>

                                    <strong class="note-title">

                                        <?= htmlspecialchars(
                                            $nota['titulo'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                <?php endif; ?>


                                <?php if (
                                    !empty(
                                        $nota['text']
                                    )
                                ): ?>

                                    <p class="note-text">

                                        <?= htmlspecialchars(
                                            $nota['text'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </p>

                                <?php endif; ?>


                                <?php if (
                                    !empty(
                                        $nota['date']
                                    )
                                ): ?>

                                    <span class="note-date">

                                        <?= htmlspecialchars(
                                            $nota['date'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </span>

                                <?php endif; ?>

                            </div>


                        <?php endforeach; ?>

                    </div>


                    <div
                        class="empty-notes"
                        id="empty-notes"
                        style="<?= !empty($anotacoesImportantes)
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


            <!-- ===========================
                 LEMBRETES
            ============================ -->

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
                    >

                        <?php foreach (
                            $proximosLembretes
                            as $lembrete
                        ): ?>


                            <div class="reminder-item">

                                <div
                                    class="reminder-icon"
                                    aria-hidden="true"
                                >

                                    <i
                                        class="fa-solid fa-clock"
                                    ></i>

                                </div>


                                <div class="reminder-text">

                                    <?= htmlspecialchars(
                                        $lembrete['texto'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>


                                <div class="reminder-time">

                                    <?php

                                    if (
                                        $lembrete['data'] ===
                                        ''
                                    ) {

                                        echo 'Sem data';

                                    } elseif (
                                        $lembrete['data'] ===
                                        $hoje
                                    ) {

                                        echo 'Hoje';

                                    } else {

                                        echo htmlspecialchars(
                                            formatarDataLembreteInicio(
                                                $lembrete['data']
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                    }


                                    if (
                                        $lembrete['hora'] !==
                                        ''
                                    ) {

                                        echo ' · ';

                                        echo htmlspecialchars(
                                            $lembrete['hora'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                    }

                                    ?>

                                </div>

                            </div>


                        <?php endforeach; ?>

                    </div>


                    <div
                        class="empty-reminders"
                        id="empty-reminders"
                        style="<?= !empty($proximosLembretes)
                            ? 'display:none;'
                            : '' ?>"
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
                aria-label="Fechar"
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
            ></textarea>


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
     LOGOUT
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


<script>
    window.INICIO_NOTE_SAVE_URL =
        'salvar_anotacao.php';
</script>

<script src="inicio.js"></script>

<script src="../configuracoes/aparencia.js?v=2"></script>
</body>

</html>
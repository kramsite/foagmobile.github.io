<?php

// ==========================================
// ESTRUTURA PADRÃO DOS PONTOS
// ==========================================

function estruturaPadraoPontos()
{
    return [
        'estrelas' => 0,

        'historico' => [],

        'controle' => [

            'pomodoro' => [
                'bonus_diarios' => []
            ],

            'flashcards' => [
                'ultimo_dia' => null,
                'estrelas_dia' => 0
            ],

            'frequencia' => [
                'meses_processados' => [],

                'sequencia_presenca' => [
                    'dias' => 0,
                    'ultima_data' => null,
                    'marcos_recebidos' => []
                ]
            ],

            'notas' => [
                'recompensas' => []
            ],

            'sequencia_estudo' => [
                'dias' => 0,
                'ultima_data_estudo' => null,
                'marcos_recebidos' => []
            ],

            'recompensas_recebidas' => []
        ]
    ];
}


// ==========================================
// CAMINHO DO PONTOS.JSON
// ==========================================

function caminhoArquivoPontos($codigoUsuario)
{
    return
        __DIR__ .
        '/../json/usuarios/' .
        $codigoUsuario .
        '/pontos.json';
}


// ==========================================
// CARREGAR PONTOS
// ==========================================

function carregarPontos($codigoUsuario)
{
    $estrutura = estruturaPadraoPontos();

    $arquivo =
        caminhoArquivoPontos(
            $codigoUsuario
        );


    if (!file_exists($arquivo)) {
        return $estrutura;
    }


    $conteudo =
        file_get_contents(
            $arquivo
        );


    if ($conteudo === false) {
        return $estrutura;
    }


    $dados =
        json_decode(
            $conteudo,
            true
        );


    if (!is_array($dados)) {
        return $estrutura;
    }


    return array_replace_recursive(
        $estrutura,
        $dados
    );
}


// ==========================================
// SALVAR PONTOS
// ==========================================

function salvarPontos(
    $codigoUsuario,
    $dados
) {

    $arquivo =
        caminhoArquivoPontos(
            $codigoUsuario
        );


    $pasta =
        dirname(
            $arquivo
        );


    if (!is_dir($pasta)) {

        mkdir(
            $pasta,
            0777,
            true
        );
    }


    return file_put_contents(
        $arquivo,

        json_encode(
            $dados,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ),

        LOCK_EX
    ) !== false;
}


// ==========================================
// ADICIONAR ESTRELAS
// ==========================================

function adicionarEstrelas(
    $codigoUsuario,
    $tipo,
    $descricao,
    $quantidade,
    $chave
) {

    $quantidade =
        (int) $quantidade;


    if (
        $quantidade <= 0 ||
        empty($chave)
    ) {
        return false;
    }


    $pontos =
        carregarPontos(
            $codigoUsuario
        );


    // ======================================
    // VERIFICAR DUPLICAÇÃO
    // ======================================

    $recebidas =
        $pontos['controle']['recompensas_recebidas']
        ?? [];


    if (
        in_array(
            $chave,
            $recebidas,
            true
        )
    ) {
        return false;
    }


    // ======================================
    // SOMAR ESTRELAS
    // ======================================

    $pontos['estrelas'] +=
        $quantidade;


    // ======================================
    // HISTÓRICO
    // ======================================

    $pontos['historico'][] = [

        'id' =>
            'estrela_' .
            bin2hex(
                random_bytes(6)
            ),

        'tipo' =>
            $tipo,

        'descricao' =>
            $descricao,

        'estrelas' =>
            $quantidade,

        'data' =>
            date(
                'Y-m-d H:i:s'
            ),

        'timestamp' =>
            time(),

        'chave' =>
            $chave
    ];


    // ======================================
    // MARCAR RECOMPENSA COMO RECEBIDA
    // ======================================

    $pontos['controle']['recompensas_recebidas'][] =
        $chave;


    return salvarPontos(
        $codigoUsuario,
        $pontos
    );
}


// ==========================================
// IDENTIFICAR SESSÃO
// ==========================================

function identificarSessaoPomodoro(
    $sessao,
    $indice = 0
) {

    if (
        !empty(
            $sessao['id']
        )
    ) {

        return
            (string)
            $sessao['id'];
    }


    $dados = [
        $sessao['ts'] ?? '',
        $sessao['minutes'] ?? '',
        $sessao['mode'] ?? '',
        $sessao['source'] ?? '',
        $sessao['discipline'] ?? '',
        $indice
    ];


    return
        hash(
            'sha256',
            implode(
                '|',
                $dados
            )
        );
}


// ==========================================
// PROCESSAR ESTRELAS DO POMODORO
// ==========================================

function processarEstrelasPomodoro(
    $codigoUsuario,
    $dadosAnteriores,
    $dadosNovos
) {

    $sessoesAntigas =
        $dadosAnteriores['sessions']
        ?? [];

    $sessoesNovas =
        $dadosNovos['sessions']
        ?? [];


    if (
        !is_array($sessoesAntigas) ||
        !is_array($sessoesNovas)
    ) {
        return;
    }


    // ======================================
    // LISTAR SESSÕES ANTIGAS
    // ======================================

    $idsAntigos = [];


    foreach (
        $sessoesAntigas
        as $indice => $sessao
    ) {

        if (!is_array($sessao)) {
            continue;
        }


        $id =
            identificarSessaoPomodoro(
                $sessao,
                $indice
            );


        $idsAntigos[$id] =
            true;
    }


    // ======================================
    // ANALISAR SESSÕES NOVAS
    // ======================================

    foreach (
        $sessoesNovas
        as $indice => $sessao
    ) {

        if (!is_array($sessao)) {
            continue;
        }


        $idSessao =
            identificarSessaoPomodoro(
                $sessao,
                $indice
            );


        // Sessão já existia
        if (
            isset(
                $idsAntigos[$idSessao]
            )
        ) {
            continue;
        }


        $modo =
            $sessao['mode']
            ?? '';

        $origem =
            $sessao['source']
            ?? '';

        $minutos =
            (int)(
                $sessao['minutes']
                ?? 0
            );


        // ==================================
        // POMODORO
        // ==================================

        if (
            $origem === 'pomodoro' &&
            $modo === 'focus'
        ) {

            adicionarEstrelas(
                $codigoUsuario,
                'pomodoro',
                'Sessão de Pomodoro concluída',
                2,
                'pomodoro_' . $idSessao
            );
        }


        // ==================================
        // CRONÔMETRO
        // MÍNIMO DE 30 MINUTOS
        // ==================================

        if (
            $origem === 'cronometro' &&
            $modo === 'focus' &&
            $minutos >= 1
        ) {

            adicionarEstrelas(
                $codigoUsuario,
                'cronometro',
                'Sessão de 30 minutos ou mais no cronômetro',
                2,
                'cronometro_' . $idSessao
            );
        }
    }


    // ======================================
    // BÔNUS DE TEMPO DO DIA
    // ======================================

    processarBonusTempoDia(
        $codigoUsuario,
        $sessoesNovas
    );
}


// ==========================================
// PROCESSAR BÔNUS DO DIA
// ==========================================

function processarBonusTempoDia(
    $codigoUsuario,
    $sessoes
) {

    $hoje =
        date(
            'Y-m-d'
        );


    $inicioHoje =
        strtotime(
            $hoje .
            ' 00:00:00'
        ) * 1000;


    $fimHoje =
        strtotime(
            $hoje .
            ' 23:59:59'
        ) * 1000;


    $totalMinutos = 0;


    foreach (
        $sessoes
        as $sessao
    ) {

        if (!is_array($sessao)) {
            continue;
        }


        $timestamp =
            (int)(
                $sessao['ts']
                ?? 0
            );


        if (
            $timestamp < $inicioHoje ||
            $timestamp > $fimHoje
        ) {
            continue;
        }


        $modo =
            $sessao['mode']
            ?? '';

        $origem =
            $sessao['source']
            ?? '';

        $minutos =
            (int)(
                $sessao['minutes']
                ?? 0
            );


        // ==================================
        // SOMENTE FOCO
        // ==================================

        if ($modo !== 'focus') {
            continue;
        }


        // ==================================
        // POMODORO
        // ==================================

        if (
            $origem === 'pomodoro'
        ) {

            $totalMinutos +=
                $minutos;
        }


        // ==================================
        // CRONÔMETRO
        // ==================================

        if (
            $origem === 'cronometro' &&
            $minutos >= 30
        ) {

            $totalMinutos +=
                $minutos;
        }
    }


    // ======================================
    // 2 HORAS
    // ======================================

    if ($totalMinutos >= 120) {

        adicionarEstrelas(
            $codigoUsuario,
            'pomodoro',
            '2 horas estudadas no dia',
            3,
            'bonus_estudo_2h_' . $hoje
        );
    }


    // ======================================
    // 4 HORAS
    // ======================================

    if ($totalMinutos >= 240) {

        adicionarEstrelas(
            $codigoUsuario,
            'pomodoro',
            '4 horas estudadas no dia',
            5,
            'bonus_estudo_4h_' . $hoje
        );
    }
}
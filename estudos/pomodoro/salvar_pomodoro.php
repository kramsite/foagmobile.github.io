<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

// ======================================
// VERIFICAR LOGIN
// ======================================

if (empty($_SESSION['codigo_usuario'])) {
    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário não autenticado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];


// ======================================
// ACEITAR SOMENTE POST
// ======================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir = __DIR__ . '/../../json/usuarios';

$pastaUsuario =
    $baseJsonDir . '/' . $codigoUsuario;

$arquivoPomodoro =
    $pastaUsuario . '/pomodoro.json';


// A pasta deve ter sido criada no cadastro
if (!is_dir($pastaUsuario)) {
    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Pasta do usuário não encontrada.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// RECEBER JSON
// ======================================

$input =
    file_get_contents('php://input');

if (
    $input === false ||
    trim($input) === ''
) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Nenhum dado foi recebido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$data =
    json_decode($input, true);


// Verifica erro real de JSON
if (
    json_last_error() !==
    JSON_ERROR_NONE
) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'JSON inválido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (!is_array($data)) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Formato de dados inválido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// CARREGAR MATÉRIAS OFICIAIS
// ======================================

$arquivoMaterias =
    $pastaUsuario . '/materias.json';

$disciplinas = [
    'Geral'
];


if (file_exists($arquivoMaterias)) {

    $materiasData =
        json_decode(
            file_get_contents(
                $arquivoMaterias
            ),
            true
        );


    if (
        is_array($materiasData) &&
        isset(
            $materiasData['materias']
        ) &&
        is_array(
            $materiasData['materias']
        )
    ) {

        foreach (
            $materiasData['materias']
            as $materia
        ) {

            $nome =
                trim(
                    $materia['nome']
                    ?? ''
                );


            if (
                $nome !== '' &&
                $nome !== 'Geral' &&
                !in_array(
                    $nome,
                    $disciplinas,
                    true
                )
            ) {

                $disciplinas[] =
                    $nome;

            }

        }

    }

}


// ======================================
// NORMALIZAR SESSÕES
// ======================================

$sessoesRecebidas =
    $data['sessions'] ?? [];

$sessoes = [];


if (
    is_array(
        $sessoesRecebidas
    )
) {

    foreach (
        $sessoesRecebidas
        as $sessao
    ) {

        if (
            !is_array($sessao)
        ) {
            continue;
        }


        $timestamp =
            (int) (
                $sessao['ts']
                ?? 0
            );


        $minutos =
            (int) (
                $sessao['minutes']
                ?? 0
            );


        $modo =
            trim(
                $sessao['mode']
                ?? 'focus'
            );


        $disciplina =
            trim(
                $sessao['discipline']
                ?? 'Geral'
            );


        // Sessão inválida
        if (
            $timestamp <= 0 ||
            $minutos <= 0
        ) {
            continue;
        }


        // Modos permitidos
        $modosPermitidos = [
            'focus',
            'short',
            'long'
        ];


        if (
            !in_array(
                $modo,
                $modosPermitidos,
                true
            )
        ) {

            $modo = 'focus';

        }


        // Se a matéria não existe mais,
        // mantém como Geral
        if (
            !in_array(
                $disciplina,
                $disciplinas,
                true
            )
        ) {

            $disciplina =
                'Geral';

        }


        $sessoes[] = [
            'ts' =>
                $timestamp,

            'minutes' =>
                $minutos,

            'mode' =>
                $modo,

            'discipline' =>
                $disciplina
        ];

    }

}


// ======================================
// NORMALIZAR METAS
// ======================================

$metasRecebidas =
    $data['goals'] ?? [];

$metas = [];


if (
    is_array(
        $metasRecebidas
    )
) {

    foreach (
        $metasRecebidas
        as $disciplina => $horas
    ) {

        $disciplina =
            trim(
                (string)
                $disciplina
            );


        $horas =
            (float)
            $horas;


        if (
            $disciplina === '' ||
            $horas <= 0
        ) {
            continue;
        }


        // Só salva meta de matéria válida
        if (
            !in_array(
                $disciplina,
                $disciplinas,
                true
            )
        ) {
            continue;
        }


        $metas[
            $disciplina
        ] =
            $horas;

    }

}


// ======================================
// ESTRUTURA FINAL
// ======================================

$dadosPomodoro = [
    'disciplines' =>
        $disciplinas,

    'sessions' =>
        $sessoes,

    // stdClass garante {} quando não há metas
    'goals' =>
        empty($metas)
            ? new stdClass()
            : $metas
];


// ======================================
// CONVERTER PARA JSON
// ======================================

$json =
    json_encode(
        $dadosPomodoro,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


if ($json === false) {
    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao gerar os dados do Pomodoro.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// SALVAR ARQUIVO
// ======================================

$resultado =
    file_put_contents(
        $arquivoPomodoro,
        $json,
        LOCK_EX
    );


if ($resultado === false) {
    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível salvar o Pomodoro.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// RESPOSTA
// ======================================

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Dados do Pomodoro salvos com sucesso.'
], JSON_UNESCAPED_UNICODE);
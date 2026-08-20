<?php

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);


// ======================================
// LOGIN
// ======================================

if (empty($_SESSION['codigo_usuario'])) {

    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário não autenticado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$codigoUsuario =
    $_SESSION['codigo_usuario'];


// ======================================
// POST
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
// PASTA
// ======================================

$baseJsonDir =
    __DIR__ . '/../../json/usuarios';

$pastaUsuario =
    $baseJsonDir . '/' . $codigoUsuario;

$arquivoFlashcards =
    $pastaUsuario . '/flashcards.json';


if (
    !is_dir($pastaUsuario) ||
    !file_exists($arquivoFlashcards)
) {

    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados de flashcards não encontrados.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// RECEBER JSON
// ======================================

$input =
    file_get_contents(
        'php://input'
    );

$data =
    json_decode(
        $input,
        true
    );


if (
    json_last_error() !==
    JSON_ERROR_NONE ||
    !is_array($data)
) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados inválidos.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// CAMPOS
// ======================================

$idBaralho =
    trim(
        $data['baralho_id']
        ?? ''
    );

$idCartao =
    trim(
        $data['cartao_id']
        ?? ''
    );

$resultado =
    trim(
        $data['resultado']
        ?? ''
    );


if (
    $idBaralho === '' ||
    $idCartao === ''
) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Baralho ou cartão não informado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (
    !in_array(
        $resultado,
        [
            'acerto',
            'erro'
        ],
        true
    )
) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Resultado inválido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// ABRIR ARQUIVO COM BLOQUEIO
// ======================================

$handle =
    fopen(
        $arquivoFlashcards,
        'c+'
    );


if (!$handle) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível abrir o arquivo.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (!flock($handle, LOCK_EX)) {

    fclose($handle);

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível bloquear o arquivo.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// LER JSON
// ======================================

rewind($handle);

$conteudo =
    stream_get_contents(
        $handle
    );


$flashcardsData =
    json_decode(
        $conteudo,
        true
    );


if (
    !is_array($flashcardsData) ||
    !isset($flashcardsData['baralhos']) ||
    !is_array($flashcardsData['baralhos'])
) {

    flock($handle, LOCK_UN);
    fclose($handle);

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Arquivo de flashcards inválido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// ENCONTRAR BARALHO E CARTÃO
// ======================================

$indiceBaralho = null;

$indiceCartao = null;


foreach (
    $flashcardsData['baralhos']
    as $i => $baralho
) {

    if (
        ($baralho['id'] ?? '') !==
        $idBaralho
    ) {
        continue;
    }

    $indiceBaralho = $i;


    $cartoes =
        $baralho['cartoes']
        ?? [];


    if (!is_array($cartoes)) {
        break;
    }


    foreach (
        $cartoes
        as $j => $cartao
    ) {

        if (
            ($cartao['id'] ?? '') ===
            $idCartao
        ) {

            $indiceCartao = $j;
            break 2;
        }
    }
}


// ======================================
// NÃO ENCONTRADO
// ======================================

if (
    $indiceBaralho === null ||
    $indiceCartao === null
) {

    flock($handle, LOCK_UN);
    fclose($handle);

    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Cartão não encontrado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// REFERÊNCIAS
// ======================================

$cartao =
    &$flashcardsData[
        'baralhos'
    ][
        $indiceBaralho
    ][
        'cartoes'
    ][
        $indiceCartao
    ];


$baralho =
    &$flashcardsData[
        'baralhos'
    ][
        $indiceBaralho
    ];


// ======================================
// NORMALIZAR CARTÃO
// ======================================

$cartao['acertos'] =
    (int) (
        $cartao['acertos']
        ?? 0
    );

$cartao['erros'] =
    (int) (
        $cartao['erros']
        ?? 0
    );


if (
    !isset($cartao['revisoes']) ||
    !is_array($cartao['revisoes'])
) {

    $cartao['revisoes'] = [];
}


// ======================================
// NORMALIZAR ESTATÍSTICAS DO BARALHO
// ======================================

if (
    !isset($baralho['estatisticas']) ||
    !is_array($baralho['estatisticas'])
) {

    $baralho['estatisticas'] = [];
}


$baralho['estatisticas']['acertos'] =
    (int) (
        $baralho[
            'estatisticas'
        ][
            'acertos'
        ]
        ?? 0
    );


$baralho['estatisticas']['erros'] =
    (int) (
        $baralho[
            'estatisticas'
        ][
            'erros'
        ]
        ?? 0
    );


$baralho['estatisticas']['revisoes'] =
    (int) (
        $baralho[
            'estatisticas'
        ][
            'revisoes'
        ]
        ?? 0
    );


// ======================================
// SALVAR RESULTADO
// ======================================

if ($resultado === 'acerto') {

    $cartao['acertos']++;

    $baralho[
        'estatisticas'
    ][
        'acertos'
    ]++;

} else {

    $cartao['erros']++;

    $baralho[
        'estatisticas'
    ][
        'erros'
    ]++;

}


$baralho[
    'estatisticas'
][
    'revisoes'
]++;


// ======================================
// REGISTRO DA REVISÃO
// ======================================

$cartao['revisoes'][] = [

    'ts' =>
        (int) round(
            microtime(true) *
            1000
        ),

    'resultado' =>
        $resultado

];


// ======================================
// GERAR JSON
// ======================================

$json =
    json_encode(
        $flashcardsData,

        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


if ($json === false) {

    flock($handle, LOCK_UN);
    fclose($handle);

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao gerar JSON.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// ESCREVER
// ======================================

rewind($handle);

ftruncate(
    $handle,
    0
);

$resultadoEscrita =
    fwrite(
        $handle,
        $json
    );

fflush($handle);

flock(
    $handle,
    LOCK_UN
);

fclose($handle);


if ($resultadoEscrita === false) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível salvar a revisão.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// RESPOSTA
// ======================================

echo json_encode([

    'sucesso' =>
        true,

    'mensagem' =>
        'Revisão salva.',

    'resultado' =>
        $resultado,

    'acertos' =>
        $cartao['acertos'],

    'erros' =>
        $cartao['erros']

], JSON_UNESCAPED_UNICODE);
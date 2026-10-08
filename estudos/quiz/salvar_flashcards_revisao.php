<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['codigo_usuario'])) {
    http_response_code(401);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário não autenticado.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];

$baseJsonDir = __DIR__ . '/../../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    http_response_code(404);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Pasta do usuário não encontrada.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$payload = json_decode(
    file_get_contents('php://input'),
    true
);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados inválidos.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$materiaNome = trim($payload['materia'] ?? '');
$cards = $payload['cards'] ?? [];

if ($materiaNome === '') {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Matéria não informada.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!is_array($cards) || !$cards) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Nenhum flashcard foi selecionado.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (count($cards) > 30) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Limite de 30 flashcards por revisão.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$arquivoMaterias = $pastaUsuario . '/materias.json';
$arquivoFlashcards = $pastaUsuario . '/flashcards.json';

$materiasData = file_exists($arquivoMaterias)
    ? json_decode(file_get_contents($arquivoMaterias), true)
    : ['materias' => []];

$materiaEncontrada = null;

if (
    is_array($materiasData) &&
    isset($materiasData['materias']) &&
    is_array($materiasData['materias'])
) {
    foreach ($materiasData['materias'] as $materia) {
        $nomeAtual = trim($materia['nome'] ?? '');

        if (
            mb_strtolower($nomeAtual) ===
            mb_strtolower($materiaNome)
        ) {
            $materiaEncontrada = $materia;
            break;
        }
    }
}

if (!$materiaEncontrada) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'A matéria selecionada não existe mais.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$flashcardsData = file_exists($arquivoFlashcards)
    ? json_decode(file_get_contents($arquivoFlashcards), true)
    : ['baralhos' => []];

if (!is_array($flashcardsData)) {
    $flashcardsData = ['baralhos' => []];
}

if (
    !isset($flashcardsData['baralhos']) ||
    !is_array($flashcardsData['baralhos'])
) {
    $flashcardsData['baralhos'] = [];
}

try {
    $idBaralho = 'BAR_' . strtoupper(bin2hex(random_bytes(4)));
} catch (Exception $e) {
    $idBaralho = 'BAR_' . strtoupper(uniqid());
}

$agora = new DateTime('now');
$nomeBase = 'Revisão do Quiz - ' . $agora->format('d/m/Y');
$nomeBaralho = $nomeBase;
$contador = 2;

$nomesExistentes = [];

foreach ($flashcardsData['baralhos'] as $baralho) {
    $nomesExistentes[] = mb_strtolower(
        trim($baralho['nome'] ?? '')
    );
}

while (
    in_array(
        mb_strtolower($nomeBaralho),
        $nomesExistentes,
        true
    )
) {
    $nomeBaralho = $nomeBase . ' (' . $contador . ')';
    $contador++;
}

$novosCartoes = [];

foreach ($cards as $card) {
    $pergunta = trim($card['pergunta'] ?? '');
    $resposta = trim($card['resposta'] ?? '');

    if ($pergunta === '' || $resposta === '') {
        continue;
    }

    if (mb_strlen($pergunta) > 500) {
        $pergunta = mb_substr($pergunta, 0, 500);
    }

    if (mb_strlen($resposta) > 1000) {
        $resposta = mb_substr($resposta, 0, 1000);
    }

    try {
        $idCartao = 'CARD_' . strtoupper(bin2hex(random_bytes(4)));
    } catch (Exception $e) {
        $idCartao = 'CARD_' . strtoupper(uniqid());
    }

    $novosCartoes[] = [
        'id' => $idCartao,
        'pergunta' => $pergunta,
        'resposta' => $resposta,
        'acertos' => 0,
        'erros' => 0,
        'revisoes' => [],
        'criado_em' => date('Y-m-d H:i:s'),
        'origem' => 'quiz'
    ];
}

if (!$novosCartoes) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Os flashcards selecionados estão vazios.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$novoBaralho = [
    'id' => $idBaralho,
    'nome' => $nomeBaralho,
    'materia' => $materiaNome,
    'descricao' => 'Flashcards criados automaticamente a partir das questões erradas no Quiz.',
    'cor' => $materiaEncontrada['cor'] ?? '#38a5ff',
    'icone' => $materiaEncontrada['icone'] ?? 'fa-book',
    'cartoes' => $novosCartoes,
    'estatisticas' => [
        'acertos' => 0,
        'erros' => 0,
        'revisoes' => 0
    ],
    'criado_em' => date('Y-m-d H:i:s'),
    'origem' => 'quiz'
];

$flashcardsData['baralhos'][] = $novoBaralho;

$json = json_encode(
    $flashcardsData,
    JSON_PRETTY_PRINT |
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);

if ($json === false) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível preparar os flashcards.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (
    file_put_contents(
        $arquivoFlashcards,
        $json,
        LOCK_EX
    ) === false
) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível salvar os flashcards.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Flashcards de revisão criados com sucesso.',
    'baralho' => [
        'id' => $idBaralho,
        'nome' => $nomeBaralho,
        'quantidade' => count($novosCartoes)
    ]
], JSON_UNESCAPED_UNICODE);

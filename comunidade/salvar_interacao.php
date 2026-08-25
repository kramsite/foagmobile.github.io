<?php
// salvar_interacoes.php — Salva as interações do usuário (curtidas e salvos)

session_start();

header('Content-Type: application/json; charset=utf-8');

// Verifica se o usuário está logado
if (!isset($_SESSION['codigo_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método inválido']);
    exit;
}

$userId = $_SESSION['codigo_usuario'];

// Caminho do arquivo
$baseJsonDir = __DIR__ . '/../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $userId;
$arquivoInteracoes = $pastaUsuario . '/interacoes.json';

// Cria a pasta se não existir
if (!is_dir($pastaUsuario)) {
    mkdir($pastaUsuario, 0755, true);
}

// Lê os dados enviados
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

// Garante a estrutura mínima
if (!isset($data['curtidas']) || !is_array($data['curtidas'])) {
    $data['curtidas'] = [];
}
if (!isset($data['salvos']) || !is_array($data['salvos'])) {
    $data['salvos'] = [];
}

// Salva no arquivo
$resultado = file_put_contents(
    $arquivoInteracoes,
    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if ($resultado === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao salvar interações']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Interações salvas com sucesso'
]);
?>
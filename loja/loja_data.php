<?php
session_start();

// ======================================
// VERIFICAR LOGIN
// ======================================

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensagem' => 'Não autenticado']);
    exit;
}

$userId = $_SESSION['user_id'];

// ======================================
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir = __DIR__ . '/../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $userId;
$arquivoLoja = $pastaUsuario . '/loja.json';

// ======================================
// CARREGAR DADOS
// ======================================

if (!file_exists($arquivoLoja)) {
    echo json_encode([
        'estrelas' => 0,
        'itens_comprados' => [],
        'itens' => []
    ]);
    exit;
}

$dados = json_decode(file_get_contents($arquivoLoja), true);

if (!is_array($dados)) {
    $dados = ['estrelas' => 0, 'itens_comprados' => [], 'itens' => []];
}

echo json_encode($dados);
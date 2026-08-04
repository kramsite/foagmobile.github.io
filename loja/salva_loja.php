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

if (!is_dir($pastaUsuario)) {
    mkdir($pastaUsuario, 0755, true);
}

// ======================================
// SALVAR DADOS
// ======================================

$dados = json_decode(file_get_contents('php://input'), true);

if ($dados === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensagem' => 'Dados inválidos']);
    exit;
}

$arquivoLoja = $pastaUsuario . '/loja.json';

// Carregar dados existentes para preservar a estrutura
$dadosExistentes = [];
if (file_exists($arquivoLoja)) {
    $dadosExistentes = json_decode(file_get_contents($arquivoLoja), true);
    if (!is_array($dadosExistentes)) {
        $dadosExistentes = [];
    }
}

// Mesclar dados (manter itens que não vieram)
$dadosMesclados = array_merge($dadosExistentes, $dados);

// Garantir que itens está presente
if (!isset($dadosMesclados['itens'])) {
    $dadosMesclados['itens'] = [];
}

// Salvar
if (file_put_contents($arquivoLoja, json_encode($dadosMesclados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensagem' => 'Erro ao salvar']);
}
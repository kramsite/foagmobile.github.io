<?php
session_start();

// ======================================
// VERIFICAR LOGIN - USANDO codigo_usuario
// ======================================

if (!isset($_SESSION['codigo_usuario'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensagem' => 'Não autenticado']);
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];

// ======================================
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir = __DIR__ . '/../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

// ======================================
// FUNÇÃO PARA LER JSON (IGUAL AO RANKING)
// ======================================

function lerJson($arquivo) {
    if (!file_exists($arquivo)) {
        return [];
    }
    $conteudo = file_get_contents($arquivo);
    if ($conteudo === false) {
        return [];
    }
    $dados = json_decode($conteudo, true);
    return is_array($dados) ? $dados : [];
}

// ======================================
// CARREGAR DADOS DA LOJA
// ======================================

$arquivoLoja = $pastaUsuario . '/loja.json';

if (!file_exists($arquivoLoja)) {
    echo json_encode([
        'estrelas' => 0,
        'total_estudado' => 0,
        'itens_comprados' => [],
        'itens' => []
    ]);
    exit;
}

$dados = lerJson($arquivoLoja);

if (empty($dados)) {
    $dados = ['estrelas' => 0, 'total_estudado' => 0, 'itens_comprados' => [], 'itens' => []];
}

echo json_encode($dados);
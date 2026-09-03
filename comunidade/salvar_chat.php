<?php
// salvar_chat.php — Salva os dados do chat (perguntas e respostas)

session_start();

header('Content-Type: application/json; charset=utf-8');

// ==========================================
// CONFIGURAÇÃO DE ERROS
// ==========================================

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ==========================================
// FUNÇÃO DE RESPOSTA
// ==========================================

function responder($dados, $status = 200)
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ==========================================
// 1. VERIFICAR USUÁRIO
// ==========================================

$codigoUsuario = $_SESSION['codigo_usuario'] ?? $_SESSION['user_id'] ?? null;

if (!$codigoUsuario) {
    responder([
        'ok' => false,
        'erro' => 'USUARIO_NAO_LOGADO',
        'mensagem' => 'Usuário não autenticado.'
    ], 401);
}

// ==========================================
// 2. VERIFICAR MÉTODO
// ==========================================

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responder([
        'ok' => false,
        'erro' => 'METODO_INVALIDO',
        'mensagem' => 'Use uma requisição POST.'
    ], 405);
}

// ==========================================
// 3. PASTA DO USUÁRIO
// ==========================================

$baseJsonDir = __DIR__ . '/../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    $criou = @mkdir($pastaUsuario, 0755, true);
    if (!$criou && !is_dir($pastaUsuario)) {
        responder([
            'ok' => false,
            'erro' => 'ERRO_CRIAR_PASTA',
            'mensagem' => 'Não foi possível criar a pasta do usuário.'
        ], 500);
    }
}

// ==========================================
// 4. LER DADOS RECEBIDOS
// ==========================================

$raw = file_get_contents('php://input');

if ($raw === false || trim($raw) === '') {
    responder([
        'ok' => false,
        'erro' => 'DADOS_VAZIOS',
        'mensagem' => 'Nenhum dado foi recebido.'
    ], 400);
}

$dados = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    responder([
        'ok' => false,
        'erro' => 'JSON_INVALIDO',
        'mensagem' => 'Os dados enviados estão em formato JSON inválido.'
    ], 400);
}

// ==========================================
// 5. GARANTIR ESTRUTURA
// ==========================================

if (!isset($dados['perguntas']) || !is_array($dados['perguntas'])) {
    $dados = ['perguntas' => []];
}

// ==========================================
// 6. ARQUIVO DO CHAT
// ==========================================

$arquivoChat = $pastaUsuario . '/chat.json';

$jsonChat = json_encode(
    $dados,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

if ($jsonChat === false) {
    responder([
        'ok' => false,
        'erro' => 'ERRO_JSON_CHAT',
        'mensagem' => 'Não foi possível preparar os dados do chat.'
    ], 500);
}

// ==========================================
// 7. GRAVAR CHAT
// ==========================================

$resultado = @file_put_contents($arquivoChat, $jsonChat, LOCK_EX);

if ($resultado === false) {
    responder([
        'ok' => false,
        'erro' => 'ERRO_SALVAR_CHAT',
        'mensagem' => 'Não foi possível salvar o chat.'
    ], 500);
}

// ==========================================
// 8. SUCESSO
// ==========================================

responder([
    'ok' => true,
    'mensagem' => 'Chat salvo com sucesso.'
]);
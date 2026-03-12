<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// ------- CONFIG DE ERROS (não sujar o JSON) -------
ini_set('display_errors', 0);     // não exibe erro na tela
ini_set('log_errors', 1);         // loga no error_log do PHP
error_reporting(E_ALL);

// ------- 1) Confere usuário logado -------
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode([
        'ok'   => false,
        'erro' => 'USUARIO_NAO_LOGADO'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ------- 2) Confere método -------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'ok'   => false,
        'erro' => 'METODO_INVALIDO'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ------- 3) Pega o HTML da tabela -------
// Tenta primeiro POST comum
$html = $_POST['html'] ?? null;

// Se não veio por POST, tenta JSON cru no corpo
if ($html === null) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['html'])) {
            $html = $decoded['html'];
        }
    }
}

// Se ainda não tiver nada, considera string vazia
if ($html === null) {
    $html = '';
}

// ------- 4) Garante pasta do usuário -------
$baseJsonDir  = __DIR__ . '/../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $userId;

if (!is_dir($pastaUsuario)) {
    if (!mkdir($pastaUsuario, 0755, true) && !is_dir($pastaUsuario)) {
        echo json_encode([
            'ok'   => false,
            'erro' => 'NAO_CONSEGUI_CRIAR_PASTA_USUARIO'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$arquivoHorario = $pastaUsuario . '/horario.json';

// ------- 5) Monta estrutura no formato que o horario_api.php espera -------
$dadosSalvar = [
    'html' => $html
];

// ------- 6) Salva no arquivo -------
$result = @file_put_contents(
    $arquivoHorario,
    json_encode($dadosSalvar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if ($result === false) {
    echo json_encode([
        'ok'   => false,
        'erro' => 'ERRO_AO_SALVAR_ARQUIVO',
        'arquivo' => $arquivoHorario
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ------- 7) Sucesso -------
echo json_encode([
    'ok'      => true,
    'mensagem'=> 'Horário salvo com sucesso',
    'arquivo' => $arquivoHorario
], JSON_UNESCAPED_UNICODE);

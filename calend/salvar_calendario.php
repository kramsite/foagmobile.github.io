<?php
// salvar_calendario.php — salva marcações do calendário por usuário

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'error' => 'NOT_LOGGED']);
    exit;
}

$userId = $_SESSION['user_id'];

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['ok' => false, 'error' => 'INVALID_JSON']);
    exit;
}

$dias  = isset($data['dias'])  && is_array($data['dias'])  ? $data['dias']  : [];
$metas = isset($data['metas']) && is_array($data['metas']) ? $data['metas'] : [];

$baseJsonDir  = __DIR__ . '/../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $userId;

if (!is_dir($pastaUsuario)) {
    mkdir($pastaUsuario, 0755, true);
}

$arquivoCalend = $pastaUsuario . '/calendario.json';

$salvar = [
    'dias'  => $dias,
    'metas' => $metas
];

file_put_contents(
    $arquivoCalend,
    json_encode($salvar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo json_encode(['ok' => true]);

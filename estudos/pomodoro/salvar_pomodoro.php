<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

// Verifica login
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Usuário não autenticado']);
  exit;
}

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Método inválido']);
  exit;
}

$userId = $_SESSION['user_id'];

$baseJsonDir    = __DIR__ . '/../json/usuarios';
$pastaUsuario   = $baseJsonDir . '/' . $userId;
$arquivoPomodoro = $pastaUsuario . '/pomodoro.json';

if (!is_dir($pastaUsuario)) {
  mkdir($pastaUsuario, 0755, true);
}

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if ($data === null) {
  http_response_code(400);
  echo json_encode(['error' => 'JSON inválido']);
  exit;
}

file_put_contents(
  $arquivoPomodoro,
  json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo json_encode([
  'status' => 'ok',
  'file'   => $arquivoPomodoro
]);

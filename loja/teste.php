<?php
session_start();

// Verificar sessão
if (!isset($_SESSION['codigo_usuario'])) {
    die("❌ Sessão não encontrada! Faça login primeiro.");
}

$codigo = $_SESSION['codigo_usuario'];
$pasta = __DIR__ . '/../json/usuarios/' . $codigo;
$arquivo = $pasta . '/loja.json';

// Dados para enviar via POST
$dados = [
    'estrelas' => 100,
    'total_estudado' => 0,
    'itens_comprados' => ['teste_item']
];

// Simular requisição POST
$ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/loja/salvar_loja.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$resposta = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>Teste POST para salvar_loja.php</h1>";
echo "<p><strong>HTTP Code:</strong> " . $httpCode . "</p>";
echo "<p><strong>Resposta:</strong></p>";
echo "<pre>" . htmlspecialchars($resposta) . "</pre>";

if (file_exists($arquivo)) {
    echo "<h2>Conteúdo do arquivo:</h2>";
    echo "<pre>" . htmlspecialchars(file_get_contents($arquivo)) . "</pre>";
}
?>
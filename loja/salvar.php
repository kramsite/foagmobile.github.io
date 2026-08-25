<?php
session_start();

// Verificar sessão
if (!isset($_SESSION['codigo_usuario'])) {
    die("❌ Sessão não encontrada! Faça login primeiro.");
}

$codigo = $_SESSION['codigo_usuario'];
echo "<h1>Teste de Salvamento</h1>";
echo "<p>Usuário: " . $codigo . "</p>";

// Definir arquivo
$pasta = __DIR__ . '/../json/usuarios/' . $codigo;
$arquivo = $pasta . '/loja.json';

// Criar pasta se não existir
if (!is_dir($pasta)) {
    mkdir($pasta, 0755, true);
    echo "<p>📁 Pasta criada: " . $pasta . "</p>";
}

// Dados de teste
$dados = [
    'estrelas' => 50,
    'teste' => 'funcionou',
    'data' => date('Y-m-d H:i:s')
];

// Salvar
$json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$resultado = file_put_contents($arquivo, $json);

if ($resultado !== false) {
    echo "<p style='color:green;'>✅ Arquivo salvo com sucesso!</p>";
    echo "<p>Arquivo: " . $arquivo . "</p>";
    echo "<pre>";
    echo htmlspecialchars(file_get_contents($arquivo));
    echo "</pre>";
} else {
    echo "<p style='color:red;'>❌ ERRO: Não foi possível salvar!</p>";
    echo "<p>Verifique as permissões da pasta.</p>";
}

// Verificar permissões
echo "<p><strong>Permissões da pasta:</strong> " . substr(sprintf('%o', fileperms($pasta)), -4) . "</p>";
if (file_exists($arquivo)) {
    echo "<p><strong>Permissões do arquivo:</strong> " . substr(sprintf('%o', fileperms($arquivo)), -4) . "</p>";
}
?>
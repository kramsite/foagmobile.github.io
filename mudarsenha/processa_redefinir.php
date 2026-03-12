<?php
// processa_redefinir.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mudar.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$nova_senha = $_POST['nova_senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

// Validações básicas
if (empty($email) || empty($nova_senha) || empty($confirmar_senha)) {
    echo "Por favor, preencha todos os campos.";
    exit;
}

if ($nova_senha !== $confirmar_senha) {
    echo "As senhas não coincidem.";
    exit;
}

// Validação senha conforme regras (mín 8 caracteres, 1 maiúscula, 1 número, 1 símbolo)
if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/', $nova_senha)) {
    echo "Senha não atende aos requisitos de segurança.";
    exit;
}

$arquivo = '../cadastro/usuario.txt';
if (!file_exists($arquivo)) {
    echo "Arquivo de usuários não encontrado.";
    exit;
}

// Lê todas as linhas do arquivo
$linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$encontrou = false;

foreach ($linhas as $index => $linha) {
    // Cada linha: email|senhaHash|outrosCampos
    $partes = explode('|', $linha);
    $email_arquivo = $partes[0] ?? '';

    if (strcasecmp($email, $email_arquivo) === 0) {
        // Usuário encontrado, vamos atualizar a senha
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);

        // Monta a linha atualizada mantendo os outros campos (exceto senha que atualizamos)
        $partes[1] = $hash;
        $linhas[$index] = implode('|', $partes);

        $encontrou = true;
        break;
    }
}

if (!$encontrou) {
    echo "E-mail não cadastrado.";
    exit;
}

// Escreve de volta no arquivo
file_put_contents($arquivo, implode(PHP_EOL, $linhas) . PHP_EOL);

echo "Senha redefinida com sucesso! Você pode <a href='../index/index.php'>fazer login</a> agora.";

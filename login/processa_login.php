<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($senha)) {
        $arquivo = __DIR__ . '/../json/usuarios.json';

        if (!file_exists($arquivo)) {
            echo "Nenhum usuário cadastrado.";
            exit;
        }

        $conteudo = file_get_contents($arquivo);
        $usuarios = json_decode($conteudo, true) ?? [];

        foreach ($usuarios as $usuario) {
            // confere email
            if ($usuario['email'] === $email && password_verify($senha, $usuario['senha'])) {

                // guarda infos na sessão
                // (IMPORTANTE: agora vai o id também)
                $_SESSION['user_id']    = $usuario['id'] ?? null;
                $_SESSION['user_nome']  = $usuario['nome'] ?? '';
                $_SESSION['user_email'] = $usuario['email'] ?? '';

                // mantém o que você já usava
                $_SESSION['usuario'] = $usuario['nome'] ?? $usuario['email'];

                exibirMensagem("Login realizado com sucesso!", "entrada.php");
                exit;
            }
        }

        exibirMensagem("E-mail ou senha incorretos.", "index.php");
        exit;
    } else {
        exibirMensagem("Preencha o e-mail e a senha corretamente.", "login6.php");
        exit;
    }
} else {
    echo "Acesso inválido.";
    exit;
}

function exibirMensagem($mensagem, $redirect) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta http-equiv="refresh" content="2;url={$redirect}">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #38a5ff, rgb(46, 154, 241));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            color: white;
        }
        h2 {
            font-size: 1.8em;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
        }
    </style>
</head>
<body>
    <h2>{$mensagem}</h2>
    <p>Redirecionando...</p>
</body>
</html>
HTML;
}
?>

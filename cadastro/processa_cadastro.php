<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletar dados do formulário
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $data_nascimento = trim($_POST['data_nascimento'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $serie = trim($_POST['serie'] ?? '');
    $escola = trim($_POST['escola'] ?? '');
    $termos = isset($_POST['termos']) ? true : false;
    $data_cadastro = date("Y-m-d H:i:s");

    // Validações básicas
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($nome) || empty($senha) || empty($data_nascimento)) {
        exit("Por favor, preencha todos os campos corretamente.");
    }

    // Sanitizar nome (remove tags HTML)
    $nome = strip_tags($nome);

    // Validar senha (back-end também protege contra requisições diretas)
    $regexSenha = '/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/';
    if (!preg_match($regexSenha, $senha)) {
        exit("Senha inválida. Deve ter ao menos 8 caracteres, 1 letra maiúscula, 1 número e 1 símbolo.");
    }

    $arquivo = __DIR__ . '/../json/usuarios.json';

    // Carregar dados existentes
    $usuarios = [];
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        $usuarios = json_decode($conteudo, true) ?? [];
    }

    // --- GERAR ID NOVO ---
    $ultimoId = 0;
    if (!empty($usuarios)) {
        $ids = array_column($usuarios, 'id');
        $ultimoId = max($ids);
    }
    $novoId = $ultimoId + 1;

    // Verificar se o e-mail já existe
    foreach ($usuarios as $usuario) {
        if ($usuario['email'] === $email) {
            exibirMensagem("Ops... este e-mail já está cadastrado!", "cadastro.php");
            exit;
        }
    }

    // Criar hash seguro da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Adicionar usuário (AGORA COM ID)
    $usuarios[] = [
        'id' => $novoId,
        'nome' => $nome,
        'email' => $email,
        'senha' => $senha_hash,
        'nascimento' => $data_nascimento,
        'telefone' => $telefone,
        'serie' => $serie,
        'escola' => $escola,
        'termos_aceitos' => $termos,
        'cadastrado_em' => $data_cadastro
    ];

    // Salvar no JSON
    file_put_contents($arquivo, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // --- CRIAR PASTA INDIVIDUAL DO USUÁRIO ---
    $pastaUsuario = __DIR__ . '/../json/usuarios/' . $novoId;

    if (!is_dir($pastaUsuario)) {
        mkdir($pastaUsuario, 0755, true);
    }

    // --- CRIAR ARQUIVOS JSON PADRÃO ---
    $arquivosIniciais = [
        'agenda.json',
        'calendario.json',
        'horario.json',
        'pomodoro.json',
        'notas.json'
    ];

    foreach ($arquivosIniciais as $arquivoBase) {
        $caminho = $pastaUsuario . '/' . $arquivoBase;
        if (!file_exists($caminho)) {
            file_put_contents($caminho, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    // Mensagem + redirecionamento
    exibirMensagem("Cadastro realizado com sucesso :)", "../login/index.php");
    exit;

} else {
    exit("Acesso inválido.");
}

// Exibe mensagens e redireciona
function exibirMensagem($mensagem, $redirect) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Mensagem</title>
    <meta http-equiv="refresh" content="3;url={$redirect}">
    <style>
        body {
            font-family:'Poppins', sans-serif;
            background: linear-gradient(to right, #38a5ff, rgb(46, 154, 241));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        h1 {
            font-size: 5em;
            font-family: 'Snap ITC', sans-serif;
            color: white;
            margin-bottom: 10px;
        }
        h2 {
            font-size: 2.5em;
            color: white;
            margin-bottom: 10px;
            text-align: center;
        }
        p, small {
            color: white;
            text-align: center;
        }
        a {
            color: yellow;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>FOAG</h1>
    <h2>{$mensagem}</h2>
    <p>Você será redirecionado em alguns segundos...</p>
    <small>Se não for redirecionado, <a href="{$redirect}">clique aqui</a>.</small>
</body>
</html>
HTML;
}
?>

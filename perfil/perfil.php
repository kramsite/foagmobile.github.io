<?php
// Caminhos
$caminho_json = "../json/usuarios.json";
$pasta_fotos = "../img/perfil/";
$foto_padrao = "foto_padrao.png";

// Verifica se o arquivo existe
if (!file_exists($caminho_json)) {
    echo "Arquivo de usuários não encontrado!";
    exit;
}

// Carrega todos os usuários
$usuarios = json_decode(file_get_contents($caminho_json), true);

// Garante que é um array válido e não está vazio
if (!is_array($usuarios) || empty($usuarios)) {
    echo "Nenhum usuário cadastrado!";
    exit;
}

// Seleciona o último usuário cadastrado (último do array)
$usuario_logado = end($usuarios);

// Define a foto (caso tenha sido salva depois)
$foto_perfil = (!empty($usuario_logado["foto"]) && file_exists($pasta_fotos . $usuario_logado["foto"]))
    ? $usuario_logado["foto"]
    : $foto_padrao;
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Perfil - Foag</title>
  <link rel="stylesheet" href="perfilfil.css">
 <link rel="stylesheet" href="dark-per.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="../m.escuro/dark-mode.js"></script>
</head>
<body>
  <button class="btn-sair" onclick="location.href='../inicio/inicio.php'">Sair</button>

  <div class="container">
    <div class="left-panel">
      <img src="<?= $pasta_fotos . $foto_perfil ?>" alt="Foto de perfil" class="avatar">
      <h2>Seu Perfil</h2>
      <p>Essas são suas informações salvas no sistema.</p>
      <button class="btn-seta" onclick="location.href='editar.php'">✎</button>
    </div>

    <div class="right-panel">
      <div class="campo-info">
        <label>Nome</label>
        <div class="texto-info"><?= htmlspecialchars($usuario_logado["nome"]) ?></div>
      </div>

      <div class="campo-info">
        <label>Email</label>
        <div class="texto-info"><?= htmlspecialchars($usuario_logado["email"]) ?></div>
      </div>

      <div class="campo-info">
        <label>Data de Nascimento</label>
        <div class="texto-info"><?= htmlspecialchars($usuario_logado["nascimento"]) ?></div>
      </div>

      <div class="campo-info">
        <label>Telefone</label>
        <div class="texto-info"><?= htmlspecialchars($usuario_logado["telefone"]) ?></div>
      </div>

      <div class="campo-info">
        <label>Série / Ano</label>
        <div class="texto-info"><?= htmlspecialchars($usuario_logado["serie"]) ?></div>
      </div>

      <div class="campo-info">
        <label>Escola / Faculdade</label>
        <div class="texto-info"><?= htmlspecialchars($usuario_logado["escola"]) ?></div>
      </div>
    </div>
  </div>
</body>
</html>

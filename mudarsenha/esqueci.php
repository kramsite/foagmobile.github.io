<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar Senha</title>
  <link rel="stylesheet" href="mudar.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
  <!-- Cabeçalho -->
  <div class="logo">FOAG</div>

  <!-- Layout de recuperação -->
  <div class="login-page">
    <!-- Coluna esquerda com imagem -->
    <div class="left-section">
      <img src="../img/livro.png" alt="Imagem" style="width: 100%; height: 100%; object-fit: cover;">
    </div>

    <!-- Coluna direita com formulário -->
    <div class="right-section">
      <h1>Recuperar Senha</h1>
      <p style="text-align:center; margin-bottom:20px; color:#555;">
        Digite seu e-mail e enviaremos um link para redefinir sua senha.
      </p>
      <form method="POST" action="processa_recuperacao.php">
        <label for="email">E-mail:</label><br>
        <input type="email" id="email" name="email" required autocomplete="off"><br><br>

        <button type="submit">Enviar Link</button>
      </form>

      <div style="text-align:center; margin-top:20px;">
        <a href="login.php">Voltar ao Login</a>
      </div>
    </div>
  </div>
</body>
</html>

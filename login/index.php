<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Login</title>

  <link rel="stylesheet" href="estilo.css">

  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap"
    rel="stylesheet"
  >
</head>

<body>

  <!-- Cabeçalho fixo -->
  <div class="logo">
    FOAG
  </div>

  <!-- Página de login -->
  <div class="login-page">

    <!-- Lado esquerdo -->
    <div class="left-section">

      <img
        src="../img/login.jpeg"
        alt="Inspirational image"
      >

    </div>

    <!-- Lado direito -->
    <div class="right-section">

      <h1>Login</h1>

      <form
        method="POST"
        action="processa_login.php"
      >

        <!-- E-MAIL -->

        <label for="email">
          E-mail:
        </label>

        <br>

        <input
          type="email"
          id="email"
          name="email"
          required
          autocomplete="off"
        >

        <br><br>


        <!-- SENHA -->

        <label for="senha">
          Senha:
        </label>

        <br>

        <div class="password-wrapper">

          <input
            type="password"
            id="senha"
            name="senha"
            required
            autocomplete="off"
          >

          <span
            class="toggle-visibility"
            data-target="senha"
          >
            🙈
          </span>

        </div>

        <br><br>


        <!-- LINKS -->

        <div class="login-links">

          <a
            href="../cadastro/cadastro.php"
            class="link-cadastro"
          >
            CADASTRE-SE
          </a>

          <a
            href="../mudarsenha/esqueci.php"
            class="link-recuperar"
          >
            Esqueci minha senha?
          </a>

        </div>


        <!-- BOTÃO -->

        <button type="submit">
          Entrar
        </button>

      </form>

    </div>

  </div>


  <script>

    // Alternar visibilidade da senha
    document
      .querySelectorAll('.toggle-visibility')
      .forEach(icon => {

        icon.addEventListener('click', () => {

          const targetId =
            icon.getAttribute('data-target');

          const input =
            document.getElementById(targetId);

          const isPassword =
            input.type === 'password';

          input.type =
            isPassword
              ? 'text'
              : 'password';

          icon.textContent =
            isPassword
              ? '🙉'
              : '🙈';

        });

      });

  </script>

</body>

</html>
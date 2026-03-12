<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Redefinir Senha</title>
  <link rel="stylesheet" href="mudar.css" />
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap"
    rel="stylesheet"
  />
</head>
<body>
  <div class="logo">FOAG</div>

  <div class="login-page">
    <div class="left-section">
      <img
        src="../img/livro.png"
        alt="Inspirational image"
        style="width: 100%; height: 100%; object-fit: cover"
      />
    </div>

    <div class="right-section">
      <h1>Redefinir Senha</h1>
      <form method="POST" action="processa_redefinir.php" id="form-redefinir">
  <label for="email">E-mail:</label><br />
  <input type="email" id="email" name="email" placeholder="fulano@ciclano.com" required autocomplete="off" /><br /><br />
  
  <div class="password-wrapper">
    <label for="nova_senha">Nova senha:</label><br />
    <input
      type="password"
      id="nova_senha"
      name="nova_senha"
      placeholder="********"
      required
    />
    <span class="toggle-visibility" data-target="nova_senha">🙈</span>
  </div>
  <br />

  <div class="password-wrapper">
    <label for="confirmar_senha">Confirmar senha:</label><br />
    <input
      type="password"
      id="confirmar_senha"
      name="confirmar_senha"
      placeholder="********"
      required
    />
    <span class="toggle-visibility" data-target="confirmar_senha">🙈</span>
  </div>

  <!-- restante do formulário -->
  <button type="submit">Redefinir Senha</button>
</form>

<script>
    const novaSenha = document.getElementById("nova_senha");
    const confirmarSenha = document.getElementById("confirmar_senha");
    const form = document.getElementById("form-redefinir");
    const balao = document.getElementById("requisitos-senha");
    const reqTamanho = document.getElementById("req-tamanho");
    const reqMaiuscula = document.getElementById("req-maiuscula");
    const reqNumero = document.getElementById("req-numero");
    const reqSimbolo = document.getElementById("req-simbolo");

    // Validação em tempo real dos requisitos da senha
    novaSenha.addEventListener("input", () => {
      const valor = novaSenha.value;
      if (valor.length === 0) {
        balao.style.display = "none";
        return;
      }
      balao.style.display = "block";
      reqTamanho.style.color = valor.length >= 8 ? "green" : "red";
      reqMaiuscula.style.color = /[A-Z]/.test(valor) ? "green" : "red";
      reqNumero.style.color = /\d/.test(valor) ? "green" : "red";
      reqSimbolo.style.color = /[!@#$%^&*()\-_=+{};:,<.>]/.test(valor) ? "green" : "red";
    });

    // Esconde os requisitos quando perde o foco
    novaSenha.addEventListener("blur", () => {
      balao.style.display = "none";
    });

    // Alternar visibilidade da senha
    document.querySelectorAll(".toggle-visibility").forEach((icon) => {
      icon.addEventListener("click", () => {
        const targetId = icon.getAttribute("data-target");
        const input = document.getElementById(targetId);
        const isPassword = input.type === "password";
        input.type = isPassword ? "text" : "password";
        icon.textContent = isPassword ? "🙉" : "🙈";
      });
    });

    // Validação ao enviar o formulário
    form.addEventListener("submit", (e) => {
      const senhaVal = novaSenha.value;
      const regexSenha = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/;

      if (!regexSenha.test(senhaVal)) {
        e.preventDefault();
        alert(
          "A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, um número e um símbolo especial."
        );
        novaSenha.focus();
        return;
      }

      if (senhaVal !== confirmarSenha.value) {
        e.preventDefault();
        alert("As senhas não coincidem. Por favor, verifique.");
        confirmarSenha.focus();
        return;
      }
    });
  </script>
</body>
</html>

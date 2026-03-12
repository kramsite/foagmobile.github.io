document.getElementById("loginForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Evita o envio automático do formulário

    // Obtenha os valores dos campos de e-mail e senha
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    // Verifique se algum dos campos está vazio
    if (email === "" || password === "") {
      alert("Por favor, preencha todos os campos.");
      return;
    }

    // Desabilitar o botão enquanto o formulário é processado
    const submitButton = document.querySelector("button[type='submit']");
    submitButton.disabled = true;

    // Simulação de tempo de resposta (substitua com sua lógica real)
    setTimeout(() => {  // Simulando tempo de resposta do servidor
      window.location.href = "../inicio/inicio.php"; // Redireciona para o calendário
      submitButton.disabled = false; // Habilitar o botão novamente
    }, 1000);  // Simula um delay de 1 segundo
  });

  // Função para alternar a visibilidade da senha
  document.getElementById("togglePassword").addEventListener("click", function() {
    const passwordField = document.getElementById("password");
    const passwordFieldType = passwordField.type === "password" ? "text" : "password";
    passwordField.type = passwordFieldType;

    // Mudar o texto do botão conforme o estado
    this.textContent = passwordFieldType === "password" ? "Mostrar Senha" : "Ocultar Senha";
  });
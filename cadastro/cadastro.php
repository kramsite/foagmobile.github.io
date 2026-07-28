<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro de Usuário</title>
  <link rel="stylesheet" href="estilocads.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="logo">FOAG</div>
  <div class="background"></div>

  <div class="form-container">
    <h2>Cadastro de Usuário</h2>
    <form id="form-cadastro" method="POST" action="processa_cadastro.php">
      
      <div id="step-1">
        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" placeholder="fulano" required>

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" placeholder="fulano@ciclano.com" required>

        <label for="data_nascimento">Nascimento</label>
        <input type="date" id="data_nascimento" name="data_nascimento" required style="margin-bottom: 15px;">
        
        <button type="button" id="btn-proximo" class="mobile-only">Próximo</button>
      </div>

      <div id="step-2">
        <div class="form-row">
          <div class="password-wrapper">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" placeholder="********" required>
            <span class="toggle-visibility" data-target="senha">🙈</span>
          </div>

          <div class="password-wrapper">
            <label for="confirmar_senha">Confirmar senha</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="********" required>
            <span class="toggle-visibility" data-target="confirmar_senha">🙈</span>
          </div>
        </div>

        <label class="termos">
          <input type="checkbox" name="termos" required>

          <span>
            Aceito os <a href="termos.php">termos de uso</a>
          </span>
        </label>

        <div class="btn-group">
          <button type="button" id="btn-voltar" class="btn-secondary mobile-only">Voltar</button>
          <button type="submit">Cadastrar</button>
        </div>
      </div>
    </form>
  </div>

  <script>
    // Seleção de elementos
    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const btnProximo = document.getElementById('btn-proximo');
    const btnVoltar = document.getElementById('btn-voltar');
    const form = document.getElementById('form-cadastro');

    // 1. FUNÇÃO PARA CONTROLAR O LAYOUT
    function aplicarLayout() {
      if (window.innerWidth <= 768) {
        // Se for celular: esconde a etapa 2 e mostra a etapa 1
        step2.classList.add('hidden-mobile');
        step1.classList.remove('hidden-mobile');
      } else {
        // Se for computador: mostra as duas etapas ao mesmo tempo
        step1.classList.remove('hidden-mobile');
        step2.classList.remove('hidden-mobile');
      }
    }

    // Executa ao carregar e ao girar/redimensionar a tela
    window.addEventListener('load', aplicarLayout);
    window.addEventListener('resize', aplicarLayout);

    // 2. NAVEGAÇÃO NO MOBILE
    btnProximo.addEventListener('click', () => {
      const nome = document.getElementById('nome').value;
      const email = document.getElementById('email').value;
      const data = document.getElementById('data_nascimento').value;

      if (nome && email && data) {
        step1.classList.add('hidden-mobile');
        step2.classList.remove('hidden-mobile');
      } else {
        alert("Por favor, preencha todos os campos antes de continuar.");
      }
    });

    btnVoltar.addEventListener('click', () => {
      step2.classList.add('hidden-mobile');
      step1.classList.remove('hidden-mobile');
    });

    // 3. VISIBILIDADE DA SENHA (MACACÃO)
    document.querySelectorAll('.toggle-visibility').forEach(icon => {
      icon.addEventListener('click', () => {
        const input = document.getElementById(icon.getAttribute('data-target'));
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.textContent = isPassword ? '🙉' : '🙈';
      });
    });

    // 4. VALIDAÇÃO ANTES DE ENVIAR O FORMULÁRIO
    form.addEventListener('submit', function(event) {
      const senha = document.getElementById('senha').value;
      const confirmar = document.getElementById('confirmar_senha').value;
      
      // Regra: 8 caracteres, uma maiúscula, um número e um símbolo
      const regexSenha = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/;

      if (!regexSenha.test(senha)) {
        event.preventDefault();
        alert('A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, um número e um símbolo especial.');
        return;
      }

      if (senha !== confirmar) {
        event.preventDefault();
        alert('As senhas não coincidem. Verifique novamente.');
      }
    });
  </script>
</body>
</html>
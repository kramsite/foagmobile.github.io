document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  const emailInput = document.getElementById('email');
  const senhaInput = document.getElementById('senha');
  const botaoEntrar = form?.querySelector('button[type="submit"]');

  // Mostrar / ocultar senha
  document.querySelectorAll('.toggle-visibility').forEach(icon => {
    icon.addEventListener('click', () => {
      const targetId = icon.getAttribute('data-target');
      const input = document.getElementById(targetId);

      if (!input) return;

      const mostrandoSenha = input.type === 'text';

      input.type = mostrandoSenha ? 'password' : 'text';
      icon.textContent = mostrandoSenha ? '🙈' : '🙉';
    });
  });

  // Validação básica antes de enviar
  if (form) {
    form.addEventListener('submit', event => {
      const email = emailInput?.value.trim() || '';
      const senha = senhaInput?.value || '';

      if (email === '' || senha === '') {
        event.preventDefault();
        alert('Por favor, preencha o e-mail e a senha.');
        return;
      }

      if (!emailValido(email)) {
        event.preventDefault();
        alert('Digite um e-mail válido.');
        emailInput.focus();
        return;
      }

      // Não usamos preventDefault aqui.
      // O formulário será enviado normalmente para processa_login.php.

      if (botaoEntrar) {
        botaoEntrar.disabled = true;
        botaoEntrar.textContent = 'Entrando...';
      }
    });
  }

  function emailValido(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }
});
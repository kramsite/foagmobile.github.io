<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FOAG – Contato</title>

  <!-- CSS da página -->
  <link rel="stylesheet" href="contato.css" />
  <link rel="stylesheet" href="dark_agenda.css">

  <!-- Fontes / Ícones -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <!-- Dark mode (já existe no seu projeto) -->
  <script src="../m.escuro/dark-mode.js" defer></script>
</head>
<body>

  <!-- CABEÇALHO FOAG -->
  <header class="cabecalho">
    <div class="logo-foag">FOAG</div>

    <div class="header-actions">
      <button class="icon-btn" onclick="window.location.href='../inicio.php'" title="Início">
        <i class="fa-solid fa-house"></i>
      </button>
      <button class="icon-btn" onclick="window.history.back()" title="Voltar">
        <i class="fa-solid fa-arrow-left"></i>
      </button>
      <!-- Se seu dark-mode usa outra função, troca aqui -->
      <button class="icon-btn" id="toggle-dark" title="Tema claro/escuro">
        <i class="fa-solid fa-moon"></i>
      </button>
    </div>
  </header>

  <!-- CONTEÚDO PRINCIPAL -->
  <main class="contato-main">
    <section class="contato-card">
      <!-- LADO ESQUERDO: INFO / CONTATOS -->
      <div class="contato-info">
        <h2 class="titulo">📞 Fale com o FOAG</h2>
        <p class="sub">
          Dúvidas, sugestões ou bug esquisito? Manda pra gente.  
          A equipe do FOAG responde o quanto antes.
        </p>

        <div class="bloco">
          <h3>📧 E-mail</h3>
          <ul class="lista-contato">
            <li><strong>Suporte geral:</strong> suporte@foag.com</li>
            <li><strong>Suporte técnico:</strong> tecnico@foag.com</li>
            <li><strong>Sugestões e feedback:</strong> feedback@foag.com</li>
            <li><strong>Parcerias:</strong> parcerias@foag.com</li>
          </ul>
        </div>

        <div class="bloco">
          <h3>💬 Atendimento rápido</h3>
          <p class="texto-menor">
            Precisa de algo agora? Chama no Whats:
          </p>
          <a href="https://wa.me/5599999999999" target="_blank" class="botao-whatsapp">
            <i class="fa-brands fa-whatsapp"></i>
            Abrir WhatsApp
          </a>
        </div>

        <div class="bloco">
          <h3>🌐 Redes sociais</h3>
          <ul class="redes-lista">
            <li><a href="#"><i class="fa-brands fa-instagram"></i> Instagram</a></li>
            <li><a href="#"><i class="fa-brands fa-tiktok"></i> TikTok</a></li>
            <li><a href="#"><i class="fa-brands fa-youtube"></i> YouTube</a></li>
            <li><a href="#"><i class="fa-brands fa-linkedin"></i> LinkedIn</a></li>
          </ul>
        </div>
      </div>

      <!-- LADO DIREITO: FORMULÁRIO -->
      <div class="contato-form-wrapper">
        <h3>📝 Formulário de contato</h3>
        <p class="texto-menor">
          Preenche aqui que a gente responde no seu e-mail.
        </p>

        <form action="#" method="post" class="form-contato">
          <div class="campo">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" required placeholder="Seu nome completo">
          </div>

          <div class="campo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required placeholder="nome@exemplo.com">
          </div>

          <div class="campo">
            <label for="mensagem">Mensagem</label>
            <textarea id="mensagem" name="mensagem" rows="4" required placeholder="Nos conte como podemos ajudar"></textarea>
          </div>

          <button type="submit" class="btn-enviar">
            Enviar mensagem
          </button>
        </form>
      </div>
    </section>
  </main>

  <footer class="rodape">
    FOAG — foco, organização e boas notas.
  </footer>

  <!-- Se o dark-mode.js precisa de um ID específico, você ajusta aqui -->
  <script>
    const toggle = document.getElementById('toggle-dark');
    if (toggle && typeof toggleDarkMode === 'function') {
      toggle.addEventListener('click', toggleDarkMode);
    }
  </script>
</body>
</html>

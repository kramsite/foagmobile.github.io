<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro de Usuário</title>
  <link rel="stylesheet" href="estilocads.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
</head>
<body>
  <div class="logo">FOAG</div>
  <div class="background"></div>

  <div class="form-container">
    <h2>Cadastro de Usuário</h2>
    <form method="POST" action="processa_cadastro.php">
      <label for="nome">Nome</label>
      <input type="text" id="nome" name="nome" placeholder="fulano"required autocomplete="off">

      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" placeholder="fulano@ciclano.com" required autocomplete="off">

      <div class="form-row">
  <div class="password-wrapper">
    <label for="senha">Senha</label>
    <input type="password" id="senha" name="senha"  placeholder="********" required autocomplete="new-password">
    <span class="toggle-visibility" data-target="senha">🙈</span>
    <div id="requisitos-senha" class="tooltip-hidden">
      <p>Requisitos da senha:</p>
      <ul>
        <li id="req-tamanho" class="invalid">Mínimo 8 caracteres</li>
        <li id="req-maiuscula" class="invalid">Uma letra maiúscula</li>
        <li id="req-numero" class="invalid">Um número</li>
        <li id="req-simbolo" class="invalid">Um símbolo especial (!@#$...)</li>
      </ul>
    </div>
  </div>

  <div class="password-wrapper">
    <label for="confirmar_senha">Confirmar senha</label>
    <input type="password" id="confirmar_senha" name="confirmar_senha"  placeholder="********" required autocomplete="new-password">
    <span class="toggle-visibility" data-target="confirmar_senha">🙈</span>
  </div>
</div>



      <div class="form-row">
        <div>
          <label for="data_nascimento">Nascimento</label>
          <input type="date" id="data_nascimento" name="data_nascimento" required autocomplete="off">
        </div>
        <div>
          <label for="telefone">Telefone</label>
          <input type="tel" id="telefone" name="telefone" placeholder="(00) 0000-0000" required autocomplete="off">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="serie">Série/Curso</label>
          <select id="serie" name="serie" required>
            <option value="">Carregando...</option>
          </select>
        </div>
        <div class="form-group">
          <label for="escola">Escola/Faculdade</label>
          <select id="escola" name="escola" required>
            <option value="">Carregando escolas...</option>
          </select>
        </div>
      </div>

      <label class="termos">
        <input type="checkbox" name="termos" required>
        Aceito os <a href="#">termos de uso</a> e a política de privacidade.
      </label>

      <button type="submit">Cadastrar</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
  // Choices para selects
  const selectEscola = document.getElementById('escola');
  const selectSerie  = document.getElementById('serie');

  const choicesEscola = new Choices(selectEscola, {
    searchEnabled: true,
    itemSelectText: '',
    shouldSort: false,
    placeholderValue: 'Digite para buscar...'
  });

  const choicesSerie = new Choices(selectSerie, {
    searchEnabled: true,
    itemSelectText: '',
    shouldSort: false,
    placeholderValue: 'Digite para buscar...'
  });

  async function carregarJSON(url) {
    const res = await fetch(url, { cache: 'no-store' });
    if (!res.ok) throw new Error(`Erro ao buscar ${url}: ${res.status}`);
    return res.json();
  }

  function popularChoices(choicesInstance, dados) {
    // dados é um array de strings -> convertemos para {value,label}
    const opcoes = (dados || []).map(nome => ({ value: nome, label: nome }));
    // Limpa e coloca um placeholder selecionado
    choicesInstance.clearStore();
    choicesInstance.setChoices(
      [{ value: '', label: 'Selecione...', selected: true, disabled: true }],
      'value',
      'label',
      true // <- substitui as opções existentes
    );
    // Adiciona as opções do JSON
    choicesInstance.setChoices(opcoes, 'value', 'label', false);
  }

  (async function init() {
    try {
      // Ajuste os caminhos RELATIVOS corretos:
      const [escolas, series] = await Promise.all([
        carregarJSON('../json/escolas.json'),
        carregarJSON('../json/series.json')
      ]);

      // (Opcional) Remover duplicatas de escolas, só para garantir
      const uniq = arr => [...new Set(arr)];
      popularChoices(choicesEscola, uniq(escolas));
      popularChoices(choicesSerie,  uniq(series));

      // Log para depurar
      console.log('Escolas carregadas:', escolas.length);
      console.log('Séries carregadas:', series.length);
    } catch (err) {
      console.error(err);
      // Fallback visual caso dê erro
      choicesEscola.clearStore();
      choicesEscola.setChoices([{ value:'', label:'Erro ao carregar escolas', selected:true, disabled:true }], 'value', 'label', true);
      choicesSerie.clearStore();
      choicesSerie.setChoices([{ value:'', label:'Erro ao carregar séries', selected:true, disabled:true }], 'value', 'label', true);
    }
  })();

    // Validação senha + confirmação
    const form = document.querySelector('form');
    const senha = document.getElementById('senha');
    const confirmarSenha = document.getElementById('confirmar_senha');

    form.addEventListener('submit', function(event) {
      const senhaVal = senha.value;
      const regexSenha = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/;

      if (!regexSenha.test(senhaVal)) {
        event.preventDefault();
        alert('A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, um número e um símbolo especial.');
        senha.focus();
        return;
      }

      if (senhaVal !== confirmarSenha.value) {
        event.preventDefault();
        alert('As senhas não coincidem. Por favor, verifique.');
        confirmarSenha.focus();
        return;
      }
    });

    // Alternar visibilidade das senhas com animação
document.querySelectorAll('.toggle-visibility').forEach(icon => {
  icon.addEventListener('click', () => {
    const targetId = icon.getAttribute('data-target');
    const input = document.getElementById(targetId);

    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';

    // Troca o ícone e aplica animação
   icon.textContent = isPassword ? '🙉' : '🙈';
    icon.classList.toggle('active');
  });
});


    // Balão de requisitos de senha
    const balao = document.getElementById('requisitos-senha');
    const reqTamanho = document.getElementById('req-tamanho');
    const reqMaiuscula = document.getElementById('req-maiuscula');
    const reqNumero = document.getElementById('req-numero');
    const reqSimbolo = document.getElementById('req-simbolo');

    senha.addEventListener('input', () => {
      const valor = senha.value;
      if (valor.length === 0) {
        balao.classList.add('tooltip-hidden');
        return;
      }
      balao.classList.remove('tooltip-hidden');
      reqTamanho.classList.toggle('valid', valor.length >= 8);
      reqTamanho.classList.toggle('invalid', valor.length < 8);
      reqMaiuscula.classList.toggle('valid', /[A-Z]/.test(valor));
      reqMaiuscula.classList.toggle('invalid', !/[A-Z]/.test(valor));
      reqNumero.classList.toggle('valid', /\d/.test(valor));
      reqNumero.classList.toggle('invalid', !/\d/.test(valor));
      reqSimbolo.classList.toggle('valid', /[!@#$%^&*()\-_=+{};:,<.>]/.test(valor));
      reqSimbolo.classList.toggle('invalid', !/[!@#$%^&*()\-_=+{};:,<.>]/.test(valor));
    });

    senha.addEventListener('blur', () => balao.classList.add('tooltip-hidden'));

    // Máscara telefone
    const telefoneInput = document.getElementById('telefone');
    telefoneInput.addEventListener('input', function(e) {
      let x = e.target.value.replace(/\D/g, '');
      if (x.length > 11) x = x.slice(0, 11);
      if (x.length > 6) {
        if (x.length === 11) {
          e.target.value = `(${x.slice(0,2)}) ${x.slice(2,7)}-${x.slice(7)}`;
        } else {
          e.target.value = `(${x.slice(0,2)}) ${x.slice(2,6)}-${x.slice(6)}`;
        }
      } else if (x.length > 2) {
        e.target.value = `(${x.slice(0,2)}) ${x.slice(2)}`;
      } else if (x.length > 0) {
        e.target.value = `(${x}`;
      }
    });
    

    
  </script>
</body>
</html>

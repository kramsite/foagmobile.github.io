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
<div class="data-wrapper">
  <div class="data-input-group">
    <input 
      type="text" 
      id="data_dia" 
      placeholder="DD" 
      maxlength="2"
      class="data-parte"
      required
    >
    <span class="data-separador">/</span>
    <input 
      type="text" 
      id="data_mes" 
      placeholder="MM" 
      maxlength="2"
      class="data-parte"
      required
    >
    <span class="data-separador">/</span>
    <input 
      type="text" 
      id="data_ano" 
      placeholder="AAAA" 
      maxlength="4"
      class="data-parte"
      required
    >
  </div>
  <input type="hidden" id="data_nascimento" name="data_nascimento">
  <span class="data-erro" id="data-erro" style="display: none; color: #dc3545; font-size: 0.8rem; margin-top: 5px;">
    ⚠️ Data inválida! Use DD/MM/AAAA entre 1930 e 2026
  </span>
</div>
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
  <div vw class="enabled">
  <div vw-access-button class="active"></div>
  <div vw-plugin-wrapper>
    <div class="vw-plugin-top-wrapper"></div>
  </div>
</div>

<script src="https://vlibras.gov.br/app.js"></script>
<script>
  new window.VLibras.Widget('https://vlibras.gov.br/app');
</script>
  
<script>
// ============================================================
// 0. VALIDAÇÃO DA DATA DE NASCIMENTO (COMPLETA)
// ============================================================
const diaInput = document.getElementById('data_dia');
const mesInput = document.getElementById('data_mes');
const anoInput = document.getElementById('data_ano');
const dataHidden = document.getElementById('data_nascimento');
const erroData = document.getElementById('data-erro');

const anoAtual = new Date().getFullYear();

// Função para validar data completa
function validarDataCompleta(dia, mes, ano) {
    // Converte para números
    const d = parseInt(dia);
    const m = parseInt(mes);
    const a = parseInt(ano);
    
    // Verifica se são números válidos
    if (isNaN(d) || isNaN(m) || isNaN(a)) {
        return false;
    }
    
    // Valida ano (1930 a ano atual)
    if (a < 1930 || a > anoAtual) {
        return false;
    }
    
    // Valida mês (1 a 12)
    if (m < 1 || m > 12) {
        return false;
    }
    
    // Valida dia baseado no mês
    const diasPorMes = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    
    // Verifica ano bissexto
    let diasNoMes = diasPorMes[m - 1];
    if (m === 2 && (a % 400 === 0 || (a % 4 === 0 && a % 100 !== 0))) {
        diasNoMes = 29;
    }
    
    if (d < 1 || d > diasNoMes) {
        return false;
    }
    
    return true;
}

// Função para mostrar/ocultar erro
function mostrarErro(mostrar) {
    const inputs = [diaInput, mesInput, anoInput];
    
    if (mostrar) {
        erroData.style.display = 'block';
        erroData.classList.add('visivel');
        inputs.forEach(input => {
            input.classList.add('invalido');
            input.classList.remove('valido');
        });
    } else {
        erroData.style.display = 'none';
        erroData.classList.remove('visivel');
        inputs.forEach(input => {
            input.classList.remove('invalido');
            input.classList.remove('valido');
        });
    }
}

// Função para validar e atualizar campo hidden
function validarEAtualizar() {
    const dia = diaInput.value.trim();
    const mes = mesInput.value.trim();
    const ano = anoInput.value.trim();
    
    // Se algum campo estiver vazio, não valida
    if (!dia || !mes || !ano) {
        dataHidden.value = '';
        mostrarErro(false);
        return false;
    }
    
    // Valida a data completa
    if (validarDataCompleta(dia, mes, ano)) {
        // Formata a data para YYYY-MM-DD
        const d = dia.padStart(2, '0');
        const m = mes.padStart(2, '0');
        const a = ano;
        const dataFormatada = `${a}-${m}-${d}`;
        
        dataHidden.value = dataFormatada;
        mostrarErro(false);
        
        // Marca como válido
        [diaInput, mesInput, anoInput].forEach(input => {
            input.classList.remove('invalido');
            input.classList.add('valido');
        });
        
        return true;
    } else {
        dataHidden.value = '';
        mostrarErro(true);
        return false;
    }
}

// ============================================================
// EVENTOS PARA CADA CAMPO
// ============================================================

// 1. Ao digitar - apenas permite números e navegação automática
function configurarCampoData(input, proximo, maxLength) {
    input.addEventListener('input', function() {
        // Remove qualquer caractere que não seja número
        this.value = this.value.replace(/\D/g, '');
        
        // Se atingiu o tamanho máximo e tem próximo campo, vai para ele
        if (this.value.length === maxLength && proximo) {
            proximo.focus();
        }
        
        // Se está vazio ou incompleto, limpa a validação
        if (this.value.length < maxLength) {
            this.classList.remove('valido', 'invalido');
            dataHidden.value = '';
            mostrarErro(false);
        }
        
        // Valida apenas quando todos os campos estão preenchidos
        if (diaInput.value.length === 2 && mesInput.value.length === 2 && anoInput.value.length === 4) {
            validarEAtualizar();
        }
    });
    
    // Ao perder o foco, valida
    input.addEventListener('blur', function() {
        // Se o campo está vazio, não faz nada
        if (!this.value) {
            return;
        }
        
        // Se todos os campos estão preenchidos, valida
        if (diaInput.value.length === 2 && mesInput.value.length === 2 && anoInput.value.length === 4) {
            validarEAtualizar();
        }
    });
    
    // Ao pressionar Enter, valida e avança
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (diaInput.value.length === 2 && mesInput.value.length === 2 && anoInput.value.length === 4) {
                validarEAtualizar();
            }
            // Avança para o próximo campo ou envia
            if (proximo) {
                proximo.focus();
            } else {
                // Se for o último campo, tenta enviar o formulário
                const form = document.getElementById('form-cadastro');
                if (form) {
                    form.dispatchEvent(new Event('submit'));
                }
            }
        }
    });
    
    // Ao colar, filtra apenas números
    input.addEventListener('paste', function(e) {
        e.preventDefault();
        const texto = (e.clipboardData || window.clipboardData).getData('text');
        const numeros = texto.replace(/\D/g, '');
        this.value = numeros.slice(0, maxLength);
        
        // Se atingiu o tamanho máximo e tem próximo campo, vai para ele
        if (this.value.length === maxLength && proximo) {
            proximo.focus();
        }
    });
}

// Configura cada campo
configurarCampoData(diaInput, mesInput, 2);
configurarCampoData(mesInput, anoInput, 2);
configurarCampoData(anoInput, null, 4);

// ============================================================
// FUNÇÃO PARA VALIDAR ANTES DE AVANÇAR
// ============================================================
function validarDataAntesAvancar() {
    const dia = diaInput.value.trim();
    const mes = mesInput.value.trim();
    const ano = anoInput.value.trim();
    
    // Verifica se todos os campos estão preenchidos
    if (!dia || !mes || !ano) {
        alert('Por favor, preencha todos os campos da data de nascimento.');
        return false;
    }
    
    // Valida a data
    if (validarDataCompleta(dia, mes, ano)) {
        return true;
    } else {
        alert(`⚠️ Data inválida! Use o formato DD/MM/AAAA entre 01/01/1930 e 31/12/${anoAtual}.`);
        mostrarErro(true);
        return false;
    }
}

// ============================================================
// 1. FUNÇÃO PARA CONTROLAR O LAYOUT
// ============================================================
function aplicarLayout() {
    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    
    if (window.innerWidth <= 768) {
        step2.classList.add('hidden-mobile');
        step1.classList.remove('hidden-mobile');
    } else {
        step1.classList.remove('hidden-mobile');
        step2.classList.remove('hidden-mobile');
    }
}

// ============================================================
// 2. SELEÇÃO DE ELEMENTOS
// ============================================================
const step1 = document.getElementById('step-1');
const step2 = document.getElementById('step-2');
const btnProximo = document.getElementById('btn-proximo');
const btnVoltar = document.getElementById('btn-voltar');
const form = document.getElementById('form-cadastro');

// ============================================================
// 3. EXECUTA AO CARREGAR E AO REDIMENSIONAR
// ============================================================
window.addEventListener('load', aplicarLayout);
window.addEventListener('resize', aplicarLayout);

// ============================================================
// 4. NAVEGAÇÃO NO MOBILE
// ============================================================
btnProximo.addEventListener('click', () => {
    const nome = document.getElementById('nome').value;
    const email = document.getElementById('email').value;
    
    if (nome && email) {
        // Valida a data antes de prosseguir
        if (!validarDataAntesAvancar()) {
            return;
        }
        
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

// ============================================================
// 5. VISIBILIDADE DA SENHA (MACACÃO)
// ============================================================
document.querySelectorAll('.toggle-visibility').forEach(icon => {
    icon.addEventListener('click', () => {
        const input = document.getElementById(icon.getAttribute('data-target'));
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.textContent = isPassword ? '🙉' : '🙈';
    });
});

// ============================================================
// 6. VALIDAÇÃO ANTES DE ENVIAR O FORMULÁRIO
// ============================================================
form.addEventListener('submit', function(event) {
    const senha = document.getElementById('senha').value;
    const confirmar = document.getElementById('confirmar_senha').value;
    
    // Valida a data de nascimento
    if (!validarDataAntesAvancar()) {
        event.preventDefault();
        return;
    }
    
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

// ============================================================
// 7. VALIDAÇÃO INICIAL DA DATA (ao carregar)
// ============================================================
// Limpa os campos ao carregar a página
window.addEventListener('load', function() {
    diaInput.value = '';
    mesInput.value = '';
    anoInput.value = '';
    dataHidden.value = '';
    mostrarErro(false);
});
</script>
</body>
</html>
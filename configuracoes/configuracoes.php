<?php
session_start();

$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configurações</title>
  <link rel="stylesheet" href="configuracoes.css">
  <link rel="stylesheet" href="../m.escuro/dark_basee.css">
  <link rel="stylesheet" href="dark_configuracoes.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="../m.escuro/dark-mode.js"></script>
</head>

<body>
  <header class="cabecalho">
    FOAG

    <div class="header-icons">
      <i id="themeToggle" class="fa-solid fa-moon" title="Modo escuro"></i>
      <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
      <i id="icon-fogi" class="fa-solid fa-robot" title="Assistente FOAG — FOGi"></i>
      <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
    </div>
  </header>

  <div class="container">
    <nav class="menu">
      <a href="../inicioo/inicio.php" class="<?= $current === 'inicio.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-house"></i>
        Início
      </a>

      <a href="../calend/calendario.php" class="<?= $current === 'calendario.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-calendar-days"></i>
        Calendário
      </a>

      <a href="../bloco/agenda.php" class="<?= $current === 'agenda.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-book"></i>
        Agenda
      </a>

      <a href="../pomodoro/pomodoro.php" class="<?= $current === 'pomodoro.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-stopwatch"></i>
        Pomodoro
      </a>

      <a href="../notas/notas.php" class="<?= $current === 'notas.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-check-double"></i>
        Boletim
      </a>

      <a href="../loja/loja.php" class="<?= $current === 'loja.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-store"></i>
        Loja
      </a>

      <a href="../rank/rank.php" class="<?= $current === 'rank.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-trophy"></i>
        Ranking
      </a>

      <a href="../configuracoes/configuracoes.php" class="<?= $current === 'configuracoes.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-gear"></i>
        Configurações
      </a>
    </nav>

    <main class="conteudo configuracoes-conteudo">
      <div class="configuracoes-pagina">
        <div class="configuracoes-topo">
          <div>
            <h1>Configurações</h1>
            <p>Personalize o funcionamento e a aparência do FOAG.</p>
          </div>

          <button type="submit" form="form-configuracoes" class="btn-salvar-topo">
            <i class="fa-solid fa-floppy-disk"></i>
            Salvar
          </button>
        </div>

        <form id="form-configuracoes">
          <section class="configuracao-card" id="geral">
            <div class="card-cabecalho">
              <div class="card-icone">
                <i class="fa-solid fa-sliders"></i>
              </div>

              <div>
                <h2>Geral</h2>
                <p>Configurações utilizadas em diferentes páginas do sistema.</p>
              </div>
            </div>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <label for="formato-data">Formato de data</label>
                <span>Escolha como as datas serão exibidas.</span>
              </div>

              <select id="formato-data" name="formato_data">
                <option value="dd/mm/aaaa">DD/MM/AAAA</option>
                <option value="mm/dd/aaaa">MM/DD/AAAA</option>
                <option value="aaaa-mm-dd">AAAA-MM-DD</option>
              </select>
            </div>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <label for="formato-horario">Formato de horário</label>
                <span>Escolha entre o formato de 12 ou 24 horas.</span>
              </div>

              <select id="formato-horario" name="formato_horario">
                <option value="24">24 horas — 18:30</option>
                <option value="12">12 horas — 06:30 PM</option>
              </select>
            </div>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <label for="inicio-semana">Início da semana</label>
                <span>Defina o primeiro dia mostrado no calendário.</span>
              </div>

              <select id="inicio-semana" name="inicio_semana">
                <option value="domingo">Domingo</option>
                <option value="segunda">Segunda-feira</option>
              </select>
            </div>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <label for="pagina-inicial">Página inicial padrão</label>
                <span>Escolha a página aberta depois do login.</span>
              </div>

              <select id="pagina-inicial" name="pagina_inicial">
                <option value="inicio">Início</option>
                <option value="calendario">Calendário</option>
                <option value="agenda">Agenda</option>
                <option value="pomodoro">Pomodoro</option>
                <option value="boletim">Boletim</option>
              </select>
            </div>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <label for="visualizacao-calendario">Visualização do calendário</label>
                <span>Escolha como o calendário será aberto.</span>
              </div>

              <select id="visualizacao-calendario" name="visualizacao_calendario">
                <option value="mes">Mês</option>
                <option value="semana">Semana</option>
                <option value="dia">Dia</option>
              </select>
            </div>

            <label class="configuracao-item configuracao-switch">
              <span class="configuracao-texto">
                <strong>Mostrar atividades concluídas</strong>
                <small>Exibe atividades finalizadas na agenda.</small>
              </span>

              <span class="switch">
                <input type="checkbox" name="mostrar_concluidas" checked>
                <span class="slider"></span>
              </span>
            </label>

            <label class="configuracao-item configuracao-switch">
              <span class="configuracao-texto">
                <strong>Confirmar antes de excluir</strong>
                <small>Pede confirmação antes de apagar informações.</small>
              </span>

              <span class="switch">
                <input type="checkbox" name="confirmar_exclusao" checked>
                <span class="slider"></span>
              </span>
            </label>
          </section>

          <section class="configuracao-card" id="aparencia">
            <div class="card-cabecalho">
              <div class="card-icone">
                <i class="fa-solid fa-palette"></i>
              </div>

              <div>
                <h2>Aparência</h2>
                <p>Personalize a visualização do sistema.</p>
              </div>
            </div>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <label for="tema">Tema</label>
                <span>Escolha a aparência principal do FOAG.</span>
              </div>

              <select id="tema" name="tema">
                <option value="claro">Claro</option>
                <option value="escuro">Escuro</option>
                <option value="sistema">Tema do dispositivo</option>
              </select>
            </div>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <label for="tamanho-fonte">Tamanho da fonte</label>
                <span>Altere o tamanho dos textos do sistema.</span>
              </div>

              <select id="tamanho-fonte" name="tamanho_fonte">
                <option value="pequena">Pequena</option>
                <option value="media" selected>Média</option>
                <option value="grande">Grande</option>
              </select>
            </div>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <span class="titulo-opcao">Cor principal</span>
                <span>Escolha a cor dos botões e elementos de destaque.</span>
              </div>

              <div class="opcoes-cores">
                <label class="cor cor-azul" title="Azul">
                  <input type="radio" name="cor_principal" value="azul" checked>
                  <span></span>
                </label>

                <label class="cor cor-roxa" title="Roxo">
                  <input type="radio" name="cor_principal" value="roxo">
                  <span></span>
                </label>

                <label class="cor cor-verde" title="Verde">
                  <input type="radio" name="cor_principal" value="verde">
                  <span></span>
                </label>

                <label class="cor cor-rosa" title="Rosa">
                  <input type="radio" name="cor_principal" value="rosa">
                  <span></span>
                </label>
              </div>
            </div>

            <label class="configuracao-item configuracao-switch">
              <span class="configuracao-texto">
                <strong>Modo compacto</strong>
                <small>Reduz os espaços entre os elementos.</small>
              </span>

              <span class="switch">
                <input type="checkbox" name="modo_compacto">
                <span class="slider"></span>
              </span>
            </label>

            <label class="configuracao-item configuracao-switch">
              <span class="configuracao-texto">
                <strong>Reduzir animações</strong>
                <small>Diminui movimentos e transições nas páginas.</small>
              </span>

              <span class="switch">
                <input type="checkbox" name="reduzir_animacoes">
                <span class="slider"></span>
              </span>
            </label>
          </section>

          <section class="configuracao-card" id="notificacoes">
            <div class="card-cabecalho">
              <div class="card-icone">
                <i class="fa-solid fa-bell"></i>
              </div>

              <div>
                <h2>Notificações</h2>
                <p>Escolha os lembretes que deseja receber.</p>
              </div>
            </div>

            <label class="configuracao-item configuracao-switch">
              <span class="configuracao-texto">
                <strong>Notificações do navegador</strong>
                <small>Permite que o FOAG envie avisos no dispositivo.</small>
              </span>

              <span class="switch">
                <input type="checkbox" id="notificacoes-navegador" name="notificacoes_navegador">
                <span class="slider"></span>
              </span>
            </label>

            <label class="configuracao-item configuracao-switch">
              <span class="configuracao-texto">
                <strong>Lembretes de atividades</strong>
                <small>Receba avisos sobre atividades próximas.</small>
              </span>

              <span class="switch">
                <input type="checkbox" name="lembrete_atividades" checked>
                <span class="slider"></span>
              </span>
            </label>

            <label class="configuracao-item configuracao-switch">
              <span class="configuracao-texto">
                <strong>Lembretes de provas</strong>
                <small>Receba avisos antes das datas de provas.</small>
              </span>

              <span class="switch">
                <input type="checkbox" name="lembrete_provas" checked>
                <span class="slider"></span>
              </span>
            </label>

            <label class="configuracao-item configuracao-switch">
              <span class="configuracao-texto">
                <strong>Lembretes de metas</strong>
                <small>Receba avisos sobre metas próximas do prazo.</small>
              </span>

              <span class="switch">
                <input type="checkbox" name="lembrete_metas">
                <span class="slider"></span>
              </span>
            </label>

            <label class="configuracao-item configuracao-switch">
              <span class="configuracao-texto">
                <strong>Finalização do Pomodoro</strong>
                <small>Mostra um aviso quando o período de foco terminar.</small>
              </span>

              <span class="switch">
                <input type="checkbox" name="notificacao_pomodoro" checked>
                <span class="slider"></span>
              </span>
            </label>

            <label class="configuracao-item configuracao-switch">
              <span class="configuracao-texto">
                <strong>Som do Pomodoro</strong>
                <small>Reproduz um som quando o ciclo terminar.</small>
              </span>

              <span class="switch">
                <input type="checkbox" name="som_pomodoro" checked>
                <span class="slider"></span>
              </span>
            </label>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <label for="antecedencia-lembrete">Antecedência dos lembretes</label>
                <span>Defina quanto tempo antes o aviso será mostrado.</span>
              </div>

              <select id="antecedencia-lembrete" name="antecedencia_lembrete">
                <option value="5">5 minutos</option>
                <option value="15" selected>15 minutos</option>
                <option value="30">30 minutos</option>
                <option value="60">1 hora</option>
                <option value="1440">1 dia</option>
              </select>
            </div>

            <div class="linha-botao">
              <button type="button" class="btn-secundario" id="btn-testar-notificacao">
                <i class="fa-regular fa-bell"></i>
                Testar notificação
              </button>
            </div>
          </section>

          <section class="configuracao-card" id="privacidade">
            <div class="card-cabecalho">
              <div class="card-icone">
                <i class="fa-solid fa-shield-halved"></i>
              </div>

              <div>
                <h2>Privacidade e dados</h2>
                <p>Gerencie as informações armazenadas pelo FOAG.</p>
              </div>
            </div>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <strong>Exportar meus dados</strong>
                <small>Baixe uma cópia das suas informações em JSON.</small>
              </div>

              <button type="button" class="btn-secundario" id="btn-exportar">
                <i class="fa-solid fa-download"></i>
                Exportar
              </button>
            </div>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <strong>Importar backup</strong>
                <small>Restaure informações usando um arquivo JSON.</small>
              </div>

              <input type="file" id="arquivo-backup" accept=".json,application/json" hidden>

              <button type="button" class="btn-secundario" id="btn-importar">
                <i class="fa-solid fa-upload"></i>
                Importar
              </button>
            </div>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <strong>Limpar histórico do Pomodoro</strong>
                <small>Exclui todos os ciclos registrados anteriormente.</small>
              </div>

              <button type="button" class="btn-perigo-outline" data-acao="limpar-pomodoro">
                Limpar
              </button>
            </div>

            <div class="configuracao-item">
              <div class="configuracao-texto">
                <strong>Limpar atividades concluídas</strong>
                <small>Remove todas as atividades finalizadas.</small>
              </div>

              <button type="button" class="btn-perigo-outline" data-acao="limpar-atividades">
                Limpar
              </button>
            </div>

            <div class="zona-perigo">
              <div>
                <h3>Zona de perigo</h3>
                <p>Essas ações não poderão ser desfeitas.</p>
              </div>

              <button type="button" class="btn-perigo" data-acao="apagar-dados">
                Apagar todos os dados
              </button>
            </div>
          </section>

          <section class="configuracao-card" id="sobre">
            <div class="card-cabecalho">
              <div class="card-icone">
                <i class="fa-solid fa-circle-info"></i>
              </div>

              <div>
                <h2>Sobre</h2>
                <p>Informações sobre o projeto.</p>
              </div>
            </div>

            <div class="sobre-foag">
              <div class="logo-foag">
                F
              </div>

              <div>
                <h3>FOAG</h3>
                <p>Ferramenta de Organização Acadêmica Geral</p>
                <span>Versão 1.0.0</span>
              </div>
            </div>

            <div class="sobre-texto">
              <p>
                O FOAG foi desenvolvido para ajudar estudantes a organizar
                atividades, horários, metas, notas e períodos de estudo.
              </p>
            </div>

            <div class="sobre-links">
              <a href="../termos/termos.php">
                <i class="fa-solid fa-file-contract"></i>
                Termos de uso
              </a>

              <a href="../privacidade/privacidade.php">
                <i class="fa-solid fa-lock"></i>
                Política de privacidade
              </a>

              <a href="../ajuda/ajuda.php">
                <i class="fa-solid fa-circle-question"></i>
                Central de ajuda
              </a>
            </div>
          </section>

          <div class="barra-salvar">
            <p id="status-configuracoes">Nenhuma alteração pendente.</p>

            <div>
              <button type="reset" class="btn-secundario">
                Cancelar
              </button>

              <button type="submit" class="btn-principal">
                <i class="fa-solid fa-floppy-disk"></i>
                Salvar alterações
              </button>
            </div>
          </div>
        </form>
      </div>
    </main>
  </div>

  <div id="logout-modal" class="modal">
    <div class="modal-content">
      <h3>Ah... já vai?</h3>
      <h4>Tem certeza que deseja sair?</h4>

      <div class="modal-buttons">
        <button id="confirm-logout">Sim</button>
        <button id="cancel-logout">Cancelar</button>
      </div>
    </div>
  </div>

  <div id="confirmacao-modal" class="modal">
    <div class="modal-content">
      <div class="modal-alerta">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>

      <h3 id="confirmacao-titulo">Confirmar ação</h3>
      <p id="confirmacao-texto">Esta ação não poderá ser desfeita.</p>

      <div class="modal-buttons">
        <button id="confirmar-acao">Confirmar</button>
        <button id="cancelar-acao">Cancelar</button>
      </div>
    </div>
  </div>

  <div id="fogi-modal">
    <div class="fogi-container">
      <div class="fogi-header">
        <span>FOGi — Assistente de Estudos</span>
        <button id="fogi-close">Fechar</button>
      </div>

      <iframe id="fogi-iframe" src="about:blank"></iframe>
    </div>
  </div>

  <div id="toast-configuracoes">
    <i class="fa-solid fa-check"></i>
    <span>Configurações salvas com sucesso!</span>
  </div>

  <footer>&copy; 2026 FOAG. Todos os direitos reservados.</footer>

  <script src="configuracoes.js"></script>
</body>
</html>
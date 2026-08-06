<?php
session_start();

// ============================================
// SEM VERIFICAÇÃO DE LOGIN - FUNCIONA SEM BANCO
// ============================================
$current = basename($_SERVER['PHP_SELF']);

// Criar sessão temporária se não existir
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_nome'] = 'Usuário';
}

// ============================================
// CONFIGURAÇÕES PADRÃO
// ============================================
$config_padrao = [
    'formato_data' => 'dd/mm/aaaa',
    'formato_horario' => '24',
    'inicio_semana' => 'domingo',
    'pagina_inicial' => 'inicio',
    'visualizacao_calendario' => 'mes',
    'mostrar_concluidas' => 1,
    'confirmar_exclusao' => 1,
    'tema' => 'claro',
    'tamanho_fonte' => 'media',
    'cor_principal' => 'azul',
    'modo_compacto' => 0,
    'reduzir_animacoes' => 0,
    'notificacoes_navegador' => 0,
    'lembrete_atividades' => 1,
    'lembrete_provas' => 1,
    'lembrete_metas' => 0,
    'notificacao_pomodoro' => 1,
    'som_pomodoro' => 1,
    'antecedencia_lembrete' => 15
];

// ============================================
// CARREGAR CONFIGURAÇÕES DA SESSÃO
// ============================================
function carregarConfiguracoesSessao() {
    global $config_padrao;
    
    if (isset($_SESSION['configuracoes']) && is_array($_SESSION['configuracoes'])) {
        return array_merge($config_padrao, $_SESSION['configuracoes']);
    }
    
    return $config_padrao;
}

$config = carregarConfiguracoesSessao();

// ============================================
// PROCESSAR REQUISIÇÕES AJAX
// ============================================

// 1. SALVAR CONFIGURAÇÕES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'salvar') {
    header('Content-Type: application/json');
    
    try {
        $dados = $_POST;
        unset($dados['acao']);
        
        $checkboxes = ['mostrar_concluidas', 'confirmar_exclusao', 'modo_compacto', 
                       'reduzir_animacoes', 'notificacoes_navegador', 'lembrete_atividades',
                       'lembrete_provas', 'lembrete_metas', 'notificacao_pomodoro', 'som_pomodoro'];
        
        foreach ($checkboxes as $campo) {
            $dados[$campo] = isset($dados[$campo]) ? 1 : 0;
        }
        
        $_SESSION['configuracoes'] = $dados;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Configurações salvas com sucesso!'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao salvar: ' . $e->getMessage()
        ]);
    }
    exit();
}

// 2. EXPORTAR CONFIGURAÇÕES
if (isset($_GET['exportar']) && $_GET['exportar'] === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="foag_config_' . date('Y-m-d') . '.json"');
    
    $dados = [
        'usuario' => $_SESSION['usuario_nome'] ?? 'Usuário',
        'data_exportacao' => date('Y-m-d H:i:s'),
        'configuracoes' => $config,
        'versao' => '1.0.0'
    ];
    
    echo json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

// 3. IMPORTAR CONFIGURAÇÕES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup'])) {
    header('Content-Type: application/json');
    
    try {
        $arquivo = $_FILES['backup'];
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erro no upload do arquivo');
        }
        
        $conteudo = file_get_contents($arquivo['tmp_name']);
        $dados = json_decode($conteudo, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Arquivo JSON inválido');
        }
        
        if (!isset($dados['configuracoes']) || !is_array($dados['configuracoes'])) {
            throw new Exception('Estrutura do arquivo inválida');
        }
        
        $configImportada = array_merge($config_padrao, $dados['configuracoes']);
        $_SESSION['configuracoes'] = $configImportada;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Backup importado com sucesso!'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao importar: ' . $e->getMessage()
        ]);
    }
    exit();
}

// 4. AÇÕES DE PERIGO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_perigo'])) {
    header('Content-Type: application/json');
    
    try {
        $acao = $_POST['acao_perigo'];
        
        switch ($acao) {
            case 'limpar-pomodoro':
                $_SESSION['pomodoro_limpo'] = date('Y-m-d H:i:s');
                break;
                
            case 'limpar-atividades':
                $_SESSION['atividades_limpas'] = date('Y-m-d H:i:s');
                break;
                
            case 'apagar-dados':
                $_SESSION['configuracoes'] = $config_padrao;
                break;
                
            default:
                throw new Exception('Ação desconhecida');
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Ação executada com sucesso!'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro: ' . $e->getMessage()
        ]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#38a5ff">
  <title>Configurações - FOAG</title>
  
  <!-- Estilos -->
  <link rel="stylesheet" href="configuracoes.css">
  <link rel="stylesheet" href="../m.escuro/dark_basee.css">
  <link rel="stylesheet" href="dark_configuracoes.css">
  
  <!-- Fontes e ícones -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- JavaScript -->
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
        <i class="fa-solid fa-house"></i> Início
      </a>
      <a href="../calend/calendario.php" class="<?= $current === 'calendario.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-calendar-days"></i> Calendário
      </a>
      <a href="../bloco/agenda.php" class="<?= $current === 'agenda.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-book"></i> Agenda
      </a>
      <a href="../pomodoro/pomodoro.php" class="<?= $current === 'pomodoro.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-stopwatch"></i> Pomodoro
      </a>
      <a href="../notas/notas.php" class="<?= $current === 'notas.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-check-double"></i> Boletim
      </a>
      <a href="../loja/loja.php" class="<?= $current === 'loja.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-store"></i> Loja
      </a>
      <a href="../rank/rank.php" class="<?= $current === 'rank.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-trophy"></i> Ranking
      </a>
      <a href="../configuracoes/configuracoes.php" class="<?= $current === 'configuracoes.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-gear"></i> Configurações
      </a>
    </nav>

    <main class="conteudo configuracoes-conteudo">
      <div class="configuracoes-pagina">
        <!-- TOPO -->
        <div class="configuracoes-topo">
          <div>
            <h1>⚙️ Configurações</h1>
            <p>Personalize o funcionamento e a aparência do FOAG.</p>
            <p style="font-size:0.8rem;color:#94a3b8;margin-top:4px;">
              <i class="fa-solid fa-database" style="color:#22c55e;"></i> 
              Modo sem banco de dados - Configurações salvas na sessão
            </p>
          </div>
          <button type="submit" form="form-configuracoes" class="btn-salvar-topo">
            <i class="fa-solid fa-floppy-disk"></i> Salvar
          </button>
        </div>

        <form id="form-configuracoes" method="POST">
          <input type="hidden" name="acao" value="salvar">
          
          <!-- ============================================ -->
          <!-- SEÇÃO 1: GERAL -->
          <!-- ============================================ -->
          <section class="configuracao-card" id="geral">
            <div class="card-cabecalho">
              <div class="card-icone"><i class="fa-solid fa-sliders"></i></div>
              <div>
                <h2>Geral</h2>
                <p>Configurações utilizadas em diferentes páginas do sistema.</p>
              </div>
              <span class="card-toggle"><i class="fa-solid fa-chevron-down"></i></span>
            </div>
            <div class="card-conteudo">
              <!-- Formato de data -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <label for="formato-data">Formato de data</label>
                  <span>Escolha como as datas serão exibidas.</span>
                </div>
                <select id="formato-data" name="formato_data">
                  <option value="dd/mm/aaaa" <?= $config['formato_data'] === 'dd/mm/aaaa' ? 'selected' : '' ?>>DD/MM/AAAA</option>
                  <option value="mm/dd/aaaa" <?= $config['formato_data'] === 'mm/dd/aaaa' ? 'selected' : '' ?>>MM/DD/AAAA</option>
                  <option value="aaaa-mm-dd" <?= $config['formato_data'] === 'aaaa-mm-dd' ? 'selected' : '' ?>>AAAA-MM-DD</option>
                </select>
              </div>

              <!-- Formato de horário -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <label for="formato-horario">Formato de horário</label>
                  <span>Escolha entre o formato de 12 ou 24 horas.</span>
                </div>
                <select id="formato-horario" name="formato_horario">
                  <option value="24" <?= $config['formato_horario'] === '24' ? 'selected' : '' ?>>24 horas — 18:30</option>
                  <option value="12" <?= $config['formato_horario'] === '12' ? 'selected' : '' ?>>12 horas — 06:30 PM</option>
                </select>
              </div>

              <!-- Início da semana -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <label for="inicio-semana">Início da semana</label>
                  <span>Defina o primeiro dia mostrado no calendário.</span>
                </div>
                <select id="inicio-semana" name="inicio_semana">
                  <option value="domingo" <?= $config['inicio_semana'] === 'domingo' ? 'selected' : '' ?>>Domingo</option>
                  <option value="segunda" <?= $config['inicio_semana'] === 'segunda' ? 'selected' : '' ?>>Segunda-feira</option>
                </select>
              </div>

              <!-- Página inicial -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <label for="pagina-inicial">Página inicial padrão</label>
                  <span>Escolha a página aberta depois do login.</span>
                </div>
                <select id="pagina-inicial" name="pagina_inicial">
                  <option value="inicio" <?= $config['pagina_inicial'] === 'inicio' ? 'selected' : '' ?>>Início</option>
                  <option value="calendario" <?= $config['pagina_inicial'] === 'calendario' ? 'selected' : '' ?>>Calendário</option>
                  <option value="agenda" <?= $config['pagina_inicial'] === 'agenda' ? 'selected' : '' ?>>Agenda</option>
                  <option value="pomodoro" <?= $config['pagina_inicial'] === 'pomodoro' ? 'selected' : '' ?>>Pomodoro</option>
                  <option value="boletim" <?= $config['pagina_inicial'] === 'boletim' ? 'selected' : '' ?>>Boletim</option>
                </select>
              </div>

              <!-- Visualização calendário -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <label for="visualizacao-calendario">Visualização do calendário</label>
                  <span>Escolha como o calendário será aberto.</span>
                </div>
                <select id="visualizacao-calendario" name="visualizacao_calendario">
                  <option value="mes" <?= $config['visualizacao_calendario'] === 'mes' ? 'selected' : '' ?>>Mês</option>
                  <option value="semana" <?= $config['visualizacao_calendario'] === 'semana' ? 'selected' : '' ?>>Semana</option>
                  <option value="dia" <?= $config['visualizacao_calendario'] === 'dia' ? 'selected' : '' ?>>Dia</option>
                </select>
              </div>

              <!-- Switch: Mostrar concluídas -->
              <label class="configuracao-item configuracao-switch">
                <span class="configuracao-texto">
                  <strong>Mostrar atividades concluídas</strong>
                  <small>Exibe atividades finalizadas na agenda.</small>
                </span>
                <span class="switch">
                  <input type="checkbox" name="mostrar_concluidas" <?= $config['mostrar_concluidas'] ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </span>
              </label>

              <!-- Switch: Confirmar exclusão -->
              <label class="configuracao-item configuracao-switch">
                <span class="configuracao-texto">
                  <strong>Confirmar antes de excluir</strong>
                  <small>Pede confirmação antes de apagar informações.</small>
                </span>
                <span class="switch">
                  <input type="checkbox" name="confirmar_exclusao" <?= $config['confirmar_exclusao'] ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </span>
              </label>
            </div>
          </section>

          <!-- ============================================ -->
          <!-- SEÇÃO 2: APARÊNCIA -->
          <!-- ============================================ -->
          <section class="configuracao-card" id="aparencia">
            <div class="card-cabecalho">
              <div class="card-icone"><i class="fa-solid fa-palette"></i></div>
              <div>
                <h2>Aparência</h2>
                <p>Personalize a visualização do sistema.</p>
              </div>
              <span class="card-toggle"><i class="fa-solid fa-chevron-down"></i></span>
            </div>
            <div class="card-conteudo">
              <!-- Tema -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <label for="tema">Tema</label>
                  <span>Escolha a aparência principal do FOAG.</span>
                </div>
                <select id="tema" name="tema">
                  <option value="claro" <?= $config['tema'] === 'claro' ? 'selected' : '' ?>>Claro</option>
                  <option value="escuro" <?= $config['tema'] === 'escuro' ? 'selected' : '' ?>>Escuro</option>
                  <option value="sistema" <?= $config['tema'] === 'sistema' ? 'selected' : '' ?>>Tema do dispositivo</option>
                </select>
              </div>

              <!-- Tamanho da fonte -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <label for="tamanho-fonte">Tamanho da fonte</label>
                  <span>Altere o tamanho dos textos do sistema.</span>
                </div>
                <select id="tamanho-fonte" name="tamanho_fonte">
                  <option value="pequena" <?= $config['tamanho_fonte'] === 'pequena' ? 'selected' : '' ?>>Pequena</option>
                  <option value="media" <?= $config['tamanho_fonte'] === 'media' ? 'selected' : '' ?>>Média</option>
                  <option value="grande" <?= $config['tamanho_fonte'] === 'grande' ? 'selected' : '' ?>>Grande</option>
                </select>
              </div>

              <!-- Cor principal -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <span class="titulo-opcao">Cor principal</span>
                  <span>Escolha a cor dos botões e elementos de destaque.</span>
                </div>
                <div class="opcoes-cores">
                  <label class="cor cor-azul" title="Azul">
                    <input type="radio" name="cor_principal" value="azul" <?= $config['cor_principal'] === 'azul' ? 'checked' : '' ?>>
                    <span></span>
                  </label>
                  <label class="cor cor-roxa" title="Roxo">
                    <input type="radio" name="cor_principal" value="roxo" <?= $config['cor_principal'] === 'roxo' ? 'checked' : '' ?>>
                    <span></span>
                  </label>
                  <label class="cor cor-verde" title="Verde">
                    <input type="radio" name="cor_principal" value="verde" <?= $config['cor_principal'] === 'verde' ? 'checked' : '' ?>>
                    <span></span>
                  </label>
                  <label class="cor cor-rosa" title="Rosa">
                    <input type="radio" name="cor_principal" value="rosa" <?= $config['cor_principal'] === 'rosa' ? 'checked' : '' ?>>
                    <span></span>
                  </label>
                </div>
              </div>

              <!-- Switch: Modo compacto -->
              <label class="configuracao-item configuracao-switch">
                <span class="configuracao-texto">
                  <strong>Modo compacto</strong>
                  <small>Reduz os espaços entre os elementos.</small>
                </span>
                <span class="switch">
                  <input type="checkbox" name="modo_compacto" <?= $config['modo_compacto'] ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </span>
              </label>

              <!-- Switch: Reduzir animações -->
              <label class="configuracao-item configuracao-switch">
                <span class="configuracao-texto">
                  <strong>Reduzir animações</strong>
                  <small>Diminui movimentos e transições nas páginas.</small>
                </span>
                <span class="switch">
                  <input type="checkbox" name="reduzir_animacoes" <?= $config['reduzir_animacoes'] ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </span>
              </label>
            </div>
          </section>

          <!-- ============================================ -->
          <!-- SEÇÃO 3: NOTIFICAÇÕES -->
          <!-- ============================================ -->
          <section class="configuracao-card" id="notificacoes">
            <div class="card-cabecalho">
              <div class="card-icone"><i class="fa-solid fa-bell"></i></div>
              <div>
                <h2>Notificações</h2>
                <p>Escolha os lembretes que deseja receber.</p>
              </div>
              <span class="card-toggle"><i class="fa-solid fa-chevron-down"></i></span>
            </div>
            <div class="card-conteudo">
              <label class="configuracao-item configuracao-switch">
                <span class="configuracao-texto">
                  <strong>Notificações do navegador</strong>
                  <small>Permite que o FOAG envie avisos no dispositivo.</small>
                </span>
                <span class="switch">
                  <input type="checkbox" name="notificacoes_navegador" <?= $config['notificacoes_navegador'] ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </span>
              </label>

              <label class="configuracao-item configuracao-switch">
                <span class="configuracao-texto">
                  <strong>Lembretes de atividades</strong>
                  <small>Receba avisos sobre atividades próximas.</small>
                </span>
                <span class="switch">
                  <input type="checkbox" name="lembrete_atividades" <?= $config['lembrete_atividades'] ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </span>
              </label>

              <label class="configuracao-item configuracao-switch">
                <span class="configuracao-texto">
                  <strong>Lembretes de provas</strong>
                  <small>Receba avisos antes das datas de provas.</small>
                </span>
                <span class="switch">
                  <input type="checkbox" name="lembrete_provas" <?= $config['lembrete_provas'] ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </span>
              </label>

              <label class="configuracao-item configuracao-switch">
                <span class="configuracao-texto">
                  <strong>Lembretes de metas</strong>
                  <small>Receba avisos sobre metas próximas do prazo.</small>
                </span>
                <span class="switch">
                  <input type="checkbox" name="lembrete_metas" <?= $config['lembrete_metas'] ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </span>
              </label>

              <label class="configuracao-item configuracao-switch">
                <span class="configuracao-texto">
                  <strong>Finalização do Pomodoro</strong>
                  <small>Mostra um aviso quando o período de foco terminar.</small>
                </span>
                <span class="switch">
                  <input type="checkbox" name="notificacao_pomodoro" <?= $config['notificacao_pomodoro'] ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </span>
              </label>

              <label class="configuracao-item configuracao-switch">
                <span class="configuracao-texto">
                  <strong>Som do Pomodoro</strong>
                  <small>Reproduz um som quando o ciclo terminar.</small>
                </span>
                <span class="switch">
                  <input type="checkbox" name="som_pomodoro" <?= $config['som_pomodoro'] ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </span>
              </label>

              <!-- Antecedência -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <label for="antecedencia-lembrete">Antecedência dos lembretes</label>
                  <span>Defina quanto tempo antes o aviso será mostrado.</span>
                </div>
                <select id="antecedencia-lembrete" name="antecedencia_lembrete">
                  <option value="5" <?= $config['antecedencia_lembrete'] == 5 ? 'selected' : '' ?>>5 minutos</option>
                  <option value="15" <?= $config['antecedencia_lembrete'] == 15 ? 'selected' : '' ?>>15 minutos</option>
                  <option value="30" <?= $config['antecedencia_lembrete'] == 30 ? 'selected' : '' ?>>30 minutos</option>
                  <option value="60" <?= $config['antecedencia_lembrete'] == 60 ? 'selected' : '' ?>>1 hora</option>
                  <option value="1440" <?= $config['antecedencia_lembrete'] == 1440 ? 'selected' : '' ?>>1 dia</option>
                </select>
              </div>

              <!-- Testar notificação -->
              <div class="linha-botao">
                <button type="button" class="btn-secundario" id="btn-testar-notificacao">
                  <i class="fa-regular fa-bell"></i> Testar notificação
                </button>
              </div>
            </div>
          </section>

          <!-- ============================================ -->
          <!-- SEÇÃO 4: PRIVACIDADE -->
          <!-- ============================================ -->
          <section class="configuracao-card" id="privacidade">
            <div class="card-cabecalho">
              <div class="card-icone"><i class="fa-solid fa-shield-halved"></i></div>
              <div>
                <h2>Privacidade e dados</h2>
                <p>Gerencie as informações armazenadas pelo FOAG.</p>
              </div>
              <span class="card-toggle"><i class="fa-solid fa-chevron-down"></i></span>
            </div>
            <div class="card-conteudo">
              <!-- Exportar -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <strong>Exportar meus dados</strong>
                  <small>Baixe uma cópia das suas informações em JSON.</small>
                </div>
                <a href="?exportar=json" class="btn-secundario" style="text-decoration:none;">
                  <i class="fa-solid fa-download"></i> Exportar
                </a>
              </div>

              <!-- Importar -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <strong>Importar backup</strong>
                  <small>Restaure informações usando um arquivo JSON.</small>
                </div>
                <form id="form-importar" method="POST" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:center;">
                  <input type="file" name="backup" accept=".json,application/json" style="display:none;" id="arquivo-backup">
                  <button type="button" class="btn-secundario" id="btn-importar">
                    <i class="fa-solid fa-upload"></i> Importar
                  </button>
                </form>
              </div>

              <!-- Limpar Pomodoro -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <strong>Limpar histórico do Pomodoro</strong>
                  <small>Exclui todos os ciclos registrados anteriormente.</small>
                </div>
                <button type="button" class="btn-perigo-outline" data-acao="limpar-pomodoro">
                  Limpar
                </button>
              </div>

              <!-- Limpar atividades -->
              <div class="configuracao-item">
                <div class="configuracao-texto">
                  <strong>Limpar atividades concluídas</strong>
                  <small>Remove todas as atividades finalizadas.</small>
                </div>
                <button type="button" class="btn-perigo-outline" data-acao="limpar-atividades">
                  Limpar
                </button>
              </div>

              <!-- Zona de perigo -->
              <div class="zona-perigo">
                <div>
                  <h3>⚠️ Zona de perigo</h3>
                  <p>Essas ações não poderão ser desfeitas.</p>
                </div>
                <button type="button" class="btn-perigo" data-acao="apagar-dados">
                  Apagar todos os dados
                </button>
              </div>
            </div>
          </section>

          <!-- ============================================ -->
          <!-- SEÇÃO 5: SOBRE -->
          <!-- ============================================ -->
          <section class="configuracao-card" id="sobre">
            <div class="card-cabecalho">
              <div class="card-icone"><i class="fa-solid fa-circle-info"></i></div>
              <div>
                <h2>Sobre</h2>
                <p>Informações sobre o projeto.</p>
              </div>
              <span class="card-toggle"><i class="fa-solid fa-chevron-down"></i></span>
            </div>
            <div class="card-conteudo">
              <div class="sobre-foag">
                <div class="logo-foag">F</div>
                <div>
                  <h3>FOAG</h3>
                  <p>Ferramenta de Organização Acadêmica Geral</p>
                  <span>Versão 1.0.0 (Sem banco de dados)</span>
                </div>
              </div>

              <div class="sobre-texto">
                <p>
                  O FOAG foi desenvolvido para ajudar estudantes a organizar
                  atividades, horários, metas, notas e períodos de estudo.
                  <br><br>
                  <strong>💡 Modo offline:</strong> As configurações são salvas na sessão do navegador.
                  Para salvar permanentemente, instale o banco de dados.
                </p>
              </div>

              <div class="sobre-links">
                <a href="#"><i class="fa-solid fa-file-contract"></i> Termos de uso</a>
                <a href="#"><i class="fa-solid fa-lock"></i> Política de privacidade</a>
                <a href="#"><i class="fa-solid fa-circle-question"></i> Central de ajuda</a>
              </div>
            </div>
          </section>

          <!-- BARRA SALVAR -->
          <div class="barra-salvar">
            <p id="status-configuracoes">
              <i class="fa-solid fa-circle" style="color:#22c55e;font-size:0.6rem;"></i>
              <?= date('H:i') ?> - Configurações carregadas
            </p>
            <div>
              <button type="reset" class="btn-secundario">
                <i class="fa-solid fa-rotate-left"></i> Cancelar
              </button>
              <button type="submit" class="btn-principal">
                <i class="fa-solid fa-floppy-disk"></i> Salvar alterações
              </button>
            </div>
          </div>
        </form>
      </div>
    </main>
  </div>

  <!-- MODAIS -->
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
      <div class="modal-alerta"><i class="fa-solid fa-triangle-exclamation"></i></div>
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
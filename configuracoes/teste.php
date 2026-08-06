<?php
session_start();

// ============================================
// SEM VERIFICAÇÃO DE LOGIN
// ============================================
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_nome'] = 'Usuário';
}

$current = basename($_SERVER['PHP_SELF']);

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

function carregarConfiguracoes() {
    global $config_padrao;
    if (isset($_SESSION['configuracoes']) && is_array($_SESSION['configuracoes'])) {
        return array_merge($config_padrao, $_SESSION['configuracoes']);
    }
    return $config_padrao;
}

$config = carregarConfiguracoes();

// ============================================
// PROCESSAR REQUISIÇÕES AJAX
// ============================================

// SALVAR
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
        echo json_encode(['success' => true, 'message' => 'Configurações salvas com sucesso!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar: ' . $e->getMessage()]);
    }
    exit();
}

// EXPORTAR
if (isset($_GET['exportar']) && $_GET['exportar'] === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="foag_config_' . date('Y-m-d') . '.json"');
    echo json_encode([
        'usuario' => $_SESSION['usuario_nome'] ?? 'Usuário',
        'data_exportacao' => date('Y-m-d H:i:s'),
        'configuracoes' => $config,
        'versao' => '1.0.0'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

// IMPORTAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup'])) {
    header('Content-Type: application/json');
    try {
        $arquivo = $_FILES['backup'];
        if ($arquivo['error'] !== UPLOAD_ERR_OK) throw new Exception('Erro no upload');
        $conteudo = file_get_contents($arquivo['tmp_name']);
        $dados = json_decode($conteudo, true);
        if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('Arquivo JSON inválido');
        if (!isset($dados['configuracoes'])) throw new Exception('Estrutura inválida');
        $_SESSION['configuracoes'] = array_merge($config_padrao, $dados['configuracoes']);
        echo json_encode(['success' => true, 'message' => 'Backup importado com sucesso!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
    exit();
}

// AÇÕES DE PERIGO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_perigo'])) {
    header('Content-Type: application/json');
    try {
        $acao = $_POST['acao_perigo'];
        switch ($acao) {
            case 'limpar-pomodoro': break;
            case 'limpar-atividades': break;
            case 'apagar-dados': $_SESSION['configuracoes'] = $config_padrao; break;
            default: throw new Exception('Ação desconhecida');
        }
        echo json_encode(['success' => true, 'message' => 'Ação executada com sucesso!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - FOAG</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; flex-direction: column; min-height: 100vh; background: #ffffff; color: #1f2937; }
        
        header.cabecalho {
            font-family: 'Snap ITC', sans-serif;
            width: 100%; background: #38a5ff; color: #fff;
            text-align: left; padding: 15px; font-size: 24px; font-weight: bold;
            position: relative; box-shadow: 5px 5px 15px rgba(0,0,0,0.2); z-index: 10;
        }
        .header-icons {
            position: absolute; top: 50%; right: 20px;
            transform: translateY(-50%);
            display: flex; gap: 15px; align-items: center;
            font-size: 1.3rem; color: #fff; user-select: none;
        }
        .header-icons i { color: #fff; cursor: pointer; transition: transform 0.2s; }
        .header-icons i:hover { transform: translateY(-13%) scale(1.1); color: #ddd; }
        
        .container { display: flex; flex: 1; min-height: 0; }
        
        nav.menu {
            width: 260px; flex-shrink: 0; background: #38a5ff;
            padding: 18px; display: flex; flex-direction: column; gap: 14px;
            box-shadow: 5px 5px 15px rgba(0,0,0,0.15);
        }
        nav.menu a {
            text-decoration: none; color: #fff; font-size: 16px;
            padding: 10px 12px; border-radius: 8px;
            transition: background 0.2s; display: flex; align-items: center; gap: 8px;
        }
        nav.menu a:hover, nav.menu a.active { background: rgba(255,255,255,0.18); }
        
        .conteudo { flex: 1; min-width: 0; padding: 25px; }
        .configuracoes-conteudo { background: #f6f8fb; overflow-y: auto; }
        .configuracoes-pagina { width: 100%; max-width: 1050px; margin: 0 auto; }
        
        .configuracoes-topo {
            display: flex; align-items: center; justify-content: space-between;
            gap: 20px; margin-bottom: 22px;
        }
        .configuracoes-topo h1 { color: #1f2937; font-size: 1.8rem; margin-bottom: 4px; }
        .configuracoes-topo p { color: #6b7280; font-size: 0.94rem; }
        
        /* ACORDEON */
        .configuracao-card {
            background: #fff; border: 1px solid #e5eaf0; border-radius: 14px;
            margin-bottom: 12px; box-shadow: 0 4px 14px rgba(15,23,42,0.06);
            overflow: hidden;
        }
        .card-cabecalho {
            display: flex; align-items: center; gap: 13px;
            padding: 18px 22px; cursor: pointer; user-select: none;
            transition: background 0.2s; border-bottom: 1px solid transparent;
        }
        .card-cabecalho:hover { background: rgba(56,165,255,0.05); }
        .card-cabecalho .card-toggle {
            margin-left: auto; font-size: 0.9rem; color: #94a3b8;
            transition: transform 0.3s; display: inline-block;
        }
        .card-cabecalho .card-toggle.ativo { transform: rotate(180deg); }
        
        .card-icone {
            width: 43px; height: 43px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            background: #dff3ff; color: #168ce8; font-size: 1.1rem; flex-shrink: 0;
        }
        .card-cabecalho h2 { color: #1f2937; font-size: 1.15rem; margin-bottom: 2px; }
        .card-cabecalho p { color: #6b7280; font-size: 0.86rem; }
        
        .card-conteudo {
            max-height: 0; overflow: hidden;
            transition: max-height 0.4s ease-in-out, padding 0.3s ease-in-out;
            padding: 0 22px;
        }
        .card-conteudo.aberto {
            max-height: 3000px;
            padding: 8px 22px 8px 22px;
        }
        
        .configuracao-item {
            min-height: 76px; padding: 15px 4px;
            border-bottom: 1px solid #edf0f4;
            display: flex; align-items: center; justify-content: space-between; gap: 25px;
        }
        .configuracao-item:last-child { border-bottom: none; }
        
        .configuracao-texto { display: flex; flex-direction: column; gap: 4px; }
        .configuracao-texto label, .configuracao-texto strong, .titulo-opcao {
            color: #273142; font-size: 0.94rem; font-weight: 600;
        }
        .configuracao-texto span, .configuracao-texto small {
            color: #6b7280; font-size: 0.82rem; font-weight: 400;
        }
        
        .configuracao-item select {
            width: 240px; padding: 10px 12px;
            border: 1px solid #d8dee7; border-radius: 9px;
            background: #fff; color: #374151; outline: none; cursor: pointer;
        }
        .configuracao-item select:focus {
            border-color: #38a5ff; box-shadow: 0 0 0 3px rgba(56,165,255,0.14);
        }
        
        .configuracao-switch { cursor: pointer; }
        .switch {
            position: relative; width: 48px; height: 26px; flex-shrink: 0;
        }
        .switch input { display: none; }
        .slider {
            position: absolute; inset: 0; background: #cbd5e1;
            border-radius: 999px; transition: 0.25s ease;
        }
        .slider::before {
            content: ""; position: absolute; width: 20px; height: 20px;
            left: 3px; top: 3px; background: #fff; border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.18); transition: 0.25s ease;
        }
        .switch input:checked + .slider { background: #38a5ff; }
        .switch input:checked + .slider::before { transform: translateX(22px); }
        
        .opcoes-cores { display: flex; align-items: center; gap: 12px; }
        .cor { cursor: pointer; }
        .cor input { display: none; }
        .cor span {
            width: 28px; height: 28px; display: block; border-radius: 50%;
            border: 3px solid transparent; box-shadow: 0 0 0 1px #d1d5db;
            transition: transform 0.2s;
        }
        .cor:hover span { transform: scale(1.1); }
        .cor input:checked + span {
            border-color: #fff; box-shadow: 0 0 0 3px #273142;
        }
        .cor-azul span { background: #38a5ff; }
        .cor-roxa span { background: #8b5cf6; }
        .cor-verde span { background: #22c55e; }
        .cor-rosa span { background: #ec4899; }
        
        .btn-principal, .btn-salvar-topo, .btn-secundario, .btn-perigo, .btn-perigo-outline {
            border: none; border-radius: 9px; padding: 10px 15px;
            font-size: 0.87rem; font-weight: 600; cursor: pointer;
            transition: 0.2s; display: inline-flex; align-items: center;
            justify-content: center; gap: 7px;
        }
        .btn-principal, .btn-salvar-topo { background: #38a5ff; color: #fff; }
        .btn-principal:hover, .btn-salvar-topo:hover { background: #168ce8; }
        .btn-secundario { background: #eef2f6; color: #374151; }
        .btn-secundario:hover { background: #dfe5eb; }
        .btn-perigo { background: #dc3545; color: #fff; }
        .btn-perigo:hover { background: #bd2130; }
        .btn-perigo-outline { background: transparent; color: #dc3545; border: 1px solid #dc3545; }
        .btn-perigo-outline:hover { background: #dc3545; color: #fff; }
        
        .linha-botao { padding-top: 18px; display: flex; justify-content: flex-end; }
        
        .zona-perigo {
            margin-top: 20px; border: 1px solid #fecaca; background: #fff7f7;
            border-radius: 11px; padding: 16px;
            display: flex; align-items: center; justify-content: space-between; gap: 20px;
        }
        .zona-perigo h3 { color: #b91c1c; font-size: 0.95rem; margin-bottom: 3px; }
        .zona-perigo p { color: #7f1d1d; font-size: 0.8rem; }
        
        .sobre-foag {
            display: flex; align-items: center; gap: 15px;
            padding: 22px 4px; border-bottom: 1px solid #edf0f4;
        }
        .logo-foag {
            width: 58px; height: 58px; border-radius: 15px;
            background: #38a5ff; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Snap ITC', sans-serif; font-size: 1.7rem;
        }
        .sobre-foag h3 { color: #1f2937; font-size: 1.2rem; }
        .sobre-foag p { color: #4b5563; font-size: 0.87rem; }
        .sobre-foag span { color: #9ca3af; font-size: 0.76rem; }
        .sobre-texto {
            padding: 18px 4px; color: #4b5563; font-size: 0.87rem;
            line-height: 1.65; border-bottom: 1px solid #edf0f4;
        }
        .sobre-links {
            display: flex; flex-wrap: wrap; gap: 10px; padding-top: 18px;
        }
        .sobre-links a {
            text-decoration: none; color: #168ce8; background: #eef8ff;
            border: 1px solid #cfeaff; padding: 9px 12px; border-radius: 8px;
            font-size: 0.84rem; display: flex; align-items: center; gap: 7px;
        }
        .sobre-links a:hover { background: #dff3ff; }
        
        .barra-salvar {
            position: sticky; bottom: 0;
            background: rgba(255,255,255,0.96);
            border: 1px solid #e5eaf0; border-radius: 12px;
            padding: 13px 16px; box-shadow: 0 -5px 20px rgba(15,23,42,0.08);
            display: flex; justify-content: space-between; align-items: center;
            gap: 15px; z-index: 5;
        }
        .barra-salvar p { color: #6b7280; font-size: 0.82rem; }
        .barra-salvar > div { display: flex; gap: 10px; }
        
        /* MODAIS */
        .modal {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 2000;
            justify-content: center; align-items: center;
        }
        .modal-content {
            background: #fff; color: #333; padding: 25px;
            border-radius: 10px; text-align: center;
            width: min(90%, 350px); box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            animation: aparecer 0.3s ease;
        }
        .modal-content h3 { margin-bottom: 5px; }
        .modal-content h4, .modal-content p { font-weight: 400; color: #6b7280; }
        .modal-alerta { font-size: 2rem; color: #f59e0b; margin-bottom: 10px; }
        .modal-buttons { margin-top: 20px; display: flex; justify-content: center; gap: 15px; }
        .modal-buttons button { padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: bold; }
        #confirm-logout, #confirmar-acao { background: #38a5ff; color: #fff; }
        #cancel-logout, #cancelar-acao { background: #e0e0e0; color: #333; }
        
        @keyframes aparecer {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        #fogi-modal {
            display: none; position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
            align-items: center; justify-content: center;
        }
        #fogi-modal .fogi-container {
            background: #fff; width: 90%; max-width: 1100px; height: 80vh;
            border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;
            box-shadow: 0 10px 35px rgba(0,0,0,0.2);
        }
        #fogi-modal .fogi-header {
            display: flex; align-items: center; justify-content: space-between;
            background: #38a5ff; color: #fff; padding: 8px 14px;
            font-weight: 600; font-size: 0.95rem;
        }
        #fogi-close {
            border: none; background: #fff; color: #333;
            padding: 4px 10px; border-radius: 6px; cursor: pointer;
        }
        #fogi-iframe { flex: 1; border: none; width: 100%; height: 100%; }
        
        #toast-configuracoes {
            position: fixed; right: 25px; bottom: 25px;
            background: #1f2937; color: #fff; padding: 12px 17px;
            border-radius: 9px; display: none; align-items: center; gap: 8px;
            z-index: 5000; box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        footer {
            background: #232323; color: #fff; text-align: center;
            padding: 5px; width: 100%; font-size: 1em;
        }
        
        @media (max-width: 768px) {
            .container { flex-direction: column; }
            nav.menu { width: 100%; }
            .conteudo { padding: 15px; }
            .configuracoes-topo { align-items: flex-start; }
            .configuracao-item { align-items: flex-start; flex-direction: column; gap: 12px; }
            .configuracao-item select { width: 100%; }
            .zona-perigo { align-items: flex-start; flex-direction: column; }
            .barra-salvar { align-items: flex-start; flex-direction: column; }
            .barra-salvar > div { width: 100%; }
            .barra-salvar button { flex: 1; }
            .card-cabecalho { padding: 14px 16px; }
            .card-conteudo { padding: 0 16px; }
            .card-conteudo.aberto { padding: 8px 16px 8px 16px; }
        }
        @media (max-width: 480px) {
            header.cabecalho { font-size: 20px; padding: 12px; }
            .header-icons { right: 12px; gap: 11px; font-size: 1.05rem; }
            .configuracoes-topo h1 { font-size: 1.45rem; }
            .btn-salvar-topo { display: none; }
            .card-cabecalho { padding: 12px 14px; }
            .card-cabecalho h2 { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <header class="cabecalho">
        FOAG
        <div class="header-icons">
            <i id="themeToggle" class="fa-solid fa-moon" title="Modo escuro"></i>
            <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
            <i id="icon-fogi" class="fa-solid fa-robot" title="Assistente FOAG"></i>
            <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
        </div>
    </header>

    <div class="container">
        <nav class="menu">
            <a href="../inicioo/inicio.php"><i class="fa-solid fa-house"></i> Início</a>
            <a href="../calend/calendario.php"><i class="fa-solid fa-calendar-days"></i> Calendário</a>
            <a href="../bloco/agenda.php"><i class="fa-solid fa-book"></i> Agenda</a>
            <a href="../pomodoro/pomodoro.php"><i class="fa-solid fa-stopwatch"></i> Pomodoro</a>
            <a href="../notas/notas.php"><i class="fa-solid fa-check-double"></i> Boletim</a>
            <a href="../loja/loja.php"><i class="fa-solid fa-store"></i> Loja</a>
            <a href="../rank/rank.php"><i class="fa-solid fa-trophy"></i> Ranking</a>
            <a href="../configuracoes/configuracoes.php" class="active"><i class="fa-solid fa-gear"></i> Configurações</a>
        </nav>

        <main class="conteudo configuracoes-conteudo">
            <div class="configuracoes-pagina">
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
                    
                    <!-- GERAL -->
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

                    <!-- APARÊNCIA -->
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

                    <!-- NOTIFICAÇÕES -->
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

                            <div class="linha-botao">
                                <button type="button" class="btn-secundario" id="btn-testar-notificacao">
                                    <i class="fa-regular fa-bell"></i> Testar notificação
                                </button>
                            </div>
                        </div>
                    </section>

                    <!-- PRIVACIDADE -->
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
                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <strong>Exportar meus dados</strong>
                                    <small>Baixe uma cópia das suas informações em JSON.</small>
                                </div>
                                <a href="?exportar=json" class="btn-secundario" style="text-decoration:none;">
                                    <i class="fa-solid fa-download"></i> Exportar
                                </a>
                            </div>

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

                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <strong>Limpar histórico do Pomodoro</strong>
                                    <small>Exclui todos os ciclos registrados anteriormente.</small>
                                </div>
                                <button type="button" class="btn-perigo-outline" data-acao="limpar-pomodoro">Limpar</button>
                            </div>

                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <strong>Limpar atividades concluídas</strong>
                                    <small>Remove todas as atividades finalizadas.</small>
                                </div>
                                <button type="button" class="btn-perigo-outline" data-acao="limpar-atividades">Limpar</button>
                            </div>

                            <div class="zona-perigo">
                                <div>
                                    <h3>⚠️ Zona de perigo</h3>
                                    <p>Essas ações não poderão ser desfeitas.</p>
                                </div>
                                <button type="button" class="btn-perigo" data-acao="apagar-dados">Apagar todos os dados</button>
                            </div>
                        </div>
                    </section>

                    <!-- SOBRE -->
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
                                </p>
                            </div>
                            <div class="sobre-links">
                                <a href="#"><i class="fa-solid fa-file-contract"></i> Termos de uso</a>
                                <a href="#"><i class="fa-solid fa-lock"></i> Política de privacidade</a>
                                <a href="#"><i class="fa-solid fa-circle-question"></i> Central de ajuda</a>
                            </div>
                        </div>
                    </section>

                    <div class="barra-salvar">
                        <p id="status-configuracoes">
                            <i class="fa-solid fa-circle" style="color:#22c55e;font-size:0.6rem;"></i>
                            <?= date('H:i') ?> - Configurações carregadas
                        </p>
                        <div>
                            <button type="reset" class="btn-secundario"><i class="fa-solid fa-rotate-left"></i> Cancelar</button>
                            <button type="submit" class="btn-principal"><i class="fa-solid fa-floppy-disk"></i> Salvar</button>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        // ============================================
        // 1. ACORDEON
        // ============================================
        const cabecalhos = document.querySelectorAll('.card-cabecalho');

        function toggleSecao(cabecalho) {
            const card = cabecalho.closest('.configuracao-card');
            if (!card) return;
            const conteudo = card.querySelector('.card-conteudo');
            const toggle = cabecalho.querySelector('.card-toggle');
            if (!conteudo) return;
            const isOpen = conteudo.classList.contains('aberto');
            if (isOpen) {
                conteudo.classList.remove('aberto');
                if (toggle) toggle.classList.remove('ativo');
            } else {
                conteudo.classList.add('aberto');
                if (toggle) toggle.classList.add('ativo');
            }
        }

        cabecalhos.forEach(cabecalho => {
            cabecalho.addEventListener('click', function(e) {
                if (e.target.closest('button') || e.target.closest('.switch') || 
                    e.target.closest('select') || e.target.closest('.cor') ||
                    e.target.closest('a') || e.target.closest('input')) {
                    return;
                }
                toggleSecao(this);
            });
        });

        // Abrir primeira seção
        const primeiroCard = document.querySelector('.configuracao-card');
        if (primeiroCard) {
            const conteudo = primeiroCard.querySelector('.card-conteudo');
            const toggle = primeiroCard.querySelector('.card-toggle');
            if (conteudo) {
                conteudo.classList.add('aberto');
                if (toggle) toggle.classList.add('ativo');
            }
        }

        // ============================================
        // 2. TOAST
        // ============================================
        function mostrarToast(mensagem, tipo) {
            const toast = document.getElementById('toast-configuracoes');
            if (!toast) return;
            const icone = toast.querySelector('i');
            const span = toast.querySelector('span');
            if (tipo === 'sucesso') {
                icone.className = 'fa-solid fa-check-circle';
                icone.style.color = '#22c55e';
            } else {
                icone.className = 'fa-solid fa-exclamation-circle';
                icone.style.color = '#ef4444';
            }
            span.textContent = mensagem;
            toast.style.display = 'flex';
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.style.transition = 'all 0.3s ease';
                toast.style.opacity = '1';
            }, 50);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => { toast.style.display = 'none'; }, 300);
            }, 3500);
        }

        // ============================================
        // 3. MODAL
        // ============================================
        function abrirModal(titulo, texto, callback) {
            const modal = document.getElementById('confirmacao-modal');
            const tituloEl = document.getElementById('confirmacao-titulo');
            const textoEl = document.getElementById('confirmacao-texto');
            const confirmarBtn = document.getElementById('confirmar-acao');
            const cancelarBtn = document.getElementById('cancelar-acao');
            if (!modal) return;
            tituloEl.textContent = titulo || 'Confirmar ação';
            textoEl.textContent = texto || 'Esta ação não poderá ser desfeita.';
            modal.style.display = 'flex';
            const handleConfirm = () => {
                modal.style.display = 'none';
                confirmarBtn.removeEventListener('click', handleConfirm);
                cancelarBtn.removeEventListener('click', handleCancel);
                if (callback) callback(true);
            };
            const handleCancel = () => {
                modal.style.display = 'none';
                confirmarBtn.removeEventListener('click', handleConfirm);
                cancelarBtn.removeEventListener('click', handleCancel);
                if (callback) callback(false);
            };
            confirmarBtn.addEventListener('click', handleConfirm);
            cancelarBtn.addEventListener('click', handleCancel);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    confirmarBtn.removeEventListener('click', handleConfirm);
                    cancelarBtn.removeEventListener('click', handleCancel);
                    if (callback) callback(false);
                }
            });
        }

        // ============================================
        // 4. SALVAR
        // ============================================
        const form = document.getElementById('form-configuracoes');

        function salvarConfiguracoes(formData) {
            const dados = {};
            for (let [key, value] of formData.entries()) {
                dados[key] = value;
            }
            dados.acao = 'salvar';
            fetch('configuracoes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarToast('✅ ' + data.message, 'sucesso');
                    document.getElementById('status-configuracoes').innerHTML = 
                        '<i class="fa-solid fa-circle" style="color:#22c55e;font-size:0.6rem;"></i> ' +
                        new Date().toLocaleTimeString() + ' - Configurações salvas';
                } else {
                    mostrarToast('❌ ' + data.message, 'erro');
                }
            })
            .catch(() => mostrarToast('❌ Erro ao comunicar com o servidor', 'erro'));
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                salvarConfiguracoes(new FormData(this));
            });
        }

        document.querySelectorAll('.btn-salvar-topo, .btn-principal').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (this.type === 'submit') return;
                e.preventDefault();
                if (form) salvarConfiguracoes(new FormData(form));
            });
        });

        // ============================================
        // 5. IMPORTAR
        // ============================================
        const btnImportar = document.getElementById('btn-importar');
        const inputBackup = document.getElementById('arquivo-backup');
        if (btnImportar && inputBackup) {
            btnImportar.addEventListener('click', () => inputBackup.click());
            inputBackup.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;
                const formData = new FormData();
                formData.append('backup', file);
                mostrarToast('⏳ Importando...', 'info');
                fetch('configuracoes.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarToast('✅ ' + data.message, 'sucesso');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        mostrarToast('❌ ' + data.message, 'erro');
                    }
                })
                .catch(() => mostrarToast('❌ Erro ao importar', 'erro'));
                this.value = '';
            });
        }

        // ============================================
        // 6. AÇÕES DE PERIGO
        // ============================================
        document.querySelectorAll('[data-acao]').forEach(btn => {
            btn.addEventListener('click', function() {
                const acao = this.dataset.acao;
                const titulos = {
                    'limpar-pomodoro': '🧹 Limpar histórico do Pomodoro',
                    'limpar-atividades': '🧹 Limpar atividades concluídas',
                    'apagar-dados': '⚠️ Apagar todos os dados'
                };
                const textos = {
                    'limpar-pomodoro': 'Todos os ciclos do Pomodoro serão removidos.',
                    'limpar-atividades': 'Todas as atividades finalizadas serão removidas.',
                    'apagar-dados': 'TODOS os dados serão removidos. Irreversível!'
                };
                abrirModal(titulos[acao] || 'Confirmar', textos[acao] || 'Irreversível!', function(confirmado) {
                    if (confirmado) {
                        fetch('configuracoes.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({ acao_perigo: acao })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                mostrarToast('✅ ' + data.message, 'sucesso');
                                if (acao === 'apagar-dados') setTimeout(() => location.reload(), 1500);
                            } else {
                                mostrarToast('❌ ' + data.message, 'erro');
                            }
                        })
                        .catch(() => mostrarToast('❌ Erro ao executar', 'erro'));
                    }
                });
            });
        });

        // ============================================
        // 7. TESTAR NOTIFICAÇÃO
        // ============================================
        document.getElementById('btn-testar-notificacao')?.addEventListener('click', function() {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('🔔 Teste do FOAG', { body: 'Notificação de teste!' });
                mostrarToast('✅ Notificação enviada!', 'sucesso');
            } else if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        new Notification('🔔 Teste do FOAG', { body: 'Permissão concedida!' });
                        mostrarToast('✅ Notificação enviada!', 'sucesso');
                    } else {
                        mostrarToast('❌ Permissão negada.', 'erro');
                    }
                });
            } else {
                mostrarToast('❌ Notificações não suportadas.', 'erro');
            }
        });

        // ============================================
        // 8. LOGOUT
        // ============================================
        document.getElementById('icon-sair')?.addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('logout-modal').style.display = 'flex';
        });
        document.getElementById('confirm-logout')?.addEventListener('click', function() {
            document.getElementById('logout-modal').style.display = 'none';
            mostrarToast('👋 Saindo...', 'sucesso');
            setTimeout(() => window.location.href = '../login/login.php', 800);
        });
        document.getElementById('cancel-logout')?.addEventListener('click', function() {
            document.getElementById('logout-modal').style.display = 'none';
        });

        // ============================================
        // 9. PERFIL
        // ============================================
        document.getElementById('icon-perfil')?.addEventListener('click', function(e) {
            e.stopPropagation();
            mostrarToast('👤 Redirecionando...', 'info');
            setTimeout(() => window.location.href = '../perfil/perfil.php', 500);
        });

        // ============================================
        // 10. FOGI
        // ============================================
        const iconFogi = document.getElementById('icon-fogi');
        const fogiModal = document.getElementById('fogi-modal');
        const fogiClose = document.getElementById('fogi-close');
        const fogiIframe = document.getElementById('fogi-iframe');
        if (iconFogi && fogiModal) {
            iconFogi.addEventListener('click', function(e) {
                e.stopPropagation();
                if (fogiModal.style.display === 'flex') {
                    fogiModal.style.display = 'none';
                    if (fogiIframe) fogiIframe.src = 'about:blank';
                } else {
                    fogiModal.style.display = 'flex';
                    if (fogiIframe) fogiIframe.src = 'https://www.youtube.com/embed/dQw4w9WgXcQ';
                }
            });
        }
        if (fogiClose && fogiModal) {
            fogiClose.addEventListener('click', function() {
                fogiModal.style.display = 'none';
                if (fogiIframe) fogiIframe.src = 'about:blank';
            });
        }
        if (fogiModal) {
            fogiModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    if (fogiIframe) fogiIframe.src = 'about:blank';
                }
            });
        }

        // ============================================
        // 11. ESC
        // ============================================
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal, #fogi-modal').forEach(modal => {
                    if (modal.style.display === 'flex') {
                        modal.style.display = 'none';
                        if (modal.id === 'fogi-modal' && fogiIframe) fogiIframe.src = 'about:blank';
                    }
                });
            }
        });

        // ============================================
        // 12. MODO ESCURO (simples)
        // ============================================
        const themeToggle = document.getElementById('themeToggle');
        let darkMode = localStorage.getItem('darkMode') === 'true';
        
        function toggleDarkMode() {
            darkMode = !darkMode;
            document.body.classList.toggle('dark-mode', darkMode);
            localStorage.setItem('darkMode', darkMode);
            themeToggle.className = darkMode ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            // Atualizar select de tema
            const temaSelect = document.getElementById('tema');
            if (temaSelect) temaSelect.value = darkMode ? 'escuro' : 'claro';
        }
        
        if (darkMode) {
            document.body.classList.add('dark-mode');
            themeToggle.className = 'fa-solid fa-sun';
        }
        
        themeToggle?.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDarkMode();
        });
        
        // Sincronizar com select de tema
        document.getElementById('tema')?.addEventListener('change', function() {
            const valor = this.value;
            if (valor === 'escuro' && !darkMode) toggleDarkMode();
            else if (valor === 'claro' && darkMode) toggleDarkMode();
            else if (valor === 'sistema') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark !== darkMode) toggleDarkMode();
            }
        });

        console.log('⚙️ Configurações FOAG carregadas!');
    });
    </script>

    <style>
        /* Modo escuro - estilos adicionais */
        body.dark-mode {
            background: #0f172a !important;
            color: #f1f5f9 !important;
        }
        body.dark-mode .configuracoes-conteudo { background: #0f172a !important; }
        body.dark-mode .configuracao-card { background: #1e293b !important; border-color: #334155 !important; }
        body.dark-mode .card-cabecalho { border-color: #334155 !important; }
        body.dark-mode .card-cabecalho h2 { color: #f1f5f9 !important; }
        body.dark-mode .card-cabecalho p { color: #94a3b8 !important; }
        body.dark-mode .configuracao-texto label,
        body.dark-mode .configuracao-texto strong,
        body.dark-mode .titulo-opcao { color: #f1f5f9 !important; }
        body.dark-mode .configuracao-texto span,
        body.dark-mode .configuracao-texto small { color: #94a3b8 !important; }
        body.dark-mode .configuracao-item { border-color: #334155 !important; }
        body.dark-mode .configuracao-item select {
            background: #0f172a !important;
            color: #f1f5f9 !important;
            border-color: #334155 !important;
        }
        body.dark-mode .card-icone { background: rgba(56,165,255,0.16) !important; color: #60a5fa !important; }
        body.dark-mode .btn-secundario { background: #334155 !important; color: #e2e8f0 !important; }
        body.dark-mode .btn-secundario:hover { background: #475569 !important; }
        body.dark-mode .zona-perigo { background: rgba(220,53,69,0.08) !important; border-color: rgba(220,53,69,0.4) !important; }
        body.dark-mode .zona-perigo h3 { color: #f87171 !important; }
        body.dark-mode .zona-perigo p { color: #fca5a5 !important; }
        body.dark-mode .barra-salvar { background: #1e293b !important; border-color: #334155 !important; }
        body.dark-mode .barra-salvar p { color: #94a3b8 !important; }
        body.dark-mode .sobre-foag { border-color: #334155 !important; }
        body.dark-mode .sobre-foag h3 { color: #f1f5f9 !important; }
        body.dark-mode .sobre-foag p { color: #94a3b8 !important; }
        body.dark-mode .sobre-texto { border-color: #334155 !important; color: #94a3b8 !important; }
        body.dark-mode .sobre-links a {
            background: rgba(56,165,255,0.12) !important;
            border-color: rgba(56,165,255,0.25) !important;
            color: #60a5fa !important;
        }
        body.dark-mode .logo-foag { background: #3b82f6 !important; }
        body.dark-mode .modal-content { background: #1e293b !important; color: #f1f5f9 !important; border: 1px solid #334155 !important; }
        body.dark-mode .modal-content h4 { color: #94a3b8 !important; }
        body.dark-mode .slider { background: #475569 !important; }
        body.dark-mode .switch input:checked + .slider { background: #3b82f6 !important; }
        body.dark-mode .cor input:checked + span { border-color: #0f172a !important; box-shadow: 0 0 0 3px #60a5fa !important; }
        body.dark-mode .cor span { box-shadow: 0 0 0 1px #334155 !important; }
        body.dark-mode .btn-perigo-outline { color: #f87171 !important; border-color: #f87171 !important; }
        body.dark-mode .btn-perigo-outline:hover { background: #f87171 !important; color: #fff !important; }
        body.dark-mode .btn-perigo { background: #dc3545 !important; }
        body.dark-mode .btn-perigo:hover { background: #bd2130 !important; }
        body.dark-mode header.cabecalho { background: #0b1a2e !important; border-bottom: 1px solid #334155 !important; }
        body.dark-mode nav.menu { background: #0b1a2e !important; border-right: 1px solid #334155 !important; }
        body.dark-mode nav.menu a { color: #94a3b8 !important; }
        body.dark-mode nav.menu a:hover, body.dark-mode nav.menu a.active { background: rgba(96,165,250,0.15) !important; color: #60a5fa !important; }
        body.dark-mode footer { background: #020617 !important; color: #94a3b8 !important; border-top: 1px solid #334155 !important; }
        body.dark-mode #toast-configuracoes { background: #1e293b !important; color: #f1f5f9 !important; border: 1px solid #334155 !important; }
        body.dark-mode #fogi-modal .fogi-container { background: #1e293b !important; border: 1px solid #334155 !important; }
        body.dark-mode #fogi-modal .fogi-header { background: #0b1a2e !important; border-bottom: 1px solid #334155 !important; }
        body.dark-mode #fogi-close { background: #334155 !important; color: #f1f5f9 !important; }
        body.dark-mode .configuracoes-topo h1 { color: #f1f5f9 !important; }
        body.dark-mode .configuracoes-topo p { color: #94a3b8 !important; }
        body.dark-mode #cancel-logout { background: #334155 !important; color: #e2e8f0 !important; }
        body.dark-mode .card-cabecalho:hover { background: rgba(96,165,250,0.06) !important; }
    </style>
</body>
</html>
<?php
// explorar.php — Página para explorar perguntas de todos os usuários

session_start();

// ======================================
// VERIFICAR LOGIN
// ======================================

if (empty($_SESSION['codigo_usuario'])) {
    header("Location: ../login/index.php");
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];
$nomeUsuario = $_SESSION['nome_usuario'] ?? 'Usuário';

$current = basename($_SERVER['PHP_SELF']);

// ======================================
// PASTA DO USUÁRIO (para salvar interações)
// ======================================

$baseJsonDir = __DIR__ . '/../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    mkdir($pastaUsuario, 0755, true);
}

// Arquivo de interações (curtidas, salvos)
$arquivoInteracoes = $pastaUsuario . '/interacoes.json';

$interacoesPadrao = [
    'curtidas' => [],
    'salvos' => []
];

if (!file_exists($arquivoInteracoes)) {
    file_put_contents(
        $arquivoInteracoes,
        json_encode($interacoesPadrao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

$interacoes = json_decode(file_get_contents($arquivoInteracoes), true);
if (!is_array($interacoes) || !isset($interacoes['curtidas'])) {
    $interacoes = $interacoesPadrao;
}

// ======================================
// CARREGAR PERGUNTAS DE TODOS OS USUÁRIOS
// ======================================

$todasPerguntas = [];
$usuariosEncontrados = [];

// Escaneia a pasta de usuários
$usuariosDir = $baseJsonDir;
if (is_dir($usuariosDir)) {
    $pastas = scandir($usuariosDir);
    
    foreach ($pastas as $pasta) {
        if ($pasta === '.' || $pasta === '..') continue;
        if (!is_dir($usuariosDir . '/' . $pasta)) continue;
        
        $arquivoChat = $usuariosDir . '/' . $pasta . '/chat.json';
        if (!file_exists($arquivoChat)) continue;
        
        $dados = json_decode(file_get_contents($arquivoChat), true);
        if (!is_array($dados) || !isset($dados['perguntas'])) continue;
        
        foreach ($dados['perguntas'] as $pergunta) {
            // Adiciona o ID do usuário que fez a pergunta
            $pergunta['usuario_id'] = $pasta;
            $todasPerguntas[] = $pergunta;
        }
    }
}

// Ordenar por data (mais recentes primeiro)
usort($todasPerguntas, function($a, $b) {
    return strtotime($b['data'] ?? 0) - strtotime($a['data'] ?? 0);
});

// ======================================
// FILTROS
// ======================================

$filtroMateria = $_GET['materia'] ?? 'todas';
$filtroBusca = trim($_GET['busca'] ?? '');

if ($filtroMateria !== 'todas') {
    $todasPerguntas = array_filter($todasPerguntas, function($p) use ($filtroMateria) {
        return ($p['materia'] ?? 'Geral') === $filtroMateria;
    });
}

if (!empty($filtroBusca)) {
    $buscaLower = mb_strtolower($filtroBusca);
    $todasPerguntas = array_filter($todasPerguntas, function($p) use ($buscaLower) {
        $texto = mb_strtolower($p['texto'] ?? '');
        $autor = mb_strtolower($p['autor'] ?? '');
        return strpos($texto, $buscaLower) !== false || strpos($autor, $buscaLower) !== false;
    });
}

// Reordenar após filtros
$todasPerguntas = array_values($todasPerguntas);

// ======================================
// LISTA DE MATÉRIAS PARA FILTRO
// ======================================

$materias = [];
foreach ($todasPerguntas as $p) {
    $materia = $p['materia'] ?? 'Geral';
    if (!in_array($materia, $materias)) {
        $materias[] = $materia;
    }
}
sort($materias);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Explorar Comunidade - FOAG</title>

    <link rel="stylesheet" href="comunidade.css">
    <link rel="stylesheet" href="../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="dark_comunidade.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ACESSIBILIDADE GLOBAL -->
    <link rel="stylesheet" href="../acessibilidade/acessibilidade.css">
    <script src="../acessibilidade/acessibilidade.js?v=4" defer></script>

    <script src="../m.escuro/dark-mode.js"></script>

      <?php include '../configuracoes/geral.php'; ?>
<script src="<?= get_aparencia_path() ?>"></script>
 <script src="../configuracoes/aparencia.js?v=1"></script>

    <script>
        window.PERGUNTAS_DATA = <?= json_encode(
            $todasPerguntas,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;

        window.USUARIO_CODIGO = <?= json_encode($codigoUsuario, JSON_UNESCAPED_UNICODE); ?>;
        window.USUARIO_NOME = <?= json_encode($nomeUsuario, JSON_UNESCAPED_UNICODE); ?>;
        window.INTERACOES = <?= json_encode($interacoes, JSON_UNESCAPED_UNICODE); ?>;
    </script>

    <style>
        /* ======================================
           ORGANIZAÇÃO DA PÁGINA
        ====================================== */

        .main-content {
            flex: 1;
            min-width: 0;
            width: 100%;
            padding: 25px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
            gap: 25px;
        }

        /* ======================================
           CARD DO EXPLORAR
        ====================================== */

        .explorar-card {
            width: 100%;
            background: #ffffff;
            border: 1px solid #e3e8ef;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .explorar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eef2f7;
        }

        .explorar-header h2 {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #222222;
            font-size: 22px;
        }

        .explorar-header h2 i {
            color: #38a5ff;
        }

        .explorar-header p {
            color: #64748b;
            font-size: 14px;
        }

        .explorar-stats {
            display: flex;
            gap: 20px;
            color: #64748b;
            font-size: 13px;
        }

        .explorar-stats span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .explorar-stats i {
            color: #38a5ff;
        }

        /* ======================================
           FILTROS E BUSCA
        ====================================== */

        .filtros-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 25px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #eef2f7;
        }

        .filtros-container .busca-wrapper {
            flex: 1;
            min-width: 200px;
            display: flex;
            gap: 10px;
        }

        .filtros-container .busca-wrapper input {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid #d8dee7;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s ease;
            background: #ffffff;
            color: #232323;
        }

        .filtros-container .busca-wrapper input:focus {
            border-color: #38a5ff;
            box-shadow: 0 0 0 3px rgba(56, 165, 255, 0.1);
        }

        .filtros-container .busca-wrapper input::placeholder {
            color: #94a3b8;
        }

        .filtros-container .busca-wrapper button {
            padding: 10px 18px;
            background: #38a5ff;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .filtros-container .busca-wrapper button:hover {
            background: #168fe8;
        }

        .filtros-container .filtro-materia {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filtros-container .filtro-materia label {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
        }

        .filtros-container .filtro-materia select {
            padding: 9px 14px;
            border: 1px solid #d8dee7;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            background: #ffffff;
            color: #232323;
            cursor: pointer;
        }

        .filtros-container .filtro-materia select:focus {
            border-color: #38a5ff;
        }

        .btn-limpar-filtros {
            padding: 9px 16px;
            background: #eef2f7;
            color: #475569;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.2s ease;
        }

        .btn-limpar-filtros:hover {
            background: #e2e8f0;
        }

        /* ======================================
           LISTA DE PERGUNTAS
        ====================================== */

        .perguntas-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .pergunta-card {
            background: #ffffff;
            border: 1px solid #e3e8ef;
            border-radius: 12px;
            padding: 20px;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .pergunta-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .pergunta-card .pergunta-topo {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .pergunta-card .pergunta-autor {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .pergunta-card .pergunta-autor .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #38a5ff;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }

        .pergunta-card .pergunta-autor .nome {
            font-weight: 600;
            color: #222222;
            font-size: 14px;
        }

        .pergunta-card .pergunta-autor .data {
            color: #94a3b8;
            font-size: 12px;
        }

        .pergunta-card .pergunta-autor .usuario-tag {
            font-size: 11px;
            color: #94a3b8;
            background: #eef2f7;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .pergunta-card .pergunta-materia {
            padding: 3px 10px;
            border-radius: 20px;
            background: #eef2f7;
            color: #475569;
            font-size: 12px;
            font-weight: 500;
            flex-shrink: 0;
        }

        .pergunta-card .pergunta-texto {
            color: #333333;
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 12px;
            word-break: break-word;
            padding-left: 46px;
        }

        .pergunta-card .pergunta-rodape {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding-left: 46px;
            padding-top: 10px;
            border-top: 1px solid #eef2f7;
        }

        .pergunta-card .pergunta-acoes {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .pergunta-card .pergunta-acoes button {
            background: none;
            border: none;
            color: #64748b;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 6px;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .pergunta-card .pergunta-acoes button:hover {
            background: #f1f5f9;
        }

        .pergunta-card .pergunta-acoes button i {
            font-size: 14px;
        }

        .pergunta-card .pergunta-acoes .btn-curtir {
            color: #64748b;
        }

        .pergunta-card .pergunta-acoes .btn-curtir.curtido {
            color: #ef4444;
        }

        .pergunta-card .pergunta-acoes .btn-curtir.curtido i {
            font-weight: 900;
        }

        .pergunta-card .pergunta-acoes .btn-curtir:hover {
            background: #fef2f2;
            color: #ef4444;
        }

        .pergunta-card .pergunta-acoes .btn-salvar {
            color: #64748b;
        }

        .pergunta-card .pergunta-acoes .btn-salvar.salvo {
            color: #f59e0b;
        }

        .pergunta-card .pergunta-acoes .btn-salvar.salvo i {
            font-weight: 900;
        }

        .pergunta-card .pergunta-acoes .btn-salvar:hover {
            background: #fffbeb;
            color: #f59e0b;
        }

        .pergunta-card .pergunta-acoes .btn-ver-respostas {
            color: #38a5ff;
        }

        .pergunta-card .pergunta-acoes .btn-ver-respostas:hover {
            background: #eef8ff;
        }

        .pergunta-card .pergunta-origem {
            font-size: 12px;
            color: #94a3b8;
        }

        .pergunta-card .pergunta-origem i {
            margin-right: 4px;
        }

        /* ======================================
           RESPOSTAS (expandidas)
        ====================================== */

        .respostas-expandidas {
            margin-top: 12px;
            padding-left: 46px;
            display: none;
        }

        .respostas-expandidas.visivel {
            display: block;
        }

        .respostas-expandidas .resposta-item {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .respostas-expandidas .resposta-item:last-child {
            border-bottom: none;
        }

        .respostas-expandidas .resposta-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2px;
        }

        .respostas-expandidas .resposta-header .avatar-pequeno {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #94a3b8;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 10px;
            flex-shrink: 0;
        }

        .respostas-expandidas .resposta-header .nome {
            font-weight: 500;
            color: #222222;
            font-size: 13px;
        }

        .respostas-expandidas .resposta-header .data {
            color: #94a3b8;
            font-size: 11px;
        }

        .respostas-expandidas .resposta-texto {
            color: #444444;
            font-size: 14px;
            line-height: 1.6;
            margin-top: 2px;
        }

        .respostas-expandidas .sem-respostas {
            color: #94a3b8;
            font-size: 13px;
            padding: 8px 0;
            font-style: italic;
        }

        /* ======================================
           SEM RESULTADOS
        ====================================== */

        .sem-resultados {
            padding: 50px 20px;
            text-align: center;
            color: #94a3b8;
        }

        .sem-resultados i {
            font-size: 48px;
            color: #d8dee7;
            margin-bottom: 15px;
            display: block;
        }

        .sem-resultados h3 {
            color: #64748b;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .sem-resultados p {
            font-size: 14px;
        }

        /* ======================================
           MODO ESCURO
        ====================================== */

        body.dark-mode .explorar-card {
            background: #1e293b !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3) !important;
        }

        body.dark-mode .explorar-header {
            border-color: #334155 !important;
        }

        body.dark-mode .explorar-header h2 {
            color: #e2e8f0 !important;
        }

        body.dark-mode .explorar-header p {
            color: #94a3b8 !important;
        }

        body.dark-mode .explorar-stats {
            color: #94a3b8 !important;
        }

        body.dark-mode .filtros-container {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        body.dark-mode .filtros-container .busca-wrapper input {
            background: #1e293b !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
        }

        body.dark-mode .filtros-container .busca-wrapper input:focus {
            border-color: #669ada !important;
            box-shadow: 0 0 0 3px rgba(102, 154, 218, 0.2) !important;
        }

        body.dark-mode .filtros-container .busca-wrapper input::placeholder {
            color: #64748b !important;
        }

        body.dark-mode .filtros-container .filtro-materia select {
            background: #1e293b !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
        }

        body.dark-mode .filtros-container .filtro-materia select:focus {
            border-color: #669ada !important;
        }

        body.dark-mode .filtros-container .filtro-materia label {
            color: #94a3b8 !important;
        }

        body.dark-mode .btn-limpar-filtros {
            background: #334155 !important;
            color: #94a3b8 !important;
        }

        body.dark-mode .btn-limpar-filtros:hover {
            background: #475569 !important;
        }

        body.dark-mode .pergunta-card {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .pergunta-card .pergunta-autor .nome {
            color: #e2e8f0 !important;
        }

        body.dark-mode .pergunta-card .pergunta-autor .data {
            color: #64748b !important;
        }

        body.dark-mode .pergunta-card .pergunta-autor .usuario-tag {
            background: #334155 !important;
            color: #94a3b8 !important;
        }

        body.dark-mode .pergunta-card .pergunta-materia {
            background: #334155 !important;
            color: #94a3b8 !important;
        }

        body.dark-mode .pergunta-card .pergunta-texto {
            color: #e2e8f0 !important;
        }

        body.dark-mode .pergunta-card .pergunta-rodape {
            border-color: #334155 !important;
        }

        body.dark-mode .pergunta-card .pergunta-acoes button {
            color: #94a3b8 !important;
        }

        body.dark-mode .pergunta-card .pergunta-acoes button:hover {
            background: #334155 !important;
        }

        body.dark-mode .pergunta-card .pergunta-acoes .btn-curtir:hover {
            background: #450a0a !important;
            color: #f87171 !important;
        }

        body.dark-mode .pergunta-card .pergunta-acoes .btn-curtir.curtido {
            color: #f87171 !important;
        }

        body.dark-mode .pergunta-card .pergunta-acoes .btn-salvar:hover {
            background: #451a03 !important;
            color: #fbbf24 !important;
        }

        body.dark-mode .pergunta-card .pergunta-acoes .btn-salvar.salvo {
            color: #fbbf24 !important;
        }

        body.dark-mode .pergunta-card .pergunta-acoes .btn-ver-respostas:hover {
            background: #1a2a3a !important;
            color: #669ada !important;
        }

        body.dark-mode .pergunta-card .pergunta-origem {
            color: #64748b !important;
        }

        body.dark-mode .respostas-expandidas .resposta-item {
            border-color: #334155 !important;
        }

        body.dark-mode .respostas-expandidas .resposta-header .nome {
            color: #e2e8f0 !important;
        }

        body.dark-mode .respostas-expandidas .resposta-header .data {
            color: #64748b !important;
        }

        body.dark-mode .respostas-expandidas .resposta-texto {
            color: #cbd5e1 !important;
        }

        body.dark-mode .respostas-expandidas .sem-respostas {
            color: #64748b !important;
        }

        body.dark-mode .sem-resultados i {
            color: #334155 !important;
        }

        body.dark-mode .sem-resultados h3 {
            color: #94a3b8 !important;
        }

        body.dark-mode .sem-resultados p {
            color: #64748b !important;
        }

        /* ======================================
           RESPONSIVIDADE
        ====================================== */

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .explorar-card {
                padding: 18px;
            }

            .explorar-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .explorar-stats {
                flex-wrap: wrap;
                gap: 10px;
            }

            .filtros-container {
                flex-direction: column;
            }

            .filtros-container .busca-wrapper {
                flex-direction: column;
            }

            .filtros-container .busca-wrapper button {
                width: 100%;
            }

            .filtros-container .filtro-materia {
                flex-wrap: wrap;
            }

            .pergunta-card .pergunta-texto {
                padding-left: 0;
            }

            .pergunta-card .pergunta-rodape {
                padding-left: 0;
                flex-direction: column;
                align-items: flex-start;
            }

            .pergunta-card .pergunta-topo {
                flex-wrap: wrap;
            }

            .respostas-expandidas {
                padding-left: 0;
            }
        }

        @media (max-width: 480px) {
            .explorar-header h2 {
                font-size: 18px;
            }

            .pergunta-card {
                padding: 15px;
            }

            .pergunta-card .pergunta-texto {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

    <!-- ======================================
         CABEÇALHO
    ======================================= -->

    <header class="cabecalho">
        FOAG

        <div class="header-icons">
            <i id="icon-configuracoes" class="fa-solid fa-gear" title="Configurações"></i>
            <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
            <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
        </div>
    </header>

    <div class="container">

        <!-- ======================================
             MENU
        ======================================= -->

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

            <a href="../estudos/estudos.php" class="<?= $current === 'estudos.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-graduation-cap"></i> Estudos
            </a>

            <a href="../notas/notas.php" class="<?= $current === 'notas.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-check-double"></i> Boletim
            </a>

            <a href="../comunidade/comunidade.php" class="<?= $current === 'comunidade.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-comments"></i> Comunidade
            </a>

            <a href="../comunidade/explorar.php" class="<?= $current === 'explorar.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-compass"></i> Explorar
            </a>

            <a href="../loja/loja.php" class="<?= $current === 'loja.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-store"></i> Loja
            </a>

            <a href="../rank/rank.php" class="<?= $current === 'rank.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-trophy"></i> Ranking
            </a>
        </nav>

        <!-- ======================================
             CONTEÚDO
        ======================================= -->

        <main class="main-content" id="conteudo-principal" tabindex="-1">

            <section class="explorar-card">

                <div class="explorar-header">
                    <div>
                        <h2>
                            <i class="fa-solid fa-compass"></i>
                            Explorar Comunidade
                        </h2>
                        <p>Veja perguntas de todos os alunos do FOAG e ajude com respostas.</p>
                    </div>
                    <div class="explorar-stats">
                        <span><i class="fa-regular fa-message"></i> <span id="total-perguntas"><?= count($todasPerguntas) ?></span> perguntas</span>
                        <span><i class="fa-regular fa-comment"></i> <span id="total-respostas">0</span> respostas</span>
                        <span><i class="fa-regular fa-user"></i> <span id="total-usuarios"><?= count($usuariosEncontrados) ?></span> usuários</span>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filtros-container">
                    <div class="busca-wrapper">
                        <input type="text" id="busca-input" placeholder="Buscar perguntas ou autores..." value="<?= htmlspecialchars($filtroBusca) ?>">
                        <button id="btn-buscar"><i class="fa-solid fa-search"></i> Buscar</button>
                    </div>
                    <div class="filtro-materia">
                        <label for="filtro-materia"><i class="fa-solid fa-tag"></i> Matéria:</label>
                        <select id="filtro-materia">
                            <option value="todas" <?= $filtroMateria === 'todas' ? 'selected' : '' ?>>Todas</option>
                            <?php foreach ($materias as $materia): ?>
                                <option value="<?= htmlspecialchars($materia) ?>" <?= $filtroMateria === $materia ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($materia) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn-limpar-filtros" id="btn-limpar-filtros">
                        <i class="fa-solid fa-rotate-left"></i> Limpar
                    </button>
                </div>

                <!-- Lista de Perguntas -->
                <div class="perguntas-grid" id="perguntas-grid">
                    <?php if (count($todasPerguntas) === 0): ?>
                        <div class="sem-resultados">
                            <i class="fa-regular fa-face-frown"></i>
                            <h3>Nenhuma pergunta encontrada</h3>
                            <p>
                                <?php if (!empty($filtroBusca) || $filtroMateria !== 'todas'): ?>
                                    Tente ajustar os filtros de busca.
                                <?php else: ?>
                                    Ainda não há perguntas na comunidade. Seja o primeiro a perguntar!
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($todasPerguntas as $pergunta): 
                            $respostas = $pergunta['respostas'] ?? [];
                            $totalResp = count($respostas);
                            $ehDoUsuario = ($pergunta['usuario_id'] ?? '') === $codigoUsuario;
                        ?>
                            <div class="pergunta-card" data-id="<?= htmlspecialchars($pergunta['id'] ?? '') ?>">
                                <div class="pergunta-topo">
                                    <div class="pergunta-autor">
                                        <div class="avatar"><?= htmlspecialchars(obterIniciais($pergunta['autor'] ?? '?')) ?></div>
                                        <div>
                                            <span class="nome"><?= htmlspecialchars($pergunta['autor'] ?? 'Anônimo') ?></span>
                                            <span class="data"><?= formatarData($pergunta['data'] ?? '') ?></span>
                                            <?php if ($ehDoUsuario): ?>
                                                <span class="usuario-tag"><i class="fa-regular fa-user"></i> Você</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span class="pergunta-materia"><?= htmlspecialchars($pergunta['materia'] ?? 'Geral') ?></span>
                                </div>
                                <div class="pergunta-texto"><?= htmlspecialchars($pergunta['texto'] ?? '') ?></div>
                                <div class="pergunta-rodape">
                                    <div class="pergunta-acoes">
                                        <button class="btn-curtir" data-id="<?= htmlspecialchars($pergunta['id'] ?? '') ?>">
                                            <i class="fa-regular fa-heart"></i> <span class="curtidas-count">0</span>
                                        </button>
                                        <button class="btn-salvar" data-id="<?= htmlspecialchars($pergunta['id'] ?? '') ?>">
                                            <i class="fa-regular fa-bookmark"></i> Salvar
                                        </button>
                                        <button class="btn-ver-respostas" data-id="<?= htmlspecialchars($pergunta['id'] ?? '') ?>">
                                            <i class="fa-regular fa-comment"></i> <?= $totalResp ?> resposta<?= $totalResp !== 1 ? 's' : '' ?>
                                        </button>
                                    </div>
                                    <div class="pergunta-origem">
                                        <i class="fa-regular fa-user"></i> 
                                        <?= $ehDoUsuario ? 'Sua pergunta' : 'Pergunta de ' . htmlspecialchars($pergunta['autor'] ?? 'outro usuário') ?>
                                    </div>
                                </div>

                                <!-- Respostas expandidas -->
                                <div class="respostas-expandidas" id="respostas-<?= htmlspecialchars($pergunta['id'] ?? '') ?>">
                                    <?php if ($totalResp > 0): ?>
                                        <?php foreach ($respostas as $resposta): ?>
                                            <div class="resposta-item">
                                                <div class="resposta-header">
                                                    <div class="avatar-pequeno"><?= htmlspecialchars(obterIniciais($resposta['autor'] ?? '?')) ?></div>
                                                    <span class="nome"><?= htmlspecialchars($resposta['autor'] ?? 'Anônimo') ?></span>
                                                    <span class="data"><?= formatarData($resposta['data'] ?? '') ?></span>
                                                </div>
                                                <div class="resposta-texto"><?= htmlspecialchars($resposta['texto'] ?? '') ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="sem-respostas">Nenhuma resposta ainda. Seja o primeiro a responder!</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </section>
        </main>
    </div>

    <!-- ======================================
         MODAL: LOGOUT
    ======================================= -->

    <div id="logout-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="titulo-logout">
        <div class="modal-content">
            <h3 id="titulo-logout">Ah... já vai?</h3>
            <h4>Tem certeza de que deseja sair?</h4>
            <div class="modal-buttons">
                <button id="confirm-logout">Sim</button>
                <button id="cancel-logout">Cancelar</button>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2025 FOAG. Todos os direitos reservados.
    </footer>

    <!-- ======================================
         JAVASCRIPT
    ======================================= -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            'use strict';

            // =================================================
            // CONFIGURAÇÕES
            // =================================================

            let perguntas = window.PERGUNTAS_DATA || [];
            let interacoes = window.INTERACOES || { curtidas: [], salvos: [] };
            const usuarioCodigo = window.USUARIO_CODIGO || '';
            const usuarioNome = window.USUARIO_NOME || 'Usuário';

            // =================================================
            // FUNÇÕES AUXILIARES
            // =================================================

            function obterIniciais(nome) {
                if (!nome) return '?';
                const partes = nome.trim().split(' ');
                if (partes.length === 1) return partes[0].charAt(0).toUpperCase();
                return (partes[0].charAt(0) + partes[partes.length - 1].charAt(0)).toUpperCase();
            }

            function formatarData(data) {
                if (!data) return 'Data desconhecida';
                try {
                    const d = new Date(data);
                    if (isNaN(d.getTime())) return 'Data inválida';
                    return d.toLocaleString('pt-BR', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch {
                    return 'Data inválida';
                }
            }

            // =================================================
            // ELEMENTOS
            // =================================================

            const buscaInput = document.getElementById('busca-input');
            const btnBuscar = document.getElementById('btn-buscar');
            const filtroMateria = document.getElementById('filtro-materia');
            const btnLimparFiltros = document.getElementById('btn-limpar-filtros');
            const perguntasGrid = document.getElementById('perguntas-grid');

            // =================================================
            // INTERAÇÕES (Curtir / Salvar)
            // =================================================

            function toggleCurtir(perguntaId) {
                const index = interacoes.curtidas.indexOf(perguntaId);
                if (index > -1) {
                    interacoes.curtidas.splice(index, 1);
                } else {
                    interacoes.curtidas.push(perguntaId);
                }
                salvarInteracoes();
                atualizarBotoes();
            }

            function toggleSalvar(perguntaId) {
                const index = interacoes.salvos.indexOf(perguntaId);
                if (index > -1) {
                    interacoes.salvos.splice(index, 1);
                } else {
                    interacoes.salvos.push(perguntaId);
                }
                salvarInteracoes();
                atualizarBotoes();
            }

            function isCurtido(perguntaId) {
                return interacoes.curtidas.includes(perguntaId);
            }

            function isSalvo(perguntaId) {
                return interacoes.salvos.includes(perguntaId);
            }

            function salvarInteracoes() {
                fetch('salvar_interacoes.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(interacoes)
                }).catch(console.error);
            }

            function atualizarBotoes() {
                document.querySelectorAll('.btn-curtir').forEach(btn => {
                    const id = btn.dataset.id;
                    const icon = btn.querySelector('i');
                    const countSpan = btn.querySelector('.curtidas-count');
                    
                    if (isCurtido(id)) {
                        btn.classList.add('curtido');
                        icon.className = 'fa-solid fa-heart';
                    } else {
                        btn.classList.remove('curtido');
                        icon.className = 'fa-regular fa-heart';
                    }
                });

                document.querySelectorAll('.btn-salvar').forEach(btn => {
                    const id = btn.dataset.id;
                    const icon = btn.querySelector('i');
                    
                    if (isSalvo(id)) {
                        btn.classList.add('salvo');
                        icon.className = 'fa-solid fa-bookmark';
                    } else {
                        btn.classList.remove('salvo');
                        icon.className = 'fa-regular fa-bookmark';
                    }
                });
            }

            // =================================================
            // EXPANDIR RESPOSTAS
            // =================================================

            function toggleRespostas(perguntaId) {
                const container = document.getElementById(`respostas-${perguntaId}`);
                if (container) {
                    container.classList.toggle('visivel');
                }
            }

            // =================================================
            // FILTROS
            // =================================================

            function aplicarFiltros() {
                const busca = buscaInput.value.trim().toLowerCase();
                const materia = filtroMateria.value;

                let url = 'explorar.php?';
                if (busca) url += 'busca=' + encodeURIComponent(busca) + '&';
                if (materia !== 'todas') url += 'materia=' + encodeURIComponent(materia);

                window.location.href = url;
            }

            // =================================================
            // EVENTOS
            // =================================================

            // Buscar
            btnBuscar?.addEventListener('click', aplicarFiltros);
            buscaInput?.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    aplicarFiltros();
                }
            });

            // Filtro matéria
            filtroMateria?.addEventListener('change', aplicarFiltros);

            // Limpar filtros
            btnLimparFiltros?.addEventListener('click', function() {
                window.location.href = 'explorar.php';
            });

            // Curtir
            document.querySelectorAll('.btn-curtir').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const id = this.dataset.id;
                    toggleCurtir(id);
                });
            });

            // Salvar
            document.querySelectorAll('.btn-salvar').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const id = this.dataset.id;
                    toggleSalvar(id);
                });
            });

            // Ver respostas
            document.querySelectorAll('.btn-ver-respostas').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const id = this.dataset.id;
                    toggleRespostas(id);
                });
            });

            // =================================================
            // ÍCONES DO HEADER
            // =================================================

            document.getElementById('icon-configuracoes')?.addEventListener('click', function() {
                window.location.href = '../configuracoes/configuracoes.php';
            });

            document.getElementById('icon-perfil')?.addEventListener('click', function() {
                window.location.href = '../perfil/perfil.php';
            });

            const iconSair = document.getElementById('icon-sair');
            const logoutModal = document.getElementById('logout-modal');
            const confirmLogout = document.getElementById('confirm-logout');
            const cancelLogout = document.getElementById('cancel-logout');

            if (iconSair && logoutModal) {
                iconSair.addEventListener('click', function() {
                    logoutModal.style.display = 'flex';
                });
            }

            if (confirmLogout) {
                confirmLogout.addEventListener('click', function() {
                    window.location.href = '../login/index.php';
                });
            }

            if (cancelLogout && logoutModal) {
                cancelLogout.addEventListener('click', function() {
                    logoutModal.style.display = 'none';
                });

                logoutModal.addEventListener('click', function(e) {
                    if (e.target === logoutModal) {
                        logoutModal.style.display = 'none';
                    }
                });
            }

            // =================================================
            // INICIALIZAR
            // =================================================

            // Atualizar estado dos botões
            atualizarBotoes();

            // Contar respostas
            let totalRespostas = 0;
            perguntas.forEach(p => {
                totalRespostas += (p.respostas || []).length;
            });
            document.getElementById('total-respostas').textContent = totalRespostas;

            console.log('Explorar FOAG carregado ✅');
            console.log('Perguntas:', perguntas.length);
            console.log('Respostas:', totalRespostas);
        });
    </script>

</body>

</html>

<?php
// ======================================
// FUNÇÕES AUXILIARES (PHP)
// ======================================

function obterIniciais($nome) {
    if (empty($nome)) return '?';
    $partes = explode(' ', trim($nome));
    if (count($partes) === 1) return strtoupper(substr($partes[0], 0, 1));
    return strtoupper(substr($partes[0], 0, 1) . substr(end($partes), 0, 1));
}

function formatarData($data) {
    if (empty($data)) return 'Data desconhecida';
    try {
        $d = new DateTime($data);
        return $d->format('d/m/Y H:i');
    } catch (Exception $e) {
        return 'Data inválida';
    }
}
?>
<?php
// comunidade.php — Página principal da comunidade com censura

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
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir = __DIR__ . '/../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    mkdir($pastaUsuario, 0755, true);
}

// ======================================
// CARREGAR PALAVRAS PROIBIDAS
// ======================================

$palavrasProibidas = require_once 'palavram.php';

// ======================================
// FUNÇÃO DE CENSURA
// ======================================

function censurarTexto($texto, $palavrasProibidas) {
    if (empty($texto)) return $texto;
    
    $textoOriginal = $texto;
    
    // Ordenar por tamanho (maiores primeiro) para evitar problemas com palavras compostas
    usort($palavrasProibidas, function($a, $b) {
        return strlen($b) - strlen($a);
    });
    
    foreach ($palavrasProibidas as $palavra) {
        $palavra = trim($palavra);
        if (empty($palavra)) continue;
        
        // Criar padrão para encontrar a palavra com variações de case
        $padrao = '/\b' . preg_quote($palavra, '/') . '\b/i';
        
        // Substituir por asteriscos mantendo o comprimento original
        $texto = preg_replace_callback($padrao, function($matches) {
            return str_repeat('*', mb_strlen($matches[0]));
        }, $texto);
        
        // Também substituir variações com números (leet speak) - apenas se a palavra original tiver letras
        if (preg_match('/[a-zA-Z]/', $palavra)) {
            // Gerar variações leet speak
            $leetVariations = gerarLeetVariations($palavra);
            foreach ($leetVariations as $leet) {
                $padraoLeet = '/\b' . preg_quote($leet, '/') . '\b/i';
                $texto = preg_replace_callback($padraoLeet, function($matches) {
                    return str_repeat('*', mb_strlen($matches[0]));
                }, $texto);
            }
        }
    }
    
    return $texto;
}

function gerarLeetVariations($palavra) {
    $leetMap = [
        'a' => ['4', '@'],
        'e' => ['3'],
        'i' => ['1', '!'],
        'o' => ['0'],
        's' => ['5', '$'],
        't' => ['7'],
        'b' => ['8'],
        'g' => ['6', '9'],
        'l' => ['1'],
        'z' => ['2'],
        'c' => ['('],
        'u' => ['v'],
        'v' => ['u'],
    ];
    
    $variacoes = [$palavra];
    $palavraLower = mb_strtolower($palavra);
    
    // Gerar combinações de substituições
    $chars = str_split($palavraLower);
    $substituicoes = [];
    
    foreach ($chars as $i => $char) {
        if (isset($leetMap[$char])) {
            $substituicoes[$i] = $leetMap[$char];
        }
    }
    
    // Se não houver substituições, retorna apenas a palavra original
    if (empty($substituicoes)) {
        return $variacoes;
    }
    
    // Gerar combinações
    $keys = array_keys($substituicoes);
    $totalCombinacoes = 1;
    foreach ($keys as $key) {
        $totalCombinacoes *= count($substituicoes[$key]);
    }
    
    // Limitar a 50 combinações para não sobrecarregar
    if ($totalCombinacoes > 50) {
        // Pegar apenas as primeiras substituições
        $keys = array_slice($keys, 0, 3);
        $totalCombinacoes = 1;
        foreach ($keys as $key) {
            $totalCombinacoes *= count($substituicoes[$key]);
        }
    }
    
    // Gerar combinações recursivamente
    function gerarCombinacoes($chars, $substituicoes, $keys, $index = 0) {
        if ($index >= count($keys)) {
            return [implode('', $chars)];
        }
        
        $key = $keys[$index];
        $opcoes = $substituicoes[$key];
        $resultados = [];
        
        foreach ($opcoes as $opcao) {
            $charsCopy = $chars;
            $charsCopy[$key] = $opcao;
            $resultados = array_merge($resultados, gerarCombinacoes($charsCopy, $substituicoes, $keys, $index + 1));
        }
        
        return $resultados;
    }
    
    $chars = str_split($palavraLower);
    $combinacoes = gerarCombinacoes($chars, $substituicoes, $keys);
    
    // Adicionar combinações únicas
    foreach ($combinacoes as $combinacao) {
        if (!in_array($combinacao, $variacoes)) {
            $variacoes[] = $combinacao;
        }
    }
    
    return $variacoes;
}

// ======================================
// DADOS DO CHAT
// ======================================

$arquivoChat = $pastaUsuario . '/chat.json';

$estruturaChatPadrao = [
    'perguntas' => []
];

if (!file_exists($arquivoChat)) {
    file_put_contents(
        $arquivoChat,
        json_encode(
            $estruturaChatPadrao,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        )
    );
}

$chatData = json_decode(
    file_get_contents($arquivoChat),
    true
);

if (!is_array($chatData) || !isset($chatData['perguntas'])) {
    $chatData = $estruturaChatPadrao;
}

// ======================================
// INTERAÇÕES DO USUÁRIO
// ======================================

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
            $pergunta['usuario_id'] = $pasta;
            $pergunta['usuario_nome'] = $pergunta['autor'] ?? 'Anônimo';
            
            // Censurar o texto da pergunta
            if (isset($pergunta['texto'])) {
                $pergunta['texto_original'] = $pergunta['texto'];
                $pergunta['texto'] = censurarTexto($pergunta['texto'], $palavrasProibidas);
            }
            
            // Censurar respostas
            if (isset($pergunta['respostas']) && is_array($pergunta['respostas'])) {
                foreach ($pergunta['respostas'] as &$resposta) {
                    if (isset($resposta['texto'])) {
                        $resposta['texto_original'] = $resposta['texto'];
                        $resposta['texto'] = censurarTexto($resposta['texto'], $palavrasProibidas);
                    }
                }
            }
            
            $todasPerguntas[] = $pergunta;
        }
    }
}

// Também censurar as perguntas do usuário atual
if (isset($chatData['perguntas']) && is_array($chatData['perguntas'])) {
    foreach ($chatData['perguntas'] as &$pergunta) {
        if (isset($pergunta['texto']) && !isset($pergunta['texto_original'])) {
            $pergunta['texto_original'] = $pergunta['texto'];
            $pergunta['texto'] = censurarTexto($pergunta['texto'], $palavrasProibidas);
        }
        if (isset($pergunta['respostas']) && is_array($pergunta['respostas'])) {
            foreach ($pergunta['respostas'] as &$resposta) {
                if (isset($resposta['texto']) && !isset($resposta['texto_original'])) {
                    $resposta['texto_original'] = $resposta['texto'];
                    $resposta['texto'] = censurarTexto($resposta['texto'], $palavrasProibidas);
                }
            }
        }
    }
}

// Ordenar por data
usort($todasPerguntas, function($a, $b) {
    return strtotime($b['data'] ?? 0) - strtotime($a['data'] ?? 0);
});

// ======================================
// FILTROS
// ======================================

$filtroMateria = $_GET['materia'] ?? 'todas';
$filtroBusca = trim($_GET['busca'] ?? '');
$abaAtiva = $_GET['aba'] ?? 'minhas';

if ($abaAtiva === 'explorar') {
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
            $materia = mb_strtolower($p['materia'] ?? '');
            return strpos($texto, $buscaLower) !== false || 
                   strpos($autor, $buscaLower) !== false ||
                   strpos($materia, $buscaLower) !== false;
        });
    }
}

$todasPerguntas = array_values($todasPerguntas);

// ======================================
// LISTA DE MATÉRIAS
// ======================================

$materias = ['Geral'];
foreach ($todasPerguntas as $p) {
    $materia = $p['materia'] ?? 'Geral';
    if (!in_array($materia, $materias)) {
        $materias[] = $materia;
    }
}
sort($materias);

// ======================================
// FUNÇÕES AUXILIARES
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

function isCurtido($perguntaId, $interacoes) {
    return in_array($perguntaId, $interacoes['curtidas'] ?? []);
}

function isSalvo($perguntaId, $interacoes) {
    return in_array($perguntaId, $interacoes['salvos'] ?? []);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Comunidade - FOAG</title>

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

    <script>
        window.CHAT_DATA = <?= json_encode(
            $chatData,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;

        window.TODAS_PERGUNTAS = <?= json_encode(
            $todasPerguntas,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;

        window.CHAT_SAVE_URL = "salvar_chat.php";
        window.INTERACOES_SAVE_URL = "salvar_interacoes.php";
        window.USUARIO_NOME = <?= json_encode($nomeUsuario, JSON_UNESCAPED_UNICODE); ?>;
        window.USUARIO_CODIGO = <?= json_encode($codigoUsuario, JSON_UNESCAPED_UNICODE); ?>;
        window.INTERACOES = <?= json_encode($interacoes, JSON_UNESCAPED_UNICODE); ?>;
        window.ABA_ATIVA = <?= json_encode($abaAtiva, JSON_UNESCAPED_UNICODE); ?>;
        window.FILTRO_MATERIA = <?= json_encode($filtroMateria, JSON_UNESCAPED_UNICODE); ?>;
        window.FILTRO_BUSCA = <?= json_encode($filtroBusca, JSON_UNESCAPED_UNICODE); ?>;
        
        // Palavras proibidas para censura no front-end
        window.PALAVRAS_PROIBIDAS = <?= json_encode($palavrasProibidas, JSON_UNESCAPED_UNICODE); ?>;
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
           CARD DO CHAT
        ====================================== */

        .chat-card {
            width: 100%;
            background: #ffffff;
            border: 1px solid #e3e8ef;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eef2f7;
        }

        .chat-header h2 {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #222222;
            font-size: 22px;
        }

        .chat-header h2 i {
            color: #38a5ff;
        }

        .chat-header p {
            color: #64748b;
            font-size: 14px;
        }

        .chat-stats {
            display: flex;
            gap: 20px;
            color: #64748b;
            font-size: 13px;
        }

        .chat-stats span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .chat-stats i {
            color: #38a5ff;
        }

        /* ======================================
           AVISO DE CENSURA
        ====================================== */

        .censure-notice {
            background: #fefce8;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 8px 14px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #92400e;
            font-size: 13px;
        }

        .censure-notice i {
            color: #f59e0b;
            font-size: 16px;
        }

        body.dark-mode .censure-notice {
            background: #1e293b !important;
            border-color: #f59e0b !important;
            color: #fbbf24 !important;
        }

        /* ======================================
           ABAS
        ====================================== */

        .abas-container {
            display: flex;
            gap: 4px;
            margin-bottom: 25px;
            border-bottom: 2px solid #eef2f7;
            padding-bottom: 0;
        }

        .aba-btn {
            padding: 10px 24px;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            color: #64748b;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .aba-btn:hover {
            color: #38a5ff;
            background: #f8fafc;
        }

        .aba-btn.ativo {
            color: #38a5ff;
            border-bottom-color: #38a5ff;
        }

        .aba-btn i {
            font-size: 16px;
        }

        .aba-btn .badge {
            background: #eef2f7;
            color: #64748b;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 500;
        }

        .aba-btn.ativo .badge {
            background: #eef8ff;
            color: #38a5ff;
        }

        /* ======================================
           CONTEÚDO DAS ABAS
        ====================================== */

        .aba-conteudo {
            display: none;
        }

        .aba-conteudo.ativo {
            display: block;
        }

        /* ======================================
           FORMULÁRIO DE PERGUNTA
        ====================================== */

        .pergunta-form {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #eef2f7;
        }

        .pergunta-form textarea {
            width: 100%;
            min-height: 80px;
            padding: 14px;
            background: #ffffff;
            color: #232323;
            border: 1px solid #d8dee7;
            border-radius: 8px;
            font-size: 15px;
            line-height: 1.6;
            resize: vertical;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            font-family: 'Poppins', sans-serif;
        }

        .pergunta-form textarea:focus {
            border-color: #38a5ff;
            box-shadow: 0 0 0 3px rgba(56, 165, 255, 0.15);
        }

        .pergunta-form textarea::placeholder {
            color: #94a3b8;
        }

        .pergunta-form .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 12px;
        }

        .pergunta-form .form-actions .left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .pergunta-form .form-actions select {
            padding: 8px 12px;
            border: 1px solid #d8dee7;
            border-radius: 8px;
            background: #ffffff;
            color: #232323;
            font-size: 13px;
            outline: none;
            cursor: pointer;
        }

        .pergunta-form .form-actions select:focus {
            border-color: #38a5ff;
        }

        .btn-postar {
            padding: 10px 24px;
            background: #38a5ff;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .btn-postar:hover {
            background: #168fe8;
            transform: translateY(-1px);
        }

        .btn-postar:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* ======================================
           FILTROS
        ====================================== */

        .filtros-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
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

        .perguntas-lista {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .pergunta-item {
            background: #ffffff;
            border: 1px solid #e3e8ef;
            border-radius: 12px;
            padding: 20px;
            transition: box-shadow 0.2s ease;
        }

        .pergunta-item:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .pergunta-item .pergunta-topo {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .pergunta-item .pergunta-autor {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .pergunta-item .pergunta-autor .avatar {
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

        .pergunta-item .pergunta-autor .nome {
            font-weight: 600;
            color: #222222;
            font-size: 14px;
        }

        .pergunta-item .pergunta-autor .data {
            color: #94a3b8;
            font-size: 12px;
        }

        .pergunta-item .pergunta-autor .usuario-tag {
            font-size: 11px;
            color: #94a3b8;
            background: #eef2f7;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .pergunta-item .pergunta-materia {
            padding: 3px 10px;
            border-radius: 20px;
            background: #eef2f7;
            color: #475569;
            font-size: 12px;
            font-weight: 500;
            flex-shrink: 0;
        }

        .pergunta-item .pergunta-texto {
            color: #333333;
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 12px;
            word-break: break-word;
            padding-left: 46px;
        }

        /* Estilo para texto censurado */
        .pergunta-item .pergunta-texto .censurado {
            background: #fef2f2;
            color: #ef4444;
            padding: 1px 4px;
            border-radius: 4px;
            font-weight: 600;
            cursor: help;
        }

        body.dark-mode .pergunta-item .pergunta-texto .censurado {
            background: #450a0a;
            color: #f87171;
        }

        .pergunta-item .pergunta-rodape {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding-left: 46px;
            padding-top: 10px;
            border-top: 1px solid #eef2f7;
        }

        .pergunta-item .pergunta-acoes {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .pergunta-item .pergunta-acoes button {
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

        .pergunta-item .pergunta-acoes button:hover {
            background: #f1f5f9;
        }

        .pergunta-item .pergunta-acoes button i {
            font-size: 14px;
        }

        .pergunta-item .pergunta-acoes .btn-curtir {
            color: #64748b;
        }

        .pergunta-item .pergunta-acoes .btn-curtir.curtido {
            color: #ef4444;
        }

        .pergunta-item .pergunta-acoes .btn-curtir.curtido i {
            font-weight: 900;
        }

        .pergunta-item .pergunta-acoes .btn-curtir:hover {
            background: #fef2f2;
            color: #ef4444;
        }

        .pergunta-item .pergunta-acoes .btn-salvar {
            color: #64748b;
        }

        .pergunta-item .pergunta-acoes .btn-salvar.salvo {
            color: #f59e0b;
        }

        .pergunta-item .pergunta-acoes .btn-salvar.salvo i {
            font-weight: 900;
        }

        .pergunta-item .pergunta-acoes .btn-salvar:hover {
            background: #fffbeb;
            color: #f59e0b;
        }

        .pergunta-item .pergunta-acoes .btn-ver-respostas {
            color: #38a5ff;
        }

        .pergunta-item .pergunta-acoes .btn-ver-respostas:hover {
            background: #eef8ff;
        }

        .pergunta-item .pergunta-acoes .btn-excluir {
            color: #ef4444;
        }

        .pergunta-item .pergunta-acoes .btn-excluir:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        /* ======================================
           RESPOSTAS
        ====================================== */

        .respostas-container {
            margin-top: 12px;
            padding-left: 46px;
            display: none;
        }

        .respostas-container.visivel {
            display: block;
        }

        .resposta-item {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .resposta-item:last-child {
            border-bottom: none;
        }

        .resposta-item .resposta-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2px;
        }

        .resposta-item .resposta-header .avatar-pequeno {
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

        .resposta-item .resposta-header .nome {
            font-weight: 500;
            color: #222222;
            font-size: 13px;
        }

        .resposta-item .resposta-header .data {
            color: #94a3b8;
            font-size: 11px;
        }

        .resposta-item .resposta-texto {
            color: #444444;
            font-size: 14px;
            line-height: 1.6;
            margin-top: 2px;
        }

        .resposta-item .resposta-texto .censurado {
            background: #fef2f2;
            color: #ef4444;
            padding: 1px 4px;
            border-radius: 4px;
            font-weight: 600;
            cursor: help;
        }

        body.dark-mode .resposta-item .resposta-texto .censurado {
            background: #450a0a;
            color: #f87171;
        }

        .resposta-item .sem-respostas {
            color: #94a3b8;
            font-size: 13px;
            padding: 8px 0;
            font-style: italic;
        }

        /* ======================================
           SEM RESULTADOS
        ====================================== */

        .sem-resultados {
            padding: 40px 20px;
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

        /* ... (estilos dark mode já existentes) ... */

        /* ======================================
           RESPONSIVIDADE
        ====================================== */

        /* ... (responsividade já existente) ... */
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

            <section class="chat-card">

                <div class="chat-header">
                    <div>
                        <h2>
                            <i class="fa-solid fa-comments"></i>
                            Comunidade FOAG
                        </h2>
                        <p>Tire dúvidas, ajude outros alunos e compartilhe conhecimento.</p>
                    </div>
                    <div class="chat-stats">
                        <span><i class="fa-regular fa-message"></i> <span id="total-perguntas"><?= count($chatData['perguntas'] ?? []) ?></span> minhas</span>
                        <span><i class="fa-regular fa-compass"></i> <span id="total-explorar"><?= count($todasPerguntas) ?></span> comunidade</span>
                    </div>
                </div>

                <!-- Aviso de Censura -->
                <div class="censure-notice">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Este espaço é para aprendizado. Palavras ofensivas serão automaticamente censuradas com <strong>*</strong>.</span>
                </div>

                <!-- ======================================
                     ABAS
                ====================================== -->

                <div class="abas-container">
                    <button class="aba-btn <?= $abaAtiva === 'minhas' ? 'ativo' : '' ?>" data-aba="minhas">
                        <i class="fa-regular fa-user"></i> Minhas Perguntas
                        <span class="badge"><?= count($chatData['perguntas'] ?? []) ?></span>
                    </button>
                    <button class="aba-btn <?= $abaAtiva === 'explorar' ? 'ativo' : '' ?>" data-aba="explorar">
                        <i class="fa-solid fa-compass"></i> Explorar
                        <span class="badge"><?= count($todasPerguntas) ?></span>
                    </button>
                </div>

                <!-- ======================================
                     ABA: MINHAS PERGUNTAS
                ====================================== -->

                <div class="aba-conteudo <?= $abaAtiva === 'minhas' ? 'ativo' : '' ?>" id="aba-minhas">

                    <!-- Formulário de Pergunta -->
                    <div class="pergunta-form">
                        <textarea id="pergunta-texto" placeholder="Faça sua pergunta para a comunidade..." rows="3"></textarea>
                        <div class="form-actions">
                            <div class="left">
                                <select id="pergunta-materia">
                                    <option value="Geral">Geral</option>
                                    <option value="Matemática">Matemática</option>
                                    <option value="Português">Português</option>
                                    <option value="Ciências">Ciências</option>
                                    <option value="História">História</option>
                                    <option value="Geografia">Geografia</option>
                                    <option value="Inglês">Inglês</option>
                                    <option value="Artes">Artes</option>
                                    <option value="Educação Física">Educação Física</option>
                                    <option value="Química">Química</option>
                                    <option value="Física">Física</option>
                                    <option value="Biologia">Biologia</option>
                                    <option value="Filosofia">Filosofia</option>
                                    <option value="Sociologia">Sociologia</option>
                                    <option value="Programação">Programação</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            <button class="btn-postar" id="btn-postar-pergunta">
                                <i class="fa-regular fa-paper-plane"></i> Publicar pergunta
                            </button>
                        </div>
                        <div id="censure-preview" style="display:none; margin-top:10px; padding:10px; background:#fef2f2; border-radius:8px; font-size:14px; color:#ef4444;">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span id="censure-preview-text"></span>
                        </div>
                    </div>

                    <!-- Perguntas -->
                    <div class="perguntas-lista" id="minhas-perguntas-lista">
                        <!-- Renderizado pelo JavaScript -->
                    </div>
                </div>

                <!-- ======================================
                     ABA: EXPLORAR
                ====================================== -->

                <div class="aba-conteudo <?= $abaAtiva === 'explorar' ? 'ativo' : '' ?>" id="aba-explorar">

                    <!-- Filtros -->
                    <div class="filtros-container">
                        <div class="busca-wrapper">
                            <input type="text" id="busca-input" placeholder="Buscar perguntas, autores ou matérias..." value="<?= htmlspecialchars($filtroBusca) ?>">
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

                    <!-- Perguntas da Comunidade -->
                    <div class="perguntas-lista" id="explorar-perguntas-lista">
                        <!-- Renderizado pelo JavaScript -->
                    </div>
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

    <!-- ======================================
         MODAL: EXCLUSÃO
    ======================================= -->

    <div id="modal-excluir" class="modal-excluir" role="dialog" aria-modal="true" aria-labelledby="excluir-titulo">
        <div class="modal-content">
            <div class="excluir-icon">
                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
            </div>
            <h3 id="excluir-titulo">Excluir Pergunta</h3>
            <p id="excluir-mensagem">Tem certeza que deseja excluir esta pergunta? Todas as respostas também serão removidas.</p>
            <div class="modal-buttons">
                <button id="confirmar-exclusao" class="btn-excluir-confirmar">Excluir</button>
                <button id="cancelar-exclusao" class="btn-cancelar">Cancelar</button>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2025 FOAG. Todos os direitos reservados.
    </footer>

    <!-- ======================================
         JAVASCRIPT
    ====================================== -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            'use strict';

            // =================================================
            // CONFIGURAÇÕES
            // =================================================

            let chatData = window.CHAT_DATA || { perguntas: [] };
            let todasPerguntas = window.TODAS_PERGUNTAS || [];
            let interacoes = window.INTERACOES || { curtidas: [], salvos: [] };
            const usuarioCodigo = window.USUARIO_CODIGO || '';
            const usuarioNome = window.USUARIO_NOME || 'Usuário';
            const CHAT_SAVE_URL = window.CHAT_SAVE_URL || 'salvar_chat.php';
            const INTERACOES_SAVE_URL = window.INTERACOES_SAVE_URL || 'salvar_interacoes.php';
            const abaAtiva = window.ABA_ATIVA || 'minhas';
            const palavrasProibidas = window.PALAVRAS_PROIBIDAS || [];

            if (!Array.isArray(chatData.perguntas)) {
                chatData.perguntas = [];
            }

            // =================================================
            // FUNÇÃO DE CENSURA (FRONT-END)
            // =================================================

            function censurarTexto(texto) {
                if (!texto) return texto;
                
                let textoCensurado = texto;
                const palavras = [...palavrasProibidas];
                
                // Ordenar por tamanho (maiores primeiro)
                palavras.sort((a, b) => b.length - a.length);
                
                for (const palavra of palavras) {
                    if (!palavra) continue;
                    
                    // Padrão para encontrar a palavra (case insensitive)
                    const padrao = new RegExp('\\b' + escaparRegex(palavra) + '\\b', 'gi');
                    
                    textoCensurado = textoCensurado.replace(padrao, function(match) {
                        return '*'.repeat(match.length);
                    });
                }
                
                return textoCensurado;
            }

            function escaparRegex(texto) {
                return texto.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function verificarCensura(texto) {
                if (!texto) return { censurado: false, palavras: [] };
                
                const palavrasEncontradas = [];
                const palavras = [...palavrasProibidas];
                
                for (const palavra of palavras) {
                    if (!palavra) continue;
                    const padrao = new RegExp('\\b' + escaparRegex(palavra) + '\\b', 'gi');
                    if (padrao.test(texto)) {
                        palavrasEncontradas.push(palavra);
                    }
                }
                
                return {
                    censurado: palavrasEncontradas.length > 0,
                    palavras: palavrasEncontradas
                };
            }

            // =================================================
            // FUNÇÕES AUXILIARES
            // =================================================

            function gerarId() {
                return Date.now() + '_' + Math.random().toString(36).substr(2, 6);
            }

            function isCurtido(id) {
                return interacoes.curtidas.includes(id);
            }

            function isSalvo(id) {
                return interacoes.salvos.includes(id);
            }

            function escaparHtml(valor) {
                if (!valor) return '';
                return String(valor)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

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
            // SALVAR DADOS
            // =================================================

            async function salvarChat() {
                try {
                    const resposta = await fetch(CHAT_SAVE_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(chatData)
                    });
                    return resposta.ok;
                } catch (erro) {
                    console.error('Erro ao salvar chat:', erro);
                    return false;
                }
            }

            async function salvarInteracoes() {
                try {
                    const resposta = await fetch(INTERACOES_SAVE_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(interacoes)
                    });
                    return resposta.ok;
                } catch (erro) {
                    console.error('Erro ao salvar interações:', erro);
                    return false;
                }
            }

            // =================================================
            // RENDERIZAR
            // =================================================

            function renderizarMinhasPerguntas() {
                const container = document.getElementById('minhas-perguntas-lista');
                const perguntas = chatData.perguntas || [];

                if (perguntas.length === 0) {
                    container.innerHTML = `
                        <div class="sem-resultados">
                            <i class="fa-regular fa-comment-dots"></i>
                            <h3>Nenhuma pergunta sua ainda</h3>
                            <p>Seja o primeiro a fazer uma pergunta para a comunidade!</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                const perguntasOrdenadas = [...perguntas].sort((a, b) => {
                    return new Date(b.data) - new Date(a.data);
                });

                perguntasOrdenadas.forEach(pergunta => {
                    const respostas = pergunta.respostas || [];
                    const totalResp = respostas.length;
                    const id = pergunta.id || '';
                    
                    // Censurar texto (já deve vir censurado do PHP, mas garantimos)
                    const textoExibido = pergunta.texto || pergunta.texto_original || '';
                    const textoCensurado = censurarTexto(textoExibido);

                    html += `
                        <div class="pergunta-item" data-id="${id}">
                            <div class="pergunta-topo">
                                <div class="pergunta-autor">
                                    <div class="avatar">${obterIniciais(usuarioNome)}</div>
                                    <div>
                                        <span class="nome">${escaparHtml(usuarioNome)}</span>
                                        <span class="data">${formatarData(pergunta.data)}</span>
                                        <span class="usuario-tag"><i class="fa-regular fa-user"></i> Você</span>
                                    </div>
                                </div>
                                <span class="pergunta-materia">${escaparHtml(pergunta.materia || 'Geral')}</span>
                            </div>
                            <div class="pergunta-texto">${escaparHtml(textoCensurado)}</div>
                            <div class="pergunta-rodape">
                                <div class="pergunta-acoes">
                                    <button class="btn-ver-respostas" data-id="${id}">
                                        <i class="fa-regular fa-comment"></i> ${totalResp} resposta${totalResp !== 1 ? 's' : ''}
                                    </button>
                                    <button class="btn-excluir" data-id="${id}">
                                        <i class="fa-regular fa-trash-can"></i> Excluir
                                    </button>
                                </div>
                            </div>
                            <div class="respostas-container" id="respostas-${id}">
                                ${renderizarRespostas(respostas)}
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
                adicionarEventosMinhasPerguntas();
            }

            function renderizarExplorar() {
                const container = document.getElementById('explorar-perguntas-lista');
                const perguntas = todasPerguntas || [];

                if (perguntas.length === 0) {
                    container.innerHTML = `
                        <div class="sem-resultados">
                            <i class="fa-regular fa-face-frown"></i>
                            <h3>Nenhuma pergunta encontrada</h3>
                            <p>Tente ajustar os filtros de busca.</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                perguntas.forEach(pergunta => {
                    const respostas = pergunta.respostas || [];
                    const totalResp = respostas.length;
                    const id = pergunta.id || '';
                    const ehDoUsuario = (pergunta.usuario_id || '') === usuarioCodigo;
                    const curtido = isCurtido(id);
                    const salvo = isSalvo(id);
                    
                    // Texto já vem censurado do PHP
                    const textoExibido = pergunta.texto || pergunta.texto_original || '';

                    html += `
                        <div class="pergunta-item" data-id="${id}">
                            <div class="pergunta-topo">
                                <div class="pergunta-autor">
                                    <div class="avatar">${obterIniciais(pergunta.autor || '?')}</div>
                                    <div>
                                        <span class="nome">${escaparHtml(pergunta.autor || 'Anônimo')}</span>
                                        <span class="data">${formatarData(pergunta.data)}</span>
                                        ${ehDoUsuario ? `<span class="usuario-tag"><i class="fa-regular fa-user"></i> Você</span>` : ''}
                                    </div>
                                </div>
                                <span class="pergunta-materia">${escaparHtml(pergunta.materia || 'Geral')}</span>
                            </div>
                            <div class="pergunta-texto">${escaparHtml(textoExibido)}</div>
                            <div class="pergunta-rodape">
                                <div class="pergunta-acoes">
                                    <button class="btn-curtir ${curtido ? 'curtido' : ''}" data-id="${id}">
                                        <i class="${curtido ? 'fa-solid' : 'fa-regular'} fa-heart"></i> 
                                        <span class="curtidas-count">0</span>
                                    </button>
                                    <button class="btn-salvar ${salvo ? 'salvo' : ''}" data-id="${id}">
                                        <i class="${salvo ? 'fa-solid' : 'fa-regular'} fa-bookmark"></i> 
                                        <span>${salvo ? 'Salvo' : 'Salvar'}</span>
                                    </button>
                                    <button class="btn-ver-respostas" data-id="${id}">
                                        <i class="fa-regular fa-comment"></i> ${totalResp} resposta${totalResp !== 1 ? 's' : ''}
                                    </button>
                                </div>
                            </div>
                            <div class="respostas-container" id="respostas-${id}">
                                ${renderizarRespostas(respostas)}
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
                adicionarEventosExplorar();
            }

            function renderizarRespostas(respostas) {
                if (!respostas || respostas.length === 0) {
                    return `<div class="sem-respostas">Nenhuma resposta ainda.</div>`;
                }

                let html = '';
                respostas.forEach(resposta => {
                    const textoExibido = resposta.texto || resposta.texto_original || '';
                    
                    html += `
                        <div class="resposta-item">
                            <div class="resposta-header">
                                <div class="avatar-pequeno">${obterIniciais(resposta.autor || '?')}</div>
                                <span class="nome">${escaparHtml(resposta.autor || 'Anônimo')}</span>
                                <span class="data">${formatarData(resposta.data)}</span>
                            </div>
                            <div class="resposta-texto">${escaparHtml(textoExibido)}</div>
                        </div>
                    `;
                });

                return html;
            }

            // =================================================
            // EVENTOS
            // =================================================

            function adicionarEventosMinhasPerguntas() {
                document.querySelectorAll('#aba-minhas .btn-ver-respostas').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const container = document.getElementById(`respostas-${id}`);
                        if (container) container.classList.toggle('visivel');
                    });
                });

                document.querySelectorAll('#aba-minhas .btn-excluir').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        abrirModalExclusao(id);
                    });
                });
            }

            function adicionarEventosExplorar() {
                document.querySelectorAll('#aba-explorar .btn-ver-respostas').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const container = document.getElementById(`respostas-${id}`);
                        if (container) container.classList.toggle('visivel');
                    });
                });

                document.querySelectorAll('#aba-explorar .btn-curtir').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        toggleCurtir(id);
                    });
                });

                document.querySelectorAll('#aba-explorar .btn-salvar').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        toggleSalvar(id);
                    });
                });
            }

            // =================================================
            // INTERAÇÕES
            // =================================================

            function toggleCurtir(id) {
                const index = interacoes.curtidas.indexOf(id);
                if (index > -1) {
                    interacoes.curtidas.splice(index, 1);
                } else {
                    interacoes.curtidas.push(id);
                }
                salvarInteracoes();
                renderizarExplorar();
            }

            function toggleSalvar(id) {
                const index = interacoes.salvos.indexOf(id);
                if (index > -1) {
                    interacoes.salvos.splice(index, 1);
                } else {
                    interacoes.salvos.push(id);
                }
                salvarInteracoes();
                renderizarExplorar();
            }

            // =================================================
            // POSTAR PERGUNTA COM CENSURA
            // =================================================

            const btnPostar = document.getElementById('btn-postar-pergunta');
            const perguntaTexto = document.getElementById('pergunta-texto');
            const perguntaMateria = document.getElementById('pergunta-materia');
            const censurePreview = document.getElementById('censure-preview');
            const censurePreviewText = document.getElementById('censure-preview-text');

            // Verificar censura em tempo real
            perguntaTexto?.addEventListener('input', function() {
                const texto = this.value;
                const resultado = verificarCensura(texto);
                
                if (resultado.censurado) {
                    censurePreview.style.display = 'block';
                    censurePreviewText.innerHTML = `⚠️ Palavra(s) ofensiva(s) detectada(s): <strong>${resultado.palavras.join(', ')}</strong>. Elas serão substituídas por * ao publicar.`;
                } else {
                    censurePreview.style.display = 'none';
                }
            });

            btnPostar?.addEventListener('click', async function() {
                const texto = perguntaTexto.value.trim();
                if (!texto) {
                    alert('Escreva sua pergunta antes de publicar.');
                    return;
                }

                if (texto.length < 5) {
                    alert('Sua pergunta é muito curta. Escreva mais detalhes.');
                    return;
                }

                // Censurar o texto antes de salvar
                const textoCensurado = censurarTexto(texto);
                const materia = perguntaMateria.value || 'Geral';

                const novaPergunta = {
                    id: gerarId(),
                    autor: usuarioNome,
                    texto: textoCensurado,
                    texto_original: texto, // Guardar original para referência
                    materia: materia,
                    data: new Date().toISOString(),
                    respostas: []
                };

                chatData.perguntas.push(novaPergunta);
                perguntaTexto.value = '';
                censurePreview.style.display = 'none';

                this.disabled = true;
                this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Publicando...';

                const salvou = await salvarChat();
                this.disabled = false;
                this.innerHTML = '<i class="fa-regular fa-paper-plane"></i> Publicar pergunta';

                if (salvou) {
                    renderizarMinhasPerguntas();
                    document.querySelector('.aba-btn[data-aba="minhas"] .badge').textContent = chatData.perguntas.length;
                    // Recarregar explorar também
                    location.reload();
                }
            });

            // Ctrl+Enter para postar
            perguntaTexto?.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.ctrlKey) {
                    e.preventDefault();
                    btnPostar?.click();
                }
            });

            // =================================================
            // MODAL DE EXCLUSÃO
            // =================================================

            const modalExcluir = document.getElementById('modal-excluir');
            const excluirTitulo = document.getElementById('excluir-titulo');
            const excluirMensagem = document.getElementById('excluir-mensagem');
            const btnConfirmarExclusao = document.getElementById('confirmar-exclusao');
            const btnCancelarExclusao = document.getElementById('cancelar-exclusao');
            let idParaExcluir = null;

            function abrirModalExclusao(id) {
                idParaExcluir = id;
                modalExcluir.style.display = 'flex';
            }

            function fecharModalExclusao() {
                modalExcluir.style.display = 'none';
                idParaExcluir = null;
            }

            btnConfirmarExclusao?.addEventListener('click', async function() {
                if (!idParaExcluir) return;

                chatData.perguntas = chatData.perguntas.filter(p => p.id !== idParaExcluir);
                const salvou = await salvarChat();
                fecharModalExclusao();

                if (salvou) {
                    renderizarMinhasPerguntas();
                    document.querySelector('.aba-btn[data-aba="minhas"] .badge').textContent = chatData.perguntas.length;
                }
            });

            btnCancelarExclusao?.addEventListener('click', fecharModalExclusao);
            modalExcluir?.addEventListener('click', function(e) {
                if (e.target === modalExcluir) fecharModalExclusao();
            });

            // =================================================
            // ABAS
            // =================================================

            document.querySelectorAll('.aba-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const aba = this.dataset.aba;

                    document.querySelectorAll('.aba-btn').forEach(b => b.classList.remove('ativo'));
                    this.classList.add('ativo');

                    document.querySelectorAll('.aba-conteudo').forEach(c => c.classList.remove('ativo'));
                    document.getElementById(`aba-${aba}`).classList.add('ativo');

                    const url = new URL(window.location);
                    url.searchParams.set('aba', aba);
                    window.history.pushState({}, '', url);
                });
            });

            // =================================================
            // FILTROS
            // =================================================

            const buscaInput = document.getElementById('busca-input');
            const btnBuscar = document.getElementById('btn-buscar');
            const filtroMateriaSelect = document.getElementById('filtro-materia');
            const btnLimparFiltros = document.getElementById('btn-limpar-filtros');

            function aplicarFiltros() {
                const busca = buscaInput.value.trim();
                const materia = filtroMateriaSelect.value;

                const url = new URL(window.location);
                url.searchParams.set('aba', 'explorar');
                if (busca) url.searchParams.set('busca', busca);
                else url.searchParams.delete('busca');
                if (materia !== 'todas') url.searchParams.set('materia', materia);
                else url.searchParams.delete('materia');

                window.location.href = url;
            }

            btnBuscar?.addEventListener('click', aplicarFiltros);
            buscaInput?.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') aplicarFiltros();
            });
            filtroMateriaSelect?.addEventListener('change', aplicarFiltros);

            btnLimparFiltros?.addEventListener('click', function() {
                const url = new URL(window.location);
                url.searchParams.delete('busca');
                url.searchParams.delete('materia');
                url.searchParams.set('aba', 'explorar');
                window.location.href = url;
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
                    if (e.target === logoutModal) logoutModal.style.display = 'none';
                });
            }

            // =================================================
            // INICIALIZAR
            // =================================================

            renderizarMinhasPerguntas();
            renderizarExplorar();

            console.log('Comunidade FOAG com censura carregada ✅');
            console.log('Palavras proibidas:', palavrasProibidas.length);
            console.log('Minhas perguntas:', chatData.perguntas.length);
            console.log('Perguntas da comunidade:', todasPerguntas.length);
        });
    </script>

</body>

</html>
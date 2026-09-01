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
$nomeUsuario = $_SESSION['user_nome'] ?? 'Usuário';

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

$palavrasProibidas = require __DIR__ . '/palavram.php';

if (!is_array($palavrasProibidas)) {
    $palavrasProibidas = [];
}

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
    $chars = str_split($palavraLower);
    $substituicoes = [];

    foreach ($chars as $i => $char) {
        if (isset($leetMap[$char])) {
            $substituicoes[$i] = $leetMap[$char];
        }
    }

    if (empty($substituicoes)) {
        return $variacoes;
    }

    $keys = array_keys($substituicoes);

    // Limita a quantidade de posições alteradas para evitar combinações demais
    if (count($keys) > 3) {
        $keys = array_slice($keys, 0, 3);
    }

    $gerarCombinacoes = function($charsAtual, $index = 0) use (&$gerarCombinacoes, $substituicoes, $keys) {
        if ($index >= count($keys)) {
            return [implode('', $charsAtual)];
        }

        $key = $keys[$index];
        $resultados = [];

        foreach ($substituicoes[$key] as $opcao) {
            $charsCopy = $charsAtual;
            $charsCopy[$key] = $opcao;

            $resultados = array_merge(
                $resultados,
                $gerarCombinacoes($charsCopy, $index + 1)
            );
        }

        return $resultados;
    };

    $combinacoes = $gerarCombinacoes($chars);

    foreach ($combinacoes as $combinacao) {
        if (!in_array($combinacao, $variacoes, true)) {
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
    <link rel="stylesheet" href="dark_comu.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ======================================
         MODO ESCURO GLOBAL
    ======================================= -->
    <script src="../m.escuro/dark-mode.js"></script>

    <!-- ======================================
         APARÊNCIA GLOBAL (TAMANHO DA FONTE)
    ======================================= -->
    <script src="../configuracoes/aparencia.js?v=1"></script>

    <!-- ======================================
         ACESSIBILIDADE GLOBAL
    ======================================= -->
    <link rel="stylesheet" href="../acessibilidade/acessibilidade.css">
    <script src="../acessibilidade/acessibilidade.js?v=13" defer></script>

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

    <script src="comunidade.js"></script>

</body>

</html>
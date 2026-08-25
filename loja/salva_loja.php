<?php
session_start();

// ======================================
// VERIFICAR LOGIN
// ======================================

if (!isset($_SESSION['codigo_usuario'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensagem' => 'Não autenticado']);
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];

// ======================================
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir = __DIR__ . '/../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    if (!mkdir($pastaUsuario, 0755, true)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensagem' => 'Erro ao criar pasta']);
        exit;
    }
}

// ======================================
// RECEBER DADOS
// ======================================

$dados = json_decode(file_get_contents('php://input'), true);

if ($dados === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensagem' => 'Dados inválidos']);
    exit;
}

// ======================================
// CARREGAR DADOS EXISTENTES
// ======================================

$arquivoLoja = $pastaUsuario . '/loja.json';

// Carregar dados atuais
$dadosAtuais = [];
if (file_exists($arquivoLoja)) {
    $conteudo = file_get_contents($arquivoLoja);
    if ($conteudo !== false) {
        $dadosAtuais = json_decode($conteudo, true);
        if (!is_array($dadosAtuais)) {
            $dadosAtuais = [];
        }
    }
}

// ======================================
// MESCLAR DADOS
// ======================================

// Atualizar apenas as chaves que vieram
foreach ($dados as $chave => $valor) {
    if ($chave !== 'itens') { // Não substituir os itens da loja
        $dadosAtuais[$chave] = $valor;
    }
}

// Garantir que itens existe
if (!isset($dadosAtuais['itens']) || empty($dadosAtuais['itens'])) {
    $dadosAtuais['itens'] = [
        ['id' => 'tema_azul', 'nome' => 'Tema Azul', 'descricao' => 'Mude o tema do perfil para azul', 'preco' => 50, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas'],
        ['id' => 'tema_roxo', 'nome' => 'Tema Roxo', 'descricao' => 'Mude o tema do perfil para roxo', 'preco' => 50, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas'],
        ['id' => 'tema_verde', 'nome' => 'Tema Verde', 'descricao' => 'Mude o tema do perfil para verde', 'preco' => 50, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas'],
        ['id' => 'tema_vermelho', 'nome' => 'Tema Vermelho', 'descricao' => 'Mude o tema do perfil para vermelho', 'preco' => 55, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas'],
        ['id' => 'tema_amarelo', 'nome' => 'Tema Amarelo', 'descricao' => 'Mude o tema do perfil para amarelo', 'preco' => 55, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas'],
        ['id' => 'tema_rosa', 'nome' => 'Tema Rosa', 'descricao' => 'Mude o tema do perfil para rosa', 'preco' => 60, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas'],
        ['id' => 'tema_preto', 'nome' => 'Tema Preto', 'descricao' => 'Mude o tema do perfil para preto', 'preco' => 65, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas'],
        ['id' => 'badge_estudioso', 'nome' => 'Insígnia Estudioso', 'descricao' => 'Mostre que você é um estudante dedicado', 'preco' => 30, 'icone' => 'fa-solid fa-graduation-cap', 'categoria' => 'insignias'],
        ['id' => 'badge_mestre', 'nome' => 'Insígnia Mestre', 'descricao' => 'Para os mestres do estudo', 'preco' => 80, 'icone' => 'fa-solid fa-crown', 'categoria' => 'insignias'],
        ['id' => 'badge_maratonista', 'nome' => 'Insígnia Maratonista', 'descricao' => 'Para quem estuda por longas horas', 'preco' => 60, 'icone' => 'fa-solid fa-running', 'categoria' => 'insignias'],
        ['id' => 'badge_dedicado', 'nome' => 'Insígnia Dedicado', 'descricao' => 'Dedicação é a chave do sucesso', 'preco' => 40, 'icone' => 'fa-solid fa-heart', 'categoria' => 'insignias'],
        ['id' => 'badge_campeao', 'nome' => 'Insígnia Campeão', 'descricao' => 'Você é um verdadeiro campeão', 'preco' => 100, 'icone' => 'fa-solid fa-trophy', 'categoria' => 'insignias'],
        ['id' => 'badge_lider', 'nome' => 'Insígnia Líder', 'descricao' => 'Liderança e determinação', 'preco' => 70, 'icone' => 'fa-solid fa-flag', 'categoria' => 'insignias'],
        ['id' => 'badge_persistente', 'nome' => 'Insígnia Persistente', 'descricao' => 'Nunca desista dos seus sonhos', 'preco' => 50, 'icone' => 'fa-solid fa-fire', 'categoria' => 'insignias'],
        ['id' => 'emoji_foguete', 'nome' => 'Emoji Foguete', 'descricao' => '🚀 Mostre sua determinação', 'preco' => 20, 'icone' => 'fa-solid fa-rocket', 'categoria' => 'emojis'],
        ['id' => 'emoji_livro', 'nome' => 'Emoji Livro', 'descricao' => '📚 Para os amantes da leitura', 'preco' => 15, 'icone' => 'fa-solid fa-book', 'categoria' => 'emojis'],
        ['id' => 'emoji_lampada', 'nome' => 'Emoji Lâmpada', 'descricao' => '💡 Ideias brilhantes', 'preco' => 25, 'icone' => 'fa-solid fa-lightbulb', 'categoria' => 'emojis'],
        ['id' => 'emoji_alvo', 'nome' => 'Emoji Alvo', 'descricao' => '🎯 Foco no objetivo', 'preco' => 22, 'icone' => 'fa-solid fa-bullseye', 'categoria' => 'emojis'],
        ['id' => 'emoji_cerebro', 'nome' => 'Emoji Cérebro', 'descricao' => '🧠 Inteligência e raciocínio', 'preco' => 28, 'icone' => 'fa-solid fa-brain', 'categoria' => 'emojis'],
        ['id' => 'emoji_raio', 'nome' => 'Emoji Raio', 'descricao' => '⚡ Energia e velocidade', 'preco' => 20, 'icone' => 'fa-solid fa-bolt', 'categoria' => 'emojis'],
        ['id' => 'emoji_estrela_brilhante', 'nome' => 'Emoji Estrela Brilhante', 'descricao' => '🌟 Você é uma estrela', 'preco' => 30, 'icone' => 'fa-solid fa-star', 'categoria' => 'emojis'],
        ['id' => 'emoji_trofeu', 'nome' => 'Emoji Troféu', 'descricao' => '🏆 Para os vencedores', 'preco' => 35, 'icone' => 'fa-solid fa-trophy', 'categoria' => 'emojis'],
        ['id' => 'fundo_neon', 'nome' => 'Fundo Neon', 'descricao' => 'Um fundo neon para seu perfil', 'preco' => 100, 'icone' => 'fa-solid fa-star', 'categoria' => 'fundos'],
        ['id' => 'fundo_galaxia', 'nome' => 'Fundo Galáxia', 'descricao' => 'Viaje pelas estrelas', 'preco' => 120, 'icone' => 'fa-solid fa-galaxy', 'categoria' => 'fundos'],
        ['id' => 'fundo_por_do_sol', 'nome' => 'Fundo Pôr do Sol', 'descricao' => '🌅 Um pôr do sol relaxante', 'preco' => 130, 'icone' => 'fa-solid fa-sun', 'categoria' => 'fundos'],
        ['id' => 'fundo_oceano', 'nome' => 'Fundo Oceano', 'descricao' => '🌊 A calma do oceano', 'preco' => 110, 'icone' => 'fa-solid fa-water', 'categoria' => 'fundos'],
        ['id' => 'fundo_nebulosa', 'nome' => 'Fundo Nebulosa', 'descricao' => '🌌 Cores cósmicas', 'preco' => 150, 'icone' => 'fa-solid fa-cloud', 'categoria' => 'fundos'],
        ['id' => 'fundo_montanha', 'nome' => 'Fundo Montanha', 'descricao' => '🏔️ Picos e natureza', 'preco' => 100, 'icone' => 'fa-solid fa-mountain', 'categoria' => 'fundos'],
        ['id' => 'moldura_dourada', 'nome' => 'Moldura Dourada', 'descricao' => '🖼️ Uma moldura elegante e dourada', 'preco' => 80, 'icone' => 'fa-regular fa-image', 'categoria' => 'molduras'],
        ['id' => 'moldura_prata', 'nome' => 'Moldura Prata', 'descricao' => '🖼️ Sofisticação em prata', 'preco' => 60, 'icone' => 'fa-regular fa-image', 'categoria' => 'molduras'],
        ['id' => 'moldura_neon', 'nome' => 'Moldura Neon', 'descricao' => '🖼️ Uma moldura neon vibrante', 'preco' => 90, 'icone' => 'fa-regular fa-image', 'categoria' => 'molduras'],
        ['id' => 'moldura_minimalista', 'nome' => 'Moldura Minimalista', 'descricao' => '🖼️ Simples e elegante', 'preco' => 40, 'icone' => 'fa-regular fa-image', 'categoria' => 'molduras'],
        ['id' => 'efeito_brilho', 'nome' => 'Efeito Brilho', 'descricao' => '✨ Um brilho especial no perfil', 'preco' => 70, 'icone' => 'fa-solid fa-wand-magic-sparkles', 'categoria' => 'efeitos'],
        ['id' => 'efeito_particulas', 'nome' => 'Efeito Partículas', 'descricao' => '✨ Partículas flutuantes', 'preco' => 85, 'icone' => 'fa-solid fa-sparkles', 'categoria' => 'efeitos'],
        ['id' => 'efeito_neon', 'nome' => 'Efeito Neon', 'descricao' => '✨ Brilho neon no perfil', 'preco' => 95, 'icone' => 'fa-solid fa-lightbulb', 'categoria' => 'efeitos'],
        ['id' => 'efeito_sombra', 'nome' => 'Efeito Sombra', 'descricao' => '✨ Sombras suaves e elegantes', 'preco' => 55, 'icone' => 'fa-solid fa-circle-half-stroke', 'categoria' => 'efeitos']
    ];
}

// ======================================
// SALVAR
// ======================================

$json = json_encode($dadosAtuais, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$resultado = file_put_contents($arquivoLoja, $json);

if ($resultado !== false) {
    echo json_encode([
        'ok' => true,
        'dados' => $dadosAtuais,
        'arquivo' => $arquivoLoja
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensagem' => 'Erro ao salvar arquivo',
        'arquivo' => $arquivoLoja
    ]);
}
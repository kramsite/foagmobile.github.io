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

// Garantir que itens existe com a estrutura completa
if (!isset($dadosAtuais['itens']) || empty($dadosAtuais['itens'])) {
    $dadosAtuais['itens'] = [
        // ===================================
        // TEMAS
        // ===================================
        ['id' => 'tema_roxo', 'nome' => 'Tema Roxo', 'descricao' => 'Mude o tema do perfil para roxo', 'preco' => 50, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas', 'imagem' => ''],
        ['id' => 'tema_verde', 'nome' => 'Tema Verde', 'descricao' => 'Mude o tema do perfil para verde', 'preco' => 50, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas', 'imagem' => ''],
        ['id' => 'tema_vermelho', 'nome' => 'Tema Vermelho', 'descricao' => 'Mude o tema do perfil para vermelho', 'preco' => 55, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas', 'imagem' => ''],
        ['id' => 'tema_amarelo', 'nome' => 'Tema Amarelo', 'descricao' => 'Mude o tema do perfil para amarelo', 'preco' => 55, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas', 'imagem' => ''],
        ['id' => 'tema_rosa', 'nome' => 'Tema Rosa', 'descricao' => 'Mude o tema do perfil para rosa', 'preco' => 60, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas', 'imagem' => ''],
        ['id' => 'tema_preto', 'nome' => 'Tema Preto', 'descricao' => 'Mude o tema do perfil para preto', 'preco' => 65, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas', 'imagem' => ''],
        ['id' => 'tema_laranja', 'nome' => 'Tema Laranja', 'descricao' => 'Mude o tema do perfil para laranja', 'preco' => 55, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas', 'imagem' => ''],
        ['id' => 'tema_magenta', 'nome' => 'Tema Magenta', 'descricao' => 'Mude o tema do perfil para magenta', 'preco' => 65, 'icone' => 'fa-solid fa-palette', 'categoria' => 'temas', 'imagem' => ''],

        // ===================================
        // INSÍGNIAS
        // ===================================
        ['id' => 'badge_estudioso', 'nome' => 'Insígnia Estudioso', 'descricao' => 'Mostre que você é um estudante dedicado', 'preco' => 30, 'icone' => 'fa-solid fa-graduation-cap', 'categoria' => 'insignias', 'imagem' => ''],
        ['id' => 'badge_mestre', 'nome' => 'Insígnia Mestre', 'descricao' => 'Para os mestres do estudo', 'preco' => 80, 'icone' => 'fa-solid fa-crown', 'categoria' => 'insignias', 'imagem' => ''],
        ['id' => 'badge_maratonista', 'nome' => 'Insígnia Maratonista', 'descricao' => 'Para quem estuda por longas horas', 'preco' => 60, 'icone' => 'fa-solid fa-running', 'categoria' => 'insignias', 'imagem' => ''],
        ['id' => 'badge_dedicado', 'nome' => 'Insígnia Dedicado', 'descricao' => 'Dedicação é a chave do sucesso', 'preco' => 40, 'icone' => 'fa-solid fa-heart', 'categoria' => 'insignias', 'imagem' => ''],
        ['id' => 'badge_campeao', 'nome' => 'Insígnia Campeão', 'descricao' => 'Você é um verdadeiro campeão', 'preco' => 100, 'icone' => 'fa-solid fa-trophy', 'categoria' => 'insignias', 'imagem' => ''],
        ['id' => 'badge_lider', 'nome' => 'Insígnia Líder', 'descricao' => 'Liderança e determinação', 'preco' => 70, 'icone' => 'fa-solid fa-flag', 'categoria' => 'insignias', 'imagem' => ''],
        ['id' => 'badge_persistente', 'nome' => 'Insígnia Persistente', 'descricao' => 'Nunca desista dos seus sonhos', 'preco' => 50, 'icone' => 'fa-solid fa-fire', 'categoria' => 'insignias', 'imagem' => ''],
        ['id' => 'badge_sabio', 'nome' => 'Insígnia Sábio', 'descricao' => 'A sabedoria é o seu maior dom', 'preco' => 90, 'icone' => 'fa-solid fa-owl', 'categoria' => 'insignias', 'imagem' => ''],
        ['id' => 'badge_guerreiro', 'nome' => 'Insígnia Guerreiro', 'descricao' => 'Você enfrenta qualquer desafio', 'preco' => 75, 'icone' => 'fa-solid fa-shield-halved', 'categoria' => 'insignias', 'imagem' => ''],
        ['id' => 'badge_lenda', 'nome' => 'Insígnia Lenda', 'descricao' => 'Você é uma lenda viva do estudo', 'preco' => 150, 'icone' => 'fa-solid fa-chess-queen', 'categoria' => 'insignias', 'imagem' => ''],

        // ===================================
        // EMOJIS
        // ===================================
        ['id' => 'emoji_foguete', 'nome' => 'Emoji Foguete', 'descricao' => '🚀 Mostre sua determinação', 'preco' => 20, 'icone' => 'fa-solid fa-rocket', 'categoria' => 'emojis', 'imagem' => ''],
        ['id' => 'emoji_livro', 'nome' => 'Emoji Livro', 'descricao' => '📚 Para os amantes da leitura', 'preco' => 15, 'icone' => 'fa-solid fa-book', 'categoria' => 'emojis', 'imagem' => ''],
        ['id' => 'emoji_lampada', 'nome' => 'Emoji Lâmpada', 'descricao' => '💡 Ideias brilhantes', 'preco' => 25, 'icone' => 'fa-solid fa-lightbulb', 'categoria' => 'emojis', 'imagem' => ''],
        ['id' => 'emoji_alvo', 'nome' => 'Emoji Alvo', 'descricao' => '🎯 Foco no objetivo', 'preco' => 22, 'icone' => 'fa-solid fa-bullseye', 'categoria' => 'emojis', 'imagem' => ''],
        ['id' => 'emoji_cerebro', 'nome' => 'Emoji Cérebro', 'descricao' => '🧠 Inteligência e raciocínio', 'preco' => 28, 'icone' => 'fa-solid fa-brain', 'categoria' => 'emojis', 'imagem' => ''],
        ['id' => 'emoji_raio', 'nome' => 'Emoji Raio', 'descricao' => '⚡ Energia e velocidade', 'preco' => 20, 'icone' => 'fa-solid fa-bolt', 'categoria' => 'emojis', 'imagem' => ''],
        ['id' => 'emoji_estrela_brilhante', 'nome' => 'Emoji Estrela Brilhante', 'descricao' => '🌟 Você é uma estrela', 'preco' => 30, 'icone' => 'fa-solid fa-star', 'categoria' => 'emojis', 'imagem' => ''],
        ['id' => 'emoji_trofeu', 'nome' => 'Emoji Troféu', 'descricao' => '🏆 Para os vencedores', 'preco' => 35, 'icone' => 'fa-solid fa-trophy', 'categoria' => 'emojis', 'imagem' => ''],
        ['id' => 'emoji_coracao', 'nome' => 'Emoji Coração', 'descricao' => '❤️ Amor e dedicação', 'preco' => 15, 'icone' => 'fa-solid fa-heart', 'categoria' => 'emojis', 'imagem' => ''],
        ['id' => 'emoji_planeta', 'nome' => 'Emoji Planeta', 'descricao' => '🪐 Explore novos mundos', 'preco' => 25, 'icone' => 'fa-solid fa-globe', 'categoria' => 'emojis', 'imagem' => ''],
        ['id' => 'emoji_nuvem', 'nome' => 'Emoji Nuvem', 'descricao' => '☁️ Sonhe alto', 'preco' => 18, 'icone' => 'fa-solid fa-cloud', 'categoria' => 'emojis', 'imagem' => ''],
        ['id' => 'emoji_chave', 'nome' => 'Emoji Chave', 'descricao' => '🔑 A chave do sucesso', 'preco' => 22, 'icone' => 'fa-solid fa-key', 'categoria' => 'emojis', 'imagem' => ''],

        // ===================================
        // FUNDOS
        // ===================================
        ['id' => 'fundo_neon', 'nome' => 'Fundo Neon', 'descricao' => 'Um fundo neon para seu perfil', 'preco' => 100, 'icone' => 'fa-solid fa-star', 'categoria' => 'fundos', 'imagem' => ''],
        ['id' => 'fundo_galaxia', 'nome' => 'Fundo Galáxia', 'descricao' => 'Viaje pelas estrelas', 'preco' => 120, 'icone' => 'fa-solid fa-star', 'categoria' => 'fundos', 'imagem' => ''],
        ['id' => 'fundo_por_do_sol', 'nome' => 'Fundo Pôr do Sol', 'descricao' => '🌅 Um pôr do sol relaxante', 'preco' => 130, 'icone' => 'fa-solid fa-sun', 'categoria' => 'fundos', 'imagem' => ''],
        ['id' => 'fundo_oceano', 'nome' => 'Fundo Oceano', 'descricao' => '🌊 A calma do oceano', 'preco' => 110, 'icone' => 'fa-solid fa-water', 'categoria' => 'fundos', 'imagem' => ''],
        ['id' => 'fundo_nebulosa', 'nome' => 'Fundo Nebulosa', 'descricao' => '🌌 Cores cósmicas', 'preco' => 150, 'icone' => 'fa-solid fa-cloud', 'categoria' => 'fundos', 'imagem' => ''],
        ['id' => 'fundo_montanha', 'nome' => 'Fundo Montanha', 'descricao' => '🏔️ Picos e natureza', 'preco' => 100, 'icone' => 'fa-solid fa-mountain', 'categoria' => 'fundos', 'imagem' => ''],
        ['id' => 'fundo_floresta', 'nome' => 'Fundo Floresta', 'descricao' => '🌲 Natureza exuberante', 'preco' => 110, 'icone' => 'fa-solid fa-tree', 'categoria' => 'fundos', 'imagem' => ''],
        ['id' => 'fundo_cidade', 'nome' => 'Fundo Cidade', 'descricao' => '🏙️ A vida urbana', 'preco' => 105, 'icone' => 'fa-solid fa-city', 'categoria' => 'fundos', 'imagem' => ''],
        ['id' => 'fundo_espaco', 'nome' => 'Fundo Espaço', 'descricao' => '🚀 O infinito universo', 'preco' => 140, 'icone' => 'fa-solid fa-rocket', 'categoria' => 'fundos', 'imagem' => ''],
        ['id' => 'fundo_praia', 'nome' => 'Fundo Praia', 'descricao' => '🏖️ Areia e mar', 'preco' => 115, 'icone' => 'fa-solid fa-umbrella-beach', 'categoria' => 'fundos', 'imagem' => ''],
        ['id' => 'fundo_castelo', 'nome' => 'Fundo Castelo', 'descricao' => '🏰 Um mundo de fantasia', 'preco' => 125, 'icone' => 'fa-solid fa-castle', 'categoria' => 'fundos', 'imagem' => ''],
        ['id' => 'fundo_jardim', 'nome' => 'Fundo Jardim', 'descricao' => '🌺 Flores e cores', 'preco' => 105, 'icone' => 'fa-solid fa-flower', 'categoria' => 'fundos', 'imagem' => ''],

        // ===================================
        // MOLDURAS
        // ===================================
        ['id' => 'moldura_dourada', 'nome' => 'Moldura Dourada', 'descricao' => '🖼️ Uma moldura elegante e dourada', 'preco' => 80, 'icone' => 'fa-regular fa-image', 'categoria' => 'molduras', 'imagem' => ''],
        ['id' => 'moldura_prata', 'nome' => 'Moldura Prata', 'descricao' => '🖼️ Sofisticação em prata', 'preco' => 60, 'icone' => 'fa-regular fa-image', 'categoria' => 'molduras', 'imagem' => ''],
        ['id' => 'moldura_neon', 'nome' => 'Moldura Neon', 'descricao' => '🖼️ Uma moldura neon vibrante', 'preco' => 90, 'icone' => 'fa-regular fa-image', 'categoria' => 'molduras', 'imagem' => ''],
        ['id' => 'moldura_minimalista', 'nome' => 'Moldura Minimalista', 'descricao' => '🖼️ Simples e elegante', 'preco' => 40, 'icone' => 'fa-regular fa-image', 'categoria' => 'molduras', 'imagem' => ''],
        ['id' => 'moldura_vintage', 'nome' => 'Moldura Vintage', 'descricao' => '🖼️ Estilo retrô', 'preco' => 70, 'icone' => 'fa-regular fa-image', 'categoria' => 'molduras', 'imagem' => ''],
        ['id' => 'moldura_rustica', 'nome' => 'Moldura Rústica', 'descricao' => '🖼️ Madeira e natureza', 'preco' => 65, 'icone' => 'fa-regular fa-image', 'categoria' => 'molduras', 'imagem' => ''],
        ['id' => 'moldura_moderna', 'nome' => 'Moldura Moderna', 'descricao' => '🖼️ Design contemporâneo', 'preco' => 75, 'icone' => 'fa-regular fa-image', 'categoria' => 'molduras', 'imagem' => ''],
        ['id' => 'moldura_classica', 'nome' => 'Moldura Clássica', 'descricao' => '🖼️ Elegância atemporal', 'preco' => 85, 'icone' => 'fa-regular fa-image', 'categoria' => 'molduras', 'imagem' => ''],

        // ===================================
        // EFEITOS
        // ===================================
        ['id' => 'efeito_brilho', 'nome' => 'Efeito Brilho', 'descricao' => '✨ Um brilho especial no perfil', 'preco' => 70, 'icone' => 'fa-solid fa-wand-magic-sparkles', 'categoria' => 'efeitos', 'imagem' => ''],
        ['id' => 'efeito_particulas', 'nome' => 'Efeito Partículas', 'descricao' => '✨ Partículas flutuantes', 'preco' => 85, 'icone' => 'fa-solid fa-wand-magic-sparkles', 'categoria' => 'efeitos', 'imagem' => ''],
        ['id' => 'efeito_neon', 'nome' => 'Efeito Neon', 'descricao' => '✨ Brilho neon no perfil', 'preco' => 95, 'icone' => 'fa-solid fa-lightbulb', 'categoria' => 'efeitos', 'imagem' => ''],
        ['id' => 'efeito_sombra', 'nome' => 'Efeito Sombra', 'descricao' => '✨ Sombras suaves e elegantes', 'preco' => 55, 'icone' => 'fa-solid fa-circle-half-stroke', 'categoria' => 'efeitos', 'imagem' => ''],
        ['id' => 'efeito_arco_iris', 'nome' => 'Efeito Arco-Íris', 'descricao' => '🌈 Cores vibrantes no perfil', 'preco' => 100, 'icone' => 'fa-solid fa-rainbow', 'categoria' => 'efeitos', 'imagem' => ''],
        ['id' => 'efeito_estrelas', 'nome' => 'Efeito Estrelas Cadentes', 'descricao' => '⭐ Estrelas caindo no perfil', 'preco' => 110, 'icone' => 'fa-solid fa-star', 'categoria' => 'efeitos', 'imagem' => ''],
        ['id' => 'efeito_borboleta', 'nome' => 'Efeito Borboletas', 'descricao' => '🦋 Borboletas voando pelo perfil', 'preco' => 95, 'icone' => 'fa-solid fa-butterfly', 'categoria' => 'efeitos', 'imagem' => ''],
        ['id' => 'efeito_fogo', 'nome' => 'Efeito Fogo', 'descricao' => '🔥 Chamas de paixão', 'preco' => 105, 'icone' => 'fa-solid fa-fire', 'categoria' => 'efeitos', 'imagem' => ''],
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
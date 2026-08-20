<?php
session_start();

// ======================================
// VERIFICAR LOGIN
// ======================================

if (empty($_SESSION['codigo_usuario'])) {
    header("Location: ../login/index.php");
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];

$current = basename($_SERVER['PHP_SELF']);

// ======================================
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir = __DIR__ . '/../json/usuarios';

$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    exit("Pasta do usuário não encontrada.");
}

// ======================================
// FUNÇÃO PARA SALVAR JSON
// ======================================

function salvarJson($caminho, $dados)
{
    $json = json_encode(
        $dados,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    if ($json === false) {
        return false;
    }

    return file_put_contents(
        $caminho,
        $json,
        LOCK_EX
    ) !== false;
}

// ======================================
// ARQUIVO DA LOJA
// ======================================

$arquivoLoja = $pastaUsuario . '/loja.json';

// ======================================
// ESTRUTURA PADRÃO DA LOJA
// ======================================

$estruturaLojaPadrao = [

    'estrelas' => 0,

    'total_estudado' => 0,

    'itens_comprados' => [],

    'itens' => [

        // ===================================
        // TEMAS
        // ===================================

        [
            'id' => 'tema_azul',
            'nome' => 'Tema Azul',
            'descricao' => 'Mude o tema do perfil para azul',
            'preco' => 50,
            'icone' => 'fa-solid fa-palette',
            'categoria' => 'temas'
        ],

        [
            'id' => 'tema_roxo',
            'nome' => 'Tema Roxo',
            'descricao' => 'Mude o tema do perfil para roxo',
            'preco' => 50,
            'icone' => 'fa-solid fa-palette',
            'categoria' => 'temas'
        ],

        [
            'id' => 'tema_verde',
            'nome' => 'Tema Verde',
            'descricao' => 'Mude o tema do perfil para verde',
            'preco' => 50,
            'icone' => 'fa-solid fa-palette',
            'categoria' => 'temas'
        ],

        [
            'id' => 'tema_vermelho',
            'nome' => 'Tema Vermelho',
            'descricao' => 'Mude o tema do perfil para vermelho',
            'preco' => 55,
            'icone' => 'fa-solid fa-palette',
            'categoria' => 'temas'
        ],

        [
            'id' => 'tema_amarelo',
            'nome' => 'Tema Amarelo',
            'descricao' => 'Mude o tema do perfil para amarelo',
            'preco' => 55,
            'icone' => 'fa-solid fa-palette',
            'categoria' => 'temas'
        ],

        [
            'id' => 'tema_rosa',
            'nome' => 'Tema Rosa',
            'descricao' => 'Mude o tema do perfil para rosa',
            'preco' => 60,
            'icone' => 'fa-solid fa-palette',
            'categoria' => 'temas'
        ],

        [
            'id' => 'tema_preto',
            'nome' => 'Tema Preto',
            'descricao' => 'Mude o tema do perfil para preto',
            'preco' => 65,
            'icone' => 'fa-solid fa-palette',
            'categoria' => 'temas'
        ],

        // ===================================
        // INSÍGNIAS
        // ===================================

        [
            'id' => 'badge_estudioso',
            'nome' => 'Insígnia Estudioso',
            'descricao' => 'Mostre que você é um estudante dedicado',
            'preco' => 30,
            'icone' => 'fa-solid fa-graduation-cap',
            'categoria' => 'insignias'
        ],

        [
            'id' => 'badge_mestre',
            'nome' => 'Insígnia Mestre',
            'descricao' => 'Para os mestres do estudo',
            'preco' => 80,
            'icone' => 'fa-solid fa-crown',
            'categoria' => 'insignias'
        ],

        [
            'id' => 'badge_maratonista',
            'nome' => 'Insígnia Maratonista',
            'descricao' => 'Para quem estuda por longas horas',
            'preco' => 60,
            'icone' => 'fa-solid fa-running',
            'categoria' => 'insignias'
        ],

        [
            'id' => 'badge_dedicado',
            'nome' => 'Insígnia Dedicado',
            'descricao' => 'Dedicação é a chave do sucesso',
            'preco' => 40,
            'icone' => 'fa-solid fa-heart',
            'categoria' => 'insignias'
        ],

        [
            'id' => 'badge_campeao',
            'nome' => 'Insígnia Campeão',
            'descricao' => 'Você é um verdadeiro campeão',
            'preco' => 100,
            'icone' => 'fa-solid fa-trophy',
            'categoria' => 'insignias'
        ],

        [
            'id' => 'badge_lider',
            'nome' => 'Insígnia Líder',
            'descricao' => 'Liderança e determinação',
            'preco' => 70,
            'icone' => 'fa-solid fa-flag',
            'categoria' => 'insignias'
        ],

        [
            'id' => 'badge_persistente',
            'nome' => 'Insígnia Persistente',
            'descricao' => 'Nunca desista dos seus sonhos',
            'preco' => 50,
            'icone' => 'fa-solid fa-fire',
            'categoria' => 'insignias'
        ],

        // ===================================
        // EMOJIS
        // ===================================

        [
            'id' => 'emoji_foguete',
            'nome' => 'Emoji Foguete',
            'descricao' => '🚀 Mostre sua determinação',
            'preco' => 20,
            'icone' => 'fa-solid fa-rocket',
            'categoria' => 'emojis'
        ],

        [
            'id' => 'emoji_livro',
            'nome' => 'Emoji Livro',
            'descricao' => '📚 Para os amantes da leitura',
            'preco' => 15,
            'icone' => 'fa-solid fa-book',
            'categoria' => 'emojis'
        ],

        [
            'id' => 'emoji_lampada',
            'nome' => 'Emoji Lâmpada',
            'descricao' => '💡 Ideias brilhantes',
            'preco' => 25,
            'icone' => 'fa-solid fa-lightbulb',
            'categoria' => 'emojis'
        ],

        [
            'id' => 'emoji_alvo',
            'nome' => 'Emoji Alvo',
            'descricao' => '🎯 Foco no objetivo',
            'preco' => 22,
            'icone' => 'fa-solid fa-bullseye',
            'categoria' => 'emojis'
        ],

        [
            'id' => 'emoji_cerebro',
            'nome' => 'Emoji Cérebro',
            'descricao' => '🧠 Inteligência e raciocínio',
            'preco' => 28,
            'icone' => 'fa-solid fa-brain',
            'categoria' => 'emojis'
        ],

        [
            'id' => 'emoji_raio',
            'nome' => 'Emoji Raio',
            'descricao' => '⚡ Energia e velocidade',
            'preco' => 20,
            'icone' => 'fa-solid fa-bolt',
            'categoria' => 'emojis'
        ],

        [
            'id' => 'emoji_estrela_brilhante',
            'nome' => 'Emoji Estrela Brilhante',
            'descricao' => '🌟 Você é uma estrela',
            'preco' => 30,
            'icone' => 'fa-solid fa-star',
            'categoria' => 'emojis'
        ],

        [
            'id' => 'emoji_trofeu',
            'nome' => 'Emoji Troféu',
            'descricao' => '🏆 Para os vencedores',
            'preco' => 35,
            'icone' => 'fa-solid fa-trophy',
            'categoria' => 'emojis'
        ],

        // ===================================
        // FUNDOS
        // ===================================

        [
            'id' => 'fundo_neon',
            'nome' => 'Fundo Neon',
            'descricao' => 'Um fundo neon para seu perfil',
            'preco' => 100,
            'icone' => 'fa-solid fa-star',
            'categoria' => 'fundos'
        ],

        [
            'id' => 'fundo_galaxia',
            'nome' => 'Fundo Galáxia',
            'descricao' => 'Viaje pelas estrelas',
            'preco' => 120,
            'icone' => 'fa-solid fa-star',
            'categoria' => 'fundos'
        ],

        [
            'id' => 'fundo_por_do_sol',
            'nome' => 'Fundo Pôr do Sol',
            'descricao' => '🌅 Um pôr do sol relaxante',
            'preco' => 130,
            'icone' => 'fa-solid fa-sun',
            'categoria' => 'fundos'
        ],

        [
            'id' => 'fundo_oceano',
            'nome' => 'Fundo Oceano',
            'descricao' => '🌊 A calma do oceano',
            'preco' => 110,
            'icone' => 'fa-solid fa-water',
            'categoria' => 'fundos'
        ],

        [
            'id' => 'fundo_nebulosa',
            'nome' => 'Fundo Nebulosa',
            'descricao' => '🌌 Cores cósmicas',
            'preco' => 150,
            'icone' => 'fa-solid fa-cloud',
            'categoria' => 'fundos'
        ],

        [
            'id' => 'fundo_montanha',
            'nome' => 'Fundo Montanha',
            'descricao' => '🏔️ Picos e natureza',
            'preco' => 100,
            'icone' => 'fa-solid fa-mountain',
            'categoria' => 'fundos'
        ],

        // ===================================
        // MOLDURAS
        // ===================================

        [
            'id' => 'moldura_dourada',
            'nome' => 'Moldura Dourada',
            'descricao' => '🖼️ Uma moldura elegante e dourada',
            'preco' => 80,
            'icone' => 'fa-regular fa-image',
            'categoria' => 'molduras'
        ],

        [
            'id' => 'moldura_prata',
            'nome' => 'Moldura Prata',
            'descricao' => '🖼️ Sofisticação em prata',
            'preco' => 60,
            'icone' => 'fa-regular fa-image',
            'categoria' => 'molduras'
        ],

        [
            'id' => 'moldura_neon',
            'nome' => 'Moldura Neon',
            'descricao' => '🖼️ Uma moldura neon vibrante',
            'preco' => 90,
            'icone' => 'fa-regular fa-image',
            'categoria' => 'molduras'
        ],

        [
            'id' => 'moldura_minimalista',
            'nome' => 'Moldura Minimalista',
            'descricao' => '🖼️ Simples e elegante',
            'preco' => 40,
            'icone' => 'fa-regular fa-image',
            'categoria' => 'molduras'
        ],

        // ===================================
        // EFEITOS
        // ===================================

        [
            'id' => 'efeito_brilho',
            'nome' => 'Efeito Brilho',
            'descricao' => '✨ Um brilho especial no perfil',
            'preco' => 70,
            'icone' => 'fa-solid fa-wand-magic-sparkles',
            'categoria' => 'efeitos'
        ],

        [
            'id' => 'efeito_particulas',
            'nome' => 'Efeito Partículas',
            'descricao' => '✨ Partículas flutuantes',
            'preco' => 85,
            'icone' => 'fa-solid fa-wand-magic-sparkles',
            'categoria' => 'efeitos'
        ],

        [
            'id' => 'efeito_neon',
            'nome' => 'Efeito Neon',
            'descricao' => '✨ Brilho neon no perfil',
            'preco' => 95,
            'icone' => 'fa-solid fa-lightbulb',
            'categoria' => 'efeitos'
        ],

        [
            'id' => 'efeito_sombra',
            'nome' => 'Efeito Sombra',
            'descricao' => '✨ Sombras suaves e elegantes',
            'preco' => 55,
            'icone' => 'fa-solid fa-circle-half-stroke',
            'categoria' => 'efeitos'
        ]
    ]
];

// ======================================
// CRIAR LOJA.JSON SE NÃO EXISTIR
// ======================================

if (!file_exists($arquivoLoja)) {

    if (!salvarJson(
        $arquivoLoja,
        $estruturaLojaPadrao
    )) {
        exit("Não foi possível criar os dados da loja.");
    }
}

// ======================================
// CARREGAR LOJA.JSON
// ======================================

$conteudoLoja = file_get_contents($arquivoLoja);

if ($conteudoLoja === false) {

    $lojaData = $estruturaLojaPadrao;

} else {

    $lojaData = json_decode(
        $conteudoLoja,
        true
    );

    if (!is_array($lojaData)) {
        $lojaData = $estruturaLojaPadrao;
    }
}

// ======================================
// GARANTIR ESTRUTURA DA LOJA
// ======================================

if (
    !isset($lojaData['estrelas']) ||
    !is_numeric($lojaData['estrelas'])
) {
    $lojaData['estrelas'] = 0;
}

if (
    !isset($lojaData['total_estudado']) ||
    !is_numeric($lojaData['total_estudado'])
) {
    $lojaData['total_estudado'] = 0;
}

if (
    !isset($lojaData['itens_comprados']) ||
    !is_array($lojaData['itens_comprados'])
) {
    $lojaData['itens_comprados'] = [];
}

/*
|--------------------------------------------------------------------------
| IMPORTANTE
|--------------------------------------------------------------------------
|
| O catálogo sempre vem da estrutura padrão.
| Assim, mesmo que loja.json esteja vazio ou seja antigo,
| os itens continuam aparecendo.
|
*/

$lojaData['itens'] = $estruturaLojaPadrao['itens'];

// ======================================
// DADOS DO PERFIL
// ======================================

$arquivoPerfil = $pastaUsuario . '/perfil.json';

$perfilData = [];

if (file_exists($arquivoPerfil)) {

    $conteudoPerfil = file_get_contents(
        $arquivoPerfil
    );

    if ($conteudoPerfil !== false) {

        $perfilData = json_decode(
            $conteudoPerfil,
            true
        );
    }
}

if (!is_array($perfilData)) {
    $perfilData = [];
}

// ======================================
// CALCULAR ESTRELAS PELO POMODORO
// ======================================

$arquivoPomodoro = $pastaUsuario . '/pomodoro.json';

$totalMinutos = 0;

if (file_exists($arquivoPomodoro)) {

    $conteudoPomodoro = file_get_contents(
        $arquivoPomodoro
    );

    if ($conteudoPomodoro !== false) {

        $pomodoroData = json_decode(
            $conteudoPomodoro,
            true
        );

        if (is_array($pomodoroData)) {

            /*
             * Se seu pomodoro já tiver total_minutos,
             * usamos diretamente.
             */

            if (
                isset($pomodoroData['total_minutos']) &&
                is_numeric($pomodoroData['total_minutos'])
            ) {

                $totalMinutos = (int)
                    $pomodoroData['total_minutos'];
            }

            /*
             * Caso não tenha total_minutos,
             * tenta calcular pelas sessões.
             */

            elseif (
                isset($pomodoroData['sessions']) &&
                is_array($pomodoroData['sessions'])
            ) {

                foreach (
                    $pomodoroData['sessions']
                    as $sessao
                ) {

                    if (!is_array($sessao)) {
                        continue;
                    }

                    if (
                        isset($sessao['duration']) &&
                        is_numeric($sessao['duration'])
                    ) {
                        $totalMinutos +=
                            (int) $sessao['duration'];
                    }

                    elseif (
                        isset($sessao['duracao']) &&
                        is_numeric($sessao['duracao'])
                    ) {
                        $totalMinutos +=
                            (int) $sessao['duracao'];
                    }

                    elseif (
                        isset($sessao['minutos']) &&
                        is_numeric($sessao['minutos'])
                    ) {
                        $totalMinutos +=
                            (int) $sessao['minutos'];
                    }
                }
            }
        }
    }
}

// 1 estrela a cada 15 minutos
$estrelasCalculadas = floor(
    $totalMinutos / 15
);

// ======================================
// NÃO PERDER ESTRELAS JÁ GANHAS
// ======================================

$estrelasSalvas =
    isset($lojaData['estrelas'])
        ? (int) $lojaData['estrelas']
        : 0;

if ($estrelasSalvas > $estrelasCalculadas) {

    $estrelasGanhas =
        $estrelasSalvas;

} else {

    $estrelasGanhas =
        $estrelasCalculadas;
}

$lojaData['estrelas'] =
    $estrelasGanhas;

$lojaData['total_estudado'] =
    $totalMinutos;

// ======================================
// SALVAR ESTRUTURA ATUALIZADA
// ======================================

salvarJson(
    $arquivoLoja,
    $lojaData
);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Loja de Estrelas — FOAG
    </title>

    <link
        rel="stylesheet"
        href="loja.css"
    >

    <link
        rel="stylesheet"
        href="../m.escuro/dark_basee.css"
    >

    <link
        rel="stylesheet"
        href="dark_loja.css"
    >

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <script src="../m.escuro/dark-mode.js"></script>

    <script>

        window.LOJA_DATA =
            <?= json_encode(
                $lojaData,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;

        window.PERFIL_DATA =
            <?= json_encode(
                $perfilData,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;

        window.LOJA_SAVE_URL =
            "salvar_loja.php";

        /*
         * Mantemos USER_ID porque o loja.js
         * pode estar usando esse nome.
         *
         * Mas o valor agora é o codigo_usuario.
         */

        window.USER_ID =
            "<?= htmlspecialchars(
                $codigoUsuario,
                ENT_QUOTES,
                'UTF-8'
            ) ?>";

    </script>

</head>

<body>

<header class="cabecalho">

    FOAG

    <div class="header-icons">

    <a href="../configuracoes/configuracoes.php" class="link-configuracoes" title="Configurações">
      <i class="fa-solid fa-gear"></i>
          </a>

        <i
            id="icon-perfil"
            class="fa-regular fa-user"
            title="Perfil">
        </i>

        <i
            id="icon-sair"
            class="fa-solid fa-right-from-bracket"
            title="Sair">
        </i>

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

            <a href="../estudos/estudos.php" class="<?= $current === 'estudos.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-graduation-cap"></i> Estudos
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

        </nav>

    <main class="main-content">

        <div class="loja-header">

            <div class="loja-titulo">

                <h1>
                    <i class="fa-solid fa-store"></i>
                    Loja de Estrelas
                </h1>

                <p>
                    Ganhe estrelas estudando e personalize seu perfil!
                </p>

            </div>

            <div class="loja-saldo">

                <div class="saldo-estrelas">

                    <i class="fa-solid fa-star"></i>

                    <span id="saldoEstrelas">
                        <?= (int) $lojaData['estrelas'] ?>
                    </span>

                    <span class="label">
                        Estrelas
                    </span>

                </div>

                <div class="tempo-estudo">

                    <i class="fa-regular fa-clock"></i>

                    <span>
                        <?= floor(
                            $lojaData['total_estudado'] / 60
                        ) ?>h

                        <?= $lojaData['total_estudado'] % 60 ?>min
                    </span>

                    <span class="label">
                        Estudados
                    </span>

                </div>

            </div>

        </div>

        <!-- ==================================
             FILTROS
        =================================== -->

        <div class="loja-filtros">

            <button
                class="filtro-btn active"
                data-filtro="todos"
            >
                <i class="fa-solid fa-th-list"></i>
                Todos
            </button>

            <button
                class="filtro-btn"
                data-filtro="temas"
            >
                <i class="fa-solid fa-palette"></i>
                Temas
            </button>

            <button
                class="filtro-btn"
                data-filtro="insignias"
            >
                <i class="fa-solid fa-award"></i>
                Insígnias
            </button>

            <button
                class="filtro-btn"
                data-filtro="emojis"
            >
                <i class="fa-regular fa-face-smile"></i>
                Emojis
            </button>

            <button
                class="filtro-btn"
                data-filtro="fundos"
            >
                <i class="fa-solid fa-image"></i>
                Fundos
            </button>

            <button
                class="filtro-btn"
                data-filtro="molduras"
            >
                <i class="fa-regular fa-image"></i>
                Molduras
            </button>

            <button
                class="filtro-btn"
                data-filtro="efeitos"
            >
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                Efeitos
            </button>

            <button
                class="filtro-btn"
                data-filtro="comprados"
            >
                <i class="fa-solid fa-check-circle"></i>
                Meus Itens
            </button>

        </div>

        <!-- ==================================
             ITENS DA LOJA
        =================================== -->

        <div
            class="loja-grid"
            id="lojaGrid"
        >
        </div>

        <!-- ==================================
             MODAL DE COMPRA
        =================================== -->

        <div
            id="modal-compra"
            class="modal-compra"
        >

            <div class="modal-content">

                <div
                    class="modal-icon"
                    id="modalIcone"
                >
                    <i class="fa-solid fa-gift"></i>
                </div>

                <h3 id="modalTitulo">
                    Comprar Item
                </h3>

                <p id="modalDescricao">
                    Tem certeza que deseja comprar este item?
                </p>

                <div class="modal-preco">

                    <i class="fa-solid fa-star"></i>

                    <span id="modalPreco">
                        0
                    </span>

                </div>

                <div class="modal-buttons">

                    <button
                        id="confirmar-compra"
                        class="btn-comprar"
                    >
                        <i class="fa-solid fa-cart-shopping"></i>
                        Comprar
                    </button>

                    <button
                        id="cancelar-compra"
                        class="btn-cancelar"
                    >
                        Cancelar
                    </button>

                </div>

            </div>

        </div>

        <!-- ==================================
             MODAL DE SUCESSO
        =================================== -->

        <div
            id="modal-sucesso"
            class="modal-sucesso"
        >

            <div class="modal-content">

                <div class="sucesso-icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>

                <h3>
                    Compra realizada!
                </h3>

                <p id="mensagemSucesso">
                    Item adicionado ao seu perfil!
                </p>

                <button
                    id="fechar-sucesso"
                    class="btn-modal"
                >
                    OK
                </button>

            </div>

        </div>

        <!-- ==================================
             MODAL DO PERFIL
        =================================== -->

        <div
            id="modal-perfil"
            class="modal-perfil"
        >

            <div class="modal-content perfil-content">

                <div class="perfil-header">

                    <h2>
                        <i class="fa-regular fa-user"></i>
                        Meu Perfil Personalizado
                    </h2>

                    <button
                        id="fechar-perfil"
                        class="btn-fechar"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                </div>

                <div
                    class="perfil-preview"
                    id="perfilPreview"
                >

                    <div class="avatar-container">

                        <div class="avatar">
                            <i class="fa-regular fa-user"></i>
                        </div>

                        <div
                            class="avatar-badges"
                            id="avatarBadges"
                        >
                        </div>

                    </div>

                    <div class="perfil-info">

                        <h3 id="perfilNome">
                            <?= htmlspecialchars(
                                $perfilData['nome']
                                    ?? 'Estudante',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h3>

                        <div
                            class="perfil-emojis"
                            id="perfilEmojis"
                        >
                        </div>

                        <div class="perfil-stats">

                            <span>
                                <i class="fa-solid fa-star"></i>

                                <span id="perfilEstrelas">
                                    <?= (int) $lojaData['estrelas'] ?>
                                </span>

                                estrelas
                            </span>

                            <span>
                                <i class="fa-regular fa-clock"></i>

                                <span id="perfilTempo">
                                    <?= floor(
                                        $lojaData['total_estudado'] / 60
                                    ) ?>h
                                </span>

                                estudados
                            </span>

                        </div>

                    </div>

                </div>

                <div class="perfil-itens">

                    <h4>
                        Itens Ativos
                    </h4>

                    <div
                        class="itens-ativos"
                        id="itensAtivos"
                    >
                    </div>

                </div>

                <button
                    id="fechar-perfil-btn"
                    class="btn-modal"
                >
                    Fechar
                </button>

            </div>

        </div>

        <!-- ==================================
             BOTÃO SECRETO
        =================================== -->

        <div class="botao-secreto-container">

            <button
                id="botaoEstrelasSecretas"
                class="botao-secreto"
                title="Clique 5 vezes rapidamente para ganhar 10 estrelas!"
            >

                <i class="fa-solid fa-star"></i>

                <span class="texto-secreto">
                    🔮
                </span>

            </button>

        </div>

        <!-- ==================================
             MODAL ESTRELAS
        =================================== -->

        <div
            id="modal-estrelas"
            class="modal-estrelas"
        >

            <div class="modal-content">

                <div class="estrelas-icon">
                    <i class="fa-solid fa-star"></i>
                </div>

                <h3>
                    🌟 Estrelas adicionadas!
                </h3>

                <p id="mensagemEstrelas">
                    Você ganhou
                    <strong>10 estrelas</strong>!
                </p>

                <button
                    id="fechar-estrelas"
                    class="btn-modal"
                >
                    Legal! 🚀
                </button>

            </div>

        </div>

    </main>

</div>

<!-- ======================================
     MODAL LOGOUT
======================================= -->

<div
    id="logout-modal"
    class="modal"
>

    <div class="modal-content">

        <h3>
            Ah... já vai?
        </h3>

        <h4>
            Tem certeza de que deseja sair?
        </h4>

        <div class="modal-buttons">

            <button id="confirm-logout">
                Sim
            </button>

            <button id="cancel-logout">
                Cancelar
            </button>

        </div>

    </div>

</div>

<footer>
    &copy; 2025 FOAG. Todos os direitos reservados.
</footer>

<!-- ======================================
     JAVASCRIPT DA LOJA
======================================= -->

<script src="loja.js"></script>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        // ==================================
        // CONFIGURAÇÕES
        // ==================================

        const configuracoesIcon =
            document.getElementById(
                'icon-configuracoes'
            );

        if (configuracoesIcon) {

            configuracoesIcon.addEventListener(
                'click',
                function () {

                    window.location.href =
                        '../configuracoes/configuracoes.php';
                }
            );
        }

        // ==================================
        // PERFIL
        // ==================================

        const perfilIcon =
            document.getElementById(
                'icon-perfil'
            );

        const modalPerfil =
            document.getElementById(
                'modal-perfil'
            );

        perfilIcon?.addEventListener(
            'click',
            function () {

                if (!modalPerfil) {
                    return;
                }

                modalPerfil.style.display =
                    'flex';

                document.body.style.overflow =
                    'hidden';
            }
        );

        // ==================================
        // LOGOUT
        // ==================================

        const logoutModal =
            document.getElementById(
                'logout-modal'
            );

        const iconSair =
            document.getElementById(
                'icon-sair'
            );

        const confirmarLogout =
            document.getElementById(
                'confirm-logout'
            );

        const cancelarLogout =
            document.getElementById(
                'cancel-logout'
            );

        iconSair?.addEventListener(
            'click',
            function () {

                if (logoutModal) {
                    logoutModal.style.display =
                        'flex';
                }
            }
        );

        confirmarLogout?.addEventListener(
            'click',
            function () {

                window.location.href =
                    '../login/logout.php';
            }
        );

        cancelarLogout?.addEventListener(
            'click',
            function () {

                if (logoutModal) {
                    logoutModal.style.display =
                        'none';
                }
            }
        );

        logoutModal?.addEventListener(
            'click',
            function (evento) {

                if (
                    evento.target ===
                    logoutModal
                ) {
                    logoutModal.style.display =
                        'none';
                }
            }
        );

        // ==================================
        // FECHAR PERFIL
        // ==================================

        document.getElementById(
            'fechar-perfil'
        )?.addEventListener(
            'click',
            function () {

                if (modalPerfil) {
                    modalPerfil.style.display =
                        'none';
                }

                document.body.style.overflow =
                    '';
            }
        );

        document.getElementById(
            'fechar-perfil-btn'
        )?.addEventListener(
            'click',
            function () {

                if (modalPerfil) {
                    modalPerfil.style.display =
                        'none';
                }

                document.body.style.overflow =
                    '';
            }
        );

        modalPerfil?.addEventListener(
            'click',
            function (evento) {

                if (
                    evento.target ===
                    modalPerfil
                ) {

                    modalPerfil.style.display =
                        'none';

                    document.body.style.overflow =
                        '';
                }
            }
        );
    }
);

</script>

</body>
</html>
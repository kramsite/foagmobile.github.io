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
// CAMINHOS
// ======================================

$baseJsonDir = __DIR__ . '/../json/usuarios';

$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;
$arquivoPerfil = $pastaUsuario . '/perfil.json';

if (!is_dir($pastaUsuario)) {
    exit("Pasta do usuário não encontrada.");
}

// ======================================
// LER JSON
// ======================================

function lerJson($arquivo)
{
    if (!file_exists($arquivo)) {
        return [];
    }

    $conteudo = file_get_contents($arquivo);

    if ($conteudo === false) {
        return [];
    }

    $dados = json_decode($conteudo, true);

    return is_array($dados) ? $dados : [];
}

// ======================================
// NORMALIZAR TEXTO
// ======================================

function normalizarTexto($texto)
{
    $texto = trim((string) $texto);

    if (function_exists('mb_strtolower')) {
        return mb_strtolower(
            $texto,
            'UTF-8'
        );
    }

    return strtolower($texto);
}

// ======================================
// DESCOBRIR REGIÃO PELO ESTADO
// ======================================

function obterRegiaoBrasil($estado)
{
    $estado = strtoupper(
        trim((string) $estado)
    );

    $regioes = [

        'Norte' => [
            'AC',
            'AP',
            'AM',
            'PA',
            'RO',
            'RR',
            'TO'
        ],

        'Nordeste' => [
            'AL',
            'BA',
            'CE',
            'MA',
            'PB',
            'PE',
            'PI',
            'RN',
            'SE'
        ],

        'Centro-Oeste' => [
            'DF',
            'GO',
            'MT',
            'MS'
        ],

        'Sudeste' => [
            'ES',
            'MG',
            'RJ',
            'SP'
        ],

        'Sul' => [
            'PR',
            'RS',
            'SC'
        ]
    ];

    foreach ($regioes as $regiao => $estados) {

        if (
            in_array(
                $estado,
                $estados,
                true
            )
        ) {
            return $regiao;
        }
    }

    return '';
}

// ======================================
// FORMATAR TEMPO
// ======================================

function formatarTempo($minutos)
{
    $minutos = (int) $minutos;

    $horas = intdiv(
        $minutos,
        60
    );

    $restante =
        $minutos % 60;

    if (
        $horas > 0
        &&
        $restante > 0
    ) {
        return
            $horas
            . 'h '
            . $restante
            . 'min';
    }

    if ($horas > 0) {
        return $horas . 'h';
    }

    return $restante . 'min';
}

// ======================================
// CALCULAR POMODORO
// ======================================

function calcularPomodoro($arquivo)
{
    $dados = lerJson($arquivo);

    if (
        !isset($dados['sessions'])
        ||
        !is_array($dados['sessions'])
    ) {
        return 0;
    }

    $totalMinutos = 0;

    foreach ($dados['sessions'] as $sessao) {

        if (
            ($sessao['mode'] ?? '')
            !== 'focus'
        ) {
            continue;
        }

        $minutos =
            $sessao['minutes']
            ?? 0;

        if (is_numeric($minutos)) {

            $totalMinutos +=
                (int) $minutos;
        }
    }

    return $totalMinutos;
}

// ======================================
// CALCULAR MÉDIA DAS NOTAS
// ======================================

function calcularMediaNotas($arquivo)
{
    $dados = lerJson($arquivo);

    if (
        !isset($dados['periodos'])
        ||
        !is_array($dados['periodos'])
    ) {
        return 0;
    }

    $pesos =
        $dados['pesos']
        ?? [];

    $somaNotas = 0;
    $somaPesos = 0;

    foreach (
        $dados['periodos']
        as $periodo
    ) {

        if (
            !isset($periodo['notas'])
            ||
            !is_array($periodo['notas'])
        ) {
            continue;
        }

        foreach (
            $periodo['notas']
            as $notasMateria
        ) {

            if (!is_array($notasMateria)) {
                continue;
            }

            foreach (
                $notasMateria
                as $numeroAvaliacao => $nota
            ) {

                if (!is_numeric($nota)) {
                    continue;
                }

                $peso = 1;

                if (
                    isset(
                        $pesos[
                            $numeroAvaliacao
                        ]
                    )
                    &&
                    is_numeric(
                        $pesos[
                            $numeroAvaliacao
                        ]
                    )
                ) {

                    $peso =
                        (float)
                        $pesos[
                            $numeroAvaliacao
                        ];
                }

                $somaNotas +=
                    (float) $nota
                    * $peso;

                $somaPesos +=
                    $peso;
            }
        }
    }

    if ($somaPesos <= 0) {
        return 0;
    }

    return
        $somaNotas
        /
        $somaPesos;
}

// ======================================
// CARREGAR PERFIL ATUAL
// ======================================

$dadosPerfilAtual =
    lerJson(
        $arquivoPerfil
    );

$usuarioAtual =
    $dadosPerfilAtual['nome']
    ?? $_SESSION['user_nome']
    ?? $_SESSION['usuario']
    ?? 'Usuário FOAG';

$estadoUsuarioAtual =
    strtoupper(
        trim(
            $dadosPerfilAtual['estado']
            ?? ''
        )
    );

$cidadeUsuarioAtual =
    trim(
        $dadosPerfilAtual['cidade']
        ?? ''
    );

$regiaoUsuarioAtual =
    obterRegiaoBrasil(
        $estadoUsuarioAtual
    );

$_SESSION['user_nome'] =
    $usuarioAtual;

$_SESSION['usuario'] =
    $usuarioAtual;

// ======================================
// CARREGAR TODOS OS USUÁRIOS
// ======================================

$usuarios = [];

$pastasUsuarios = glob(
    $baseJsonDir . '/*',
    GLOB_ONLYDIR
);

if ($pastasUsuarios === false) {
    $pastasUsuarios = [];
}

foreach ($pastasUsuarios as $pasta) {

    $codigo =
        basename($pasta);

    $perfil =
        lerJson(
            $pasta
            . '/perfil.json'
        );

    if (empty($perfil['nome'])) {
        continue;
    }

    // ==================================
    // LOCALIZAÇÃO
    // ==================================

    $estado =
        strtoupper(
            trim(
                $perfil['estado']
                ?? ''
            )
        );

    $cidade =
        trim(
            $perfil['cidade']
            ?? ''
        );

    $regiao =
        obterRegiaoBrasil(
            $estado
        );

    // ==================================
    // ESTRELAS
    // ==================================

    $estrelas = 0;

    $arquivoPontos =
        $pasta
        . '/pontos.json';

    if (file_exists($arquivoPontos)) {

        $dadosPontos =
            lerJson(
                $arquivoPontos
            );

        if (
            isset(
                $dadosPontos['estrelas']
            )
            &&
            is_numeric(
                $dadosPontos['estrelas']
            )
        ) {

            $estrelas =
                (int)
                $dadosPontos['estrelas'];
        }
    }

    // ==================================
    // POMODORO
    // ==================================

    $minutosPomodoro =
        calcularPomodoro(
            $pasta
            . '/pomodoro.json'
        );

    // ==================================
    // NOTAS
    // ==================================

    $mediaNotas =
        calcularMediaNotas(
            $pasta
            . '/notas.json'
        );

    // ==================================
    // USUÁRIO
    // ==================================

    $usuarios[] = [

        'codigo_usuario' =>
            $codigo,

        'nome' =>
            $perfil['nome'],

        'avatar' =>
            '👤',

        'estado' =>
            $estado,

        'cidade' =>
            $cidade,

        'regiao' =>
            $regiao,

        'estrelas' =>
            $estrelas,

        'pomodoro' =>
            $minutosPomodoro,

        'notas' =>
            $mediaNotas
    ];
}

// ======================================
// FILTRAR POR NÍVEL
// ======================================

function filtrarUsuariosPorNivel(
    $usuarios,
    $nivel,
    $estadoAtual,
    $cidadeAtual,
    $regiaoAtual
) {

    if ($nivel === 'nacional') {
        return $usuarios;
    }

    $resultado = [];

    foreach ($usuarios as $usuario) {

        // ==================================
        // ESTADUAL
        // ==================================

        if ($nivel === 'estadual') {

            if ($estadoAtual === '') {
                continue;
            }

            if (
                ($usuario['estado'] ?? '')
                === $estadoAtual
            ) {
                $resultado[] =
                    $usuario;
            }
        }

        // ==================================
        // MUNICIPAL
        // ==================================

        elseif ($nivel === 'municipal') {

            if (
                $estadoAtual === ''
                ||
                $cidadeAtual === ''
            ) {
                continue;
            }

            if (
                ($usuario['estado'] ?? '')
                === $estadoAtual
                &&
                normalizarTexto(
                    $usuario['cidade']
                    ?? ''
                )
                ===
                normalizarTexto(
                    $cidadeAtual
                )
            ) {

                $resultado[] =
                    $usuario;
            }
        }

        // ==================================
        // REGIONAL
        // ==================================

        elseif ($nivel === 'regional') {

            if ($regiaoAtual === '') {
                continue;
            }

            if (
                ($usuario['regiao'] ?? '')
                === $regiaoAtual
            ) {

                $resultado[] =
                    $usuario;
            }
        }
    }

    return $resultado;
}

// ======================================
// USUÁRIOS POR NÍVEL
// ======================================

$usuariosPorNivel = [

    'nacional' =>
        filtrarUsuariosPorNivel(
            $usuarios,
            'nacional',
            $estadoUsuarioAtual,
            $cidadeUsuarioAtual,
            $regiaoUsuarioAtual
        ),

    'estadual' =>
        filtrarUsuariosPorNivel(
            $usuarios,
            'estadual',
            $estadoUsuarioAtual,
            $cidadeUsuarioAtual,
            $regiaoUsuarioAtual
        ),

    'municipal' =>
        filtrarUsuariosPorNivel(
            $usuarios,
            'municipal',
            $estadoUsuarioAtual,
            $cidadeUsuarioAtual,
            $regiaoUsuarioAtual
        ),

    'regional' =>
        filtrarUsuariosPorNivel(
            $usuarios,
            'regional',
            $estadoUsuarioAtual,
            $cidadeUsuarioAtual,
            $regiaoUsuarioAtual
        )
];

// ======================================
// CRIAR RANKING
// ======================================

function criarRanking(
    $usuarios,
    $campo,
    $formatador = null
) {

    $jogadores = [];

    foreach ($usuarios as $usuario) {

        $valorBruto =
            $usuario[$campo]
            ?? 0;

        $valorExibicao =
            $valorBruto;

        if (is_callable($formatador)) {

            $valorExibicao =
                $formatador(
                    $valorBruto
                );
        }

        $jogadores[] = [

            'codigo_usuario' =>
                $usuario[
                    'codigo_usuario'
                ],

            'nome' =>
                $usuario['nome'],

            'avatar' =>
                $usuario['avatar'],

            'estado' =>
                $usuario['estado'],

            'cidade' =>
                $usuario['cidade'],

            'regiao' =>
                $usuario['regiao'],

            'valor_bruto' =>
                $valorBruto,

            'valor' =>
                $valorExibicao,

            'nivel' => 0
        ];
    }

    // ==================================
    // ORDENAR
    // ==================================

    usort(
        $jogadores,
        function ($a, $b) {

            $comparacao =
                $b['valor_bruto']
                <=>
                $a['valor_bruto'];

            if ($comparacao !== 0) {
                return $comparacao;
            }

            return strcasecmp(
                $a['nome'],
                $b['nome']
            );
        }
    );

    // ==================================
    // DEFINIR POSIÇÃO
    // ==================================

    foreach (
        $jogadores
        as $indice => &$jogador
    ) {

        $jogador['nivel'] =
            $indice + 1;
    }

    unset($jogador);

    return $jogadores;
}

// ======================================
// CRIAR NÍVEIS DE UMA CATEGORIA
// ======================================

function criarNiveisRanking(
    $usuariosPorNivel,
    $campo,
    $formatador = null
) {

    $resultado = [];

    foreach (
        $usuariosPorNivel
        as $nivel => $usuariosNivel
    ) {

        $resultado[$nivel] = [
            'jogadores' =>
                criarRanking(
                    $usuariosNivel,
                    $campo,
                    $formatador
                )
        ];
    }

    return $resultado;
}

// ======================================
// NOMES DOS NÍVEIS
// ======================================

$nomesNiveis = [

    'nacional' =>
        '🌍 Nacional',

    'estadual' =>
        $estadoUsuarioAtual !== ''
            ? '🏛️ Estadual (' . $estadoUsuarioAtual . ')'
            : '🏛️ Estadual',

    'municipal' =>
        $cidadeUsuarioAtual !== ''
            ? '🏙️ Municipal (' . $cidadeUsuarioAtual . ')'
            : '🏙️ Municipal',

    'regional' =>
        $regiaoUsuarioAtual !== ''
            ? '📌 Regional (' . $regiaoUsuarioAtual . ')'
            : '📌 Regional'
];

// ======================================
// RANKINGS
// ======================================

$rankings = [

    'estrelas' => [

        'titulo' =>
            '⭐ Mais Estrelas',

        'icone' =>
            '⭐',

        'cor' =>
            '#ffd700',

        'descricao' =>
            'Quem tem mais estrelas acumuladas',

        'niveis' =>
            criarNiveisRanking(
                $usuariosPorNivel,
                'estrelas',
                function ($valor) {
                    return (int) $valor;
                }
            )
    ],

    'pomodoro' => [

        'titulo' =>
            '⏱️ Mais Tempo no Pomodoro',

        'icone' =>
            '⏱️',

        'cor' =>
            '#4caf50',

        'descricao' =>
            'Quem estudou mais tempo com Pomodoro',

        'niveis' =>
            criarNiveisRanking(
                $usuariosPorNivel,
                'pomodoro',
                function ($valor) {
                    return formatarTempo(
                        $valor
                    );
                }
            )
    ],

    'notas' => [

        'titulo' =>
            '📚 Melhores Notas',

        'icone' =>
            '📚',

        'cor' =>
            '#9c27b0',

        'descricao' =>
            'Quem tem as melhores médias',

        'niveis' =>
            criarNiveisRanking(
                $usuariosPorNivel,
                'notas',
                function ($valor) {

                    return number_format(
                        (float) $valor,
                        1,
                        ',',
                        ''
                    );
                }
            )
    ]
];

// ======================================
// COLOCAR NOME NOS NÍVEIS
// ======================================

foreach ($rankings as &$ranking) {

    foreach ($ranking['niveis'] as $nivelKey => &$nivel) {

        $nivel['nome'] =
            $nomesNiveis[$nivelKey]
            ?? ucfirst($nivelKey);
    }

    unset($nivel);
}

unset($ranking);

// ======================================
// ORDEM DAS CATEGORIAS
// ======================================

$categoriasOrdenadas = [
    'estrelas',
    'pomodoro',
    'notas'
];

$niveisDisponiveis = [
    'nacional',
    'estadual',
    'municipal',
    'regional'
];

// ======================================
// QUANTIDADE POR NÍVEL
// ======================================

$quantidadesNivel = [];

foreach (
    $niveisDisponiveis
    as $nivelKey
) {

    $quantidadesNivel[$nivelKey] =
        count(
            $usuariosPorNivel[
                $nivelKey
            ] ?? []
        );
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Ranking — FOAG</title>

    <link
        rel="stylesheet"
        href="rank.css"
    >

    <link
        rel="stylesheet"
        href="../m.escuro/dark_basee.css"
    >

    <link
        rel="stylesheet"
        href="dark_rank.css"
    >

    <!-- ACESSIBILIDADE GLOBAL -->
    <link
        rel="stylesheet"
        href="../acessibilidade/acessibilidade.css"
    >

    <script
        src="../acessibilidade/acessibilidade.js?v=4"
        defer
    ></script>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <script src="../m.escuro/dark-mode.js"></script>

          <?php include '../configuracoes/geral.php'; ?>
<script src="<?= get_aparencia_path() ?>"></script>
 <script src="../configuracoes/aparencia.js?v=1"></script>

</head>

<body>

<header class="cabecalho">

    FOAG

    <div class="header-icons">

        <a
            href="../configuracoes/configuracoes.php"
            class="link-configuracoes"
            title="Configurações"
        >
            <i class="fa-solid fa-gear"></i>
        </a>

        <i
            id="icon-perfil"
            class="fa-regular fa-user"
            title="Perfil"
        ></i>

        <i
            id="icon-sair"
            class="fa-solid fa-right-from-bracket"
            title="Sair"
        ></i>

    </div>

</header>

<div class="container">

<nav class="menu">

    <a href="../inicioo/inicio.php">
        <i class="fa-solid fa-house"></i>
        Início
    </a>

    <a href="../calend/calendario.php">
        <i class="fa-solid fa-calendar-days"></i>
        Calendário
    </a>

    <a href="../bloco/agenda.php">
        <i class="fa-solid fa-book"></i>
        Agenda
    </a>

    <a href="../estudos/estudos.php">
        <i class="fa-solid fa-graduation-cap"></i>
        Estudos
    </a>

    <a href="../notas/notas.php">
        <i class="fa-solid fa-check-double"></i>
        Boletim
    </a>

    <a href="../loja/loja.php">
        <i class="fa-solid fa-store"></i>
        Loja
    </a>

    <a href="../rank/rank.php" class="active">
        <i class="fa-solid fa-trophy"></i>
        Ranking
    </a>

</nav>

<main class="main-content">

    <div class="ranking-header">

        <div class="ranking-titulo">

            <h1>
                <i class="fa-solid fa-trophy"></i>
                Ranking FOAG
            </h1>

            <p>
                Veja quem está se destacando em cada categoria!
            </p>

        </div>

    </div>

    <!-- ==================================
         ABAS
    =================================== -->

    <div
        class="abas-niveis"
        id="abasNiveis"
    >

        <button
            class="aba-nivel active"
            data-nivel="nacional"
            type="button"
        >

            <i class="fa-solid fa-globe-americas"></i>

            Nacional

            <span class="badge-nivel">
                <?= $quantidadesNivel['nacional'] ?>
            </span>

        </button>

        <button
            class="aba-nivel"
            data-nivel="estadual"
            type="button"
        >

            <i class="fa-solid fa-building"></i>

            Estadual

            <span class="badge-nivel">
                <?= $quantidadesNivel['estadual'] ?>
            </span>

        </button>

        <button
            class="aba-nivel"
            data-nivel="municipal"
            type="button"
        >

            <i class="fa-solid fa-city"></i>

            Municipal

            <span class="badge-nivel">
                <?= $quantidadesNivel['municipal'] ?>
            </span>

        </button>

        <button
            class="aba-nivel"
            data-nivel="regional"
            type="button"
        >

            <i class="fa-solid fa-map-location-dot"></i>

            Regional

            <span class="badge-nivel">
                <?= $quantidadesNivel['regional'] ?>
            </span>

        </button>

    </div>

    <!-- ==================================
         RANKING
    =================================== -->

    <div class="rank-layout">

        <!-- MENU DE CATEGORIAS -->

        <div class="rank-menu-lateral">

            <div class="menu-titulo">

                <i class="fa-solid fa-list"></i>

                Categorias

            </div>

            <?php foreach (
                $categoriasOrdenadas
                as $index => $key
            ): ?>

                <?php

                $ranking =
                    $rankings[$key];

                $ativo =
                    $index === 0
                    ? 'active'
                    : '';

                $totalJogadores =
                    count(
                        $ranking[
                            'niveis'
                        ][
                            'nacional'
                        ][
                            'jogadores'
                        ]
                    );

                ?>

                <div
                    class="menu-item <?= $ativo ?>"
                    data-categoria="<?= htmlspecialchars($key) ?>"
                >

                    <span class="item-icone">
                        <?= htmlspecialchars($ranking['icone']) ?>
                    </span>

                    <span class="item-nome">
                        <?= htmlspecialchars($ranking['titulo']) ?>
                    </span>

                    <span class="item-badge">
                        <?= $totalJogadores ?>
                    </span>

                    <span class="indicador-ativo"></span>

                </div>

            <?php endforeach; ?>

        </div>

        <!-- CONTEÚDO -->

        <div class="rank-conteudo">

            <?php

            $primeiro = true;

            foreach (
                $categoriasOrdenadas
                as $key
            ):

                $ranking =
                    $rankings[$key];

                $hidden =
                    $primeiro
                    ? ''
                    : 'hidden';

                $primeiro = false;

                $jogadoresNacional =
                    $ranking[
                        'niveis'
                    ][
                        'nacional'
                    ][
                        'jogadores'
                    ];

            ?>

                <div
                    class="rank-full <?= $hidden ?>"
                    data-categoria="<?= htmlspecialchars($key) ?>"
                    id="rank-<?= htmlspecialchars($key) ?>"
                >

                    <!-- CABEÇALHO -->

                    <div
                        class="rank-full-header"
                        style="border-bottom-color: <?= htmlspecialchars($ranking['cor']) ?>;"
                    >

                        <div class="rank-info">

                            <div
                                class="icone-grande"
                                style="
                                    background: <?= htmlspecialchars($ranking['cor']) ?>22;
                                    color: <?= htmlspecialchars($ranking['cor']) ?>;
                                "
                            >
                                <?= htmlspecialchars($ranking['icone']) ?>
                            </div>

                            <div class="titulo">

                                <h2>
                                    <?= htmlspecialchars($ranking['titulo']) ?>
                                </h2>

                                <p>
                                    <?= htmlspecialchars($ranking['descricao']) ?>
                                </p>

                            </div>

                        </div>

                        <div class="rank-stats">

                            <span class="stat">

                                <i class="fa-solid fa-users"></i>

                                <span class="total-jogadores">
                                    <?= count($jogadoresNacional) ?>
                                </span>

                                jogadores

                            </span>

                            <span class="stat">

                                <i
                                    class="fa-solid fa-trophy"
                                    style="color: <?= htmlspecialchars($ranking['cor']) ?>;"
                                ></i>

                                Top 1:

                                <span class="top1-nome">

                                    <?=
                                        !empty($jogadoresNacional)
                                        ? htmlspecialchars(
                                            $jogadoresNacional[0]['nome']
                                        )
                                        : '-'
                                    ?>

                                </span>

                            </span>

                        </div>

                    </div>

                    <!-- CORPO -->

                    <div class="rank-full-body">

                        <?php foreach (
                            $niveisDisponiveis
                            as $nivelKey
                        ): ?>

                            <?php

                            $jogadores =
                                $ranking[
                                    'niveis'
                                ][
                                    $nivelKey
                                ][
                                    'jogadores'
                                ]
                                ?? [];

                            $mostrar =
                                $nivelKey === 'nacional'
                                ? 'block'
                                : 'none';

                            ?>

                            <div
                                class="nivel-conteudo"
                                data-nivel="<?= htmlspecialchars($nivelKey) ?>"
                                style="display: <?= $mostrar ?>;"
                            >

                                <?php if (empty($jogadores)): ?>

                                    <div
                                        style="
                                            padding: 35px 20px;
                                            text-align: center;
                                            color: #94a3b8;
                                        "
                                    >

                                        <i
                                            class="fa-solid fa-ranking-star"
                                            style="
                                                font-size: 30px;
                                                margin-bottom: 10px;
                                            "
                                        ></i>

                                        <p>
                                            Nenhum usuário encontrado neste ranking.
                                        </p>

                                    </div>

                                <?php else: ?>

                                    <?php foreach (
                                        $jogadores
                                        as $index => $jogador
                                    ): ?>

                                        <?php

                                        $posicao =
                                            $index + 1;

                                        $isUsuario =
                                            $jogador[
                                                'codigo_usuario'
                                            ]
                                            ===
                                            $codigoUsuario;

                                        $podiumClass = '';
                                        $medalha = '';

                                        if ($posicao === 1) {

                                            $podiumClass =
                                                'podium-1';

                                            $medalha =
                                                '🥇';

                                        } elseif ($posicao === 2) {

                                            $podiumClass =
                                                'podium-2';

                                            $medalha =
                                                '🥈';

                                        } elseif ($posicao === 3) {

                                            $podiumClass =
                                                'podium-3';

                                            $medalha =
                                                '🥉';
                                        }

                                        // ==================================
                                        // LOCALIZAÇÃO MOSTRADA
                                        // ==================================

                                        $localizacao = '';

                                        if (
                                            !empty(
                                                $jogador['cidade']
                                            )
                                            &&
                                            !empty(
                                                $jogador['estado']
                                            )
                                        ) {

                                            $localizacao =
                                                $jogador['cidade']
                                                . ' - '
                                                . $jogador['estado'];

                                        } elseif (
                                            !empty(
                                                $jogador['estado']
                                            )
                                        ) {

                                            $localizacao =
                                                $jogador['estado'];
                                        }

                                        ?>

                                        <div
                                            class="
                                                rank-full-item
                                                <?= $isUsuario ? 'usuario-destaque' : '' ?>
                                                <?= $podiumClass ?>
                                            "
                                        >

                                            <div class="posicao">

                                                <?php if ($medalha): ?>

                                                    <span class="medalha">
                                                        <?= $medalha ?>
                                                    </span>

                                                <?php else: ?>

                                                    <span class="numero">
                                                        #<?= $posicao ?>
                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                            <div class="avatar">
                                                <?= $jogador['avatar'] ?>
                                            </div>

                                            <div class="info">

                                                <div class="nome">

                                                    <?= htmlspecialchars(
                                                        $jogador['nome']
                                                    ) ?>

                                                    <?php if ($isUsuario): ?>

                                                        <span class="badge-eu">
                                                            Você
                                                        </span>

                                                    <?php endif; ?>

                                                    <?php if ($localizacao !== ''): ?>

                                                        <span class="tag-local">

                                                            <i class="fa-solid fa-location-dot"></i>

                                                            <?= htmlspecialchars(
                                                                $localizacao
                                                            ) ?>

                                                        </span>

                                                    <?php endif; ?>

                                                </div>

                                                <div class="detalhes">

                                                    <?= $posicao ?>º lugar

                                                    <?php if (
                                                        $nivelKey === 'regional'
                                                        &&
                                                        !empty(
                                                            $jogador['regiao']
                                                        )
                                                    ): ?>

                                                        • <?= htmlspecialchars(
                                                            $jogador['regiao']
                                                        ) ?>

                                                    <?php endif; ?>

                                                </div>

                                            </div>

                                            <div
                                                class="valor"
                                                style="color: <?= htmlspecialchars($ranking['cor']) ?>;"
                                            >

                                                <?= htmlspecialchars(
                                                    (string)
                                                    $jogador['valor']
                                                ) ?>

                                            </div>

                                            <div class="nivel">

                                                <span
                                                    class="nivel-badge"
                                                    style="background: <?= htmlspecialchars($ranking['cor']) ?>;"
                                                >

                                                    #<?= $posicao ?>

                                                </span>

                                            </div>

                                        </div>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</main>

</div>

<!-- ======================================
     LOGOUT
======================================= -->

<div
    id="logout-modal"
    class="modal"
>

    <div class="modal-content">

        <h3>Ah... já vai?</h3>

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
    &copy; 2026 FOAG. Todos os direitos reservados.
</footer>

<script>

const rankingsData =
    <?= json_encode(
        $rankings,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
    ) ?>;

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const abasNivel =
            document.querySelectorAll(
                '.aba-nivel'
            );

        const menuItems =
            document.querySelectorAll(
                '.rank-menu-lateral .menu-item'
            );

        const categorias =
            document.querySelectorAll(
                '.rank-full'
            );

        // ==================================
        // NÍVEL ATUAL
        // ==================================

        let nivelAtual =
            'nacional';

        // ==================================
        // ATUALIZAR NÍVEL
        // ==================================

        function atualizarNivel(
            nivel
        ) {

            nivelAtual =
                nivel;

            // ==============================
            // ABAS
            // ==============================

            abasNivel.forEach(
                function (aba) {

                    aba.classList.toggle(
                        'active',
                        aba.dataset.nivel
                        === nivel
                    );
                }
            );

            // ==============================
            // CONTEÚDO
            // ==============================

            categorias.forEach(
                function (categoria) {

                    const categoriaKey =
                        categoria.dataset
                            .categoria;

                    const niveis =
                        categoria.querySelectorAll(
                            '.nivel-conteudo'
                        );

                    niveis.forEach(
                        function (conteudo) {

                            conteudo.style.display =
                                conteudo.dataset.nivel
                                === nivel
                                ? 'block'
                                : 'none';
                        }
                    );

                    // ==========================
                    // STATS
                    // ==========================

                    const ranking =
                        rankingsData[
                            categoriaKey
                        ];

                    if (
                        !ranking
                        ||
                        !ranking.niveis
                        ||
                        !ranking.niveis[nivel]
                    ) {
                        return;
                    }

                    const jogadores =
                        ranking
                            .niveis[nivel]
                            .jogadores
                        || [];

                    const totalSpan =
                        categoria.querySelector(
                            '.total-jogadores'
                        );

                    const top1Span =
                        categoria.querySelector(
                            '.top1-nome'
                        );

                    if (totalSpan) {

                        totalSpan.textContent =
                            jogadores.length;
                    }

                    if (top1Span) {

                        top1Span.textContent =
                            jogadores.length > 0
                            ? jogadores[0].nome
                            : '-';
                    }
                }
            );

            // ==============================
            // BADGES DO MENU
            // ==============================

            menuItems.forEach(
                function (item) {

                    const categoria =
                        item.dataset.categoria;

                    const badge =
                        item.querySelector(
                            '.item-badge'
                        );

                    const ranking =
                        rankingsData[
                            categoria
                        ];

                    if (
                        badge
                        &&
                        ranking
                        &&
                        ranking.niveis
                        &&
                        ranking.niveis[nivel]
                    ) {

                        badge.textContent =
                            ranking
                                .niveis[nivel]
                                .jogadores
                                .length;
                    }
                }
            );
        }

        // ==================================
        // CLIQUE NAS ABAS
        // ==================================

        abasNivel.forEach(
            function (aba) {

                aba.addEventListener(
                    'click',
                    function () {

                        atualizarNivel(
                            aba.dataset.nivel
                        );
                    }
                );
            }
        );

        // ==================================
        // CATEGORIAS
        // ==================================

        menuItems.forEach(
            function (item) {

                item.addEventListener(
                    'click',
                    function () {

                        menuItems.forEach(
                            function (menu) {

                                menu.classList.remove(
                                    'active'
                                );
                            }
                        );

                        item.classList.add(
                            'active'
                        );

                        const categoria =
                            item.dataset
                                .categoria;

                        categorias.forEach(
                            function (rank) {

                                rank.classList.add(
                                    'hidden'
                                );
                            }
                        );

                        const selecionado =
                            document.getElementById(
                                'rank-'
                                + categoria
                            );

                        if (selecionado) {

                            selecionado
                                .classList
                                .remove(
                                    'hidden'
                                );

                            selecionado
                                .style
                                .animation =
                                    'none';

                            selecionado
                                .offsetHeight;

                            selecionado
                                .style
                                .animation =
                                    'slideIn 0.4s ease forwards';
                        }

                        atualizarNivel(
                            nivelAtual
                        );
                    }
                );
            }
        );

        // ==================================
        // PERFIL
        // ==================================

        const perfilIcon =
            document.getElementById(
                'icon-perfil'
            );

        perfilIcon?.addEventListener(
            'click',
            function () {

                window.location.href =
                    '../perfil/perfil.php';
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
                    evento.target
                    === logoutModal
                ) {

                    logoutModal.style.display =
                        'none';
                }
            }
        );

        document.addEventListener(
            'keydown',
            function (evento) {

                if (
                    evento.key ===
                    'Escape'
                ) {

                    if (
                        logoutModal?.style
                            .display
                        === 'flex'
                    ) {

                        logoutModal.style.display =
                            'none';
                    }
                }
            }
        );

        // ==================================
        // INICIALIZAR
        // ==================================

        atualizarNivel(
            'nacional'
        );
    }
);

</script>

</body>
</html>
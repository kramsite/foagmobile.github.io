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
// LOCALIZAR PASTA DO USUÁRIO
// ======================================

$baseJsonDir = __DIR__ . '/../json/usuarios';

$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

$arquivoPerfil = $pastaUsuario . '/perfil.json';

// A pasta do usuário deve existir
if (!is_dir($pastaUsuario)) {
    exit("Pasta do usuário não encontrada.");
}

// ======================================
// CARREGAR NOME DO USUÁRIO
// ======================================

$usuarioAtual = $_SESSION['user_nome']
    ?? $_SESSION['usuario']
    ?? 'Usuário FOAG';

if (file_exists($arquivoPerfil)) {

    $conteudoPerfil = file_get_contents($arquivoPerfil);

    if ($conteudoPerfil !== false) {

        $dadosPerfil = json_decode(
            $conteudoPerfil,
            true
        );

        if (
            is_array($dadosPerfil)
            &&
            !empty($dadosPerfil['nome'])
        ) {
            $usuarioAtual = $dadosPerfil['nome'];
        }
    }
}

// Atualiza a sessão
$_SESSION['user_nome'] = $usuarioAtual;
$_SESSION['usuario'] = $usuarioAtual;

// ======================================
// PONTOS DO USUÁRIO
// ======================================

// Por enquanto, usuário novo começa com 0 estrelas
$estrelasUsuario = 0;

// ======================================
// DADOS DO RANKING
// ======================================

$rankings = [

    // ==================================
    // ESTRELAS
    // ==================================

    'estrelas' => [

        'titulo' => '⭐ Mais Estrelas',

        'icone' => '',

        'cor' => '#ffd700',

        'descricao' =>
            'Quem tem mais estrelas acumuladas',

        'niveis' => [

            // --------------------------
            // NACIONAL
            // --------------------------

            'nacional' => [

                'nome' => '🌍 Nacional',

                'jogadores' => [

                    [
                        'nome' => 'Ana Silva',
                        'valor' => 245,
                        'avatar' => '👩‍🎓',
                        'nivel' => 1,
                        'estado' => 'SP'
                    ],

                    [
                        'nome' => 'Carlos Mendes',
                        'valor' => 198,
                        'avatar' => '👨‍🎓',
                        'nivel' => 2,
                        'estado' => 'RJ'
                    ],

                    [
                        'nome' => 'Mariana Santos',
                        'valor' => 167,
                        'avatar' => '👩‍💻',
                        'nivel' => 3,
                        'estado' => 'MG'
                    ],

                    [
                        'nome' => 'João Pereira',
                        'valor' => 143,
                        'avatar' => '👨‍💻',
                        'nivel' => 4,
                        'estado' => 'SP'
                    ],

                    [
                        'nome' => 'Juliana Costa',
                        'valor' => 128,
                        'avatar' => '👩‍🔬',
                        'nivel' => 5,
                        'estado' => 'BA'
                    ]

                ]
            ],

            // --------------------------
            // ESTADUAL
            // --------------------------

            'estadual' => [

                'nome' => '🏛️ Estadual (SP)',

                'jogadores' => [

                    [
                        'nome' => 'Ana Silva',
                        'valor' => 245,
                        'avatar' => '👩‍🎓',
                        'nivel' => 1,
                        'cidade' => 'São Paulo'
                    ],

                    [
                        'nome' => 'João Pereira',
                        'valor' => 143,
                        'avatar' => '👨‍💻',
                        'nivel' => 2,
                        'cidade' => 'Campinas'
                    ],

                    [
                        'nome' => 'Roberto Alves',
                        'valor' => 112,
                        'avatar' => '👨‍🔬',
                        'nivel' => 3,
                        'cidade' => 'Santos'
                    ],

                    [
                        'nome' => 'Camila Rocha',
                        'valor' => 95,
                        'avatar' => '👩‍🏫',
                        'nivel' => 4,
                        'cidade' => 'São José'
                    ],

                    [
                        'nome' => 'Fernando Lima',
                        'valor' => 78,
                        'avatar' => '👨‍🏫',
                        'nivel' => 5,
                        'cidade' => 'Ribeirão'
                    ]

                ]
            ],

            // --------------------------
            // MUNICIPAL
            // --------------------------

            'municipal' => [

                'nome' => '🏘️ Municipal (SP)',

                'jogadores' => [

                    [
                        'nome' => 'Ana Silva',
                        'valor' => 245,
                        'avatar' => '👩‍🎓',
                        'nivel' => 1,
                        'bairro' => 'Centro'
                    ],

                    [
                        'nome' => 'Camila Rocha',
                        'valor' => 95,
                        'avatar' => '👩‍🏫',
                        'nivel' => 2,
                        'bairro' => 'Vila Mariana'
                    ],

                    [
                        'nome' => 'Fernando Lima',
                        'valor' => 78,
                        'avatar' => '👨‍🏫',
                        'nivel' => 3,
                        'bairro' => 'Moema'
                    ],

                    [
                        'nome' => 'Roberto Alves',
                        'valor' => 112,
                        'avatar' => '👨‍🔬',
                        'nivel' => 4,
                        'bairro' => 'Pinheiros'
                    ],

                    [
                        'nome' => 'João Pereira',
                        'valor' => 143,
                        'avatar' => '👨‍💻',
                        'nivel' => 5,
                        'bairro' => 'Itaim'
                    ]

                ]
            ],

            // --------------------------
            // REGIONAL
            // --------------------------

            'regional' => [

                'nome' => '📌 Regional (Sudeste)',

                'jogadores' => [

                    [
                        'nome' => 'Ana Silva',
                        'valor' => 245,
                        'avatar' => '👩‍🎓',
                        'nivel' => 1,
                        'regiao' => 'Sudeste'
                    ],

                    [
                        'nome' => 'Carlos Mendes',
                        'valor' => 198,
                        'avatar' => '👨‍🎓',
                        'nivel' => 2,
                        'regiao' => 'Sudeste'
                    ],

                    [
                        'nome' => 'Mariana Santos',
                        'valor' => 167,
                        'avatar' => '👩‍💻',
                        'nivel' => 3,
                        'regiao' => 'Sudeste'
                    ],

                    [
                        'nome' => 'João Pereira',
                        'valor' => 143,
                        'avatar' => '👨‍💻',
                        'nivel' => 4,
                        'regiao' => 'Sudeste'
                    ],

                    [
                        'nome' => 'Juliana Costa',
                        'valor' => 128,
                        'avatar' => '👩‍🔬',
                        'nivel' => 5,
                        'regiao' => 'Sudeste'
                    ]

                ]
            ]
        ]
    ],

    // ==================================
    // POMODORO
    // ==================================

    'pomodoro' => [

        'titulo' => '⏱️ Mais Tempo no Pomodoro',

        'icone' => '',

        'cor' => '#4caf50',

        'descricao' =>
            'Quem estudou mais tempo com Pomodoro',

        'niveis' => [

            'nacional' => [

                'nome' => '🌍 Nacional',

                'jogadores' => [

                    [
                        'nome' => 'Mariana Santos',
                        'valor' => '42h 30min',
                        'avatar' => '👩‍💻',
                        'nivel' => 1
                    ],

                    [
                        'nome' => 'Ana Silva',
                        'valor' => '38h 15min',
                        'avatar' => '👩‍🎓',
                        'nivel' => 2
                    ],

                    [
                        'nome' => 'Carlos Mendes',
                        'valor' => '35h 45min',
                        'avatar' => '👨‍🎓',
                        'nivel' => 3
                    ],

                    [
                        'nome' => 'João Pereira',
                        'valor' => '29h 20min',
                        'avatar' => '👨‍💻',
                        'nivel' => 4
                    ],

                    [
                        'nome' => 'Juliana Costa',
                        'valor' => '25h 50min',
                        'avatar' => '👩‍🔬',
                        'nivel' => 5
                    ]

                ]
            ],

            'estadual' => [

                'nome' => '🏛️ Estadual (SP)',

                'jogadores' => [

                    [
                        'nome' => 'Mariana Santos',
                        'valor' => '42h 30min',
                        'avatar' => '👩‍💻',
                        'nivel' => 1
                    ],

                    [
                        'nome' => 'Ana Silva',
                        'valor' => '38h 15min',
                        'avatar' => '👩‍🎓',
                        'nivel' => 2
                    ],

                    [
                        'nome' => 'Carlos Mendes',
                        'valor' => '35h 45min',
                        'avatar' => '👨‍🎓',
                        'nivel' => 3
                    ],

                    [
                        'nome' => 'João Pereira',
                        'valor' => '29h 20min',
                        'avatar' => '👨‍💻',
                        'nivel' => 4
                    ],

                    [
                        'nome' => 'Juliana Costa',
                        'valor' => '25h 50min',
                        'avatar' => '👩‍🔬',
                        'nivel' => 5
                    ]

                ]
            ],

            'municipal' => [

                'nome' => '🏘️ Municipal (SP)',

                'jogadores' => [

                    [
                        'nome' => 'Ana Silva',
                        'valor' => '38h 15min',
                        'avatar' => '👩‍🎓',
                        'nivel' => 1
                    ],

                    [
                        'nome' => 'Carlos Mendes',
                        'valor' => '35h 45min',
                        'avatar' => '👨‍🎓',
                        'nivel' => 2
                    ],

                    [
                        'nome' => 'João Pereira',
                        'valor' => '29h 20min',
                        'avatar' => '👨‍💻',
                        'nivel' => 3
                    ],

                    [
                        'nome' => 'Juliana Costa',
                        'valor' => '25h 50min',
                        'avatar' => '👩‍🔬',
                        'nivel' => 4
                    ],

                    [
                        'nome' => 'Roberto Alves',
                        'valor' => '22h 10min',
                        'avatar' => '👨‍🔬',
                        'nivel' => 5
                    ]

                ]
            ]
        ]
    ],

    // ==================================
    // FALTAS
    // ==================================

    'faltas' => [

        'titulo' => '📊 Menos Faltas',

        'icone' => '',

        'cor' => '#2196f3',

        'descricao' =>
            'Quem teve menos faltas',

        'niveis' => [

            'nacional' => [

                'nome' => '🌍 Nacional',

                'jogadores' => [

                    [
                        'nome' => 'João Pereira',
                        'valor' => '0 faltas',
                        'avatar' => '👨‍💻',
                        'nivel' => 1
                    ],

                    [
                        'nome' => 'Ana Silva',
                        'valor' => '1 falta',
                        'avatar' => '👩‍🎓',
                        'nivel' => 2
                    ],

                    [
                        'nome' => 'Carlos Mendes',
                        'valor' => '2 faltas',
                        'avatar' => '👨‍🎓',
                        'nivel' => 3
                    ],

                    [
                        'nome' => 'Mariana Santos',
                        'valor' => '3 faltas',
                        'avatar' => '👩‍💻',
                        'nivel' => 4
                    ],

                    [
                        'nome' => 'Juliana Costa',
                        'valor' => '4 faltas',
                        'avatar' => '👩‍🔬',
                        'nivel' => 5
                    ]

                ]
            ],

            'estadual' => [

                'nome' => '🏛️ Estadual (SP)',

                'jogadores' => [

                    [
                        'nome' => 'João Pereira',
                        'valor' => '0 faltas',
                        'avatar' => '👨‍💻',
                        'nivel' => 1
                    ],

                    [
                        'nome' => 'Ana Silva',
                        'valor' => '1 falta',
                        'avatar' => '👩‍🎓',
                        'nivel' => 2
                    ],

                    [
                        'nome' => 'Carlos Mendes',
                        'valor' => '2 faltas',
                        'avatar' => '👨‍🎓',
                        'nivel' => 3
                    ],

                    [
                        'nome' => 'Mariana Santos',
                        'valor' => '3 faltas',
                        'avatar' => '👩‍💻',
                        'nivel' => 4
                    ],

                    [
                        'nome' => 'Roberto Alves',
                        'valor' => '5 faltas',
                        'avatar' => '👨‍🔬',
                        'nivel' => 5
                    ]

                ]
            ]
        ]
    ],

    // ==================================
    // NOTAS
    // ==================================

    'notas' => [

        'titulo' => '📚 Melhores Notas',

        'icone' => '',

        'cor' => '#9c27b0',

        'descricao' =>
            'Quem tem as melhores médias',

        'niveis' => [

            'nacional' => [

                'nome' => '🌍 Nacional',

                'jogadores' => [

                    [
                        'nome' => 'Juliana Costa',
                        'valor' => '9.8',
                        'avatar' => '👩‍🔬',
                        'nivel' => 1
                    ],

                    [
                        'nome' => 'Mariana Santos',
                        'valor' => '9.5',
                        'avatar' => '👩‍💻',
                        'nivel' => 2
                    ],

                    [
                        'nome' => 'Ana Silva',
                        'valor' => '9.2',
                        'avatar' => '👩‍🎓',
                        'nivel' => 3
                    ],

                    [
                        'nome' => 'Carlos Mendes',
                        'valor' => '8.9',
                        'avatar' => '👨‍🎓',
                        'nivel' => 4
                    ],

                    [
                        'nome' => 'João Pereira',
                        'valor' => '8.5',
                        'avatar' => '👨‍💻',
                        'nivel' => 5
                    ]

                ]
            ],

            'estadual' => [

                'nome' => '🏛️ Estadual (SP)',

                'jogadores' => [

                    [
                        'nome' => 'Ana Silva',
                        'valor' => '9.2',
                        'avatar' => '👩‍🎓',
                        'nivel' => 1
                    ],

                    [
                        'nome' => 'Carlos Mendes',
                        'valor' => '8.9',
                        'avatar' => '👨‍🎓',
                        'nivel' => 2
                    ],

                    [
                        'nome' => 'João Pereira',
                        'valor' => '8.5',
                        'avatar' => '👨‍💻',
                        'nivel' => 3
                    ],

                    [
                        'nome' => 'Roberto Alves',
                        'valor' => '8.1',
                        'avatar' => '👨‍🔬',
                        'nivel' => 4
                    ],

                    [
                        'nome' => 'Fernando Lima',
                        'valor' => '7.8',
                        'avatar' => '👨‍🏫',
                        'nivel' => 5
                    ]

                ]
            ]
        ]
    ],

    // ==================================
    // PRESENÇA
    // ==================================

    'presenca' => [

        'titulo' => '🎯 Maior Presença',

        'icone' => '',

        'cor' => '#ff9800',

        'descricao' =>
            'Quem tem a maior frequência',

        'niveis' => [

            'nacional' => [

                'nome' => '🌍 Nacional',

                'jogadores' => [

                    [
                        'nome' => 'Carlos Mendes',
                        'valor' => '98%',
                        'avatar' => '👨‍🎓',
                        'nivel' => 1
                    ],

                    [
                        'nome' => 'Ana Silva',
                        'valor' => '96%',
                        'avatar' => '👩‍🎓',
                        'nivel' => 2
                    ],

                    [
                        'nome' => 'João Pereira',
                        'valor' => '95%',
                        'avatar' => '👨‍💻',
                        'nivel' => 3
                    ],

                    [
                        'nome' => 'Mariana Santos',
                        'valor' => '93%',
                        'avatar' => '👩‍💻',
                        'nivel' => 4
                    ],

                    [
                        'nome' => 'Juliana Costa',
                        'valor' => '91%',
                        'avatar' => '👩‍🔬',
                        'nivel' => 5
                    ]

                ]
            ],

            'estadual' => [

                'nome' => '🏛️ Estadual (SP)',

                'jogadores' => [

                    [
                        'nome' => 'Ana Silva',
                        'valor' => '96%',
                        'avatar' => '👩‍🎓',
                        'nivel' => 1
                    ],

                    [
                        'nome' => 'João Pereira',
                        'valor' => '95%',
                        'avatar' => '👨‍💻',
                        'nivel' => 2
                    ],

                    [
                        'nome' => 'Carlos Mendes',
                        'valor' => '98%',
                        'avatar' => '👨‍🎓',
                        'nivel' => 3
                    ],

                    [
                        'nome' => 'Mariana Santos',
                        'valor' => '93%',
                        'avatar' => '👩‍💻',
                        'nivel' => 4
                    ],

                    [
                        'nome' => 'Roberto Alves',
                        'valor' => '89%',
                        'avatar' => '👨‍🔬',
                        'nivel' => 5
                    ]

                ]
            ]
        ]
    ]
];

// ======================================
// ADICIONAR USUÁRIO LOGADO NAS ESTRELAS
// ======================================

foreach (
    $rankings['estrelas']['niveis']
    as $nivelKey => &$nivelRanking
) {

    if (
        !isset($nivelRanking['jogadores'])
        ||
        !is_array($nivelRanking['jogadores'])
    ) {
        continue;
    }

    $usuarioJaExiste = false;

    foreach (
        $nivelRanking['jogadores']
        as $jogador
    ) {

        if (
            isset($jogador['nome'])
            &&
            $jogador['nome'] === $usuarioAtual
        ) {
            $usuarioJaExiste = true;
            break;
        }
    }

    if (!$usuarioJaExiste) {

        $nivelRanking['jogadores'][] = [

            'nome' => $usuarioAtual,

            'valor' => $estrelasUsuario,

            'avatar' => '👤',

            'nivel' =>
                count(
                    $nivelRanking['jogadores']
                ) + 1
        ];
    }

    // ==================================
    // ORDENAR POR ESTRELAS
    // ==================================

    usort(
        $nivelRanking['jogadores'],
        function ($a, $b) {

            $valorA = (int) (
                $a['valor'] ?? 0
            );

            $valorB = (int) (
                $b['valor'] ?? 0
            );

            return $valorB <=> $valorA;
        }
    );

    // ==================================
    // ATUALIZAR NÍVEL/POSIÇÃO
    // ==================================

    foreach (
        $nivelRanking['jogadores']
        as $indice => &$jogador
    ) {
        $jogador['nivel'] =
            $indice + 1;
    }

    unset($jogador);
}

unset($nivelRanking);

// ======================================
// ORDEM DAS CATEGORIAS
// ======================================

$categoriasOrdenadas = [
    'estrelas',
    'pomodoro',
    'faltas',
    'notas',
    'presenca'
];

$niveisDisponiveis = [
    'nacional',
    'estadual',
    'municipal',
    'regional'
];

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking — FOAG</title>

    <link rel="stylesheet" href="rank.css">
    <link rel="stylesheet" href="../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="dark_rank.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../m.escuro/dark-mode.js"></script>

    <style>
        /* ========================================
           ANIMAÇÕES
        ======================================== */

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ========================================
           ABAS DE NÍVEL
        ======================================== */

        .abas-niveis {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 2px solid #eef2f6;
        }

        .aba-nivel {
            padding: 8px 20px;
            border-radius: 25px;
            border: 2px solid #e3e8ef;
            background: #ffffff;
            color: #4a6a8a;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .aba-nivel:hover {
            border-color: #38a5ff;
            color: #38a5ff;
            transform: translateY(-2px);
        }

        .aba-nivel.active {
            background: #38a5ff;
            border-color: #38a5ff;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(56, 165, 255, 0.3);
        }

        .aba-nivel .badge-nivel {
            font-size: 11px;
            background: rgba(0,0,0,0.08);
            padding: 1px 10px;
            border-radius: 12px;
        }

        .aba-nivel.active .badge-nivel {
            background: rgba(255,255,255,0.2);
        }

        /* ========================================
           LAYOUT COM MENU LATERAL
        ======================================== */

        .rank-layout {
            display: flex;
            gap: 25px;
            align-items: flex-start;
            width: 100%;
        }

        /* ========================================
           MENU LATERAL ESQUERDO
        ======================================== */

        .rank-menu-lateral {
            min-width: 200px;
            max-width: 200px;
            flex-shrink: 0;
            background: #ffffff;
            border-radius: 16px;
            padding: 12px 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid #e3e8ef;
            position: sticky;
            top: 10px;
        }

        .rank-menu-lateral .menu-titulo {
            font-size: 13px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 14px 8px 14px;
            border-bottom: 1px solid #eef2f6;
            margin-bottom: 8px;
        }

        .rank-menu-lateral .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #4a6a8a;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            position: relative;
        }

        .rank-menu-lateral .menu-item:hover {
            background: #f1f5f9;
            color: #1a2a3a;
        }

        .rank-menu-lateral .menu-item .item-icone {
            width: 24px;
            text-align: center;
            font-size: 15px;
        }

        .rank-menu-lateral .menu-item .item-badge {
            margin-left: auto;
            font-size: 11px;
            background: #eef4fa;
            color: #64748b;
            padding: 1px 10px;
            border-radius: 12px;
        }

        .rank-menu-lateral .menu-item.active {
            background: #38a5ff;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(56, 165, 255, 0.25);
        }

        .rank-menu-lateral .menu-item.active .item-badge {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .rank-menu-lateral .menu-item .indicador-ativo {
            display: none;
            width: 4px;
            height: 20px;
            border-radius: 4px;
            position: absolute;
            right: 8px;
        }

        .rank-menu-lateral .menu-item.active .indicador-ativo {
            display: block;
            background: #ffffff;
        }

        /* ========================================
           CONTEÚDO DO RANKING
        ======================================== */

        .rank-conteudo {
            flex: 1;
            min-width: 0;
        }

        .rank-full {
            animation: slideIn 0.4s ease forwards;
            width: 100%;
            display: block;
        }

        .rank-full.hidden {
            display: none;
        }

        /* ========================================
           CABEÇALHO DO RANKING
        ======================================== */

        .rank-full-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;

            padding: 18px 25px;

            background: #ffffff;
            border-radius: 16px 16px 0 0;

            border-bottom: 4px solid #38a5ff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .rank-full-header .rank-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .rank-full-header .rank-info .icone-grande {
            font-size: 28px;
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f0f7ff;
        }

        .rank-full-header .rank-info .titulo h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1a2a3a;
            margin: 0;
        }

        .rank-full-header .rank-info .titulo p {
            font-size: 13px;
            color: #64748b;
            margin: 2px 0 0 0;
        }

        .rank-full-header .rank-stats {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .rank-full-header .rank-stats .stat {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #64748b;
            background: #f8fafc;
            padding: 5px 12px;
            border-radius: 20px;
        }

        .rank-full-header .rank-stats .stat i {
            color: #38a5ff;
        }

        /* ========================================
           CORPO DO RANKING
        ======================================== */

        .rank-full-body {
            background: #ffffff;
            border-radius: 0 0 16px 16px;
            padding: 5px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        /* ========================================
           ITENS DO RANKING
        ======================================== */

        .rank-full-item {
            display: flex;
            align-items: center;
            gap: 15px;

            padding: 10px 20px;

            border-bottom: 1px solid #f0f2f5;

            transition: all 0.3s ease;
        }

        .rank-full-item:last-child {
            border-bottom: none;
        }

        .rank-full-item:hover {
            background: #f8fafc;
        }

        .rank-full-item .posicao {
            min-width: 45px;
            font-size: 15px;
            font-weight: 700;
            color: #4a6a8a;
        }

        .rank-full-item .posicao .numero {
            display: inline-block;
            width: 32px;
            height: 32px;
            line-height: 32px;
            text-align: center;
            border-radius: 50%;
            background: #f1f5f9;
            font-size: 13px;
        }

        .rank-full-item .posicao .medalha {
            font-size: 26px;
        }

        .rank-full-item .avatar {
            font-size: 30px;
            width: 42px;
            text-align: center;
        }

        .rank-full-item .info {
            flex: 1;
        }

        .rank-full-item .info .nome {
            font-size: 16px;
            font-weight: 600;
            color: #1a2a3a;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .rank-full-item .info .nome .badge-eu {
            font-size: 10px;
            font-weight: 700;
            color: #ffffff;
            background: #38a5ff;
            padding: 1px 10px;
            border-radius: 10px;
        }

        .rank-full-item .info .detalhes {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .rank-full-item .info .detalhes .tag-local {
            display: inline-block;
            background: #eef4fa;
            padding: 1px 10px;
            border-radius: 10px;
            font-size: 11px;
            color: #64748b;
            margin-left: 4px;
        }

        .rank-full-item .valor {
            font-size: 20px;
            font-weight: 700;
            min-width: 80px;
            text-align: right;
        }

        .rank-full-item .nivel {
            min-width: 70px;
            text-align: right;
        }

        .rank-full-item .nivel .nivel-badge {
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            padding: 3px 12px;
            border-radius: 12px;
        }

        /* ========================================
           DESTAQUE DO USUÁRIO
        ======================================== */

        .rank-full-item.usuario-destaque {
            background: #e3f2fd;
            border-left: 4px solid #38a5ff;
        }

        .rank-full-item.usuario-destaque .info .nome {
            color: #1a237e;
        }

        /* ========================================
           PÓDIO
        ======================================== */

        .rank-full-item.podium-1 {
            background: linear-gradient(135deg, #fffde7, #fff8e1);
        }

        .rank-full-item.podium-1 .posicao .numero {
            background: linear-gradient(135deg, #ffd700, #ffb300);
            color: #ffffff;
            font-size: 16px;
        }

        .rank-full-item.podium-2 .posicao .numero {
            background: linear-gradient(135deg, #e0e0e0, #bdbdbd);
            color: #ffffff;
        }

        .rank-full-item.podium-3 .posicao .numero {
            background: linear-gradient(135deg, #d7a86e, #b87333);
            color: #ffffff;
        }

        /* ========================================
           MODO ESCURO - ABAS
        ======================================== */

        body.dark-mode .abas-niveis {
            border-bottom-color: #2a3a4a;
        }

        body.dark-mode .aba-nivel {
            background: #1a2a3a;
            border-color: #334155;
            color: #94a3b8;
        }

        body.dark-mode .aba-nivel:hover {
            border-color: #669ada;
            color: #e2e8f0;
        }

        body.dark-mode .aba-nivel.active {
            background: #669ada;
            border-color: #669ada;
            color: #ffffff;
        }

        body.dark-mode .aba-nivel .badge-nivel {
            background: rgba(255,255,255,0.1);
        }

        body.dark-mode .aba-nivel.active .badge-nivel {
            background: rgba(255,255,255,0.2);
        }

        /* ========================================
           MODO ESCURO - MENU LATERAL
        ======================================== */

        body.dark-mode .rank-menu-lateral {
            background: #1a2a3a;
            border-color: #334155;
        }

        body.dark-mode .rank-menu-lateral .menu-titulo {
            color: #64748b;
            border-bottom-color: #2a3a4a;
        }

        body.dark-mode .rank-menu-lateral .menu-item {
            color: #94a3b8;
        }

        body.dark-mode .rank-menu-lateral .menu-item:hover {
            background: #0f1a2a;
            color: #e2e8f0;
        }

        body.dark-mode .rank-menu-lateral .menu-item .item-badge {
            background: #0f1a2a;
            color: #94a3b8;
        }

        body.dark-mode .rank-menu-lateral .menu-item.active {
            background: #669ada;
            color: #ffffff;
        }

        body.dark-mode .rank-menu-lateral .menu-item.active .item-badge {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        /* ========================================
           MODO ESCURO - RANKING
        ======================================== */

        body.dark-mode .rank-full-header {
            background: #1a2a3a;
            border-bottom-color: #669ada;
        }

        body.dark-mode .rank-full-header .rank-info .titulo h2 {
            color: #e2e8f0;
        }

        body.dark-mode .rank-full-header .rank-info .titulo p {
            color: #94a3b8;
        }

        body.dark-mode .rank-full-header .rank-info .icone-grande {
            background: #0f1a2a;
        }

        body.dark-mode .rank-full-header .rank-stats .stat {
            background: #0f1a2a;
            color: #94a3b8;
        }

        body.dark-mode .rank-full-body {
            background: #1a2a3a;
        }

        body.dark-mode .rank-full-item {
            border-bottom-color: #2a3a4a;
        }

        body.dark-mode .rank-full-item:hover {
            background: #0f1a2a;
        }

        body.dark-mode .rank-full-item .info .nome {
            color: #e2e8f0;
        }

        body.dark-mode .rank-full-item .info .detalhes .tag-local {
            background: #0f1a2a;
            color: #94a3b8;
        }

        body.dark-mode .rank-full-item .posicao .numero {
            background: #2a3a4a;
            color: #94a3b8;
        }

        body.dark-mode .rank-full-item.usuario-destaque {
            background: #0a1a3a;
            border-left-color: #669ada;
        }

        body.dark-mode .rank-full-item.usuario-destaque .info .nome {
            color: #7aa9df;
        }

        body.dark-mode .rank-full-item.podium-1 {
            background: linear-gradient(135deg, #1a1a0a, #2a1a0a);
        }

        body.dark-mode .rank-full-item.podium-2 {
            background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
        }

        body.dark-mode .rank-full-item.podium-3 {
            background: linear-gradient(135deg, #1a1008, #2a1808);
        }

        /* ========================================
           RESPONSIVIDADE
        ======================================== */

        @media (max-width: 768px) {
            .rank-layout {
                flex-direction: column;
            }

            .rank-menu-lateral {
                min-width: 100%;
                max-width: 100%;
                position: relative;
                top: 0;
                display: flex;
                flex-wrap: wrap;
                padding: 10px 8px;
                gap: 4px;
                border-radius: 12px;
            }

            .rank-menu-lateral .menu-titulo {
                width: 100%;
                padding: 6px 12px;
                font-size: 12px;
            }

            .rank-menu-lateral .menu-item {
                padding: 6px 12px;
                font-size: 12px;
                flex: 0 0 auto;
                border-radius: 8px;
            }

            .rank-menu-lateral .menu-item .item-badge {
                display: none;
            }

            .rank-menu-lateral .menu-item .indicador-ativo {
                display: none !important;
            }

            .abas-niveis {
                justify-content: center;
            }

            .aba-nivel {
                font-size: 12px;
                padding: 6px 14px;
            }

            .rank-full-header {
                flex-direction: column;
                text-align: center;
                padding: 16px 18px;
            }

            .rank-full-header .rank-info {
                flex-direction: column;
            }

            .rank-full-header .rank-stats {
                flex-wrap: wrap;
                justify-content: center;
            }

            .rank-full-item {
                flex-wrap: wrap;
                gap: 8px;
                padding: 10px 14px;
            }

            .rank-full-item .posicao {
                min-width: 35px;
            }

            .rank-full-item .avatar {
                font-size: 24px;
                width: 36px;
            }

            .rank-full-item .info .nome {
                font-size: 14px;
            }

            .rank-full-item .valor {
                font-size: 16px;
                min-width: 60px;
            }

            .rank-full-item .nivel {
                min-width: 55px;
            }
        }

        @media (max-width: 480px) {
            .rank-full-item {
                padding: 8px 10px;
                gap: 6px;
            }

            .rank-full-item .posicao {
                min-width: 28px;
            }

            .rank-full-item .posicao .numero {
                width: 24px;
                height: 24px;
                line-height: 24px;
                font-size: 10px;
            }

            .rank-full-item .posicao .medalha {
                font-size: 20px;
            }

            .rank-full-item .avatar {
                font-size: 20px;
                width: 30px;
            }

            .rank-full-item .info .nome {
                font-size: 12px;
                flex-wrap: wrap;
            }

            .rank-full-item .info .detalhes {
                font-size: 10px;
            }

            .rank-full-item .info .detalhes .tag-local {
                font-size: 9px;
                padding: 0 8px;
            }

            .rank-full-item .valor {
                font-size: 14px;
                min-width: 50px;
            }

            .rank-full-item .nivel .nivel-badge {
                font-size: 9px;
                padding: 2px 8px;
            }

            .rank-full-header .rank-info .titulo h2 {
                font-size: 16px;
            }

            .rank-full-header .rank-info .icone-grande {
                font-size: 22px;
                width: 38px;
                height: 38px;
            }

            .rank-menu-lateral .menu-item {
                font-size: 11px;
                padding: 4px 10px;
            }

            .aba-nivel {
                font-size: 11px;
                padding: 4px 12px;
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
             MENU PRINCIPAL
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
        </nav>

        <!-- ======================================
             CONTEÚDO
        ======================================= -->

        <main class="main-content">

            <!-- ==================================
                 HEADER DO RANKING
            =================================== -->

            <div class="ranking-header">
                <div class="ranking-titulo">
                    <h1>
                        <i class="fa-solid fa-trophy"></i>
                        Ranking FOAG
                    </h1>
                    <p>Veja quem está se destacando em cada categoria!</p>
                </div>
            </div>

            <!-- ==================================
                 ABAS DE NÍVEL
            =================================== -->

            <div class="abas-niveis" id="abasNiveis">
                <button class="aba-nivel active" data-nivel="nacional">
                    <i class="fa-solid fa-globe-americas"></i> Nacional
                    <span class="badge-nivel">5</span>
                </button>
                <button class="aba-nivel" data-nivel="estadual">
                    <i class="fa-solid fa-building"></i> Estadual
                    <span class="badge-nivel">5</span>
                </button>
                <button class="aba-nivel" data-nivel="municipal">
                    <i class="fa-solid fa-city"></i> Municipal
                    <span class="badge-nivel">5</span>
                </button>
                <button class="aba-nivel" data-nivel="regional">
                    <i class="fa-solid fa-map-pin"></i> Regional
                    <span class="badge-nivel">5</span>
                </button>
            </div>

            <!-- ==================================
                 LAYOUT COM MENU LATERAL
            =================================== -->

            <div class="rank-layout">

                <!-- MENU LATERAL ESQUERDO -->
                <div class="rank-menu-lateral">
                    <div class="menu-titulo">
                        <i class="fa-solid fa-list"></i> Categorias
                    </div>

                    <?php foreach ($categoriasOrdenadas as $index => $key): 
                        $ranking = $rankings[$key];
                        $ativo = $index === 0 ? 'active' : '';
                        $totalJogadores = isset($ranking['niveis']['nacional']) ? count($ranking['niveis']['nacional']['jogadores']) : 0;
                    ?>
                    <div class="menu-item <?= $ativo ?>" data-categoria="<?= $key ?>" data-index="<?= $index ?>">
                        <span class="item-icone"><?= $ranking['icone'] ?></span>
                        <span class="item-nome"><?= $ranking['titulo'] ?></span>
                        <span class="item-badge"><?= $totalJogadores ?></span>
                        <span class="indicador-ativo"></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- CONTEÚDO DO RANKING -->
                <div class="rank-conteudo">

                    <?php 
                    $primeiro = true;
                    foreach ($categoriasOrdenadas as $key): 
                        $ranking = $rankings[$key];
                        $hidden = $primeiro ? '' : 'hidden';
                        $primeiro = false;
                        $nivelAtual = 'nacional';
                    ?>
                    <div class="rank-full <?= $hidden ?>" data-categoria="<?= $key ?>" id="rank-<?= $key ?>">
                        <!-- Cabeçalho -->
                        <div class="rank-full-header" style="border-bottom-color: <?= $ranking['cor'] ?>;">
                            <div class="rank-info">
                                <div class="icone-grande" style="background: <?= $ranking['cor'] ?>22; color: <?= $ranking['cor'] ?>;">
                                    <i class="<?= $ranking['icone'] ?>"></i>
                                </div>
                                <div class="titulo">
                                    <h2><?= $ranking['titulo'] ?></h2>
                                    <p><?= $ranking['descricao'] ?></p>
                                </div>
                            </div>
                            <div class="rank-stats">
                                <span class="stat">
                                    <i class="fa-solid fa-users"></i>
                                    <span class="total-jogadores"><?= count($ranking['niveis']['nacional']['jogadores']) ?></span> jogadores
                                </span>
                                <span class="stat">
                                    <i class="fa-solid fa-trophy" style="color: <?= $ranking['cor'] ?>;"></i>
                                    Top 1: <span class="top1-nome"><?= $ranking['niveis']['nacional']['jogadores'][0]['nome'] ?? '-' ?></span>
                                </span>
                            </div>
                        </div>

                        <!-- Corpo -->
                        <div class="rank-full-body" data-categoria="<?= $key ?>">
                            <?php 
                            // Mostrar apenas o nível nacional por padrão (os outros serão mostrados via JS)
                            $niveisParaMostrar = ['nacional'];
                            foreach ($niveisParaMostrar as $nivelKey): 
                                if (!isset($ranking['niveis'][$nivelKey])) continue;
                                $nivelData = $ranking['niveis'][$nivelKey];
                            ?>
                            <div class="nivel-conteudo" data-nivel="<?= $nivelKey ?>">
                                <?php foreach ($nivelData['jogadores'] as $index => $jogador): 
                                    $posicao = $index + 1;
                                    $isUsuario = $jogador['nome'] === $usuarioAtual;
                                    $podiumClass = '';
                                    $medalha = '';
                                    
                                    if ($posicao === 1) {
                                        $podiumClass = 'podium-1';
                                        $medalha = '🥇';
                                    } elseif ($posicao === 2) {
                                        $podiumClass = 'podium-2';
                                        $medalha = '🥈';
                                    } elseif ($posicao === 3) {
                                        $podiumClass = 'podium-3';
                                        $medalha = '🥉';
                                    }

                                    // Info adicional
                                    $infoExtra = '';
                                    if (isset($jogador['estado'])) $infoExtra = '📍 ' . $jogador['estado'];
                                    elseif (isset($jogador['cidade'])) $infoExtra = '📍 ' . $jogador['cidade'];
                                    elseif (isset($jogador['bairro'])) $infoExtra = '📍 ' . $jogador['bairro'];
                                    elseif (isset($jogador['regiao'])) $infoExtra = '📍 ' . $jogador['regiao'];
                                ?>
                                <div class="rank-full-item <?= $isUsuario ? 'usuario-destaque' : '' ?> <?= $podiumClass ?>">
                                    <div class="posicao">
                                        <?php if ($medalha): ?>
                                            <span class="medalha"><?= $medalha ?></span>
                                        <?php else: ?>
                                            <span class="numero">#<?= $posicao ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="avatar">
                                        <?= $jogador['avatar'] ?>
                                    </div>
                                    <div class="info">
                                        <div class="nome">
                                            <?= $jogador['nome'] ?>
                                            <?php if ($isUsuario): ?>
                                                <span class="badge-eu">Você</span>
                                            <?php endif; ?>
                                            <?php if ($infoExtra): ?>
                                                <span class="tag-local"><?= $infoExtra ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="detalhes">
                                            Nível <?= $jogador['nivel'] ?> • <?= $posicao ?>º lugar
                                        </div>
                                    </div>
                                    <div class="valor" style="color: <?= $ranking['cor'] ?>;">
                                        <?= $jogador['valor'] ?>
                                    </div>
                                    <div class="nivel">
                                        <span class="nivel-badge" style="background: <?= $ranking['cor'] ?>;">
                                            Nível <?= $jogador['nivel'] ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                            
                            <!-- Placeholders para os outros níveis (serão preenchidos via JS) -->
                            <?php foreach (['estadual', 'municipal', 'regional'] as $nivelKey): 
                                if (!isset($ranking['niveis'][$nivelKey])) continue;
                            ?>
                            <div class="nivel-conteudo" data-nivel="<?= $nivelKey ?>" style="display: none;">
                                <?php foreach ($ranking['niveis'][$nivelKey]['jogadores'] as $index => $jogador): 
                                    $posicao = $index + 1;
                                    $isUsuario = $jogador['nome'] === $usuarioAtual;
                                    $podiumClass = '';
                                    $medalha = '';
                                    
                                    if ($posicao === 1) {
                                        $podiumClass = 'podium-1';
                                        $medalha = '🥇';
                                    } elseif ($posicao === 2) {
                                        $podiumClass = 'podium-2';
                                        $medalha = '🥈';
                                    } elseif ($posicao === 3) {
                                        $podiumClass = 'podium-3';
                                        $medalha = '🥉';
                                    }

                                    $infoExtra = '';
                                    if (isset($jogador['estado'])) $infoExtra = '📍 ' . $jogador['estado'];
                                    elseif (isset($jogador['cidade'])) $infoExtra = '📍 ' . $jogador['cidade'];
                                    elseif (isset($jogador['bairro'])) $infoExtra = '📍 ' . $jogador['bairro'];
                                    elseif (isset($jogador['regiao'])) $infoExtra = '📍 ' . $jogador['regiao'];
                                ?>
                                <div class="rank-full-item <?= $isUsuario ? 'usuario-destaque' : '' ?> <?= $podiumClass ?>">
                                    <div class="posicao">
                                        <?php if ($medalha): ?>
                                            <span class="medalha"><?= $medalha ?></span>
                                        <?php else: ?>
                                            <span class="numero">#<?= $posicao ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="avatar">
                                        <?= $jogador['avatar'] ?>
                                    </div>
                                    <div class="info">
                                        <div class="nome">
                                            <?= $jogador['nome'] ?>
                                            <?php if ($isUsuario): ?>
                                                <span class="badge-eu">Você</span>
                                            <?php endif; ?>
                                            <?php if ($infoExtra): ?>
                                                <span class="tag-local"><?= $infoExtra ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="detalhes">
                                            Nível <?= $jogador['nivel'] ?> • <?= $posicao ?>º lugar
                                        </div>
                                    </div>
                                    <div class="valor" style="color: <?= $ranking['cor'] ?>;">
                                        <?= $jogador['valor'] ?>
                                    </div>
                                    <div class="nivel">
                                        <span class="nivel-badge" style="background: <?= $ranking['cor'] ?>;">
                                            Nível <?= $jogador['nivel'] ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
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
         MODAL: LOGOUT
    ======================================= -->

    <div id="logout-modal" class="modal">
        <div class="modal-content">
            <h3>Ah... já vai?</h3>
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
            // ==========================
            // ABAS DE NÍVEL
            // ==========================

            const abasNivel = document.querySelectorAll('.aba-nivel');
            const categorias = document.querySelectorAll('.rank-full');

            abasNivel.forEach(aba => {
                aba.addEventListener('click', function() {
                    // Remover active de todas as abas
                    abasNivel.forEach(a => a.classList.remove('active'));
                    this.classList.add('active');

                    const nivel = this.dataset.nivel;

                    // Para cada categoria, mostrar apenas o nível selecionado
                    categorias.forEach(categoria => {
                        const niveis = categoria.querySelectorAll('.nivel-conteudo');
                        niveis.forEach(n => {
                            if (n.dataset.nivel === nivel) {
                                n.style.display = 'block';
                            } else {
                                n.style.display = 'none';
                            }
                        });

                        // Atualizar stats do cabeçalho
                        const categoriaKey = categoria.dataset.categoria;
                        const rankingsData = <?= json_encode($rankings, JSON_UNESCAPED_UNICODE) ?>;
                        const ranking = rankingsData[categoriaKey];

                        if (ranking && ranking.niveis && ranking.niveis[nivel]) {
                            const dadosNivel = ranking.niveis[nivel];
                            const totalJogadores = dadosNivel.jogadores.length;
                            const top1 = dadosNivel.jogadores[0]?.nome || '-';

                            const totalSpan = categoria.querySelector('.total-jogadores');
                            const top1Span = categoria.querySelector('.top1-nome');

                            if (totalSpan) totalSpan.textContent = totalJogadores;
                            if (top1Span) top1Span.textContent = top1;
                        }
                    });
                });
            });

            // ==========================
            // MENU LATERAL - NAVEGAÇÃO
            // ==========================

            const menuItems = document.querySelectorAll('.rank-menu-lateral .menu-item');

            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Remover active de todos
                    menuItems.forEach(m => m.classList.remove('active'));
                    this.classList.add('active');

                    const categoria = this.dataset.categoria;

                    // Esconder todos os ranks
                    categorias.forEach(rank => {
                        rank.classList.add('hidden');
                    });

                    // Mostrar o rank selecionado
                    const rankSelecionado = document.getElementById('rank-' + categoria);
                    if (rankSelecionado) {
                        rankSelecionado.classList.remove('hidden');
                        // Reanimar
                        rankSelecionado.style.animation = 'none';
                        rankSelecionado.offsetHeight;
                        rankSelecionado.style.animation = 'slideIn 0.4s ease forwards';

                        // Garantir que o nível correto está visível
                        const nivelAtivo = document.querySelector('.aba-nivel.active');
                        if (nivelAtivo) {
                            const nivel = nivelAtivo.dataset.nivel;
                            const niveis = rankSelecionado.querySelectorAll('.nivel-conteudo');
                            niveis.forEach(n => {
                                if (n.dataset.nivel === nivel) {
                                    n.style.display = 'block';
                                } else {
                                    n.style.display = 'none';
                                }
                            });
                        }
                    }
                });
            });

            // ==========================
            // MODAL DE LOGOUT
            // ==========================

            const perfilIcon = document.getElementById('icon-perfil');
            const logoutModal = document.getElementById('logout-modal');
            const iconSair = document.getElementById('icon-sair');
            const confirmarLogout = document.getElementById('confirm-logout');
            const cancelarLogout = document.getElementById('cancel-logout');

            perfilIcon?.addEventListener('click', function() {
                window.location.href = '../perfil/perfil.php';
            });

            iconSair?.addEventListener('click', function() {
                if (logoutModal) {
                    logoutModal.style.display = 'flex';
                }
            });

            confirmarLogout?.addEventListener('click', function() {
                window.location.href = '../login/index.php';
            });

            cancelarLogout?.addEventListener('click', function() {
                if (logoutModal) {
                    logoutModal.style.display = 'none';
                }
            });

            logoutModal?.addEventListener('click', function(evento) {
                if (evento.target === logoutModal) {
                    logoutModal.style.display = 'none';
                }
            });

            // ==========================
            // TECLA ESC
            // ==========================

            document.addEventListener('keydown', function(evento) {
                if (evento.key === 'Escape') {
                    if (logoutModal?.style.display === 'flex') {
                        logoutModal.style.display = 'none';
                    }
                }
            });
        });
    </script>

</body>

</html>
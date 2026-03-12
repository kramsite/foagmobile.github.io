<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

/* =====================
   LOGIN OBRIGATÓRIO
   ===================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit;
}
$userId = $_SESSION['user_id'];

/* =====================
   ARQUIVO JSON POR USUÁRIO
   ===================== */
$baseJsonDir   = __DIR__ . '/../json/usuarios';
$pastaUsuario  = $baseJsonDir . '/' . $userId;
$arquivoBoletim = $pastaUsuario . '/boletim.json';

if (!is_dir($pastaUsuario)) {
    mkdir($pastaUsuario, 0755, true);
}

$defaultData = [
    'nota_maxima'     => 10,
    'media_aprovacao' => 6,
    'tipo_curso'      => 'escola', // 'escola' ou 'faculdade'
    'pesos'           => [1 => 1, 2 => 1, 3 => 1, 4 => 1],
    'periodos'        => [
        'Padrão' => [
            'materias' => [],
            'notas'    => []
        ]
    ],
    'periodo_atual'   => 'Padrão',
];

// Carrega JSON se existir
if (file_exists($arquivoBoletim)) {
    $data = json_decode(file_get_contents($arquivoBoletim), true);
    if (!is_array($data)) {
        $data = $defaultData;
    }
} else {
    // Primeira vez: tenta migrar algum dado da sessão antiga (se existir)
    $data = $defaultData;

    if (isset($_SESSION['nota_maxima'])) {
        $data['nota_maxima'] = (float)$_SESSION['nota_maxima'];
    }
    if (isset($_SESSION['media_aprovacao'])) {
        $data['media_aprovacao'] = (float)$_SESSION['media_aprovacao'];
    }
    if (isset($_SESSION['tipo_curso']) && in_array($_SESSION['tipo_curso'], ['escola', 'faculdade'])) {
        $data['tipo_curso'] = $_SESSION['tipo_curso'];
    }
    if (isset($_SESSION['pesos']) && is_array($_SESSION['pesos'])) {
        $data['pesos'] = $data['pesos'] + $_SESSION['pesos']; // mantém padrão se faltar algo
    }
    if (isset($_SESSION['periodos']) && is_array($_SESSION['periodos'])) {
        $data['periodos'] = $_SESSION['periodos'];
    } else {
        // fallback para estrutura antiga (materias/notas soltas)
        $materiasOld = isset($_SESSION['materias']) ? $_SESSION['materias'] : [];
        $notasOld    = isset($_SESSION['notas']) ? $_SESSION['notas'] : [];
        $data['periodos'] = [
            'Padrão' => [
                'materias' => $materiasOld,
                'notas'    => $notasOld
            ]
        ];
    }
    if (isset($_SESSION['periodo_atual'])) {
        $data['periodo_atual'] = (string)$_SESSION['periodo_atual'];
    }

    file_put_contents(
        $arquivoBoletim,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

/* Garante campos básicos na estrutura */
if (!isset($data['nota_maxima']))     $data['nota_maxima'] = 10;
if (!isset($data['media_aprovacao'])) $data['media_aprovacao'] = 6;
if (!isset($data['tipo_curso']))      $data['tipo_curso'] = 'escola';
if (!isset($data['pesos']) || !is_array($data['pesos'])) {
    $data['pesos'] = [1 => 1, 2 => 1, 3 => 1, 4 => 1];
}
for ($i = 1; $i <= 4; $i++) {
    if (!isset($data['pesos'][$i])) $data['pesos'][$i] = 1;
}
if (!isset($data['periodos']) || !is_array($data['periodos'])) {
    $data['periodos'] = [
        'Padrão' => [
            'materias' => [],
            'notas'    => []
        ]
    ];
}
if (!isset($data['periodo_atual']) || !isset($data['periodos'][$data['periodo_atual']])) {
    $data['periodo_atual'] = 'Padrão';
}

/* =====================
   VARIÁVEIS ATUAIS
   ===================== */
$notaMaxima     = $data['nota_maxima'];
$mediaAprovacao = $data['media_aprovacao'];
$tipoCurso      = $data['tipo_curso'];
$pesos          = $data['pesos'];
$periodos       = $data['periodos'];
$periodoAtual   = $data['periodo_atual'];

/* =====================
   FUNÇÕES AUXILIARES
   ===================== */

// média ponderada + status
function calcularMediaEStatus($notas, $mediaAprovacao, $pesos)
{
    $somaNP = 0;  // soma(nota * peso)
    $somaW  = 0;  // soma(pesos)

    for ($i = 1; $i <= 4; $i++) {
        $nota = isset($notas[$i]) ? $notas[$i] : null;
        $w    = isset($pesos[$i]) ? $pesos[$i] : 1;

        if ($nota !== null && $nota !== '' && $w > 0) {
            $nota = (float)$nota;
            $somaNP += $nota * $w;
            $somaW  += $w;
        }
    }

    if ($somaW == 0) {
        return array('media' => 0, 'status' => '-', 'precisa' => null);
    }

    $media = $somaNP / $somaW;

    if ($media >= $mediaAprovacao) {
        $status = 'Aprovado';
    } elseif ($media >= $mediaAprovacao * 0.5) {
        $status = 'Recuperação';
    } else {
        $status = 'Reprovado';
    }

    return array('media' => $media, 'status' => $status, 'precisa' => null);
}

// quanto precisa na próxima avaliação (ponderada)
function calcularQuantoPrecisa($notas, $mediaAlvo, $notaMaxima, $pesos)
{
    $totalAvaliacoes = 4;
    $indiceProxima   = null;
    $somaNP          = 0;
    $somaWFeitas     = 0;

    for ($i = 1; $i <= $totalAvaliacoes; $i++) {
        $nota = isset($notas[$i]) ? $notas[$i] : null;
        $w    = isset($pesos[$i]) ? $pesos[$i] : 1;
        if ($w <= 0) continue;

        if ($nota !== null && $nota !== '') {
            $nota        = (float)$nota;
            $somaNP      += $nota * $w;
            $somaWFeitas += $w;
        } elseif ($indiceProxima === null) {
            $indiceProxima = $i;
        }
    }

    if ($indiceProxima === null || $somaWFeitas == 0) {
        return null;
    }

    $somaWTodas = 0;
    for ($i = 1; $i <= 4; $i++) {
        $w = isset($pesos[$i]) ? $pesos[$i] : 1;
        if ($w > 0) {
            $somaWTodas += $w;
        }
    }

    $wProx = isset($pesos[$indiceProxima]) ? $pesos[$indiceProxima] : 1;
    if ($wProx <= 0 || $somaWTodas == 0) {
        return null;
    }

    // (somaNP + x*wProx) / somaWTodas = mediaAlvo
    $necessaria = ($mediaAlvo * $somaWTodas - $somaNP) / $wProx;

    if ($necessaria < 0) $necessaria = 0;
    if ($necessaria > $notaMaxima) {
        return 'Impossível';
    }

    return $necessaria;
}

/* =====================
   TRATAMENTO POST
   ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // qual período estamos mexendo nesse POST
    if (isset($_POST['periodo_atual_form']) && $_POST['periodo_atual_form'] !== '') {
        $periodoAlvo = $_POST['periodo_atual_form'];
    } else {
        $periodoAlvo = $data['periodo_atual'];
    }

    if (!isset($data['periodos'][$periodoAlvo])) {
        $data['periodos'][$periodoAlvo] = [
            'materias' => [],
            'notas'    => []
        ];
    }

    $materiasRef =& $data['periodos'][$periodoAlvo]['materias'];
    $notasRef    =& $data['periodos'][$periodoAlvo]['notas'];

    // 0) salvar tudo o que tá na tela (matérias e notas)
    foreach ($_POST as $key => $value) {

        // Matéria
        if (strpos($key, 'materia_') === 0) {
            $linha = (int) substr($key, 8);
            $materiasRef[$linha] = $value;
        }

        // Nota (nota_linha_avaliacao)
        if (strpos($key, 'nota_') === 0) {
            $parte   = substr($key, 5);
            $pedacos = explode('_', $parte);
            if (count($pedacos) === 2) {
                $linha     = (int)$pedacos[0];
                $avaliacao = (int)$pedacos[1];

                if (!isset($notasRef[$linha])) {
                    $notasRef[$linha] = [1 => null, 2 => null, 3 => null, 4 => null];
                }

                $value = trim($value);
                $notasRef[$linha][$avaliacao] = ($value === '') ? null : (float)$value;
            }
        }
    }

    // 1) Configurações
    if (isset($_POST['salvar_config'])) {

        if (isset($_POST['tipo_curso']) && ($_POST['tipo_curso'] === 'escola' || $_POST['tipo_curso'] === 'faculdade')) {
            $data['tipo_curso'] = $_POST['tipo_curso'];
        }

        // nota máxima / média
        if (isset($_POST['nota_maxima']) && $_POST['nota_maxima'] !== '') {
            $notaMax = (float)$_POST['nota_maxima'];
        } else {
            $notaMax = $data['nota_maxima'];
        }

        if (isset($_POST['media_aprovacao']) && $_POST['media_aprovacao'] !== '') {
            $mediaAp = (float)$_POST['media_aprovacao'];
        } else {
            $mediaAp = $data['media_aprovacao'];
        }

        if ($notaMax <= 0) $notaMax = 10;
        if ($mediaAp <= 0) $mediaAp = 6;

        $data['nota_maxima']     = $notaMax;
        $data['media_aprovacao'] = $mediaAp;

        // pesos
        $novosPesos = [];
        for ($i = 1; $i <= 4; $i++) {
            $campo = 'peso_' . $i;
            if (isset($_POST[$campo]) && $_POST[$campo] !== '') {
                $w = (float)$_POST[$campo];
            } else {
                $w = 1;
            }
            if ($w < 0) $w = 0;
            $novosPesos[$i] = $w;
        }
        $data['pesos'] = $novosPesos;

        // selecionar período
        if (isset($_POST['periodo_atual']) && $_POST['periodo_atual'] !== '') {
            $periodoSel = $_POST['periodo_atual'];
        } else {
            $periodoSel = $data['periodo_atual'];
        }

        // criar novo período se digitou
        $novoPeriodo = '';
        if (isset($_POST['novo_periodo'])) {
            $novoPeriodo = trim($_POST['novo_periodo']);
        }
        if ($novoPeriodo !== '') {
            if (!isset($data['periodos'][$novoPeriodo])) {
                $data['periodos'][$novoPeriodo] = [
                    'materias' => [],
                    'notas'    => []
                ];
            }
            $periodoSel = $novoPeriodo;
        }

        $data['periodo_atual'] = $periodoSel;
    }

    // 2) adicionar/remover linhas
    if (isset($_POST['adicionar_linha'])) {
        $materiasRef[] = '';
        $notasRef[]    = [1 => null, 2 => null, 3 => null, 4 => null];
    }

    if (isset($_POST['remover_linha']) && count($materiasRef) > 0) {
        array_pop($materiasRef);
        array_pop($notasRef);
    }

    // 3) limpar linha
    if (isset($_POST['limpar_linha']) && isset($_POST['linha_index'])) {
        $idx = (int)$_POST['linha_index'];
        if (isset($materiasRef[$idx])) {
            $materiasRef[$idx] = '';
            $notasRef[$idx]    = [1 => null, 2 => null, 3 => null, 4 => null];
        }
    }

    // 4) limpar tudo desse período
    if (isset($_POST['limpar_tudo'])) {
        $materiasRef = [];
        $notasRef    = [];
    }

    // Salva no JSON
    file_put_contents(
        $arquivoBoletim,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    // Atualiza variáveis depois do POST
    $notaMaxima     = $data['nota_maxima'];
    $mediaAprovacao = $data['media_aprovacao'];
    $tipoCurso      = $data['tipo_curso'];
    $pesos          = $data['pesos'];
    $periodos       = $data['periodos'];
    $periodoAtual   = $data['periodo_atual'];
}

/* labels das colunas conforme tipo */
if ($tipoCurso === 'escola') {
    $labelsAval = array('1º Bimestre', '2º Bimestre', '3º Bimestre', '4º Bimestre');
} else {
    $labelsAval = array('P1', 'P2', 'Trabalho', 'P3');
}

// garantir que o período atual exista
if (!isset($data['periodos'][$periodoAtual])) {
    $data['periodos'][$periodoAtual] = [
        'materias' => [],
        'notas'    => []
    ];
}

$materias = $data['periodos'][$periodoAtual]['materias'];
$notasAll = $data['periodos'][$periodoAtual]['notas'];

$current = basename($_SERVER['PHP_SELF']); // pra menu ativo
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FOAG — Notas e Médias</title>
  <link rel="stylesheet" href="boletim.css">
  <link rel="stylesheet" href="../m.escuro/dark_base.css">
  <link rel="stylesheet" href="dark_notas.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <script src="../m.escuro/dark-mode.js"></script>
  <style>
      #icon-fogi {
        cursor: pointer;
        transition: 0.2s;
      }
      #icon-fogi:hover {
        color: #38a5ff;
        transform: scale(1.1);
      }
      #fogi-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
      }
      #fogi-modal .fogi-container {
        background: #ffffff;
        width: 90%;
        max-width: 1100px;
        height: 80vh;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 10px 35px rgba(0,0,0,0.2);
      }
      #fogi-modal .fogi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #38a5ff;
        color: #fff;
        padding: 8px 14px;
        font-weight: 600;
        font-size: 0.95rem;
      }
      #fogi-close {
        border: none;
        background: #ffffff;
        color: #333;
        padding: 4px 10px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
      }
      #fogi-close:hover {
        background: #f1f1f1;
      }
      #fogi-iframe {
        flex: 1;
        border: none;
        width: 100%;
        height: 100%;
      }

      
  </style>
</head>

<body>
    <header class="cabecalho">
      FOAG
      <div class="header-icons">
        <i id="themeToggle" class="fa-solid fa-moon" title="Modo Escuro"></i>
        <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
        <i id="icon-fogi" class="fa-solid fa-robot" title="Assistente FOAG — FOGi"></i>
        <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
      </div>
    </header>

    <div class="container">
        <!-- Menu lateral -->
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

          <a href="../horario/horario.php" class="<?= $current === 'horario.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-clock"></i> Horário
          </a>

        </nav>

        <main class="main-content">

      <!-- CARD CONFIGURAÇÕES -->
      <section class="card-notas card-config">
        <div class="config-header">
          <h2 class="titulo-tabela">Configurações de notas</h2>
          <span class="pill-tipo">
            Modo: <?= ($tipoCurso === 'escola' ? 'Escola' : 'Faculdade'); ?> · Período: <?= htmlspecialchars($periodoAtual); ?>
          </span>
        </div>

        <p class="sub-notas">
          Ajuste o tipo de curso, a nota máxima, a média mínima e os pesos das avaliações.
          Funciona tanto para ensino básico quanto para universidade.
        </p>

        <form method="POST" class="config-form">
          <div class="tipo-curso-group">
            <span>Tipo:</span>
            <label>
              <input type="radio" name="tipo_curso" value="escola" <?= ($tipoCurso === 'escola' ? 'checked' : ''); ?>>
              Escola
            </label>
            <label>
              <input type="radio" name="tipo_curso" value="faculdade" <?= ($tipoCurso === 'faculdade' ? 'checked' : ''); ?>>
              Faculdade
            </label>
          </div>

          <div class="config-field">
            <label for="nota_maxima">Nota máxima</label>
            <input type="number" step="0.01" id="nota_maxima" name="nota_maxima"
                   value="<?= htmlspecialchars($notaMaxima); ?>" min="1">
          </div>
          <div class="config-field">
            <label for="media_aprovacao">Média para aprovação</label>
            <input type="number" step="0.01" id="media_aprovacao" name="media_aprovacao"
                   value="<?= htmlspecialchars($mediaAprovacao); ?>" min="0">
          </div>

          <div class="config-field">
            <label for="peso_1">Peso <?= htmlspecialchars($labelsAval[0]); ?></label>
            <input type="number" step="0.1" id="peso_1" name="peso_1"
                   value="<?= htmlspecialchars(isset($pesos[1]) ? $pesos[1] : 1); ?>" min="0">
          </div>
          <div class="config-field">
            <label for="peso_2">Peso <?= htmlspecialchars($labelsAval[1]); ?></label>
            <input type="number" step="0.1" id="peso_2" name="peso_2"
                   value="<?= htmlspecialchars(isset($pesos[2]) ? $pesos[2] : 1); ?>" min="0">
          </div>
          <div class="config-field">
            <label for="peso_3">Peso <?= htmlspecialchars($labelsAval[2]); ?></label>
            <input type="number" step="0.1" id="peso_3" name="peso_3"
                   value="<?= htmlspecialchars(isset($pesos[3]) ? $pesos[3] : 1); ?>" min="0">
          </div>
          <div class="config-field">
            <label for="peso_4">Peso <?= htmlspecialchars($labelsAval[3]); ?></label>
            <input type="number" step="0.1" id="peso_4" name="peso_4"
                   value="<?= htmlspecialchars(isset($pesos[4]) ? $pesos[4] : 1); ?>" min="0">
          </div>

          <div class="config-field-periodo">
            <label for="periodo_atual">Período / semestre</label>
            <select id="periodo_atual" name="periodo_atual">
              <?php
              foreach ($data['periodos'] as $nomePeriodo => $dadosPeriodo) {
                  $selected = ($nomePeriodo === $periodoAtual) ? 'selected' : '';
                  echo '<option value="' . htmlspecialchars($nomePeriodo) . '" ' . $selected . '>'
                     . htmlspecialchars($nomePeriodo)
                     . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="config-field">
            <label for="novo_periodo">Adicionar novo período</label>
            <input type="text" id="novo_periodo" name="novo_periodo" placeholder="Ex: 2025/1">
          </div>

          <input type="hidden" name="periodo_atual_form" value="<?= htmlspecialchars($periodoAtual); ?>">

          <button type="submit" name="salvar_config" class="btn-config">Salvar configurações</button>
        </form>
      </section>

      <!-- CARD PRINCIPAL DE NOTAS -->
      <section class="card-notas">
        <h2 class="titulo-tabela">Notas e cálculo de médias</h2>
        <p class="sub-notas">
          Preencha apenas as avaliações que já aconteceram.
          A média é calculada só com o que já existe.
        </p>

        <form method="POST">
          <input type="hidden" name="periodo_atual_form" value="<?= htmlspecialchars($periodoAtual); ?>">

          <table class="tabela-notas">
            <thead>
              <tr>
                <th>Matéria / Disciplina</th>
                <th><?= htmlspecialchars($labelsAval[0]); ?></th>
                <th><?= htmlspecialchars($labelsAval[1]); ?></th>
                <th><?= htmlspecialchars($labelsAval[2]); ?></th>
                <th><?= htmlspecialchars($labelsAval[3]); ?></th>
                <th>Média</th>
                <th>Situação</th>
                <th>Precisa (próx.)</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if (count($materias) === 0) {
                  echo '<tr class="linha-vazia">
                          <td colspan="9">
                            Nenhuma matéria cadastrada ainda. Clique em <strong>Adicionar matéria</strong> para começar.
                          </td>
                        </tr>';
              } else {
                  foreach ($materias as $i => $materia) {
                      $materia = htmlspecialchars((string)$materia);
                      $notas   = isset($notasAll[$i]) ? $notasAll[$i] : [1 => null, 2 => null, 3 => null, 4 => null];

                      $dados   = calcularMediaEStatus($notas, $mediaAprovacao, $pesos);
                      $media   = $dados['media'];
                      $status  = $dados['status'];
                      $precisa = calcularQuantoPrecisa($notas, $mediaAprovacao, $notaMaxima, $pesos);

                      $statusClass   = '';
                      $statusTooltip = '';
                      if ($status === 'Aprovado') {
                          $statusClass   = 'status-aprovado';
                          $statusTooltip = 'Sua média está acima da média mínima configurada.';
                      } elseif ($status === 'Recuperação') {
                          $statusClass   = 'status-recuperacao';
                          $statusTooltip = 'Você está abaixo da média mínima, mas ainda tem chance de alcançar.';
                      } elseif ($status === 'Reprovado') {
                          $statusClass   = 'status-reprovado';
                          $statusTooltip = 'Sua média ficou bem abaixo da média mínima.';
                      }

                      echo '<tr>';
                      echo '<td><input type="text" name="materia_' . $i . '" value="' . $materia . '" placeholder="Ex: Cálculo I"></td>';

                      for ($a = 1; $a <= 4; $a++) {
                          $notaVal       = isset($notas[$a]) ? $notas[$a] : null;
                          $notaStr       = ($notaVal !== null && $notaVal !== '') ? (string)$notaVal : '';
                          $notaFloat     = $notaVal !== null ? (float)$notaVal : null;
                          $notaInvalida  = ($notaFloat !== null && $notaFloat > $notaMaxima);

                          $extraClass = $notaInvalida ? ' nota-invalida' : '';
                          $titleNota  = $notaInvalida
                              ? 'A nota máxima configurada é ' . $notaMaxima . '.'
                              : 'Digite a nota dessa avaliação (máx: ' . $notaMaxima . ').';

                          echo '<td>
                                  <input 
                                    type="number" 
                                    step="0.01" 
                                    name="nota_' . $i . '_' . $a . '" 
                                    value="' . htmlspecialchars($notaStr) . '" 
                                    placeholder="Ex: 7.5" 
                                    max="' . htmlspecialchars($notaMaxima) . '" 
                                    class="input-nota' . $extraClass . '"
                                    title="' . htmlspecialchars($titleNota) . '"
                                  >
                                </td>';
                      }

                      echo '<td class="celula-media">' . number_format($media, 2, ',', '.') . '</td>';

                      echo '<td class="celula-status">
                              <span class="badge-status ' . $statusClass . '" title="' . htmlspecialchars($statusTooltip) . '">
                                ' . $status . '
                              </span>
                            </td>';

                      echo '<td class="celula-precisa">';
                      if ($precisa === null) {
                          echo '-';
                      } elseif ($precisa === 'Impossível') {
                          echo '<span class="badge-precisa impossivel" title="Mesmo com a nota máxima na próxima avaliação, não bate a média mínima.">Impossível</span>';
                      } else {
                          $tooltipPrecisa = 'Nota necessária na próxima avaliação para chegar em ' . $mediaAprovacao . '.';
                          echo '<span title="' . htmlspecialchars($tooltipPrecisa) . '">≈ ' . number_format($precisa, 2, ',', '.') . '</span>';
                      }
                      echo '</td>';

                      echo '<td>
                              <button type="submit" name="limpar_linha" value="1" class="btn-linha"
                                      onclick="document.getElementById(\'linha_index\').value=' . (int)$i . ';">
                                Limpar
                              </button>
                            </td>';

                      echo '</tr>';
                  }
              }
              ?>
            </tbody>
          </table>

          <input type="hidden" id="linha_index" name="linha_index" value="">

          <div class="buttons-notas">
            <button type="submit" name="adicionar_linha">Adicionar matéria</button>
            <button type="submit" name="remover_linha">Remover última</button>
            <button type="submit" name="limpar_tudo">Limpar tudo</button>
            <button type="submit" name="salvar_edicoes" class="btn-destaque">Salvar alterações</button>
          </div>
        </form>
      </section>

      <!-- CARD RESUMO GERAL -->
      <section class="card-notas">
        <h2 class="titulo-tabela">Resumo geral</h2>
        <?php
        $totalMaterias = count($materias);
        $aprovadas = 0;
        $recuperacao = 0;
        $reprovadas = 0;
        $somaMedias = 0;
        $contMedias = 0;

        $melhorMateria = null;
        $piorMateria   = null;

        foreach ($materias as $i => $materia) {
            $materiaNome = trim((string)$materia);
            $notas  = isset($notasAll[$i]) ? $notasAll[$i] : [1 => null, 2 => null, 3 => null, 4 => null];
            $dados  = calcularMediaEStatus($notas, $mediaAprovacao, $pesos);
            $media  = $dados['media'];
            $status = $dados['status'];

            if ($status === 'Aprovado') $aprovadas++;
            if ($status === 'Recuperação') $recuperacao++;
            if ($status === 'Reprovado') $reprovadas++;

            if ($media > 0) {
                $somaMedias += $media;
                $contMedias++;

                if ($materiaNome !== '') {
                    if ($melhorMateria === null || $media > $melhorMateria['media']) {
                        $melhorMateria = ['nome' => $materiaNome, 'media' => $media];
                    }
                    if ($piorMateria === null || $media < $piorMateria['media']) {
                        $piorMateria = ['nome' => $materiaNome, 'media' => $media];
                    }
                }
            }
        }

        $mediaGeral = $contMedias > 0 ? $somaMedias / $contMedias : 0;
        ?>
        <div class="resumo-grid">
          <div class="resumo-card">
            <span class="resumo-label">Matérias cadastradas</span>
            <span class="resumo-valor"><?= $totalMaterias; ?></span>
          </div>
          <div class="resumo-card aprovado">
            <span class="resumo-label">Aprovado</span>
            <span class="resumo-valor"><?= $aprovadas; ?></span>
          </div>
          <div class="resumo-card recuperacao">
            <span class="resumo-label">Recuperação</span>
            <span class="resumo-valor"><?= $recuperacao; ?></span>
          </div>
          <div class="resumo-card reprovado">
            <span class="resumo-label">Reprovado</span>
            <span class="resumo-valor"><?= $reprovadas; ?></span>
          </div>
          <div class="resumo-card geral">
            <span class="resumo-label">Média geral</span>
            <span class="resumo-valor"><?= number_format($mediaGeral, 2, ',', '.'); ?></span>
          </div>
        </div>

        <?php
        if ($melhorMateria || $piorMateria) {
            echo '<div class="resumo-extra">';
            if ($melhorMateria) {
                echo '<p>💪 <strong>Ponto forte:</strong> '
                   . htmlspecialchars($melhorMateria['nome'])
                   . ' (' . number_format($melhorMateria['media'], 2, ',', '.') . ')</p>';
            }
            if ($piorMateria) {
                echo '<p>⚠️ <strong>Precisa de atenção:</strong> '
                   . htmlspecialchars($piorMateria['nome'])
                   . ' (' . number_format($piorMateria['media'], 2, ',', '.') . ')</p>';
            }
            echo '</div>';

            echo '<p class="dica-foag">Dica FOAG: ';
            if ($piorMateria) {
                echo 'reserve blocos fixos no seu horário de estudos para '
                   . htmlspecialchars($piorMateria['nome'])
                   . ' e foque primeiro nas avaliações com maior peso.';
            } else {
                echo 'use o calendário do FOAG para marcar revisões antes das provas com maior peso.';
            }
            echo '</p>';
        } else {
            echo '<p class="dica-foag">
                    Comece adicionando suas matérias e notas. A partir daí o FOAG mostra onde você está indo melhor e onde precisa focar mais.
                  </p>';
        }
        ?>
      </section>

    </main>
  </div>

  <!-- Modal da FOGi -->
  <div id="fogi-modal">
    <div class="fogi-container">
      <div class="fogi-header">
        <span>FOGi — Assistente de Estudos</span>
        <button id="fogi-close">Fechar</button>
      </div>
      <iframe id="fogi-iframe" src="about:blank"></iframe>
    </div>
  </div>

  <!-- Modal de Sair -->
  <div id="logout-modal" class="modal">
    <div class="modal-content">
      <h3>Ah... já vai?</h3>
      <h4>Tem certeza que deseja sair?</h4>
      <div class="modal-buttons">
        <button id="confirm-logout" class="btn">Sim</button>
        <button id="cancel-logout" class="btn secondary">Cancelar</button>
      </div>
    </div>
  </div>

  <footer>
    &copy; 2025 FOAG. Todos os direitos reservados.
  </footer>

  <script>
    // Modal FOGi
    const fogiBtn   = document.getElementById("icon-fogi");
    const fogiModal = document.getElementById("fogi-modal");
    const fogiFrame = document.getElementById("fogi-iframe");
    const fogiClose = document.getElementById("fogi-close");

    fogiBtn && fogiBtn.addEventListener("click", () => {
      fogiFrame.src = "http://127.0.0.1:5000";
      fogiModal.style.display = "flex";
      document.body.style.overflow = "hidden";
    });

    fogiClose && fogiClose.addEventListener("click", () => {
      fogiModal.style.display = "none";
      fogiFrame.src = "about:blank";
      document.body.style.overflow = "";
    });

    window.addEventListener("message", (ev) => {
      if (ev.data && ev.data.type === "FOGI_CLOSE") {
        fogiModal.style.display = "none";
        fogiFrame.src = "about:blank";
        document.body.style.overflow = "";
      }
    });

    // Logout modal
    const logoutModal = document.getElementById('logout-modal');
    const iconPerfil = document.getElementById('icon-perfil');
    const iconSair   = document.getElementById('icon-sair');
    const confirmLogout = document.getElementById('confirm-logout');
    const cancelLogout  = document.getElementById('cancel-logout');

    iconPerfil && iconPerfil.addEventListener('click', () => {
      window.location.href = '../perfil/perfil.php';
    });

    iconSair && iconSair.addEventListener('click', () => {
      logoutModal.style.display = 'flex';
    });

    confirmLogout && confirmLogout.addEventListener('click', () => {
      window.location.href = '../login/index.php';
    });

    cancelLogout && cancelLogout.addEventListener('click', () => {
      logoutModal.style.display = 'none';
    });

    logoutModal && logoutModal.addEventListener('click', (e) => {
      if (e.target === logoutModal) logoutModal.style.display = 'none';
    });
  </script>
</body>
</html>

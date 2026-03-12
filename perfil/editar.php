<?php
// Caminhos
$caminho_json  = "../json/usuarios.json";
$pasta_fotos   = "../img/perfil/";              // garanta permissão de escrita
$foto_padrao   = "foto_padrao.png";

$escolas_json  = "../json/escolas.json";
$series_json   = "../json/series.json";

// Carrega listas de escolas/séries (vazias se não existir)
$opcoes_escolas = file_exists($escolas_json) ? json_decode(file_get_contents($escolas_json), true) : [];
$opcoes_series  = file_exists($series_json)  ? json_decode(file_get_contents($series_json),  true) : [];

// Carrega usuários
if (!file_exists($caminho_json)) {
  exit("Arquivo de usuários não encontrado!");
}
$usuarios = json_decode(file_get_contents($caminho_json), true);
if (!is_array($usuarios) || empty($usuarios)) {
  exit("Nenhum usuário cadastrado!");
}

// Seleciona o ÚLTIMO usuário do array
$usuario_index = array_key_last($usuarios);
$usuario = $usuarios[$usuario_index];

// Atualização (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Sanitização básica
  $usuarios[$usuario_index]["nome"]        = trim($_POST["nome"] ?? $usuario["nome"]);
  $usuarios[$usuario_index]["nascimento"]  = trim($_POST["nascimento"] ?? $usuario["nascimento"]);
  $usuarios[$usuario_index]["telefone"]    = trim($_POST["telefone"] ?? $usuario["telefone"]);
  $usuarios[$usuario_index]["serie"]       = trim($_POST["serie"] ?? $usuario["serie"]);
  $usuarios[$usuario_index]["escola"]      = trim($_POST["escola"] ?? $usuario["escola"]);

  // Upload de foto (opcional)
  if (isset($_FILES["foto"]) && is_uploaded_file($_FILES["foto"]["tmp_name"]) && $_FILES["foto"]["error"] === 0) {
    // Garante pasta
    if (!is_dir($pasta_fotos)) {
      @mkdir($pasta_fotos, 0775, true);
    }

    $ext_permitidas = ['jpg','jpeg','png','webp','gif'];
    $ext = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
    if (in_array($ext, $ext_permitidas, true)) {
      $nome_foto = uniqid('pf_', true) . "." . $ext;

      // Remove foto antiga (se existir e não for a padrão)
      if (!empty($usuarios[$usuario_index]["foto"])) {
        $antiga = $pasta_fotos . $usuarios[$usuario_index]["foto"];
        if (is_file($antiga) && basename($antiga) !== $foto_padrao) {
          @unlink($antiga);
        }
      }

      if (move_uploaded_file($_FILES["foto"]["tmp_name"], $pasta_fotos . $nome_foto)) {
        $usuarios[$usuario_index]["foto"] = $nome_foto;
      }
    }
  }

  // Salva JSON
  file_put_contents($caminho_json, json_encode($usuarios, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

  header("Location: perfil.php");
  exit;
}

// Define foto para exibição
$foto_perfil = (!empty($usuario["foto"]) && is_file($pasta_fotos . $usuario["foto"]))
  ? $usuario["foto"]
  : $foto_padrao;
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Editar Perfil</title>
  <link rel="stylesheet" href="editar.css">
 <link rel="stylesheet" href="dark-per.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
  <script src="../m.escuro/dark-mode.js"></script>
</head>
<body>
  <form class="container" method="post" enctype="multipart/form-data">
    <div class="left-panel">
      <img src="<?= $pasta_fotos . $foto_perfil ?>" class="avatar" alt="Foto de perfil">
      <h2>Editar Perfil</h2>
      <p>Altere suas informações abaixo.</p>

      <label for="foto" class="label-upload">Trocar Foto</label>
      <input type="file" id="foto" name="foto" accept="image/*" class="input-upload">
    </div>

    <div class="right-panel">
      <div class="campo-info">
        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario["nome"]) ?>" required>
      </div>

      <div class="campo-info">
        <label for="nascimento">Data de Nascimento</label>
        <input type="date" id="nascimento" name="nascimento" value="<?= htmlspecialchars($usuario["nascimento"]) ?>" required>
      </div>

      <div class="campo-info">
        <label for="telefone">Telefone</label>
        <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($usuario["telefone"]) ?>">
      </div>

      <div class="campo-info">
  <label for="serie">Série/Curso</label>
  <select id="serie" name="serie" required>
    <option value="">Selecione a série</option>
    <?php foreach ($opcoes_series as $serie): ?>
      <option value="<?= $serie ?>" <?= ($serie === $usuario["serie"]) ? "selected" : "" ?>>
        <?= $serie ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<div class="campo-info">
  <label for="escola">Escola/Faculdade</label>
  <select id="escola" name="escola" required>
    <option value="">Selecione a escola</option>
    <?php foreach ($opcoes_escolas as $escola): ?>
      <option value="<?= $escola ?>" <?= ($escola === $usuario["escola"]) ? "selected" : "" ?>>
        <?= $escola ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>


      <div class="botoes">
        <button type="submit" class="salvar">Salvar</button>
        <button type="button" class="cancelar" onclick="location.href='perfil.php'">Cancelar</button>
      </div>
    </div>
  </form>
  <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
  // Pega os selects
  const escolaEl = document.getElementById('escola');
  const serieEl  = document.getElementById('serie');

  // Inicializa Choices com busca ativada
  const escolaChoice = new Choices(escolaEl, {
    searchEnabled: true,
    itemSelectText: '',
    shouldSort: false,
    placeholderValue: 'Digite para buscar...'
  });

  const serieChoice = new Choices(serieEl, {
    searchEnabled: true,
    itemSelectText: '',
    shouldSort: false,
    placeholderValue: 'Digite para buscar...'
  });

  // Se por algum motivo o "selected" do PHP não for respeitado, força a seleção:
  // escolaChoice.setChoiceByValue('<?= addslashes($usuario["escola"]) ?>');
  // serieChoice.setChoiceByValue('<?= addslashes($usuario["serie"]) ?>');
</script>

</body>
</html>

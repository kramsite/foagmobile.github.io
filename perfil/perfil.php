<?php
session_start();

$current = basename($_SERVER['PHP_SELF']);

$caminho_json = "../json/usuarios.json";
$pasta_fotos_url = "../img/perfil/";
$pasta_fotos_arquivo = __DIR__ . "/../img/perfil/";
$foto_padrao = "foto_padrao.png";

function escapar($valor) {
    return htmlspecialchars($valor ?? "Não informado", ENT_QUOTES, "UTF-8");
}

function formatarData($data) {
    if (empty($data)) {
        return "Não informado";
    }

    $data_formatada = DateTime::createFromFormat("Y-m-d", $data);

    return $data_formatada ? $data_formatada->format("d/m/Y") : $data;
}

if (!file_exists($caminho_json)) {
    die("Arquivo de usuários não encontrado!");
}

$usuarios = json_decode(file_get_contents($caminho_json), true);

if (!is_array($usuarios) || empty($usuarios)) {
    die("Nenhum usuário cadastrado!");
}

$userId = $_SESSION["user_id"] ?? null;
$usuario_logado = null;

if ($userId !== null) {
    if (isset($usuarios[$userId]) && is_array($usuarios[$userId])) {
        $usuario_logado = $usuarios[$userId];
    } else {
        foreach ($usuarios as $usuario) {
            $id_usuario = $usuario["id"] ?? $usuario["user_id"] ?? null;

            if ($id_usuario !== null && (string) $id_usuario === (string) $userId) {
                $usuario_logado = $usuario;
                break;
            }
        }
    }
}

if (!$usuario_logado) {
    $usuario_logado = end($usuarios);
}

$nome = $usuario_logado["nome"] ?? "Usuário FOAG";
$email = $usuario_logado["email"] ?? "Não informado";
$nascimento = formatarData($usuario_logado["nascimento"] ?? "");
$telefone = $usuario_logado["telefone"] ?? "Não informado";
$serie = $usuario_logado["serie"] ?? "Não informado";
$escola = $usuario_logado["escola"] ?? "Não informado";

$foto_perfil = $foto_padrao;

if (!empty($usuario_logado["foto"])) {
    $foto_usuario = basename($usuario_logado["foto"]);

    if (file_exists($pasta_fotos_arquivo . $foto_usuario)) {
        $foto_perfil = $foto_usuario;
    }
}

$caminho_foto = $pasta_fotos_url . $foto_perfil;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOAG - Perfil</title>
    <link rel="stylesheet" href="perfilfil.css">
    <link rel="stylesheet" href="../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="dark-per.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../m.escuro/dark-mode.js"></script>
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
        <nav class="menu">
            <a href="../inicioo/inicio.php" class="<?= $current === "inicio.php" ? "active" : "" ?>">
                <i class="fa-solid fa-house"></i>
                Início
            </a>

            <a href="../calend/calendario.php" class="<?= $current === "calendario.php" ? "active" : "" ?>">
                <i class="fa-solid fa-calendar-days"></i>
                Calendário
            </a>

            <a href="../bloco/agenda.php" class="<?= $current === "agenda.php" ? "active" : "" ?>">
                <i class="fa-solid fa-book"></i>
                Agenda
            </a>

            <a href="../pomodoro/pomodoro.php" class="<?= $current === "pomodoro.php" ? "active" : "" ?>">
                <i class="fa-solid fa-stopwatch"></i>
                Pomodoro
            </a>

            <a href="../notas/notas.php" class="<?= $current === "notas.php" ? "active" : "" ?>">
                <i class="fa-solid fa-check-double"></i>
                Boletim
            </a>

            <a href="../loja/loja.php" class="<?= $current === "loja.php" ? "active" : "" ?>">
                <i class="fa-solid fa-store"></i>
                Loja
            </a>

            <a href="../rank/rank.php" class="<?= $current === "rank.php" ? "active" : "" ?>">
                <i class="fa-solid fa-trophy"></i>
                Ranking
            </a>
        </nav>

        <main class="conteudo perfil-conteudo">
            <div class="perfil-wrapper">
                <div class="titulo-pagina">
                    <div>
                        <span>Área do usuário</span>
                        <h1>Meu perfil</h1>
                    </div>

                    <a href="editar.php" class="btn-editar-topo">
                        <i class="fa-solid fa-pen"></i>
                        Editar perfil
                    </a>
                </div>

                <section class="perfil-destaque">
                    <div class="perfil-identidade">
                        <div class="foto-container">
                            <img src="<?= escapar($caminho_foto) ?>" alt="Foto de perfil de <?= escapar($nome) ?>">
                            <span class="foto-status"></span>
                        </div>

                        <div class="perfil-texto">
                            <span class="etiqueta-perfil">Perfil do estudante</span>
                            <h2><?= escapar($nome) ?></h2>

                            <p class="email-perfil">
                                <i class="fa-regular fa-envelope"></i>
                                <?= escapar($email) ?>
                            </p>

                            <div class="perfil-resumo">
                                <span>
                                    <i class="fa-solid fa-book-open"></i>
                                    <?= escapar($serie) ?>
                                </span>

                                <span>
                                    <i class="fa-solid fa-school"></i>
                                    <?= escapar($escola) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="perfil-ilustracao">
                        <span class="circulo circulo-1"></span>
                        <span class="circulo circulo-2"></span>
                        <span class="circulo circulo-3"></span>

                        <div class="icone-estudante">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                    </div>
                </section>

                <section class="dados-card">
                    <div class="dados-cabecalho">
                        <div class="dados-icone">
                            <i class="fa-regular fa-address-card"></i>
                        </div>

                        <div>
                            <h3>Informações cadastradas</h3>
                            <p>Confira os dados salvos na sua conta.</p>
                        </div>
                    </div>

                    <div class="dados-grid">
                        <div class="dado-item">
                            <div class="dado-item-icone">
                                <i class="fa-regular fa-user"></i>
                            </div>

                            <div>
                                <span>Nome completo</span>
                                <strong><?= escapar($nome) ?></strong>
                            </div>
                        </div>

                        <div class="dado-item">
                            <div class="dado-item-icone">
                                <i class="fa-regular fa-envelope"></i>
                            </div>

                            <div>
                                <span>E-mail</span>
                                <strong><?= escapar($email) ?></strong>
                            </div>
                        </div>

                        <div class="dado-item">
                            <div class="dado-item-icone">
                                <i class="fa-regular fa-calendar"></i>
                            </div>

                            <div>
                                <span>Data de nascimento</span>
                                <strong><?= escapar($nascimento) ?></strong>
                            </div>
                        </div>

                        <div class="dado-item">
                            <div class="dado-item-icone">
                                <i class="fa-solid fa-phone"></i>
                            </div>

                            <div>
                                <span>Telefone</span>
                                <strong><?= escapar($telefone) ?></strong>
                            </div>
                        </div>

                        <div class="dado-item">
                            <div class="dado-item-icone">
                                <i class="fa-solid fa-book-open-reader"></i>
                            </div>

                            <div>
                                <span>Série ou ano</span>
                                <strong><?= escapar($serie) ?></strong>
                            </div>
                        </div>

                        <div class="dado-item">
                            <div class="dado-item-icone">
                                <i class="fa-solid fa-school"></i>
                            </div>

                            <div>
                                <span>Escola ou faculdade</span>
                                <strong><?= escapar($escola) ?></strong>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <div id="logout-modal" class="modal">
        <div class="modal-content">
            <h3>Ah... já vai?</h3>
            <h4>Tem certeza que deseja sair?</h4>

            <div class="modal-buttons">
                <button id="confirm-logout">Sim</button>
                <button id="cancel-logout">Cancelar</button>
            </div>
        </div>
    </div>

    <div id="fogi-modal">
        <div class="fogi-container">
            <div class="fogi-header">
                <span>FOGi — Assistente de Estudos</span>
                <button id="fogi-close">Fechar</button>
            </div>

            <iframe id="fogi-iframe" src="about:blank"></iframe>
        </div>
    </div>

    <footer>&copy; 2025 FOAG. Todos os direitos reservados.</footer>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const fogiBtn = document.getElementById("icon-fogi");
            const fogiModal = document.getElementById("fogi-modal");
            const fogiFrame = document.getElementById("fogi-iframe");
            const fogiClose = document.getElementById("fogi-close");

            const sairBtn = document.getElementById("icon-sair");
            const logoutModal = document.getElementById("logout-modal");
            const confirmarLogout = document.getElementById("confirm-logout");
            const cancelarLogout = document.getElementById("cancel-logout");

            if (fogiBtn && fogiModal && fogiFrame && fogiClose) {
                fogiBtn.addEventListener("click", () => {
                    fogiFrame.src = "http://127.0.0.1:5000";
                    fogiModal.style.display = "flex";
                    document.body.style.overflow = "hidden";
                });

                fogiClose.addEventListener("click", () => {
                    fogiModal.style.display = "none";
                    fogiFrame.src = "about:blank";
                    document.body.style.overflow = "";
                });

                window.addEventListener("message", (evento) => {
                    if (evento.data && evento.data.type === "FOGI_CLOSE") {
                        fogiModal.style.display = "none";
                        fogiFrame.src = "about:blank";
                        document.body.style.overflow = "";
                    }
                });
            }

            if (sairBtn && logoutModal && confirmarLogout && cancelarLogout) {
                sairBtn.addEventListener("click", () => {
                    logoutModal.style.display = "flex";
                });

                cancelarLogout.addEventListener("click", () => {
                    logoutModal.style.display = "none";
                });

                confirmarLogout.addEventListener("click", () => {
                    window.location.href = "../login/logout.php";
                });

                logoutModal.addEventListener("click", (evento) => {
                    if (evento.target === logoutModal) {
                        logoutModal.style.display = "none";
                    }
                });
            }
        });
    </script>
</body>
</html>
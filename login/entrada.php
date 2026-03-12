<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Bem-vindo</title>
    <style>
         body {
            font-family:'Poppins', sans-serif;
            background: linear-gradient(to right, #38a5ff, rgb(46, 154, 241));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        
        h1 {
            font-size: 5em;
            font-family: 'Snap ITC', sans-serif;
            color: rgb(255, 255, 255);
            margin-bottom: 20px;
        }

        h2 {
            font-size: 2.5em;
            color: rgb(255, 255, 255);
            margin-bottom: -1px;
        }

        p {
            font-size: 1.5em;
            color: white;
            margin-bottom: 20px;
        }

        a {
            display: inline-block;
            background-color: #ffffff;
            color: #38a5ff;
            font-size: 1.2em;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease-in-out;
        }

        a:hover {
            background-color: #e6f3ff;
            transform: translateY(-3px);
        }

        a:active {
            transform: translateY(1px);
        }
    </style>
</head>
<body>
    <h1>FOAG</h1>  
    <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h2>
    <p>Você está logado com sucesso.</p>
    <a href="../inicioo/inicio.php">Entrar</a>
</body>
</html>

<?php
session_start();

$erro = "";

// Defina aqui a sua senha e usuário de Administrador
$usuario_admin = "nicoleadm";
$senha_admin   = "Glonidati@2025"; // Altere para uma senha forte!

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['usuario'] ?? '';
    $pass = $_POST['senha'] ?? '';

    if ($user === $usuario_admin && $pass === $senha_admin) {
        $_SESSION['admin_logado'] = true;
        header("Location: gerenciar_pagamentos.php");
        exit;
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login Administrador - RESTCONTROL</title>
    <style>
        body { font-family: Arial, sans-serif; background: #121212; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: #1e1e1e; padding: 30px; border-radius: 8px; border: 1px solid #8B0000; width: 300px; }
        h2 { text-align: center; color: #D4AF37; margin-top: 0; }
        input { width: 100%; padding: 10px; margin: 8px 0 16px; box-sizing: border-box; background: #262626; border: 1px solid #444; color: #fff; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #8B0000; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        button:hover { background: #a80000; }
        .error { color: #ff6b6b; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Acesso Restrito Admin</h2>
        <?php if ($erro) echo "<div class='error'>$erro</div>"; ?>
        <form method="POST">
            <label>Usuário:</label>
            <input type="text" name="usuario" required>
            
            <label>Senha:</label>
            <input type="password" name="senha" required>
            
            <button type="submit">Entrar no Painel</button>
        </form>
    </div>
</body>
</html>
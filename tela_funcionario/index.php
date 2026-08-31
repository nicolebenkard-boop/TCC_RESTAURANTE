<?php
require_once 'auth_funcionario.php';

$nome_funcionario = $_SESSION['funcionario_nome'] ?? 'Funcionário';

if (!empty($_SESSION['funcionario_primeiro_login'])) {
    header('Location: trocar_senha.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Área do Funcionário</title>
    <link rel="stylesheet" href="funcionario.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="bg-overlay"></div>

<div class="dashboard-wrapper">
    <header class="top-bar">
        <div class="brand">
            <img src="../pag_login/logo.png" alt="RestControl" class="top-logo">
            <span>RestControl</span>
        </div>

        <div class="profile-area">
            <button type="button" class="profile-button" id="profileButton" aria-expanded="false" aria-controls="profileMenu">
                <span class="profile-avatar"><i class="fa-solid fa-user"></i></span>
                <span class="profile-name"><?php echo $nome_funcionario; ?></span>
                <i class="fa-solid fa-chevron-down profile-chevron"></i>
            </button>

            <div class="profile-menu" id="profileMenu">
                <div class="profile-menu-header">
                    <span class="profile-avatar profile-avatar-large"><i class="fa-solid fa-user"></i></span>
                    <div>
                        <strong><?php echo $nome_funcionario; ?></strong>
                        <small>Funcionário</small>
                    </div>
                </div>

                <a href="perfil.php" class="profile-menu-item">
                    <i class="fa-solid fa-id-card"></i>
                    <span>Dados pessoais</span>
                </a>
                <a href="perfil.php#gestor" class="profile-menu-item">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Gestor responsável</span>
                </a>
                <a href="trocar_senha.php" class="profile-menu-item">
                    <i class="fa-solid fa-key"></i>
                    <span>Trocar senha</span>
                </a>

                <div class="profile-divider"></div>
                <a href="logout.php" class="profile-menu-item logout-item">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Sair</span>
                </a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="welcome-text">
            <h1>Olá, <?php echo $nome_funcionario; ?>!</h1>
            <p>Esta é a sua área de funcionário no RestControl.</p>
        </div>
    </main>
</div>

<script src="script.js"></script>
</body>
</html>

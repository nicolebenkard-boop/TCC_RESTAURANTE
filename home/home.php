<?php
session_start();

// Bloqueia acesso de quem não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$nome_gestor = htmlspecialchars($_SESSION['usuario_nome']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Painel Principal</title>
    <link rel="stylesheet" href="home.css">
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

            <div class="top-actions">
                <a href="#" class="top-link"><i class="fa-solid fa-user-gear"></i> Perfil</a>
                <a href="#" class="top-link"><i class="fa-solid fa-gear"></i> Configurações</a>
                <a href="#" class="top-link premium"><i class="fa-solid fa-crown"></i> Assinatura</a>
                <a href="../pag_login/index.php" class="top-link logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
            </div>
        </header>

        <main class="main-content">
            <div class="welcome-text">
                <h1>Olá, <?php echo $nome_gestor; ?>!</h1>
                <p>O que você deseja gerenciar hoje no seu restaurante?</p>
            </div>

            <div class="modules-grid">

                <a href="../ficha_tecnica/ficha.php" class="mod-card">
                    <div class="icon-box"><i class="fa-solid fa-book-open"></i></div>
                    <h3>Ficha Técnica</h3>
                </a>

                <a href="../pratos/pratos.php" class="mod-card">
                    <div class="icon-box"><i class="fa-solid fa-utensils"></i></div>
                    <h3>Pratos</h3>
                </a>

                <a href="../funcionarios/funcionarios.php" class="mod-card">
                    <div class="icon-box"><i class="fa-solid fa-users"></i></div>
                    <h3>Funcionários</h3>
                </a>

                <a href="../fornecedores/fornecedores.php" class="mod-card">
                    <div class="icon-box"><i class="fa-solid fa-truck-ramp-box"></i></div>
                    <h3>Fornecedores</h3>
                </a>

                <a href="../estoque/estoque.php" class="mod-card">
                    <div class="icon-box"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <h3>Estoque</h3>
                </a>

                <a href="#" class="mod-card">
                    <div class="icon-box"><i class="fa-solid fa-calculator"></i></div>
                    <h3>Cálculos</h3>
                </a>

                <a href="../mesas/mesas.php" class="mod-card">
                    <div class="icon-box"><i class="fa-solid fa-chair"></i></div>
                    <h3>Mesas</h3>
                </a>

                <a href="../pedidos/pedidos.php" class="mod-card">
                    <div class="icon-box"><i class="fa-solid fa-receipt"></i></div>
                    <h3>Pedidos</h3>
                </a>

                <a href="../cardapio_digital/qrcode.php" class="mod-card">
                    <div class="icon-box"><i class="fa-solid fa-qrcode"></i></div>
                    <h3>Cardápio Digital</h3>
                </a>

                <a href="../layout/layout.php" class="mod-card">
                    <div class="icon-box"><i class="fa-solid fa-paint-brush"></i></div>
                    <h3>Layout</h3>
                </a>

                <a href="../kanban/kanban.php" class="mod-card">
                    <div class="icon-box"><i class="fa-solid fa-paint-brush"></i></div>
                    <h3>Kanban</h3>
                </a>

            </div>
        </main>
    </div>

</body>
</html>

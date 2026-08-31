<?php
require_once 'auth_funcionario.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Meu Perfil</title>
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
        <div class="top-actions">
            <a href="index.php" class="top-link"><i class="fa-solid fa-house"></i> Início</a>
            <a href="logout.php" class="top-link logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
    </header>

    <main class="main-content profile-page">
        <div class="profile-card">
            <div class="profile-card-title">
                <span class="profile-avatar profile-avatar-large"><i class="fa-solid fa-user"></i></span>
                <div>
                    <h1>Meu Perfil</h1>
                    <p>Confira seus dados e o responsável pelo seu acesso.</p>
                </div>
            </div>

            <section class="profile-section">
                <h2><i class="fa-solid fa-id-card"></i> Dados pessoais</h2>
                <div class="profile-data-grid">
                    <div><span>Nome completo</span><strong><?php echo $nome_funcionario; ?></strong></div>
                    <div><span>CPF</span><strong><?php echo htmlspecialchars($cpf_formatado); ?></strong></div>
                    <div><span>Início no trabalho</span><strong><?php echo date('d/m/Y', strtotime($funcionario['inicio_trabalho'])); ?></strong></div>
                    <div><span>Desempenho</span><strong><?php echo htmlspecialchars($funcionario['desempenho']); ?></strong></div>
                </div>
            </section>

            <section class="profile-section" id="gestor">
                <h2><i class="fa-solid fa-user-tie"></i> Gestor responsável</h2>
                <div class="manager-box">
                    <div class="manager-icon"><i class="fa-solid fa-user-tie"></i></div>
                    <div>
                        <strong><?php echo $nome_gestor; ?></strong>
                        <span><?php echo $email_gestor; ?></span>
                        <?php if ($restaurante !== ''): ?><span>Restaurante: <?php echo $restaurante; ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="subscription-status">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>
                        <strong>Acesso liberado</strong>
                        <span>A assinatura do gestor está ativa até <?php echo $data_vencimento_formatada; ?>.</span>
                    </div>
                </div>
            </section>

            <div class="profile-actions">
                <a href="trocar_senha.php" class="btn btn-secondary-profile"><i class="fa-solid fa-key"></i> Trocar senha</a>
            </div>
        </div>
    </main>
</div>
</body>
</html>

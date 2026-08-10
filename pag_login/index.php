<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="login-container">
        <div class="login-banner">
            <div class="banner-content">
                <img src="logo.png" alt="RestControl Logo" class="logo-img">
                <h2>Bem-vindo de volta!</h2>
                <p>Gerencie seu restaurante de forma simples, rápida e eficiente em um só lugar.</p>
            </div>
        </div>

        <div class="login-form-wrapper">
            <div class="form-box">
                <h2>Acessar Conta</h2>
                <p class="subtitle">Insira suas credenciais abaixo</p>

                <form action="../home/home.php" method="POST" id="loginForm">
                    <div class="input-group">
                        <label for="email">E-mail ou Usuário</label>
                        <div class="input-field">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Ex: gestor@restcontrol.com" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">Senha</label>
                        <div class="input-field">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Sua senha" required>
                            <i class="fa-regular fa-eye" id="togglePassword" style="cursor: pointer;"></i>
                        </div>
                    </div>

                    <div class="form-options">
                        <a href="#" class="forgot-password">Esqueceu a senha?</a>
                    </div>

                    <div class="button-group">
                        <a href="../home/home.php" class="btn btn-primary" style="display: block; text-decoration: none;">Entrar</a>
                        <a href="../abertura/index.php" class="btn btn-secondary">Voltar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
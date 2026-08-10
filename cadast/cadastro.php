<?php
// Exemplo de processamento simples do cadastro
$mensagem_sucesso = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_responsavel = filter_input(INPUT_POST, 'nome_responsavel', FILTER_SANITIZE_SPECIAL_CHARS);
    $nome_restaurante = filter_input(INPUT_POST, 'nome_restaurante', FILTER_SANITIZE_SPECIAL_CHARS);
    $email            = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Aqui você conectará com seu Banco de Dados (MySQL) no futuro
    $mensagem_sucesso = "Cadastro de <strong>{$nome_restaurante}</strong> realizado com sucesso! Aguarde a liberação do seu acesso.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESTCONTROL - Cadastro do Estabelecimento</title>
    <!-- Fontes Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="cadastro.css">
</head>
<body>

    <!-- CABEÇALHO -->
    <header class="header">
        <div class="logo-wrapper">
            <img src="logo.png" alt="RESTCONTROL Logo">
        </div>
        <h1>RESTCONTROL</h1>
        <p>Cadastro do Estabelecimento</p>
    </header>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="main-container">
        <div class="card-cadastro">
            
            <div class="card-header">
                <h2>Criar sua Conta</h2>
                <span class="step-tag">Etapa 2 de 2</span>
            </div>

            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alert-success">
                    <?php echo $mensagem_sucesso; ?>
                </div>
            <?php endif; ?>

            <p class="instruction-text">
                Preencha os dados abaixo para criar o perfil do seu restaurante e configurar o seu acesso.
            </p>

            <form id="form-cadastro" action="cadastro.php" method="POST">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome_responsavel">Nome do Responsável / Gerente</label>
                        <input type="text" id="nome_responsavel" name="nome_responsavel" placeholder="Ex: Carlos Silva" required>
                    </div>

                    <div class="form-group">
                        <label for="nome_restaurante">Nome do Restaurante</label>
                        <input type="text" id="nome_restaurante" name="nome_restaurante" placeholder="Ex: Sabor & Arte Bistrô" required>
                    </div>

                    <div class="form-group">
                        <label for="doc">CPF ou CNPJ</label>
                        <input type="text" id="doc" name="doc" placeholder="000.000.000-00" required>
                    </div>

                    <div class="form-group">
                        <label for="whatsapp">WhatsApp para Contato</label>
                        <input type="tel" id="whatsapp" name="whatsapp" placeholder="(00) 90000-0000" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="email">E-mail de Acesso (Login)</label>
                        <input type="email" id="email" name="email" placeholder="gerencia@restaurante.com" required>
                    </div>

                    <div class="form-group">
                        <label for="senha">Crie uma Senha</label>
                        <input type="password" id="senha" name="senha" placeholder="••••••••" required>
                    </div>

                    <div class="form-group">
                        <label for="confirma_senha">Confirme a Senha</label>
                        <input type="password" id="confirma_senha" name="confirma_senha" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="termo_pagamento" required>
                    <label for="termo_pagamento">
                        Declaro que já efetuei o pagamento de <strong>R$ 10,00 via Pix</strong> para ativação da licença.
                    </label>
                </div>

                <div id="error-message" class="error-message"></div>

                <button type="submit" href="../pag_login/index.php" class="btn-cadastrar">Finalizar Cadastro e Entrar</button>
                
            </form>

        </div>
    </main>

    <!-- RODAPÉ DE NAVEGAÇÃO -->
    <footer class="footer-back">
        <a href="../assinatura/assinatura.php">← Voltar para a tela de pagamento</a>
    </footer>

    <script src="cadastro.js"></script>
</body>
</html>
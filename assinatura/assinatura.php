<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESTCONTROL - Assinatura do Plano</title>
    <!-- Fontes Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assinatura.css">
</head>
<body>

    <!-- CABEÇALHO -->
    <header class="header">
        <div class="logo-wrapper">
            <img src="logo.png" alt="RESTCONTROL Logo">
        </div>
        <h1>RESTCONTROL</h1>
        <p>Ativação de Licença Mensal</p>
    </header>

    <!-- CONTEÚDO PRINCIPAL (CARD DE PAGAMENTO) -->
    <main class="main-container">
        <div class="card-pagamento">
            
            <div class="card-header">
                <h2>Plano Mensal RESTCONTROL</h2>
                <span class="price-tag">R$ 10,00 / mês</span>
            </div>

            <p class="instruction-text">
                Para liberar o seu acesso à plataforma, faça o pagamento de <strong>R$ 10,00</strong> usando o QR Code ou a Chave Pix abaixo no app do seu banco:
            </p>

            <!-- CAIXA DO PIX -->
            <div class="pix-box">
                <span class="pix-badge">PIX INSTANTÂNEO</span>
                
                <div class="pix-qr">
                    <!-- Substitua 'qr_code_pix.png' pela imagem do seu QR Code -->
                    <img src="qr_code_pix.png" alt="QR Code Pix R$ 10,00" id="qr-img" onerror="this.src='https://via.placeholder.com/200x200/ffffff/000000?text=QR+Code+Pix'">
                </div>

                <p class="pix-label">Chave Pix Copia e Cola:</p>
                
                <div class="pix-key-wrapper">
                    <input type="text" id="pix-key" class="pix-key-input" value="14998077182" readonly>
                    <button type="button" id="btn-copy" class="btn-copy">Copiar</button>
                </div>
            </div>

            <!-- BOTÃO PARA PROSSEGUIR APÓS O PAGAMENTO -->
            <div class="action-wrapper">
                <p class="confirm-notice">Já efetuou o pagamento no seu banco?</p>
                <a href="../cadast/cadastro.php" class="btn-prosseguir">Já fiz o Pix! Criar minha Conta →</a>
            </div>

        </div>
    </main>

    <!-- RODAPÉ -->
    <footer class="footer-back">
        <a href="../abertura/index.php">← Voltar para a página inicial</a>
    </footer>

    <script src="assinatura.js"></script>
</body>
</html>
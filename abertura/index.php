<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESTCONTROL - Gerencie seu Restaurante</title>
    <!-- Fontes Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- CABEÇALHO PRINCIPAL -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo-wrapper">
                <img src="logo.png" alt="RESTCONTROL Logo" class="brand-logo">
            </div>
            <h1 class="brand-title">RESTCONTROL</h1>
            <p class="brand-tagline">Gerencie seu Restaurante</p>
        </div>
    </header>

    <main>
        <!-- SEÇÃO 1: A IMPORTÂNCIA DA GESTÃO -->
        <section class="intro-section">
            <div class="container">
                <div class="section-badge">GESTÃO INTELIGENTE</div>
                <h2>A Importância Vital de uma Plataforma de Controle para o Seu Estabelecimento</h2>
                <div class="divider"></div>
                <p class="lead-text">
                    No setor gastronômico, a organização e o controle diário são a chave para manter o restaurante lucrativo, eficiente e sem desperdícios.
                </p>
                <p class="description-text">
                    Um estabelecimento sem gestão sofre com perdas no estoque, atrasos nos pedidos e falta de clareza financeira. Ter uma plataforma centralizada permite ao gerente acompanhar métricas cruciais, otimizar fornecedores e garantir a satisfação dos clientes em cada atendimento.
                </p>
            </div>
        </section>

        <!-- SEÇÃO 2: IMAGEM DE TELA CHEIA (inicio.png) + BOTÕES DE AÇÃO -->
        <section class="banner-section">
            <div class="banner-wrapper">
                <img src="inicio.png" alt="O sucesso do seu restaurante depende da sua gestão - RESTCONTROL" class="full-banner-img">
                
                <!-- BOTÕES ABAIXO DA IMAGEM -->
                <div class="cta-container">
                    <a href="../assinatura/assinatura.php" class="btn-cta">Garanta agora!</a>
                    <br><br>
                    <a href="../pag_login/index.php" class="btn-login">Já tem nossa plataforma? Faça login!</a>
                </div>
            </div>
        </section>

        <!-- SEÇÃO 3: TEXTO COMPLEMENTAR SOBRE ORGANIZAÇÃO -->
        <section class="details-section">
            <div class="container">
                <div class="text-block">
                    <h2>Por que a Organização Transforma seu Negócio?</h2>
                    <p>
                        Com o <strong>RESTCONTROL</strong>, você substitui o caos das anotações manuais por um controle profissional e automatizado. Veja o impacto direto na rotina do seu restaurante:
                    </p>
                    <ul class="benefit-list">
                        <li><strong>Redução de Desperdícios:</strong> Controle rigoroso de estoque e compras no momento certo.</li>
                        <li><strong>Agilidade e Eficiência:</strong> Fluxo contínuo e organizado entre atendimento, salão e cozinha.</li>
                        <li><strong>Saúde Financeira:</strong> Acompanhamento detalhado de custos, margens e lucros.</li>
                        <li><strong>Tranquilidade para o Gerente:</strong> Relatórios claros para tomar decisões com segurança.</li>
                    </ul>
                </div>
            </div>
        </section>
    </main>

    <!-- RODAPÉ -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-logo">
                <img src="logo.png" alt="RESTCONTROL" class="footer-img">
            </div>
            <p class="footer-title">RESTCONTROL — Gerencie seu Restaurante</p>
            <p class="footer-copy">&copy; <?php echo date('Y'); ?> RESTCONTROL. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
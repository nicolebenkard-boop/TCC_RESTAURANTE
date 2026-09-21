<?php
session_start();
require_once '../conexao.php';

$token = $_GET['g'] ?? '';
$erro = '';

$gestor = null;
if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT id_gestor, restaurante FROM tb_gestor WHERE token_cardapio = :token");
    $stmt->execute([':token' => $token]);
    $gestor = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (empty($token) || !$gestor) {
    $erro = 'Link inválido ou expirado. Peça ao restaurante o QR Code atualizado.';
} elseif (!empty($_SESSION['erro_entrada'])) {
    $erro_form = $_SESSION['erro_entrada'];
    unset($_SESSION['erro_entrada']);
}

$nome_restaurante = $gestor ? htmlspecialchars($gestor['restaurante']) : 'RestControl';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nome_restaurante; ?> - Cardápio Digital</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="bg-overlay"></div>

    <div class="cliente-wrapper">
        <div class="topo-marca">
            <img src="../pag_login/logo.png" alt="Logo" class="logo-cliente">
            <h1><?php echo $nome_restaurante; ?></h1>
            <p>Preencha seus dados para ver o cardápio e fazer seu pedido</p>
        </div>

        <div class="form-card">
            <?php if ($erro): ?>
                <div class="alerta-erro"><?php echo htmlspecialchars($erro); ?></div>
            <?php else: ?>
                <?php if (!empty($erro_form)): ?>
                    <div class="alerta-erro"><?php echo htmlspecialchars($erro_form); ?></div>
                <?php endif; ?>
                <form method="POST" action="abrir_comanda.php" id="formEntrada">
                    <input type="hidden" name="g" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="input-group">
                        <label for="nome">Nome completo</label>
                        <div class="input-field">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="nome" name="nome" placeholder="Seu nome completo" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="mesa">Número da mesa</label>
                        <div class="input-field">
                            <i class="fa-solid fa-chair"></i>
                            <input type="number" id="mesa" name="mesa" placeholder="Ex: 12" min="1" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="cpf">CPF</label>
                        <div class="input-field">
                            <i class="fa-regular fa-id-card"></i>
                            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" inputmode="numeric" maxlength="14" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="telefone">Telefone</label>
                        <div class="input-field">
                            <i class="fa-solid fa-phone"></i>
                            <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000" inputmode="numeric" maxlength="15" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-utensils"></i> Ver Cardápio
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>

<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = $_SESSION['usuario_id'];

// Gera (ou regenera) o token único do cardápio digital deste gestor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['gerar_token'])) {
    $novo_token = bin2hex(random_bytes(12));
    $stmt = $pdo->prepare("UPDATE tb_gestor SET token_cardapio = :token WHERE id_gestor = :id_gestor");
    $stmt->execute([':token' => $novo_token, ':id_gestor' => $id_gestor]);
}

$stmt = $pdo->prepare("SELECT token_cardapio, restaurante FROM tb_gestor WHERE id_gestor = :id_gestor");
$stmt->execute([':id_gestor' => $id_gestor]);
$gestor = $stmt->fetch(PDO::FETCH_ASSOC);

$token = $gestor['token_cardapio'] ?? null;

$link_cardapio = null;
$qr_url = null;

if (!empty($token)) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_path = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/\\');
    $link_cardapio = "{$scheme}://{$host}{$base_path}/tela_clientes/index.php?g={$token}";
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=" . urlencode($link_cardapio);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Cardápio Digital</title>
    <link rel="stylesheet" href="qrcode.css">
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
                <a href="../home/home.php" class="top-link"><i class="fa-solid fa-house"></i> Início</a>
                <a href="../mesas/mesas.php" class="top-link"><i class="fa-solid fa-chair"></i> Mesas</a>
                <a href="../pedidos/pedidos.php" class="top-link"><i class="fa-solid fa-receipt"></i> Pedidos</a>
                <a href="../pag_login/index.php" class="top-link logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
            </div>
        </header>

        <main class="main-content">
            <div class="welcome-text">
                <h1>Cardápio Digital</h1>
                <p>Gere o QR Code do seu restaurante para os clientes fazerem pedidos pela mesa.</p>
            </div>

            <div class="qr-card">
                <?php if (!$link_cardapio): ?>
                    <p class="aviso">Você ainda não gerou o QR Code do seu cardápio digital.</p>
                    <form method="POST">
                        <button type="submit" name="gerar_token" class="btn btn-primary">
                            <i class="fa-solid fa-qrcode"></i> Gerar QR Code
                        </button>
                    </form>
                <?php else: ?>
                    <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="QR Code do cardápio" class="qr-img">

                    <div class="link-box">
                        <input type="text" readonly value="<?php echo htmlspecialchars($link_cardapio); ?>" id="linkCardapio">
                        <button type="button" class="btn-copiar" onclick="copiarLink()"><i class="fa-regular fa-copy"></i></button>
                    </div>
                    <p class="copiado" id="msgCopiado">Link copiado!</p>

                    <p class="explicacao">
                        Imprima este QR Code e coloque nas mesas do restaurante. Ao escanear, o cliente informa
                        nome, número da mesa, CPF e telefone, e já cai direto no cardápio para fazer o pedido — sem precisar de login.
                    </p>

                    <form method="POST" onsubmit="return confirm('Gerar um novo QR Code vai invalidar o antigo. Os que já foram impressos deixarão de funcionar. Deseja continuar?');">
                        <button type="submit" name="gerar_token" class="btn btn-secundario">
                            <i class="fa-solid fa-rotate"></i> Gerar um novo QR Code
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="qrcode.js"></script>
</body>
</html>

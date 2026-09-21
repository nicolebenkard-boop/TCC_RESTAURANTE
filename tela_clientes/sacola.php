<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['cliente_pedido_id'], $_SESSION['cliente_gestor_id'])) {
    header("Location: index.php");
    exit();
}

// Remover item da sacola (só é permitido enquanto não foi finalizado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover_item'])) {
    $id_prato = intval($_POST['id_prato']);
    unset($_SESSION['cliente_carrinho'][$id_prato]);
    header("Location: sacola.php");
    exit();
}

$carrinho = $_SESSION['cliente_carrinho'] ?? [];
$total = 0;
foreach ($carrinho as $item) {
    $total += $item['preco'] * $item['quantidade'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sacola</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="bg-overlay"></div>

    <div class="cliente-wrapper">

        <header class="cliente-top-bar">
            <div class="cliente-brand">
                <img src="../pag_login/logo.png" alt="Logo" class="logo-mini">
                <span>Mesa <?php echo htmlspecialchars($_SESSION['cliente_mesa_numero']); ?></span>
            </div>
            <div class="cliente-top-actions">
                <a href="cardapio.php" class="top-link-cliente"><i class="fa-solid fa-arrow-left"></i> Cardápio</a>
                <a href="meus_pedidos.php" class="top-link-cliente"><i class="fa-solid fa-receipt"></i> Meus Pedidos</a>
            </div>
        </header>

        <main class="cardapio-main">
            <h1 class="titulo-cardapio">Sua Sacola</h1>

            <?php if (empty($carrinho)): ?>
                <p class="vazio">Sua sacola está vazia. <a href="cardapio.php">Voltar ao cardápio</a></p>
            <?php else: ?>
                <div class="sacola-lista">
                    <?php foreach ($carrinho as $id_prato => $item): ?>
                        <div class="sacola-item">
                            <div class="sacola-item-info">
                                <span class="sacola-item-qtd"><?php echo $item['quantidade']; ?>x</span>
                                <span class="sacola-item-nome"><?php echo htmlspecialchars($item['nome']); ?></span>
                            </div>
                            <div class="sacola-item-valor">
                                R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?>
                            </div>
                            <form method="POST" class="form-remover">
                                <input type="hidden" name="id_prato" value="<?php echo $id_prato; ?>">
                                <button type="submit" name="remover_item" class="btn-remover" title="Remover">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="sacola-total">
                    Total: <strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
                </div>

                <form method="POST" action="finalizar_pedido.php">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i> Finalizar Pedido
                    </button>
                </form>
                <p class="aviso-sacola">Depois de finalizado, o pedido vai para a cozinha e não pode mais ser removido por aqui.</p>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>

<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['cliente_pedido_id'], $_SESSION['cliente_gestor_id'])) {
    header("Location: index.php");
    exit();
}

$id_pedido = $_SESSION['cliente_pedido_id'];

$stmt = $pdo->prepare("SELECT * FROM tb_pedidos WHERE id_pedido = :id");
$stmt->execute([':id' => $id_pedido]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM tb_itens_pedido WHERE id_pedido = :id ORDER BY data_hora ASC");
$stmt->execute([':id' => $id_pedido]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mostrar_confirmacao = !empty($_SESSION['pedido_confirmado']);
unset($_SESSION['pedido_confirmado']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos</title>
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
                <a href="cardapio.php" class="top-link-cliente"><i class="fa-solid fa-utensils"></i> Cardápio</a>
            </div>
        </header>

        <main class="cardapio-main">
            <?php if ($mostrar_confirmacao): ?>
                <div class="toast-sucesso">Pedido enviado para a cozinha!</div>
            <?php endif; ?>

            <h1 class="titulo-cardapio">Meus Pedidos</h1>
            <p class="subtitulo-pedidos">
                Status da mesa: <strong><?php echo $pedido['status'] === 'aberto' ? 'Em aberto' : 'Paga'; ?></strong>
            </p>

            <?php if (empty($itens)): ?>
                <p class="vazio">Você ainda não fez nenhum pedido. <a href="cardapio.php">Ver cardápio</a></p>
            <?php else: ?>
                <div class="sacola-lista">
                    <?php foreach ($itens as $item): ?>
                        <div class="sacola-item">
                            <div class="sacola-item-info">
                                <span class="sacola-item-qtd"><?php echo $item['quantidade_vendida']; ?>x</span>
                                <span class="sacola-item-nome">
                                    <?php echo htmlspecialchars($item['nome_prato'] ?? ('Prato #' . $item['id_prato'])); ?>
                                    <span class="item-hora"><?php echo date('d/m H:i', strtotime($item['data_hora'])); ?></span>
                                </span>
                            </div>
                            <div class="sacola-item-valor">
                                R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade_vendida'], 2, ',', '.'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="sacola-total">
                    Total da conta: <strong>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></strong>
                </div>

                <p class="aviso-sacola">
                    Quando quiser encerrar e pagar, chame o garçom — o pagamento é feito diretamente com o restaurante.
                </p>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>

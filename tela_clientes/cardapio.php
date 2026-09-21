<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['cliente_pedido_id'], $_SESSION['cliente_gestor_id'])) {
    header("Location: index.php");
    exit();
}

$id_gestor = $_SESSION['cliente_gestor_id'];
$mensagem = null;

// Adicionar item à sacola (fica só na sessão até "Finalizar Pedido")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_sacola'])) {
    $id_prato = intval($_POST['id_prato']);
    $quantidade = max(1, intval($_POST['quantidade'] ?? 1));

    $stmt = $pdo->prepare("SELECT id_prato, nome_prato, preco_venda, imagem FROM tb_pratos WHERE id_prato = :id AND id_gestor = :id_gestor");
    $stmt->execute([':id' => $id_prato, ':id_gestor' => $id_gestor]);
    $prato = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($prato) {
        if (!isset($_SESSION['cliente_carrinho'][$id_prato])) {
            $_SESSION['cliente_carrinho'][$id_prato] = [
                'nome' => $prato['nome_prato'],
                'preco' => (float) $prato['preco_venda'],
                'imagem' => $prato['imagem'],
                'quantidade' => 0,
            ];
        }
        $_SESSION['cliente_carrinho'][$id_prato]['quantidade'] += $quantidade;
        $mensagem = htmlspecialchars($prato['nome_prato']) . ' adicionado à sacola.';
    }
}

$stmt = $pdo->prepare("SELECT id_prato, nome_prato, imagem, preco_venda FROM tb_pratos WHERE id_gestor = :id_gestor ORDER BY nome_prato ASC");
$stmt->execute([':id_gestor' => $id_gestor]);
$pratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca os ingredientes de todos os pratos de uma vez só
$ingredientes_por_prato = [];
if (!empty($pratos)) {
    $stmt = $pdo->prepare("
        SELECT f.id_prato, f.quantidade_necessaria, f.unidade, e.nome_ingredientes
        FROM tb_item_ficha_tecnica f
        JOIN tb_estoque e ON e.id_ingrediente = f.id_ingrediente
        WHERE e.id_gestor = :id_gestor
        ORDER BY e.nome_ingredientes ASC
    ");
    $stmt->execute([':id_gestor' => $id_gestor]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $ing) {
        $ingredientes_por_prato[$ing['id_prato']][] = $ing['nome_ingredientes'];
    }
}

$total_itens_sacola = 0;
foreach ($_SESSION['cliente_carrinho'] ?? [] as $item) {
    $total_itens_sacola += $item['quantidade'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio</title>
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
                <a href="meus_pedidos.php" class="top-link-cliente"><i class="fa-solid fa-receipt"></i> Meus Pedidos</a>
                <a href="sacola.php" class="top-link-cliente sacola-link">
                    <i class="fa-solid fa-bag-shopping"></i> Sacola
                    <?php if ($total_itens_sacola > 0): ?>
                        <span class="badge"><?php echo $total_itens_sacola; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </header>

        <?php if ($mensagem): ?>
            <div class="toast-sucesso"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <main class="cardapio-main">
            <h1 class="titulo-cardapio">Cardápio</h1>

            <?php if (empty($pratos)): ?>
                <p class="vazio">O cardápio ainda não tem pratos cadastrados.</p>
            <?php else: ?>
                <div class="pratos-grid">
                    <?php foreach ($pratos as $prato): ?>
                        <div class="prato-card">
                            <div class="prato-imagem">
                                <?php if (!empty($prato['imagem'])): ?>
                                    <img src="../pratos/uploads/<?php echo htmlspecialchars($prato['imagem']); ?>" alt="<?php echo htmlspecialchars($prato['nome_prato']); ?>">
                                <?php else: ?>
                                    <div class="sem-imagem"><i class="fa-solid fa-bowl-food"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="prato-corpo">
                                <div class="prato-cabecalho">
                                    <h3><?php echo htmlspecialchars($prato['nome_prato']); ?></h3>
                                    <span class="prato-preco">R$ <?php echo number_format($prato['preco_venda'], 2, ',', '.'); ?></span>
                                </div>

                                <?php if (!empty($ingredientes_por_prato[$prato['id_prato']])): ?>
                                    <p class="prato-ingredientes">
                                        <i class="fa-solid fa-leaf"></i> <?php echo htmlspecialchars(implode(', ', $ingredientes_por_prato[$prato['id_prato']])); ?>
                                    </p>
                                <?php endif; ?>

                                <form method="POST" class="form-add-carrinho">
                                    <input type="hidden" name="id_prato" value="<?php echo $prato['id_prato']; ?>">
                                    <input type="number" name="quantidade" value="1" min="1" class="input-qtd">
                                    <button type="submit" name="adicionar_sacola" class="btn-add">
                                        <i class="fa-solid fa-plus"></i> Adicionar
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>

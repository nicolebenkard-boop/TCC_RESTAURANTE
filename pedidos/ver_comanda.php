<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = $_SESSION['usuario_id'];
$id_pedido = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT p.*, m.numero_mesa
    FROM tb_pedidos p
    LEFT JOIN tb_mesas m ON m.id_mesa = p.id_mesa
    WHERE p.id_pedido = :id AND p.id_gestor = :id_gestor
");
$stmt->execute([':id' => $id_pedido, ':id_gestor' => $id_gestor]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header("Location: pedidos.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM tb_itens_pedido WHERE id_pedido = :id ORDER BY data_hora ASC");
$stmt->execute([':id' => $id_pedido]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cpf_formatado = null;
if (!empty($pedido['cpf_cliente'])) {
    $cpf_digits = preg_replace('/\D/', '', $pedido['cpf_cliente']);
    if (strlen($cpf_digits) === 11) {
        $cpf_formatado = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf_digits);
    } else {
        $cpf_formatado = $pedido['cpf_cliente'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Detalhe da Comanda</title>
    <link rel="stylesheet" href="pedidos.css">
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
                <a href="pedidos.php" class="top-link"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
                <a href="../pag_login/index.php" class="top-link logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
            </div>
        </header>

        <main class="main-content">
            <div class="painel painel-detalhe">

                <div class="detalhe-cabecalho">
                    <h1>Mesa <?php echo htmlspecialchars($pedido['numero_mesa'] ?? '-'); ?></h1>
                    <span class="status-pill <?php echo $pedido['status'] === 'aberto' ? 'status-aberto' : 'status-fechado'; ?>">
                        <?php echo $pedido['status'] === 'aberto' ? 'Em aberto' : 'Paga'; ?>
                    </span>
                </div>

                <div class="detalhe-cliente">
                    <div><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($pedido['nome_cliente'] ?? 'Cliente'); ?></div>
                    <?php if ($cpf_formatado): ?>
                        <div><i class="fa-solid fa-id-card"></i> <?php echo htmlspecialchars($cpf_formatado); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($pedido['telefone_cliente'])): ?>
                        <div><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($pedido['telefone_cliente']); ?></div>
                    <?php endif; ?>
                    <div><i class="fa-solid fa-clock"></i> Aberta em <?php echo $pedido['data_abertura'] ? date('d/m/Y H:i', strtotime($pedido['data_abertura'])) : '-'; ?></div>
                    <?php if ($pedido['data_fechamento']): ?>
                        <div><i class="fa-solid fa-clock-rotate-left"></i> Paga em <?php echo date('d/m/Y H:i', strtotime($pedido['data_fechamento'])); ?></div>
                    <?php endif; ?>
                </div>

                <h3 class="titulo-itens">Itens pedidos</h3>

                <?php if (empty($itens)): ?>
                    <p class="vazio">Nenhum item registrado ainda.</p>
                <?php else: ?>
                    <div class="tabela-itens">
                        <?php foreach ($itens as $item): ?>
                            <div class="linha-item">
                                <div class="linha-item-nome">
                                    <?php echo htmlspecialchars($item['quantidade_vendida']); ?>x <?php echo htmlspecialchars($item['nome_prato'] ?? ('Prato #' . $item['id_prato'])); ?>
                                    <span class="linha-item-hora"><?php echo date('d/m H:i', strtotime($item['data_hora'])); ?></span>
                                </div>
                                <div class="linha-item-valor">
                                    R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade_vendida'], 2, ',', '.'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="detalhe-total">
                    Total: <strong>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></strong>
                </div>

                <?php if ($pedido['status'] === 'aberto'): ?>
                    <form method="POST" action="pedidos.php" onsubmit="return confirm('Confirma que a mesa já pagou e pode ser encerrada?');">
                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
                        <button type="submit" name="encerrar_pedido" class="btn btn-primary">
                            <i class="fa-solid fa-check"></i> Dar baixa nesta mesa
                        </button>
                    </form>
                <?php endif; ?>

            </div>
        </main>
    </div>

</body>
</html>

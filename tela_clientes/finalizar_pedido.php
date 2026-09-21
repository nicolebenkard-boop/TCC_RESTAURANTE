<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['cliente_pedido_id'], $_SESSION['cliente_gestor_id'])) {
    header("Location: index.php");
    exit();
}

$id_pedido = $_SESSION['cliente_pedido_id'];
$carrinho = $_SESSION['cliente_carrinho'] ?? [];

if (!empty($carrinho)) {
    $stmt = $pdo->prepare("
        INSERT INTO tb_itens_pedido (id_pedido, id_prato, nome_prato, preco_unitario, quantidade_vendida, data_hora)
        VALUES (:id_pedido, :id_prato, :nome_prato, :preco_unitario, :quantidade, NOW())
    ");

    foreach ($carrinho as $id_prato => $item) {
        $stmt->execute([
            ':id_pedido'      => $id_pedido,
            ':id_prato'       => $id_prato,
            ':nome_prato'     => $item['nome'],
            ':preco_unitario' => $item['preco'],
            ':quantidade'     => $item['quantidade'],
        ]);
    }

    // Recalcula o total da comanda com base em todos os itens já pedidos
    $stmt = $pdo->prepare("
        UPDATE tb_pedidos
        SET valor_total = (
            SELECT COALESCE(SUM(preco_unitario * quantidade_vendida), 0)
            FROM tb_itens_pedido
            WHERE id_pedido = :id_pedido
        )
        WHERE id_pedido = :id_pedido2
    ");
    $stmt->execute([':id_pedido' => $id_pedido, ':id_pedido2' => $id_pedido]);

    $_SESSION['cliente_carrinho'] = [];
    $_SESSION['pedido_confirmado'] = true;
}

header("Location: meus_pedidos.php");
exit();

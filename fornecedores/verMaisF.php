<?php
session_start();
require_once '../conexao.php'; // deve fornecer $pdo (PDO), igual ao login

// Bloqueia acesso de quem não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = $_SESSION['usuario_id']; // mesma chave usada no login_processa.php

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: fornecedores.php");
    exit();
}

$id_fornecedor = intval($_GET['id']);

// Busca os dados filtrando pelo ID selecionado E pelo gestor logado
$stmt = $pdo->prepare("SELECT * FROM tb_fornecedores WHERE id_fornecedor = :id AND id_gestor = :id_gestor");
$stmt->execute([
    ':id'        => $id_fornecedor,
    ':id_gestor' => $id_gestor,
]);
$fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fornecedor) {
    header("Location: fornecedores.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Ficha do Fornecedor</title>
    <link rel="stylesheet" href="verMaisF.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="bg-overlay"></div>

    <header class="top-bar">
        <div class="brand">
            <img src="../pag_login/logo.png" alt="RestControl" class="top-logo">
            <span>RestControl</span>
        </div>
        <div class="top-actions">
            <a href="fornecedores.php" class="top-link"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
        </div>
    </header>

    <div class="details-container">
        <main class="card-box details-card">
            <div class="card-header">
                <div class="user-avatar">
                    <i class="fa-solid fa-boxes-packing"></i>
                </div>
                <h2><?php echo htmlspecialchars($fornecedor['nome_fornecedor']); ?></h2>
                <p class="role-badge">Registro Identificador: #<?php echo $fornecedor['id_fornecedor']; ?></p>
            </div>

            <hr class="divider">

            <div class="details-grid">
                <div class="detail-item">
                    <label><i class="fa-solid fa-truck"></i> Nome do Fornecedor</label>
                    <p><?php echo htmlspecialchars($fornecedor['nome_fornecedor']); ?></p>
                </div>

                <div class="detail-item">
                    <label><i class="fa-solid fa-box"></i> Produto Fornecido</label>
                    <p><?php echo htmlspecialchars($fornecedor['produto_fornecido']); ?></p>
                </div>

                <div class="detail-item">
                    <label><i class="fa-solid fa-arrow-up-1-9"></i> Quantidade Total de Produtos</label>
                    <p><?php echo htmlspecialchars($fornecedor['qtd_total_produto']); ?> unidades</p>
                </div>

                <div class="detail-item">
                    <label><i class="fa-solid fa-calendar-alt"></i> Data de Reabastecimento</label>
                    <p><?php echo date('d/m/Y', strtotime($fornecedor['data_reabastecimento'])); ?></p>
                </div>
            </div>

            <hr class="divider">

            <div class="card-actions">
                <a href="fornecedores.php" class="btn btn-secondary">Voltar à Lista</a>
                <a href="fornecedores.php?excluir=<?php echo $fornecedor['id_fornecedor']; ?>" 
                    class="btn btn-danger"
                    onclick="return confirm('Tem certeza que deseja remover o lote fornecido por <?php echo htmlspecialchars($fornecedor['nome_fornecedor']); ?>?')">
                    <i class="fa-solid fa-trash-can"></i> Deletar Registro
                </a>
            </div>
        </main>
    </div>

</body>
</html>

<?php
session_start();
require_once '../conexao.php'; // deve fornecer $pdo (PDO), igual ao login

// Bloqueia acesso de quem não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = $_SESSION['usuario_id']; // mesma chave usada no login_processa.php

// PROCESSAMENTO DO FORMULÁRIO (CADASTRAR FORNECEDOR)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar'])) {
    $nome_fornecedor      = trim($_POST['nome_fornecedor']);
    $produto_fornecido    = trim($_POST['produto_fornecido']);
    $qtd_total_produto    = intval($_POST['qtd_total_produto']);
    $data_reabastecimento = $_POST['data_reabastecimento'];

    $sql_insert = "INSERT INTO tb_fornecedores (id_gestor, nome_fornecedor, produto_fornecido, qtd_total_produto, data_reabastecimento)
                   VALUES (:id_gestor, :nome, :produto, :qtd, :data)";
    $stmt = $pdo->prepare($sql_insert);
    $ok = $stmt->execute([
        ':id_gestor' => $id_gestor,
        ':nome'      => $nome_fornecedor,
        ':produto'   => $produto_fornecido,
        ':qtd'       => $qtd_total_produto,
        ':data'      => $data_reabastecimento,
    ]);

    if ($ok) {
        header("Location: fornecedores.php");
        exit();
    } else {
        echo "<script>alert('Erro ao cadastrar fornecedor.');</script>";
    }
}

// PROCESSAMENTO DE EXCLUSÃO
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);

    $stmt = $pdo->prepare("DELETE FROM tb_fornecedores WHERE id_fornecedor = :id AND id_gestor = :id_gestor");
    $stmt->execute([
        ':id'        => $id_excluir,
        ':id_gestor' => $id_gestor,
    ]);

    header("Location: fornecedores.php");
    exit();
}

// Busca os registros SOMENTE do gestor logado
$stmt = $pdo->prepare("SELECT * FROM tb_fornecedores WHERE id_gestor = :id_gestor ORDER BY id_fornecedor DESC");
$stmt->execute([':id_gestor' => $id_gestor]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Fornecedores</title>
    <link rel="stylesheet" href="fornecedores.css">
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
            <a href="../home/home.php" class="top-link"><i class="fa-solid fa-house"></i> Voltar ao Painel</a>
        </div>
    </header>

    <div class="container">
        <section class="card-box form-section">
            <h2><i class="fa-solid fa-truck-ramp-box"></i> Adicionar Fornecedor</h2>
            <p class="subtitle">Insira as informações de reabastecimento</p>

            <form action="fornecedores.php" method="POST" class="form-grid">
                <div class="input-group">
                    <label>Nome do Fornecedor / Empresa</label>
                    <input type="text" name="nome_fornecedor" placeholder="Ex: Atacadão Alimentos" required>
                </div>

                <div class="input-group">
                    <label>Produto Fornecido</label>
                    <input type="text" name="produto_fornecido" placeholder="Ex: Arroz, Feijão, Óleo" required>
                </div>

                <div class="input-group">
                    <label>Quantidade Total de Produtos</label>
                    <input type="number" name="qtd_total_produto" placeholder="Ex: 150" min="1" required>
                </div>

                <div class="input-group">
                    <label>Data do Reabastecimento</label>
                    <input type="date" name="data_reabastecimento" required>
                </div>

                <div class="button-container">
                    <button type="submit" name="cadastrar" class="btn btn-primary">Salvar Fornecedor</button>
                </div>
            </form>
        </section>

        <section class="card-box list-section">
            <h2><i class="fa-solid fa-boxes-stacked"></i> Fornecedores Ativos</h2>
            <div class="func-list">
                <?php if ($resultado && count($resultado) > 0): ?>
                    <?php foreach ($resultado as $row): ?>
                        <div class="func-item">
                            <div class="func-info-basic">
                                <div class="avatar"><i class="fa-solid fa-building"></i></div>
                                <div>
                                    <h3><?php echo htmlspecialchars($row['nome_fornecedor']); ?></h3>
                                    <p>Produto: <?php echo htmlspecialchars($row['produto_fornecido']); ?></p>
                                </div>
                            </div>
                            <a href="verMaisF.php?id=<?php echo $row['id_fornecedor']; ?>" class="btn btn-secondary">Ver Mais</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-msg">Nenhum fornecedor registrado no sistema.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

</body>
</html>

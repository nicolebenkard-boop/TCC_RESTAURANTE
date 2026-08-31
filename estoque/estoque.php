<?php
// estoque.php - Módulo de Estoque RestControl
session_start();
require_once '../conexao.php'; // deve fornecer $pdo (PDO), igual ao login

// Bloqueia acesso de quem não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = $_SESSION['usuario_id'];

// LÓGICA DE CADASTRO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar_ingrediente'])) {
    $nome_ingredientes = trim($_POST['nome_ingredientes']);
    $quantidade_atual  = intval($_POST['quantidade_atual']);
    $quantidade_minima = intval($_POST['quantidade_minima']);
    $quantidade_maxima = intval($_POST['quantidade_maxima']);
    $custo_unitario    = floatval(str_replace(',', '.', $_POST['custo_unitario']));

    $stmt = $pdo->prepare("INSERT INTO tb_estoque (id_gestor, nome_ingredientes, quantidade_atual, quantidade_minima, quantidade_maxima, custo_unitario)
                            VALUES (:id_gestor, :nome, :qatual, :qmin, :qmax, :custo)");
    $stmt->execute([
        ':id_gestor' => $id_gestor,
        ':nome'      => $nome_ingredientes,
        ':qatual'    => $quantidade_atual,
        ':qmin'      => $quantidade_minima,
        ':qmax'      => $quantidade_maxima,
        ':custo'     => $custo_unitario,
    ]);

    header("Location: estoque.php");
    exit();
}

// LÓGICA DE EXCLUSÃO (só permite excluir ingrediente do próprio gestor)
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);

    $stmt = $pdo->prepare("DELETE FROM tb_estoque WHERE id_ingrediente = :id AND id_gestor = :id_gestor");
    $stmt->execute([
        ':id'        => $id_excluir,
        ':id_gestor' => $id_gestor,
    ]);

    header("Location: estoque.php");
    exit();
}

// BUSCA SOMENTE dos ingredientes do gestor logado
$stmt = $pdo->prepare("SELECT * FROM tb_estoque WHERE id_gestor = :id_gestor ORDER BY nome_ingredientes ASC");
$stmt->execute([':id_gestor' => $id_gestor]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Estoque</title>
    <link rel="stylesheet" href="estoque.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="bg-overlay"></div>

    <div class="dashboard-wrapper">
        <header class="top-bar">
            <div class="brand">
                <i class="fa-solid fa-boxes-stacked brand-icon" style="color: #780c0c; font-size: 1.3rem; margin-right: 8px;"></i>
                <span>RestControl</span>
            </div>
            <div class="top-actions">
                <a href="../home/home.php" class="top-link"><i class="fa-solid fa-right-from-bracket"></i>← Voltar ao painel</a>
            </div>
        </header>

        <main class="main-content">

            <section class="card-box form-section">
                <h2><i class="fa-solid fa-circle-plus"></i> Registrar Ingrediente</h2>

                <form action="estoque.php" method="POST" id="formEstoque">
                    <div class="form-grid">
                        <div class="input-group">
                            <label for="nome_ingredientes">Nome do Ingrediente</label>
                            <input type="text" id="nome_ingredientes" name="nome_ingredientes" placeholder="Ex: Tomate Cereja" required>
                        </div>

                        <div class="input-group">
                            <label for="quantidade_atual">Quantidade Atual</label>
                            <input type="number" id="quantidade_atual" name="quantidade_atual" placeholder="Ex: 50" min="0" required>
                        </div>

                        <div class="input-group">
                            <label for="quantidade_minima">Quantidade Mínima</label>
                            <input type="number" id="quantidade_minima" name="quantidade_minima" placeholder="Ex: 10" min="0" required>
                        </div>

                        <div class="input-group">
                            <label for="quantidade_maxima">Quantidade Máxima</label>
                            <input type="number" id="quantidade_maxima" name="quantidade_maxima" placeholder="Ex: 100" min="0" required>
                        </div>

                        <div class="input-group">
                            <label for="custo_unitario">Custo Unitário (R$)</label>
                            <input type="text" id="custo_unitario" name="custo_unitario" placeholder="Ex: 5,50" required>
                        </div>
                    </div>

                    <div class="button-container">
                        <button type="submit" name="adicionar_ingrediente" class="btn btn-primary">Adicionar Ingrediente</button>
                    </div>
                </form>
            </section>

            <section class="card-box list-section">
                <h2><i class="fa-solid fa-boxes-stacked"></i> Ingredientes Cadastrados</h2>
                <div class="func-list">
                    <?php if ($resultado && count($resultado) > 0): ?>
                        <?php foreach ($resultado as $row): ?>
                            <?php $abaixo_minimo = $row['quantidade_atual'] <= $row['quantidade_minima']; ?>
                            <div class="func-item">
                                <div class="func-info-basic">
                                    <div class="avatar"><i class="fa-solid fa-box"></i></div>
                                    <div>
                                        <h3><?php echo htmlspecialchars($row['nome_ingredientes']); ?></h3>
                                        <p>
                                            Estoque: <?php echo (int)$row['quantidade_atual']; ?>
                                            (mín: <?php echo (int)$row['quantidade_minima']; ?> / máx: <?php echo (int)$row['quantidade_maxima']; ?>)
                                            <?php if ($abaixo_minimo): ?>
                                                <span style="color:#ef4444; font-weight:600;"> — repor!</span>
                                            <?php endif; ?>
                                        </p>
                                        <p>Custo Unitário: R$ <?php echo number_format($row['custo_unitario'], 2, ',', '.'); ?></p>
                                    </div>
                                </div>
                                <a href="estoque.php?excluir=<?php echo $row['id_ingrediente']; ?>"
                                   class="btn btn-danger"
                                   onclick="return confirm('Remover \'<?php echo htmlspecialchars($row['nome_ingredientes']); ?>\' do estoque?')">
                                   <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-msg">Nenhum ingrediente adicionado ao estoque.</p>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

    <script src="estoque.js"></script>
</body>
</html>

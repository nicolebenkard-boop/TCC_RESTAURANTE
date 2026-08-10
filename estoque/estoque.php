<?php
// estoque.php - Módulo de Estoque RestControl
session_start();

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "restaurante";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// LÓGICA DE CADASTRO (Envia os campos obrigatórios ocultos para respeitar a estrutura do banco)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar_ingrediente'])) {
    $nome_ingredientes = $conn->real_escape_string($_POST['nome_ingredientes']);
    $custo_unitario = floatval($_POST['custo_unitario']);
    
    // Solução para as restrições NOT NULL: preenche o mínimo exigido pelo banco por trás dos panos
    $qtd_produto = 0; 
    $validade_padrao = '2000-01-01'; // Data padrão aceita pelo formato DATE do MySQL
    $id_gestor_padrao = 1;           // Vincula ao Gestor Administrador padrão (ID 1)

    // Query montada exatamente com a ordem e os tipos das colunas do seu banco
    $sql = "INSERT INTO tb_estoque (nome_ingredientes, custo_unitario, qtd_produto, validade, id_gestor) 
            VALUES ('$nome_ingredientes', $custo_unitario, $qtd_produto, '$validade_padrao', $id_gestor_padrao)";
    
    if ($conn->query($sql)) {
        header("Location: estoque.php");
        exit();
    } else {
        die("Erro ao salvar no banco: " . $conn->error);
    }
}

// LÓGICA DE EXCLUSÃO
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    $conn->query("DELETE FROM tb_estoque WHERE id_ingredientes = $id_excluir");
    header("Location: estoque.php");
    exit();
}

// BUSCA DOS INGREDIENTES PARA A LISTA DA DIREITA
$sql_busca = "SELECT * FROM tb_estoque ORDER BY nome_ingredientes ASC";
$resultado = $conn->query($sql_busca);
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
                <a href="../home/home.php" class="top-link"><i class="fa-solid fa-right-from-bracket"></i> Voltar</a>
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
                            <label for="custo_unitario">Custo Unitário (R$)</label>
                            <input type="number" id="custo_unitario" name="custo_unitario" placeholder="Ex: 5.50" step="0.01" min="0" required>
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
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while($row = $resultado->fetch_assoc()): ?>
                            <div class="func-item">
                                <div class="func-info-basic">
                                    <div class="avatar"><i class="fa-solid fa-box"></i></div>
                                    <div>
                                        <h3><?php echo htmlspecialchars($row['nome_ingredientes']); ?></h3>
                                        <p>Custo Unitário: R$ <?php echo number_format($row['custo_unitario'], 2, ',', '.'); ?></p>
                                    </div>
                                </div>
                                <a href="verMaisE.php?id=<?php echo $row['id_ingredientes']; ?>" class="btn btn-secondary">Ver Mais</a>
                            </div>
                        <?php endwhile; ?>
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
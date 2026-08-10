<?php
// Inicia a sessão para identificar o gestor logado
session_start();

// Configurações de conexão com o banco de dados
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "restaurante";

$conn = new mysqli($host, $usuario, $senha, $banco);

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Para testes locais, se não houver um gestor logado na sessão, define o ID 1 como padrão
$id_gestor = isset($_SESSION['id_gestor']) ? intval($_SESSION['id_gestor']) : 1;

// Garante que o gestor padrão existe no banco para evitar erros de Chave Estrangeira (FK)
$check_gestor = $conn->query("SELECT id_gestor FROM tb_gestor WHERE id_gestor = $id_gestor");
if ($check_gestor->num_rows == 0) {
    $conn->query("INSERT INTO tb_gestor (id_gestor, CNPJ, CPF, nome_gestor, e_mail, senha)
                VALUES ($id_gestor, '00000000000000', '00000000000', 'Gestor Administrador', 'admin@restcontrol.com', '123456')");
}

// PROCESSAMENTO DO FORMULÁRIO (CADASTRAR FORNECEDOR)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar'])) {
    // Coleta e sanitiza os dados enviados pelo gestor
    $nome_fornecedor     = $conn->real_escape_string($_POST['nome_fornecedor']);
    $produto_fornecido   = $conn->real_escape_string($_POST['produto_fornecido']);
    $qtd_total_produto   = intval($_POST['qtd_total_produto']);
    $data_reabastecimento = $_POST['data_reabastecimento']; // Coleta apenas a data (AAAA-MM-DD)

    // Insere os dados incluindo a data selecionada manualmente pelo gestor
    $sql_insert = "INSERT INTO tb_fornecedores (id_gestor, nome_fornecedor, produto_fornecido, qtd_total_produto, data_reabastecimento)
                VALUES ($id_gestor, '$nome_fornecedor', '$produto_fornecido', $qtd_total_produto, '$data_reabastecimento')";

    if ($conn->query($sql_insert) === TRUE) {
        header("Location: fornecedores.php");
        exit();
    } else {
        echo "<script>alert('Erro ao cadastrar: " . $conn->error . "');</script>";
    }
}

// PROCESSAMENTO DE EXCLUSÃO
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    $sql_delete = "DELETE FROM tb_fornecedores WHERE id_fornecedor = $id_excluir AND id_gestor = $id_gestor";

    if ($conn->query($sql_delete) === TRUE) {
        header("Location: fornecedores.php");
        exit();
    } else {
        echo "<script>alert('Erro ao excluir: " . $conn->error . "');</script>";
    }
}

// Busca os registros para exibição na lista
$sql_select = "SELECT * FROM tb_fornecedores WHERE id_gestor = $id_gestor ORDER BY id_fornecedor DESC";
$resultado = $conn->query($sql_select);
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
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while($row = $resultado->fetch_assoc()): ?>
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
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">Nenhum fornecedor registrado no sistema.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

</body>
</html>
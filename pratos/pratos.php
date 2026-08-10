<?php
session_start();

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "restaurante";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Respeita o padrão que você usou nos outros arquivos para o ID do gestor logado
$id_gestor = isset($_SESSION['id_gestor']) ? intval($_SESSION['id_gestor']) : 1;

// Garante que o gestor padrão existe no banco para evitar erros de FK
$check_gestor = $conn->query("SELECT id_gestor FROM tb_gestor WHERE id_gestor = $id_gestor");
if ($check_gestor->num_rows == 0) {
    $conn->query("INSERT INTO tb_gestor (id_gestor, CNPJ, CPF, nome_gestor, e_mail, senha)
                VALUES ($id_gestor, '00000000000000', '00000000000', 'Gestor Administrador', 'admin@restcontrol.com', '123456')");
}

// LÓGICA 1: Cadastrar Novo Prato
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar'])) {
    $nome_prato = $conn->real_escape_string($_POST['nome_prato']);
    // Converte vírgula para ponto se o usuário digitar no padrão brasileiro (ex: 29,90)
    $preco_venda = str_replace(',', '.', $_POST['preco_venda']);
    $preco_venda = floatval($preco_venda);

    $sql = "INSERT INTO tb_pratos (id_gestor, nome_prato, preco_venda) VALUES ($id_gestor, '$nome_prato', $preco_venda)";
    if ($conn->query($sql)) {
        header("Location: pratos.php");
        exit();
    }
}

// LÓGICA 2: Excluir Prato diretamente na linha
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    $sql_delete = "DELETE FROM tb_pratos WHERE id_prato = $id_excluir AND id_gestor = $id_gestor";
    if ($conn->query($sql_delete)) {
        header("Location: pratos.php");
        exit();
    }
}

// LÓGICA 3: Buscar Pratos Ativos do Gestor
$sql_busca = "SELECT id_prato, nome_prato, preco_venda FROM tb_pratos WHERE id_gestor = $id_gestor ORDER BY id_prato DESC";
$resultado = $conn->query($sql_busca);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Gestão de Pratos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="pratos.css">
</head>
<body>

    <div class="bg-overlay"></div>

    <header class="top-bar">
        <div class="brand">
            <span>RestControl</span>
        </div>
        <a href="../home/home.php" class="top-link"><i class="fa-solid fa-arrow-left"></i> Voltar ao Painel</a>
    </header>

    <div class="container-dashboard">
        <section class="card-box form-section">
            <div class="header-section">
                <h2><i class="fa-solid fa-utensils"></i> Gestão do Cardápio</h2>
                <p>Adicione novos pratos à sua operação gastronômica e defina os valores de venda.</p>
            </div>
            <button class="btn btn-primary" id="openModalBtn"><i class="fa-solid fa-plus"></i> Novo Prato</button>
        </section>

        <section class="card-box list-section">
            <h2><i class="fa-solid fa-plate-wheat"></i> Pratos Ativos</h2>
            <div class="func-list">
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while($row = $resultado->fetch_assoc()): ?>
                        <div class="func-item">
                            <div class="func-info-basic">
                                <div class="avatar"><i class="fa-solid fa-bowl-food"></i></div>
                                <div>
                                    <h3><?php echo htmlspecialchars($row['nome_prato']); ?></h3>
                                    <p>Preço de Venda: R$ <?php echo number_format($row['preco_venda'], 2, ',', '.'); ?></p>
                                </div>
                            </div>
                            <a href="pratos.php?excluir=<?php echo $row['id_prato']; ?>"
                            class="btn btn-danger"
                            onclick="return confirm('Tem certeza que deseja remover o prato \'<?php echo htmlspecialchars($row['nome_prato']); ?>\' do cardápio?')">
                            <i class="fa-solid fa-trash-can"></i> Excluir
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">Nenhum prato registrado no cardápio.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="modal" id="pratoModal">
        <div class="modal-content">
            <span class="close-btn" id="closeModalBtn">&times;</span>
            <h2>Cadastrar Prato</h2>
            <form action="pratos.php" method="POST">
                <div class="input-group">
                    <label for="nome_prato">Nome do Prato</label>
                    <input type="text" id="nome_prato" name="nome_prato" placeholder="Ex: Risoto de Alho Poró" required>
                </div>
                <div class="input-group">
                    <label for="preco_venda">Preço de Venda (R$)</label>
                    <input type="text" id="preco_venda" name="preco_venda" placeholder="Ex: 49.90" required>
                </div>
                <div class="button-container">
                    <button type="submit" name="cadastrar" class="btn btn-primary">Salvar Prato</button>
                </div>
            </form>
        </div>
    </div>

    <script src="pratos.js"></script>
</body>
</html>
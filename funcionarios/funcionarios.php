<?php
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

// CORREÇÃO DEFINITIVA: Usando 'e_mail' exatamente como está no seu banco de dados
$check_gestor = $conn->query("SELECT id_gestor FROM tb_gestor WHERE id_gestor = 1");
if ($check_gestor->num_rows == 0) {
    // Insere o gestor inicial respeitando o nome exato com underline (e_mail)
    $conn->query("INSERT INTO tb_gestor (id_gestor, CNPJ, CPF, nome_gestor, e_mail, senha)
                VALUES (1, '00000000000000', '00000000000', 'Gestor Administrador', 'admin@restcontrol.com', '123456')");
}

// LÓGICA 1: Inserir Funcionário (Cadastro)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar'])) {
    $nome = $conn->real_escape_string($_POST['nome_completo']);
    $cpf = $conn->real_escape_string($_POST['cpf_funcionario']);
    $inicio = $conn->real_escape_string($_POST['inicio_work']);
    $desempenho = $conn->real_escape_string($_POST['desempenho']);

    $sql_insert = "INSERT INTO tb_funcionarios (id_gestor, nome_completo, CPF_funcionario, inicio_trabalho, desempenho)
                VALUES (1, '$nome', '$cpf', '$inicio', '$desempenho')";

    if ($conn->query($sql_insert) === TRUE) {
        header("Location: funcionarios.php?sucesso=1");
        exit();
    } else {
        echo "<script>alert('Erro ao cadastrar: " . $conn->error . "');</script>";
    }
}

// LÓGICA 2: Excluir Funcionário
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    $sql_delete = "DELETE FROM tb_funcionarios WHERE id_funcionario = $id_excluir";

    if ($conn->query($sql_delete) === TRUE) {
        header("Location: funcionarios.php?excluido=1");
        exit();
    } else {
        echo "<script>alert('Erro ao excluir: " . $conn->error . "');</script>";
    }
}

// LÓGICA 3: Buscar todos os funcionários para a listagem
$sql_select = "SELECT * FROM tb_funcionarios ORDER BY id_funcionario DESC";
$resultado = $conn->query($sql_select);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Funcionários</title>
    <link rel="stylesheet" href="funcionarios.css">
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
            <h2><i class="fa-solid fa-user-plus"></i> Adicionar Funcionário</h2>
            <p class="subtitle">Preencha os dados de acordo com os campos do banco</p>

            <form action="funcionarios.php" method="POST" class="form-grid">
                <div class="input-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome_completo" placeholder="Ex: João Silva dos Santos" required>
                </div>

                <div class="input-group">
                    <label>CPF</label>
                    <input type="text" name="cpf_funcionario" placeholder="000.000.000-00" maxsize="14" required>
                </div>

                <div class="input-group">
                    <label>Início do Trabalho</label>
                    <input type="date" name="inicio_work" required>
                </div>

                <div class="input-group">
                    <label>Desempenho Inicial</label>
                    <input type="text" name="desempenho" placeholder="Ex: Ótimo, Em Treinamento" required>
                </div>

                <div class="button-container">
                    <button type="submit" name="cadastrar" class="btn btn-primary">Salvar Funcionário</button>
                </div>
            </form>
        </section>

        <section class="card-box list-section">
            <h2><i class="fa-solid fa-users"></i> Quadro de Funcionários</h2>
            <div class="func-list">
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while($row = $resultado->fetch_assoc()): ?>
                        <div class="func-item">
                            <div class="func-info-basic">
                                <div class="avatar"><i class="fa-solid fa-user"></i></div>
                                <div>
                                    <h3><?php echo htmlspecialchars($row['nome_completo']); ?></h3>
                                    <p>CPF: <?php echo htmlspecialchars($row['CPF_funcionario']); ?></p>
                                </div>
                            </div>
                            <a href="verMais.php?id=<?php echo $row['id_funcionario']; ?>" class="btn btn-secondary">Ver Mais</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">Nenhum funcionário cadastrado ainda.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

</body>
</html>
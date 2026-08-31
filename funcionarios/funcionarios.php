<?php
session_start();
require_once '../conexao.php'; // deve fornecer $pdo (PDO), igual ao login

// 1. Bloqueia acesso de quem não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor_logado = $_SESSION['usuario_id'];

// LÓGICA 1: Inserir Funcionário (Cadastro)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar'])) {
    $nome       = trim($_POST['nome_completo']);
    $cpf        = trim($_POST['cpf_funcionario']);
    $inicio     = trim($_POST['inicio_work']);
    $desempenho = trim($_POST['desempenho']);

    $sql_insert = "INSERT INTO tb_funcionarios (id_gestor, nome_completo, CPF_funcionario, inicio_trabalho, desempenho)
                   VALUES (:id_gestor, :nome, :cpf, :inicio, :desempenho)";
    $stmt = $pdo->prepare($sql_insert);
    $ok = $stmt->execute([
        ':id_gestor'  => $id_gestor_logado, // usa o gestor da sessão, não valor fixo
        ':nome'       => $nome,
        ':cpf'        => $cpf,
        ':inicio'     => $inicio,
        ':desempenho' => $desempenho,
    ]);

    if ($ok) {
        header("Location: funcionarios.php?sucesso=1");
        exit();
    } else {
        echo "<script>alert('Erro ao cadastrar funcionário.');</script>";
    }
}

// LÓGICA 2: Excluir Funcionário (só permite excluir funcionário do próprio gestor)
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);

    $stmt = $pdo->prepare("DELETE FROM tb_funcionarios WHERE id_funcionario = :id AND id_gestor = :id_gestor");
    $stmt->execute([
        ':id'        => $id_excluir,
        ':id_gestor' => $id_gestor_logado,
    ]);

    header("Location: funcionarios.php?excluido=1");
    exit();
}

// LÓGICA 3: Buscar SOMENTE os funcionários do gestor logado
$stmt = $pdo->prepare("SELECT * FROM tb_funcionarios WHERE id_gestor = :id_gestor ORDER BY id_funcionario DESC");
$stmt->execute([':id_gestor' => $id_gestor_logado]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <?php if ($resultado && count($resultado) > 0): ?>
                    <?php foreach ($resultado as $row): ?>
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
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-msg">Nenhum funcionário cadastrado ainda.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

</body>
</html>

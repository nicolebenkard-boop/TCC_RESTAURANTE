<?php
session_start();
require_once '../conexao.php'; // deve fornecer $pdo (PDO), igual ao login

// Bloqueia acesso de quem não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = $_SESSION['usuario_id'];

// Verifica se recebeu o ID do funcionário pela URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: funcionarios.php");
    exit();
}

$id_funcionario = intval($_GET['id']);

// BUSCA OS DADOS DO FUNCIONÁRIO SELECIONADO (só se pertencer ao gestor logado)
$stmt = $pdo->prepare("SELECT * FROM tb_funcionarios WHERE id_funcionario = :id AND id_gestor = :id_gestor");
$stmt->execute([
    ':id'        => $id_funcionario,
    ':id_gestor' => $id_gestor,
]);
$funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$funcionario) {
    // Não encontrado (ou pertence a outro gestor) -> volta para a listagem
    header("Location: funcionarios.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Detalhes do Funcionário</title>
    <link rel="stylesheet" href="verMais.css">
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
            <a href="funcionarios.php" class="top-link"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
        </div>
    </header>

    <div class="details-container">
        <main class="card-box details-card">
            <div class="card-header">
                <div class="user-avatar">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <h2><?php echo htmlspecialchars($funcionario['nome_completo']); ?></h2>
                <p class="role-badge">Código de Registro: #<?php echo $funcionario['id_funcionario']; ?></p>
            </div>

            <hr class="divider">

            <div class="details-grid">
                <div class="detail-item">
                    <label><i class="fa-solid fa-id-card"></i> CPF do Funcionário</label>
                    <p><?php echo htmlspecialchars($funcionario['CPF_funcionario']); ?></p>
                </div>

                <div class="detail-item">
                    <label><i class="fa-solid fa-calendar-days"></i> Data de Admissão</label>
                    <p><?php echo date('d/m/Y', strtotime($funcionario['inicio_trabalho'])); ?></p>
                </div>

                <div class="detail-item full-width">
                    <label><i class="fa-solid fa-chart-line"></i> Desempenho e Histórico</label>
                    <div class="status-box">
                        <?php echo htmlspecialchars($funcionario['desempenho']); ?>
                    </div>
                </div>
            </div>

            <hr class="divider">

            <div class="card-actions">
                <a href="funcionarios.php" class="btn btn-secondary">Voltar à Lista</a>
                <a href="funcionarios.php?excluir=<?php echo $funcionario['id_funcionario']; ?>"
                    class="btn btn-danger"
                    onclick="return confirm('ATENÇÃO: Tem certeza absoluta que deseja demitir e excluir <?php echo htmlspecialchars($funcionario['nome_completo']); ?> do banco de dados?')">
                    <i class="fa-solid fa-trash-can"></i> Excluir Funcionário
                </a>
            </div>
        </main>
    </div>

</body>
</html>

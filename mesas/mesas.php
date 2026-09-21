<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = $_SESSION['usuario_id'];
$nome_gestor = htmlspecialchars($_SESSION['usuario_nome']);
$erro = null;
$sucesso = null;

// Adicionar mesa
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar_mesa'])) {
    $numero_mesa = intval($_POST['numero_mesa']);

    if ($numero_mesa <= 0) {
        $erro = "Digite um número de mesa válido.";
    } else {
        $stmt = $pdo->prepare("SELECT id_mesa FROM tb_mesas WHERE id_gestor = :id_gestor AND numero_mesa = :numero");
        $stmt->execute([':id_gestor' => $id_gestor, ':numero' => $numero_mesa]);

        if ($stmt->fetch()) {
            $erro = "Já existe uma mesa com esse número.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO tb_mesas (id_gestor, numero_mesa, status) VALUES (:id_gestor, :numero, 'inativa')");
            $stmt->execute([':id_gestor' => $id_gestor, ':numero' => $numero_mesa]);
            $sucesso = "Mesa {$numero_mesa} cadastrada.";
        }
    }
}

// Alternar status manualmente (ex: liberar uma mesa travada por engano)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['alternar_status'])) {
    $id_mesa = intval($_POST['id_mesa']);

    $stmt = $pdo->prepare("SELECT * FROM tb_mesas WHERE id_mesa = :id AND id_gestor = :id_gestor");
    $stmt->execute([':id' => $id_mesa, ':id_gestor' => $id_gestor]);
    $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mesa) {
        $novo_status = $mesa['status'] === 'ativa' ? 'inativa' : 'ativa';
        $stmt = $pdo->prepare("UPDATE tb_mesas SET status = :status WHERE id_mesa = :id AND id_gestor = :id_gestor");
        $stmt->execute([':status' => $novo_status, ':id' => $id_mesa, ':id_gestor' => $id_gestor]);
    }
}

// Excluir mesa
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_mesa'])) {
    $id_mesa = intval($_POST['id_mesa']);
    $stmt = $pdo->prepare("DELETE FROM tb_mesas WHERE id_mesa = :id AND id_gestor = :id_gestor");
    $stmt->execute([':id' => $id_mesa, ':id_gestor' => $id_gestor]);
}

$stmt = $pdo->prepare("SELECT * FROM tb_mesas WHERE id_gestor = :id_gestor ORDER BY numero_mesa ASC");
$stmt->execute([':id_gestor' => $id_gestor]);
$mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Mesas</title>
    <link rel="stylesheet" href="mesas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="bg-overlay"></div>

    <div class="dashboard-wrapper">

        <header class="top-bar">
            <div class="brand">
                <img src="../pag_login/logo.png" alt="RestControl" class="top-logo">
                <span>RestControl</span>
            </div>
            <div class="top-actions">
                <a href="../home/home.php" class="top-link"><i class="fa-solid fa-house"></i> Início</a>
                <a href="../cardapio_digital/qrcode.php" class="top-link"><i class="fa-solid fa-qrcode"></i> Cardápio Digital</a>
                <a href="../pedidos/pedidos.php" class="top-link"><i class="fa-solid fa-receipt"></i> Pedidos</a>
                <a href="../pag_login/index.php" class="top-link logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
            </div>
        </header>

        <main class="main-content">
            <div class="welcome-text">
                <h1>Mesas</h1>
                <p>Cadastre as mesas do seu restaurante e acompanhe quais estão ocupadas.</p>
            </div>

            <div class="painel">

                <form method="POST" class="form-add-mesa">
                    <input type="number" name="numero_mesa" min="1" placeholder="Número da mesa" required>
                    <button type="submit" name="adicionar_mesa" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Adicionar Mesa
                    </button>
                </form>

                <?php if ($erro): ?>
                    <div class="alerta-erro"><?php echo htmlspecialchars($erro); ?></div>
                <?php endif; ?>
                <?php if ($sucesso): ?>
                    <div class="alerta-sucesso"><?php echo htmlspecialchars($sucesso); ?></div>
                <?php endif; ?>

                <?php if (empty($mesas)): ?>
                    <p class="vazio">Nenhuma mesa cadastrada ainda.</p>
                <?php else: ?>
                    <div class="mesas-grid">
                        <?php foreach ($mesas as $mesa): ?>
                            <div class="mesa-card <?php echo $mesa['status'] === 'ativa' ? 'mesa-ativa' : 'mesa-inativa'; ?>">
                                <div class="mesa-numero">Mesa <?php echo htmlspecialchars($mesa['numero_mesa']); ?></div>
                                <div class="mesa-status">
                                    <i class="fa-solid fa-circle"></i>
                                    <?php echo $mesa['status'] === 'ativa' ? 'Ocupada' : 'Livre'; ?>
                                </div>
                                <div class="mesa-acoes">
                                    <form method="POST">
                                        <input type="hidden" name="id_mesa" value="<?php echo $mesa['id_mesa']; ?>">
                                        <button type="submit" name="alternar_status" class="btn-mini">
                                            <?php echo $mesa['status'] === 'ativa' ? 'Marcar como livre' : 'Marcar como ocupada'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Excluir esta mesa?');">
                                        <input type="hidden" name="id_mesa" value="<?php echo $mesa['id_mesa']; ?>">
                                        <button type="submit" name="excluir_mesa" class="btn-mini btn-mini-excluir">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <p class="dica">
                    Quando um cliente escolhe essa mesa pelo cardápio digital, ela fica automaticamente <strong>Ocupada</strong>.
                    Depois que ele pagar (fora do sistema), dê baixa na comanda na tela de <a href="../pedidos/pedidos.php">Pedidos</a> — a mesa volta a ficar <strong>Livre</strong> sozinha.
                </p>
            </div>
        </main>
    </div>

</body>
</html>

<?php
session_start();

if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../conexao.php';

// Logout
if (isset($_GET['sair'])) {
    unset($_SESSION['admin_logado']);
    header("Location: login.php");
    exit;
}

// Ação de Ativar ou Renovar +30 dias de acesso
if (isset($_GET['ativar_id'])) {
    $id = (int)$_GET['ativar_id'];
    
    // Se a data de vencimento for no futuro, adiciona +30 dias a ela.
    // Se for nula ou passada, adiciona +30 dias a partir de HOJE.
    $sql = "UPDATE tb_gestor 
            SET status = 'ativo', 
                data_vencimento = DATE_ADD(
                    IF(data_vencimento IS NOT NULL AND data_vencimento >= CURDATE(), data_vencimento, CURDATE()), 
                    INTERVAL 30 DAY
                ) 
            WHERE id_gestor = :id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    header("Location: gerenciar_pagamentos.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM tb_gestor ORDER BY id_gestor DESC");
$gestores = $stmt->fetchAll(PDO::FETCH_ASSOC);
$hoje = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin - Gerenciador de Assinaturas</title>
    <style>
        body { font-family: Arial, sans-serif; background: #121212; color: #fff; padding: 20px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-sair { background: #dc3545; color: #fff; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; background: #1e1e1e; }
        th, td { border: 1px solid #444; padding: 12px; text-align: left; }
        th { background: #8B0000; }
        .btn-renovar { background: #28a745; color: #fff; text-decoration: none; padding: 8px 12px; border-radius: 4px; font-weight: bold; display: inline-block; }
        .btn-renovar:hover { background: #218838; }
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.85rem; }
        .bg-pendente { background: #ffc107; color: #000; }
        .bg-ativo { background: #28a745; color: #fff; }
        .bg-vencido { background: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <div class="top-bar">
        <h2>Gerenciador de Assinaturas Mensais</h2>
        <a href="?sair=1" class="btn-sair">Sair do Painel</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Nome / Restaurante</th>
            <th>Contatos</th>
            <th>CPF / CNPJ</th>
            <th>Vencimento</th>
            <th>Status Assinatura</th>
            <th>Ação (Pagamento Pix)</th>
        </tr>
        <?php foreach ($gestores as $g): 
            $vencimento = $g['data_vencimento'];
            $esta_vencido = ($vencimento && $vencimento < $hoje);
            $esta_ativo = ($g['status'] === 'ativo' && $vencimento && $vencimento >= $hoje);
        ?>
        <tr>
            <td><?php echo $g['id_gestor']; ?></td>
            <td>
                <strong><?php echo htmlspecialchars($g['nome_gestor']); ?></strong><br>
                <small style="color:#D4AF37;"><?php echo htmlspecialchars($g['restaurante']); ?></small>
            </td>
            <td>
                <?php echo htmlspecialchars($g['e_mail']); ?><br>
                <small><?php echo htmlspecialchars($g['telefone']); ?></small>
            </td>
            <td>
                <small>CPF: <?php echo $g['CPF']; ?><br>CNPJ: <?php echo $g['CNPJ']; ?></small>
            </td>
            <td>
                <?php 
                if ($vencimento) {
                    echo date('d/m/Y', strtotime($vencimento));
                } else {
                    echo "<em>Nunca ativado</em>";
                }
                ?>
            </td>
            <td>
                <?php if ($g['status'] === 'pendente' || empty($vencimento)): ?>
                    <span class="badge bg-pendente">PENDENTE</span>
                <?php elseif ($esta_vencido): ?>
                    <span class="badge bg-vencido">VENCIDO</span>
                <?php else: ?>
                    <span class="badge bg-ativo">ATIVO</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="?ativar_id=<?php echo $g['id_gestor']; ?>" 
                   class="btn-renovar" 
                   onclick="return confirm('Confirmou o recebimento de R$ 10,00 Pix no seu banco?');">
                    <?php echo ($g['status'] === 'pendente' || empty($vencimento)) ? "Ativar (+30 dias)" : "Renovar +30 Dias"; ?>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = $_SESSION['usuario_id'];

// Dar baixa numa comanda (marca como fechada/paga e libera a mesa)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['encerrar_pedido'])) {
    $id_pedido = intval($_POST['id_pedido']);

    $stmt = $pdo->prepare("SELECT * FROM tb_pedidos WHERE id_pedido = :id AND id_gestor = :id_gestor");
    $stmt->execute([':id' => $id_pedido, ':id_gestor' => $id_gestor]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pedido && $pedido['status'] === 'aberto') {
        $pdo->prepare("UPDATE tb_pedidos SET status = 'fechado', data_fechamento = NOW() WHERE id_pedido = :id")
            ->execute([':id' => $id_pedido]);

        if (!empty($pedido['id_mesa'])) {
            $pdo->prepare("UPDATE tb_mesas SET status = 'inativa' WHERE id_mesa = :id_mesa AND id_gestor = :id_gestor")
                ->execute([':id_mesa' => $pedido['id_mesa'], ':id_gestor' => $id_gestor]);
        }
    }

    header("Location: pedidos.php" . (isset($_GET['aba']) ? '?aba=' . urlencode($_GET['aba']) : ''));
    exit();
}

$aba = $_GET['aba'] ?? 'abertas';

// Mesas em aberto (comandas ainda não pagas)
$stmt = $pdo->prepare("
    SELECT p.id_pedido, p.nome_cliente, p.cpf_cliente, p.telefone_cliente, p.data_abertura, p.valor_total, m.numero_mesa
    FROM tb_pedidos p
    LEFT JOIN tb_mesas m ON m.id_mesa = p.id_mesa
    WHERE p.id_gestor = :id_gestor AND p.status = 'aberto'
    ORDER BY p.data_abertura ASC
");
$stmt->execute([':id_gestor' => $id_gestor]);
$mesas_abertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Histórico (comandas já fechadas/pagas), agrupado por dia
$data_filtro = $_GET['data'] ?? null;

$sql_historico = "
    SELECT p.id_pedido, p.nome_cliente, p.cpf_cliente, p.telefone_cliente, p.data_abertura, p.data_fechamento, p.valor_total, m.numero_mesa
    FROM tb_pedidos p
    LEFT JOIN tb_mesas m ON m.id_mesa = p.id_mesa
    WHERE p.id_gestor = :id_gestor AND p.status = 'fechado'
";
$params_historico = [':id_gestor' => $id_gestor];

if (!empty($data_filtro)) {
    $sql_historico .= " AND DATE(p.data_fechamento) = :data ";
    $params_historico[':data'] = $data_filtro;
}

$sql_historico .= " ORDER BY p.data_fechamento DESC";

$stmt = $pdo->prepare($sql_historico);
$stmt->execute($params_historico);
$historico_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupa por dia (Y-m-d) para exibir como um "extrato" diário
$historico_por_dia = [];
$total_geral = 0;
foreach ($historico_raw as $h) {
    $dia = $h['data_fechamento'] ? date('Y-m-d', strtotime($h['data_fechamento'])) : 'Sem data';
    $historico_por_dia[$dia][] = $h;
    $total_geral += (float) $h['valor_total'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Pedidos</title>
    <link rel="stylesheet" href="pedidos.css">
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
                <a href="../mesas/mesas.php" class="top-link"><i class="fa-solid fa-chair"></i> Mesas</a>
                <a href="../cardapio_digital/qrcode.php" class="top-link"><i class="fa-solid fa-qrcode"></i> Cardápio Digital</a>
                <a href="../pag_login/index.php" class="top-link logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
            </div>
        </header>

        <main class="main-content">
            <div class="welcome-text">
                <h1>Pedidos</h1>
                <p>Acompanhe as mesas em aberto e o histórico do que já foi pago.</p>
            </div>

            <div class="painel">

                <div class="tabs">
                    <a href="?aba=abertas" class="tab-btn <?php echo $aba === 'abertas' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-clock"></i> Mesas em Aberto (<?php echo count($mesas_abertas); ?>)
                    </a>
                    <a href="?aba=historico" class="tab-btn <?php echo $aba === 'historico' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-clock-rotate-left"></i> Histórico
                    </a>
                </div>

                <?php if ($aba === 'abertas'): ?>

                    <?php if (empty($mesas_abertas)): ?>
                        <p class="vazio">Nenhuma mesa em aberto no momento.</p>
                    <?php else: ?>
                        <div class="lista-comandas">
                            <?php foreach ($mesas_abertas as $m): ?>
                                <div class="comanda-card">
                                    <div class="comanda-info">
                                        <div class="comanda-mesa">Mesa <?php echo htmlspecialchars($m['numero_mesa'] ?? '-'); ?></div>
                                        <div class="comanda-cliente">
                                            <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($m['nome_cliente'] ?? 'Cliente'); ?>
                                        </div>
                                        <div class="comanda-contato">
                                            <i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($m['telefone_cliente'] ?? '-'); ?>
                                        </div>
                                        <div class="comanda-hora">
                                            Aberta em <?php echo $m['data_abertura'] ? date('d/m/Y H:i', strtotime($m['data_abertura'])) : '-'; ?>
                                        </div>
                                    </div>
                                    <div class="comanda-valor">
                                        R$ <?php echo number_format($m['valor_total'], 2, ',', '.'); ?>
                                    </div>
                                    <div class="comanda-acoes">
                                        <a href="ver_comanda.php?id=<?php echo $m['id_pedido']; ?>" class="btn-mini">Ver itens</a>
                                        <form method="POST" onsubmit="return confirm('Confirma que a mesa já pagou e pode ser encerrada?');">
                                            <input type="hidden" name="id_pedido" value="<?php echo $m['id_pedido']; ?>">
                                            <button type="submit" name="encerrar_pedido" class="btn-mini btn-mini-ok">
                                                <i class="fa-solid fa-check"></i> Dar baixa
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>

                    <form method="GET" class="form-filtro">
                        <input type="hidden" name="aba" value="historico">
                        <label for="data">Filtrar por data:</label>
                        <input type="date" name="data" id="data" value="<?php echo htmlspecialchars($data_filtro ?? ''); ?>">
                        <button type="submit" class="btn-mini">Filtrar</button>
                        <?php if (!empty($data_filtro)): ?>
                            <a href="?aba=historico" class="btn-mini">Limpar</a>
                        <?php endif; ?>
                    </form>

                    <?php if (empty($historico_por_dia)): ?>
                        <p class="vazio">Nenhuma comanda paga encontrada.</p>
                    <?php else: ?>
                        <?php foreach ($historico_por_dia as $dia => $itens): ?>
                            <?php $total_dia = array_sum(array_column($itens, 'valor_total')); ?>
                            <div class="dia-bloco">
                                <div class="dia-cabecalho">
                                    <span><?php echo $dia !== 'Sem data' ? date('d/m/Y', strtotime($dia)) : 'Sem data'; ?></span>
                                    <span>Total do dia: R$ <?php echo number_format($total_dia, 2, ',', '.'); ?></span>
                                </div>
                                <div class="lista-comandas">
                                    <?php foreach ($itens as $h): ?>
                                        <div class="comanda-card comanda-fechada">
                                            <div class="comanda-info">
                                                <div class="comanda-mesa">Mesa <?php echo htmlspecialchars($h['numero_mesa'] ?? '-'); ?></div>
                                                <div class="comanda-cliente">
                                                    <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($h['nome_cliente'] ?? 'Cliente'); ?>
                                                </div>
                                                <div class="comanda-contato">
                                                    <i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($h['telefone_cliente'] ?? '-'); ?>
                                                </div>
                                                <div class="comanda-hora">
                                                    Pago em <?php echo $h['data_fechamento'] ? date('d/m/Y H:i', strtotime($h['data_fechamento'])) : '-'; ?>
                                                </div>
                                            </div>
                                            <div class="comanda-valor">
                                                R$ <?php echo number_format($h['valor_total'], 2, ',', '.'); ?>
                                            </div>
                                            <div class="comanda-acoes">
                                                <a href="ver_comanda.php?id=<?php echo $h['id_pedido']; ?>" class="btn-mini">Ver itens</a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="total-geral">
                            Total no período: <strong>R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></strong>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </main>
    </div>

</body>
</html>

<?php
session_start();
require_once '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$token = $_POST['g'] ?? '';

$stmt = $pdo->prepare("SELECT id_gestor, restaurante FROM tb_gestor WHERE token_cardapio = :token");
$stmt->execute([':token' => $token]);
$gestor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gestor) {
    header("Location: index.php?g=" . urlencode($token));
    exit();
}

$id_gestor = $gestor['id_gestor'];

$nome      = trim($_POST['nome'] ?? '');
$numero_mesa = intval($_POST['mesa'] ?? 0);
$cpf       = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
$telefone  = trim($_POST['telefone'] ?? '');

if (empty($nome) || $numero_mesa <= 0 || strlen($cpf) !== 11 || empty($telefone)) {
    $_SESSION['erro_entrada'] = 'Preencha todos os campos corretamente.';
    header("Location: index.php?g=" . urlencode($token));
    exit();
}

// A mesa precisa existir (cadastrada pelo gestor)
$stmt = $pdo->prepare("SELECT * FROM tb_mesas WHERE id_gestor = :id_gestor AND numero_mesa = :numero");
$stmt->execute([':id_gestor' => $id_gestor, ':numero' => $numero_mesa]);
$mesa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mesa) {
    $_SESSION['erro_entrada'] = "Mesa {$numero_mesa} não encontrada. Confira o número ou chame o garçom.";
    header("Location: index.php?g=" . urlencode($token));
    exit();
}

if ($mesa['status'] === 'ativa') {
    // Mesa já está em atendimento: entra na MESMA comanda aberta,
    // para que todos os pedidos da mesa caiam na mesma conta.
    $stmt = $pdo->prepare("SELECT id_pedido FROM tb_pedidos WHERE id_mesa = :id_mesa AND id_gestor = :id_gestor AND status = 'aberto' ORDER BY id_pedido DESC LIMIT 1");
    $stmt->execute([':id_mesa' => $mesa['id_mesa'], ':id_gestor' => $id_gestor]);
    $comanda = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($comanda) {
        $id_pedido = $comanda['id_pedido'];
    } else {
        // Estado inconsistente (mesa marcada ativa sem comanda aberta): cria uma nova
        $id_pedido = null;
    }
} else {
    $id_pedido = null;
}

if (empty($id_pedido)) {
    $stmt = $pdo->prepare("
        INSERT INTO tb_pedidos (id_gestor, id_mesa, nome_cliente, cpf_cliente, telefone_cliente, valor_total, status, data_abertura)
        VALUES (:id_gestor, :id_mesa, :nome, :cpf, :telefone, 0, 'aberto', NOW())
    ");
    $stmt->execute([
        ':id_gestor' => $id_gestor,
        ':id_mesa'   => $mesa['id_mesa'],
        ':nome'      => $nome,
        ':cpf'       => $cpf,
        ':telefone'  => $telefone,
    ]);
    $id_pedido = $pdo->lastInsertId();

    $pdo->prepare("UPDATE tb_mesas SET status = 'ativa' WHERE id_mesa = :id_mesa")
        ->execute([':id_mesa' => $mesa['id_mesa']]);
}

// Sessão do cliente (substitui login: identifica o dispositivo enquanto ele navega)
$_SESSION['cliente_pedido_id']   = $id_pedido;
$_SESSION['cliente_gestor_id']   = $id_gestor;
$_SESSION['cliente_nome']        = $nome;
$_SESSION['cliente_mesa_numero'] = $numero_mesa;
$_SESSION['cliente_token']       = $token;

if (!isset($_SESSION['cliente_carrinho'])) {
    $_SESSION['cliente_carrinho'] = [];
}

header("Location: cardapio.php");
exit();

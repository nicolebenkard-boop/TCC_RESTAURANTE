<?php
// Autenticação centralizada do funcionário.
// Além de validar o login, verifica se a assinatura do gestor responsável está ativa.
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['funcionario_id'])) {
    header('Location: ../pag_login/index.php');
    exit();
}

$id_funcionario = (int) $_SESSION['funcionario_id'];

$sql = "SELECT f.*, g.nome_gestor, g.e_mail AS email_gestor, g.restaurante,
               g.status AS status_assinatura, g.data_vencimento
        FROM tb_funcionarios f
        INNER JOIN tb_gestor g ON g.id_gestor = f.id_gestor
        WHERE f.id_funcionario = :id
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_funcionario]);
$funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$funcionario) {
    session_unset();
    session_destroy();
    header('Location: ../pag_login/index.php?erro=funcionario_nao_encontrado');
    exit();
}

$hoje = date('Y-m-d');
$assinatura_ativa = ($funcionario['status_assinatura'] === 'ativo'
    && !empty($funcionario['data_vencimento'])
    && $funcionario['data_vencimento'] >= $hoje);

if (!$assinatura_ativa) {
    $vencimento = !empty($funcionario['data_vencimento'])
        ? date('d/m/Y', strtotime($funcionario['data_vencimento']))
        : 'não definida';

    session_unset();
    session_destroy();

    header('Location: ../pag_login/index.php?assinatura_bloqueada=1&vencimento=' . urlencode($vencimento));
    exit();
}

// Mantém os dados principais atualizados na sessão.
$_SESSION['funcionario_nome'] = $funcionario['nome_completo'];
$_SESSION['funcionario_gestor_id'] = $funcionario['id_gestor'];
$_SESSION['funcionario_primeiro_login'] = empty($funcionario['senha']);

$nome_funcionario = htmlspecialchars($funcionario['nome_completo'], ENT_QUOTES, 'UTF-8');
$cpf_formatado = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', preg_replace('/\D/', '', $funcionario['CPF_funcionario']));
$nome_gestor = htmlspecialchars($funcionario['nome_gestor'], ENT_QUOTES, 'UTF-8');
$email_gestor = htmlspecialchars($funcionario['email_gestor'], ENT_QUOTES, 'UTF-8');
$restaurante = htmlspecialchars($funcionario['restaurante'], ENT_QUOTES, 'UTF-8');
$data_vencimento_formatada = date('d/m/Y', strtotime($funcionario['data_vencimento']));
?>

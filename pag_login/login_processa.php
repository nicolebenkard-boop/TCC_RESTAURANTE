<?php
session_start();
require_once '../conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // A decisão de qual tipo de login usar vem do checkbox "Sou funcionário".
    // Checkbox não marcado nem aparece no $_POST, então isso funciona mesmo sem JavaScript.
    $is_funcionario = isset($_POST['funcionario']);

    $identificador = trim($_POST['identificador'] ?? '');
    $senha         = $_POST['password'] ?? '';

    if (empty($identificador) || empty($senha)) {
        echo "<script>alert('Preencha todos os campos!'); window.location.href='index.php';</script>";
        exit;
    }

    // ==========================================================
    // LOGIN DO FUNCIONÁRIO (CPF + senha provisória/definitiva)
    // ==========================================================
    if ($is_funcionario) {

        // Mantém só os dígitos do CPF, independente de o usuário digitar com pontuação ou não
        $cpf_limpo = preg_replace('/\D/', '', $identificador);

        if (strlen($cpf_limpo) !== 11) {
            echo "<script>alert('Digite um CPF válido.'); window.location.href='index.php';</script>";
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM tb_funcionarios WHERE CPF_funcionario = :cpf");
        $stmt->execute([':cpf' => $cpf_limpo]);
        $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$funcionario) {
            echo "<script>alert('CPF ou senha incorretos.'); window.location.href='index.php';</script>";
            exit;
        }

        // O funcionário só pode entrar se o gestor responsável estiver com a assinatura ativa.
        $stmt_gestor = $pdo->prepare("SELECT status, data_vencimento, nome_gestor FROM tb_gestor WHERE id_gestor = :id_gestor LIMIT 1");
        $stmt_gestor->execute([':id_gestor' => $funcionario['id_gestor']]);
        $gestor = $stmt_gestor->fetch(PDO::FETCH_ASSOC);

        $hoje = date('Y-m-d');
        $assinatura_ativa = $gestor
            && $gestor['status'] === 'ativo'
            && !empty($gestor['data_vencimento'])
            && $gestor['data_vencimento'] >= $hoje;

        if (!$assinatura_ativa) {
            echo "<script>alert('Seu acesso está bloqueado porque a assinatura do gestor responsável está inativa ou vencida. Peça ao gestor para regularizar a assinatura.'); window.location.href='index.php';</script>";
            exit;
        }

        $senha_cadastrada = $funcionario['senha']; // NULL = ainda não trocou a senha provisória
        $primeiro_login   = empty($senha_cadastrada);

        $senha_confere = $primeiro_login
            ? ($senha === 'senha123')                       // primeiro acesso: compara com a senha padrão
            : password_verify($senha, $senha_cadastrada);   // acessos seguintes: compara com o hash salvo

        if (!$senha_confere) {
            echo "<script>alert('CPF ou senha incorretos.'); window.location.href='index.php';</script>";
            exit;
        }

        // Login liberado
        $_SESSION['funcionario_id']            = $funcionario['id_funcionario'];
        $_SESSION['funcionario_nome']           = $funcionario['nome_completo'];
        $_SESSION['funcionario_gestor_id']      = $funcionario['id_gestor'];
        $_SESSION['funcionario_primeiro_login'] = $primeiro_login;

        if ($primeiro_login) {
            header("Location: ../tela_funcionario/trocar_senha.php");
        } else {
            header("Location: ../tela_funcionario/index.php");
        }
        exit;
    }

    // ==========================================================
    // LOGIN DO GESTOR (mesma lógica de sempre, inalterada)
    // ==========================================================
    $email = $identificador; // sem trim extra: já veio trim() acima

    $stmt = $pdo->prepare("SELECT * FROM tb_gestor WHERE e_mail = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {

        $hoje = date('Y-m-d');
        $vencimento = $usuario['data_vencimento'];

        // 1. Caso a conta nunca tenha sido ativada
        if ($usuario['status'] === 'pendente' || empty($vencimento)) {
            echo "<script>alert('Sua conta ainda não foi ativada. Faça o Pix e aguarde a liberação.'); window.location.href='../assinatura/assinatura.php';</script>";
            exit;
        }

        // 2. Caso a assinatura de 1 mês tenha vencido
        if ($vencimento < $hoje) {
            $vencFmt = date('d/m/Y', strtotime($vencimento));
            echo "<script>alert('Sua assinatura mensal venceu em {$vencFmt}. Faça o pagamento do mês para renovar o acesso ao sistema.'); window.location.href='../assinatura/assinatura.php';</script>";
            exit;
        }

        // 3. Acesso liberado (Plano Ativo e dentro do prazo)
        $_SESSION['usuario_id'] = $usuario['id_gestor'];
        $_SESSION['usuario_nome'] = $usuario['nome_gestor'];
        header("Location: ../home/home.php");
        exit;

    } else {
        echo "<script>alert('E-mail ou senha incorretos.'); window.location.href='index.php';</script>";
        exit;
    }
}
?>

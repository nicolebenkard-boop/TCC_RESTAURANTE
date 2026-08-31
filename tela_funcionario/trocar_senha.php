<?php
require_once 'auth_funcionario.php';

$id_funcionario = $_SESSION['funcionario_id'];
$primeiro_login = !empty($_SESSION['funcionario_primeiro_login']);
$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $senha_atual   = $_POST['senha_atual'] ?? '';
    $nova_senha    = $_POST['nova_senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';

    // Confirma a senha atual (provisória "senha123" no primeiro acesso, ou a já cadastrada depois)
    $stmt = $pdo->prepare("SELECT senha FROM tb_funcionarios WHERE id_funcionario = :id");
    $stmt->execute([':id' => $id_funcionario]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    $senha_confere = empty($registro['senha'])
        ? ($senha_atual === 'senha123')
        : password_verify($senha_atual, $registro['senha']);

    if (!$senha_confere) {
        $erro = 'A senha atual informada está incorreta.';
    } elseif (strlen($nova_senha) < 6) {
        $erro = 'A nova senha deve ter pelo menos 6 caracteres.';
    } elseif ($nova_senha !== $confirma_senha) {
        $erro = 'A confirmação não coincide com a nova senha.';
    } elseif ($nova_senha === 'senha123') {
        $erro = 'Escolha uma senha diferente da senha provisória.';
    } else {
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);

        $update = $pdo->prepare("UPDATE tb_funcionarios SET senha = :senha WHERE id_funcionario = :id");
        $update->execute([':senha' => $hash, ':id' => $id_funcionario]);

        $_SESSION['funcionario_primeiro_login'] = false;

        header("Location: index.php?senha_alterada=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Trocar Senha</title>
    <link rel="stylesheet" href="funcionario.css">
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
                <?php if (!$primeiro_login): ?>
                    <a href="index.php" class="top-link"><i class="fa-solid fa-house"></i> Voltar</a>
                <?php endif; ?>
                <a href="logout.php" class="top-link logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
            </div>
        </header>

        <main class="main-content">
            <div class="senha-card">
                <div class="icon-box-grande"><i class="fa-solid fa-key"></i></div>

                <?php if ($primeiro_login): ?>
                    <h1>Primeiro acesso</h1>
                    <p class="subtitle">Por segurança, cadastre uma senha definitiva antes de continuar.</p>
                <?php else: ?>
                    <h1>Trocar Senha</h1>
                    <p class="subtitle">Informe sua senha atual e escolha uma nova senha.</p>
                <?php endif; ?>

                <?php if ($erro): ?>
                    <div class="alerta-erro"><?php echo htmlspecialchars($erro); ?></div>
                <?php endif; ?>

                <form action="trocar_senha.php" method="POST" id="formTrocarSenha">
                    <div class="input-group">
                        <label for="senha_atual"><?php echo $primeiro_login ? 'Senha provisória' : 'Senha atual'; ?></label>
                        <div class="input-field">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="senha_atual" name="senha_atual" placeholder="<?php echo $primeiro_login ? 'senha123' : 'Sua senha atual'; ?>" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="nova_senha">Nova senha</label>
                        <div class="input-field">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="nova_senha" name="nova_senha" placeholder="Mínimo de 6 caracteres" required minlength="6">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="confirma_senha">Confirmar nova senha</label>
                        <div class="input-field">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="confirma_senha" name="confirma_senha" placeholder="Repita a nova senha" required minlength="6">
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Salvar Nova Senha</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>

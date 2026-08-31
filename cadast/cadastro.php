<?php
require_once '../conexao.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_gestor = trim($_POST['nome_gestor']);
    $cpf          = trim($_POST['cpf']);
    $cnpj         = trim($_POST['cnpj']);
    $restaurante  = trim($_POST['restaurante']);
    $e_mail       = trim($_POST['e_mail']);
    $telefone     = trim($_POST['telefone']);
    $senha_hash   = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO tb_gestor (nome_gestor, CPF, CNPJ, restaurante, e_mail, telefone, senha, status) 
                VALUES (:nome, :cpf, :cnpj, :restaurante, :email, :telefone, :senha, 'pendente')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome_gestor);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':cnpj', $cnpj);
        $stmt->bindParam(':restaurante', $restaurante);
        $stmt->bindParam(':email', $e_mail);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':senha', $senha_hash);

        if ($stmt->execute()) {
            $mensagem = "<div class='alert-success'>Cadastro realizado! Seu acesso será liberado assim que o pagamento Pix for confirmado.</div>";
        }
    } catch (PDOException $e) {
        $mensagem = "<div class='error-message' style='display:block;'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>RESTCONTROL - Cadastro</title>
    <link rel="stylesheet" href="cadastro.css">
</head>
<body>
    <main class="main-container">
        <div class="card-cadastro">
            <h2>Criar Conta de Gestor</h2>
            <?php echo $mensagem; ?>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Nome Completo:</label>
                        <input type="text" name="nome_gestor" required>
                    </div>
                    <div class="form-group">
                        <label>CPF:</label>
                        <input type="text" name="cpf" required>
                    </div>
                    <div class="form-group">
                        <label>CNPJ:</label>
                        <input type="text" name="cnpj" required>
                    </div>
                    <div class="form-group">
                        <label>Nome do Restaurante:</label>
                        <input type="text" name="restaurante" required>
                    </div>
                    <div class="form-group">
                        <label>Telefone / WhatsApp:</label>
                        <input type="text" name="telefone" required>
                    </div>
                    <div class="form-group">
                        <label>E-mail:</label>
                        <input type="email" name="e_mail" required>
                    </div>
                    <div class="form-group">
                        <label>Senha:</label>
                        <input type="password" name="senha" required>
                    </div>
                </div>
                <button type="submit" class="btn-cadastrar" style="margin-top:20px;">Finalizar Cadastro</button>
                <br><br>
                <a href="../abertura/index.php" class="btn-cadastrar">Voltar</a>
            </form>
        </div>
    </main>
</body>
</html>
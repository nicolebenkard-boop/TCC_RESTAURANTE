<?php
session_start();
require_once '../conexao.php'; // deve fornecer $pdo (PDO), igual ao login

// Bloqueia acesso de quem não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = $_SESSION['usuario_id'];

$UPLOAD_DIR = __DIR__ . '/uploads/';
$UPLOAD_URL = 'uploads/';
$EXTENSOES_PERMITIDAS = ['jpg', 'jpeg', 'png', 'webp'];
$TAMANHO_MAXIMO = 5 * 1024 * 1024; // 5MB

$erro = null;

/**
 * Trata o upload de imagem enviado no campo "imagem".
 * Retorna o nome do arquivo salvo, ou null se nenhum arquivo válido foi enviado.
 */
function processarUploadImagem($UPLOAD_DIR, $EXTENSOES_PERMITIDAS, $TAMANHO_MAXIMO, &$erro) {
    if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // Nenhuma imagem enviada, tudo bem (é opcional)
    }

    if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
        $erro = "Erro ao enviar a imagem.";
        return null;
    }

    if ($_FILES['imagem']['size'] > $TAMANHO_MAXIMO) {
        $erro = "A imagem deve ter no máximo 5MB.";
        return null;
    }

    $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, $EXTENSOES_PERMITIDAS)) {
        $erro = "Formato de imagem inválido. Use JPG, PNG ou WEBP.";
        return null;
    }

    // Confere se o arquivo é realmente uma imagem (não confia só na extensão)
    $info = @getimagesize($_FILES['imagem']['tmp_name']);
    if ($info === false) {
        $erro = "O arquivo enviado não é uma imagem válida.";
        return null;
    }

    $nome_arquivo = 'prato_' . uniqid() . '_' . time() . '.' . $extensao;
    if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $UPLOAD_DIR . $nome_arquivo)) {
        $erro = "Não foi possível salvar a imagem no servidor.";
        return null;
    }

    return $nome_arquivo;
}

// LÓGICA 1: Cadastrar ou Editar Prato
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['salvar_prato'])) {
    $nome_prato  = trim($_POST['nome_prato']);
    $preco_venda = floatval(str_replace(',', '.', $_POST['preco_venda']));
    $id_prato_edicao = isset($_POST['id_prato']) && $_POST['id_prato'] !== '' ? intval($_POST['id_prato']) : 0;

    $nome_imagem = processarUploadImagem($UPLOAD_DIR, $EXTENSOES_PERMITIDAS, $TAMANHO_MAXIMO, $erro);

    if ($erro === null) {
        if ($id_prato_edicao > 0) {
            // EDIÇÃO — confirma que o prato pertence ao gestor logado antes de alterar
            $stmt = $pdo->prepare("SELECT imagem FROM tb_pratos WHERE id_prato = :id AND id_gestor = :id_gestor");
            $stmt->execute([':id' => $id_prato_edicao, ':id_gestor' => $id_gestor]);
            $prato_atual = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($prato_atual) {
                if ($nome_imagem !== null) {
                    // Enviou imagem nova: apaga a antiga do disco (se existir)
                    if (!empty($prato_atual['imagem']) && file_exists($UPLOAD_DIR . $prato_atual['imagem'])) {
                        @unlink($UPLOAD_DIR . $prato_atual['imagem']);
                    }
                    $stmt = $pdo->prepare("UPDATE tb_pratos SET nome_prato = :nome, preco_venda = :preco, imagem = :imagem WHERE id_prato = :id AND id_gestor = :id_gestor");
                    $stmt->execute([
                        ':nome'      => $nome_prato,
                        ':preco'     => $preco_venda,
                        ':imagem'    => $nome_imagem,
                        ':id'        => $id_prato_edicao,
                        ':id_gestor' => $id_gestor,
                    ]);
                } else {
                    // Sem imagem nova: mantém a imagem que já estava salva
                    $stmt = $pdo->prepare("UPDATE tb_pratos SET nome_prato = :nome, preco_venda = :preco WHERE id_prato = :id AND id_gestor = :id_gestor");
                    $stmt->execute([
                        ':nome'      => $nome_prato,
                        ':preco'     => $preco_venda,
                        ':id'        => $id_prato_edicao,
                        ':id_gestor' => $id_gestor,
                    ]);
                }
            }
        } else {
            // CADASTRO NOVO
            $stmt = $pdo->prepare("INSERT INTO tb_pratos (id_gestor, nome_prato, imagem, preco_venda) VALUES (:id_gestor, :nome, :imagem, :preco)");
            $stmt->execute([
                ':id_gestor' => $id_gestor,
                ':nome'      => $nome_prato,
                ':imagem'    => $nome_imagem,
                ':preco'     => $preco_venda,
            ]);
        }

        header("Location: pratos.php");
        exit();
    }
}

// LÓGICA 2: Excluir Prato (só do próprio gestor, e apaga a imagem do disco)
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);

    $stmt = $pdo->prepare("SELECT imagem FROM tb_pratos WHERE id_prato = :id AND id_gestor = :id_gestor");
    $stmt->execute([':id' => $id_excluir, ':id_gestor' => $id_gestor]);
    $prato = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($prato) {
        if (!empty($prato['imagem']) && file_exists($UPLOAD_DIR . $prato['imagem'])) {
            @unlink($UPLOAD_DIR . $prato['imagem']);
        }

        // Limpa itens de ficha técnica se existir essa tabela
        try {
            $stmt = $pdo->prepare("DELETE FROM tb_item_ficha_tecnica WHERE id_prato = :id");
            $stmt->execute([':id' => $id_excluir]);
        } catch (Exception $e) {
            // Caso a tabela não exista ainda, ignora o erro
        }

        $stmt = $pdo->prepare("DELETE FROM tb_pratos WHERE id_prato = :id AND id_gestor = :id_gestor");
        $stmt->execute([':id' => $id_excluir, ':id_gestor' => $id_gestor]);
    }

    header("Location: pratos.php");
    exit();
}

// LÓGICA 3: Buscar Pratos Ativos do Gestor Logado
$stmt = $pdo->prepare("SELECT id_prato, nome_prato, imagem, preco_venda FROM tb_pratos WHERE id_gestor = :id_gestor ORDER BY id_prato DESC");
$stmt->execute([':id_gestor' => $id_gestor]);
$pratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Gestão de Pratos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="pratos.css">
</head>
<body>

    <div class="bg-overlay"></div>

    <header class="top-bar">
        <div class="brand">
            <span>RestControl</span>
        </div>
        <a href="../home/home.php" class="top-link"><i class="fa-solid fa-arrow-left"></i> Voltar ao painel</a>
    </header>

    <div class="container-dashboard">
        <section class="card-box header-card">
            <div class="header-section">
                <h2><i class="fa-solid fa-utensils"></i> Gestão do Cardápio</h2>
                <p>Adicione os pratos e refeições prontas do seu restaurante, com foto e preço de venda.</p>
            </div>
            <button class="btn btn-primary" id="openModalBtn"><i class="fa-solid fa-plus"></i> Novo Prato</button>
        </section>

        <section class="pratos-grid">
            <?php if ($pratos && count($pratos) > 0): ?>
                <?php foreach ($pratos as $prato): ?>
                    <div class="prato-card">
                        <div class="prato-imagem">
                            <?php if (!empty($prato['imagem'])): ?>
                                <img src="<?php echo $UPLOAD_URL . htmlspecialchars($prato['imagem']); ?>" alt="<?php echo htmlspecialchars($prato['nome_prato']); ?>">
                            <?php else: ?>
                                <div class="sem-imagem"><i class="fa-solid fa-bowl-food"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="prato-info">
                            <h3><?php echo htmlspecialchars($prato['nome_prato']); ?></h3>
                            <p class="prato-preco">R$ <?php echo number_format($prato['preco_venda'], 2, ',', '.'); ?></p>
                        </div>
                        <div class="prato-acoes">
                            <button type="button"
                                class="btn btn-secondary btn-editar"
                                data-id="<?php echo $prato['id_prato']; ?>"
                                data-nome="<?php echo htmlspecialchars($prato['nome_prato'], ENT_QUOTES); ?>"
                                data-preco="<?php echo htmlspecialchars($prato['preco_venda'], ENT_QUOTES); ?>">
                                <i class="fa-solid fa-pen"></i> Editar
                            </button>
                            <a href="pratos.php?excluir=<?php echo $prato['id_prato']; ?>"
                               class="btn btn-danger"
                               onclick="return confirm('Excluir o prato \'<?php echo htmlspecialchars($prato['nome_prato'], ENT_QUOTES); ?>\'?')">
                                <i class="fa-solid fa-trash-can"></i> Excluir
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-msg">Nenhum prato cadastrado ainda. Clique em "Novo Prato" para começar.</p>
            <?php endif; ?>
        </section>
    </div>

    <div class="modal" id="pratoModal">
        <div class="modal-content">
            <span class="close-btn" id="closeModalBtn">&times;</span>
            <h2 id="modalTitulo">Cadastrar Prato</h2>
            <?php if ($erro): ?>
                <p class="erro-msg"><?php echo htmlspecialchars($erro); ?></p>
            <?php endif; ?>
            <form action="pratos.php" method="POST" enctype="multipart/form-data" id="formPrato">
                <input type="hidden" name="id_prato" id="id_prato" value="">

                <div class="input-group">
                    <label for="nome_prato">Nome do Prato</label>
                    <input type="text" id="nome_prato" name="nome_prato" placeholder="Ex: Risoto de Alho Poró" required>
                </div>
                <div class="input-group">
                    <label for="preco_venda">Preço de Venda (R$)</label>
                    <input type="text" id="preco_venda" name="preco_venda" placeholder="Ex: 49,90" required>
                </div>
                <div class="input-group">
                    <label for="imagem">Foto do Prato</label>
                    <input type="file" id="imagem" name="imagem" accept=".jpg,.jpeg,.png,.webp">
                    <img id="previewImagem" class="preview-imagem" style="display:none;" alt="Pré-visualização">
                    <small id="dicaEdicao" class="dica-campo" style="display:none;">Deixe em branco para manter a foto atual.</small>
                </div>
                <div class="button-container">
                    <button type="submit" name="salvar_prato" class="btn btn-primary">Salvar Prato</button>
                </div>
            </form>
        </div>
    </div>

    <script src="pratos.js"></script>
</body>
</html>
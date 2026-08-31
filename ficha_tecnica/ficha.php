<?php
session_start();
require_once '../conexao.php'; // deve fornecer $pdo (PDO), igual ao login

// Bloqueia acesso de quem não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = $_SESSION['usuario_id'];
$UPLOAD_URL = '../pratos/uploads/';

$id_prato = isset($_GET['id_prato']) ? intval($_GET['id_prato']) : null;
$erro = null;

// ================== MODO DETALHE (um prato específico) ==================
if ($id_prato !== null) {

    // Confirma que o prato pertence ao gestor logado
    $stmt = $pdo->prepare("SELECT * FROM tb_pratos WHERE id_prato = :id AND id_gestor = :id_gestor");
    $stmt->execute([':id' => $id_prato, ':id_gestor' => $id_gestor]);
    $prato = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prato) {
        header("Location: ficha.php");
        exit();
    }

    // AÇÃO: Salvar o modo de preparo (receita)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_modo_preparo'])) {
        $modo_preparo = trim($_POST['modo_preparo']);
        $stmt = $pdo->prepare("UPDATE tb_pratos SET modo_preparo = :modo WHERE id_prato = :id AND id_gestor = :id_gestor");
        $stmt->execute([':modo' => $modo_preparo, ':id' => $id_prato, ':id_gestor' => $id_gestor]);
        header("Location: ficha.php?id_prato=" . $id_prato);
        exit();
    }

    // AÇÃO: Adicionar ingrediente à composição
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_item'])) {
        $id_ingrediente = intval($_POST['id_ingrediente']);
        $quantidade     = floatval(str_replace(',', '.', $_POST['quantidade_necessaria']));
        $unidade        = trim($_POST['unidade']);

        // Confirma que o ingrediente também pertence ao gestor logado
        $stmt = $pdo->prepare("SELECT id_ingrediente FROM tb_estoque WHERE id_ingrediente = :id AND id_gestor = :id_gestor");
        $stmt->execute([':id' => $id_ingrediente, ':id_gestor' => $id_gestor]);

        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO tb_item_ficha_tecnica (id_prato, id_ingrediente, quantidade_necessaria, unidade) VALUES (:id_prato, :id_ingrediente, :qtd, :unidade)");
            $stmt->execute([
                ':id_prato'       => $id_prato,
                ':id_ingrediente' => $id_ingrediente,
                ':qtd'            => $quantidade,
                ':unidade'        => $unidade,
            ]);
        } else {
            $erro = "Ingrediente inválido.";
        }

        if (!$erro) {
            header("Location: ficha.php?id_prato=" . $id_prato);
            exit();
        }
    }

    // AÇÃO: Remover ingrediente da composição
    if (isset($_GET['remover_item'])) {
        $id_item = intval($_GET['remover_item']);
        $stmt = $pdo->prepare("DELETE FROM tb_item_ficha_tecnica WHERE id_item_ficha = :id AND id_prato = :id_prato");
        $stmt->execute([':id' => $id_item, ':id_prato' => $id_prato]);
        header("Location: ficha.php?id_prato=" . $id_prato);
        exit();
    }

    // Busca a composição atual do prato (ingredientes + quantidade)
    $stmt = $pdo->prepare("SELECT f.id_item_ficha, f.quantidade_necessaria, f.unidade, e.nome_ingredientes
                            FROM tb_item_ficha_tecnica f
                            JOIN tb_estoque e ON e.id_ingrediente = f.id_ingrediente
                            WHERE f.id_prato = :id_prato
                            ORDER BY e.nome_ingredientes ASC");
    $stmt->execute([':id_prato' => $id_prato]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Busca os ingredientes disponíveis no estoque do gestor (para o formulário)
    $stmt = $pdo->prepare("SELECT id_ingrediente, nome_ingredientes FROM tb_estoque WHERE id_gestor = :id_gestor ORDER BY nome_ingredientes ASC");
    $stmt->execute([':id_gestor' => $id_gestor]);
    $ingredientes_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Ficha Técnica: <?php echo htmlspecialchars($prato['nome_prato']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="ficha.css">
</head>
<body>

    <div class="bg-overlay"></div>

    <header class="top-bar">
        <div class="brand"><span>RestControl</span></div>
        <a href="ficha.php" class="top-link"><i class="fa-solid fa-arrow-left"></i> Voltar à Lista</a>
    </header>

    <div class="container-detalhe">

        <section class="card-box prato-resumo">
            <div class="prato-resumo-imagem">
                <?php if (!empty($prato['imagem'])): ?>
                    <img src="<?php echo $UPLOAD_URL . htmlspecialchars($prato['imagem']); ?>" alt="<?php echo htmlspecialchars($prato['nome_prato']); ?>">
                <?php else: ?>
                    <div class="sem-imagem"><i class="fa-solid fa-bowl-food"></i></div>
                <?php endif; ?>
            </div>
            <div>
                <h2><?php echo htmlspecialchars($prato['nome_prato']); ?></h2>
                <p class="prato-preco">R$ <?php echo number_format($prato['preco_venda'], 2, ',', '.'); ?></p>
                <?php if (count($itens) > 0): ?>
                    <span class="badge badge-ok"><i class="fa-solid fa-circle-check"></i> Possui receita</span>
                <?php else: ?>
                    <span class="badge badge-pendente"><i class="fa-solid fa-triangle-exclamation"></i> Ainda não possui receita</span>
                <?php endif; ?>
            </div>
        </section>

        <div class="grid-detalhe">

            <section class="card-box">
                <h3><i class="fa-solid fa-list-check"></i> Composição (Ingredientes)</h3>

                <?php if ($erro): ?>
                    <p class="erro-msg"><?php echo htmlspecialchars($erro); ?></p>
                <?php endif; ?>

                <?php if (count($ingredientes_disponiveis) === 0): ?>
                    <p class="aviso-msg">
                        Você ainda não tem ingredientes cadastrados no
                        <a href="../estoque/estoque.php">Estoque</a>. Cadastre-os lá primeiro para poder montar a receita.
                    </p>
                <?php else: ?>
                    <form action="ficha.php?id_prato=<?php echo $id_prato; ?>" method="POST" class="form-item">
                        <select name="id_ingrediente" required>
                            <option value="" disabled selected>Selecione o ingrediente</option>
                            <?php foreach ($ingredientes_disponiveis as $ing): ?>
                                <option value="<?php echo $ing['id_ingrediente']; ?>"><?php echo htmlspecialchars($ing['nome_ingredientes']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="quantidade_necessaria" placeholder="Qtd. Ex: 300" required>
                        <select name="unidade">
                            <option value="g">g</option>
                            <option value="kg">kg</option>
                            <option value="ml">ml</option>
                            <option value="l">l</option>
                            <option value="un">un</option>
                            <option value="dz">dz</option>
                        </select>
                        <button type="submit" name="adicionar_item" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Adicionar</button>
                    </form>
                <?php endif; ?>

                <div class="itens-lista">
                    <?php if (count($itens) > 0): ?>
                        <?php foreach ($itens as $item): ?>
                            <div class="item-linha">
                                <span><strong><?php echo rtrim(rtrim(number_format($item['quantidade_necessaria'], 2, ',', '.'), '0'), ','); ?> <?php echo htmlspecialchars($item['unidade']); ?></strong> de <?php echo htmlspecialchars($item['nome_ingredientes']); ?></span>
                                <a href="ficha.php?id_prato=<?php echo $id_prato; ?>&remover_item=<?php echo $item['id_item_ficha']; ?>"
                                   class="remover-item"
                                   onclick="return confirm('Remover este ingrediente da receita?')">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-msg">Nenhum ingrediente adicionado ainda.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="card-box">
                <h3><i class="fa-solid fa-book-open"></i> Modo de Preparo</h3>
                <form action="ficha.php?id_prato=<?php echo $id_prato; ?>" method="POST">
                    <textarea name="modo_preparo" rows="12" placeholder="Descreva o passo a passo do preparo..."><?php echo htmlspecialchars($prato['modo_preparo'] ?? ''); ?></textarea>
                    <div class="button-container">
                        <button type="submit" name="salvar_modo_preparo" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar Modo de Preparo</button>
                    </div>
                </form>
            </section>

        </div>
    </div>

</body>
</html>
<?php
    exit();
}

// ================== MODO LISTA (todos os pratos + filtro) ==================

$stmt = $pdo->prepare("SELECT p.id_prato, p.nome_prato, p.imagem, p.preco_venda,
                               (SELECT COUNT(*) FROM tb_item_ficha_tecnica f WHERE f.id_prato = p.id_prato) AS total_itens
                        FROM tb_pratos p
                        WHERE p.id_gestor = :id_gestor
                        ORDER BY p.nome_prato ASC");
$stmt->execute([':id_gestor' => $id_gestor]);
$pratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Ficha Técnica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="ficha.css">
</head>
<body>

    <div class="bg-overlay"></div>

    <header class="top-bar">
        <div class="brand"><span>RestControl</span></div>
        <a href="../home/home.php" class="top-link"><i class="fa-solid fa-arrow-left"></i> Voltar ao Painel</a>
    </header>

    <div class="container-dashboard">
        <section class="card-box header-card">
            <div class="header-section">
                <h2><i class="fa-solid fa-book-open"></i> Ficha Técnica</h2>
                <p>Clique em um prato para definir sua composição (ingredientes e quantidades) e o modo de preparo.</p>
            </div>
        </section>

        <?php if (count($pratos) === 0): ?>
            <p class="empty-msg">
                Você ainda não cadastrou nenhum prato. Vá até
                <a href="../pratos/pratos.php">Pratos</a> e cadastre um primeiro.
            </p>
        <?php else: ?>
            <div class="filtros">
                <button type="button" class="filtro-btn ativo" data-filtro="todos">Todos</button>
                <button type="button" class="filtro-btn" data-filtro="com">Já possui receita</button>
                <button type="button" class="filtro-btn" data-filtro="sem">Ainda não possui receita</button>
            </div>

            <section class="pratos-grid">
                <?php foreach ($pratos as $prato): ?>
                    <?php $tem_receita = $prato['total_itens'] > 0; ?>
                    <a href="ficha.php?id_prato=<?php echo $prato['id_prato']; ?>"
                       class="prato-card"
                       data-status="<?php echo $tem_receita ? 'com' : 'sem'; ?>">
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
                            <?php if ($tem_receita): ?>
                                <span class="badge badge-ok"><i class="fa-solid fa-circle-check"></i> Possui receita</span>
                            <?php else: ?>
                                <span class="badge badge-pendente"><i class="fa-solid fa-triangle-exclamation"></i> Sem receita</span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>

    <script src="ficha.js"></script>
</body>
</html>
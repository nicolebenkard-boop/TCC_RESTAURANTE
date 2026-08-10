<?php
// layout.php - Sistema de Gerenciamento de Plantas RestControl
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "restaurante";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro de conexão com o banco de dados: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// 1. AÇÃO: EXCLUIR LAYOUT SALVO
if (isset($_GET['deletar_id'])) {
    $id_del = intval($_GET['deletar_id']);
    $conn->query("DELETE FROM tb_layout WHERE id_layout = $id_del");
    header("Location: layout.php");
    exit();
}

// 2. AÇÃO: SALVAR O LAYOUT COM O NOME PERSONALIZADO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_layout'])) {
    $nome_personalizado = $conn->real_escape_string($_POST['nome_layout']);
    $id_cenario = isset($_POST['layout_id_tecnico']) ? $conn->real_escape_string($_POST['layout_id_tecnico']) : '01';

    // Insere o nome digitado pelo usuário e o modelo correspondente
    $sql = "INSERT INTO tb_layout (nome_layout, id_cenario) VALUES ('$nome_personalizado', '$id_cenario')";
    $conn->query($sql);

    header("Location: layout.php?ver_id=" . urlencode($id_cenario));
    exit();
}

// 3. AÇÃO: BUSCAR HISTÓRICO PARA A LISTA LATERAL
$layouts_salvos = [];
$result = $conn->query("SELECT id_layout, nome_layout, id_cenario FROM tb_layout ORDER BY id_layout DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $layouts_salvos[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="layout.css">
</head>

<body>

    <header class="top-bar"
        style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background-color: #1a1a1a; color: #fff;">
        <div class="brand" style="font-size: 1.2rem; font-weight: bold;">
            <span>RestControl <span
                    style="font-size: 0.9rem; font-weight: normal; color: #ccc; margin-left: 5px;">Blueprint Arch
                    v4.0</span></span>
        </div>
        <a href="../home/home.php" class="top-link" style="color: #fff; text-decoration: none;"><i
                class="fa-solid fa-arrow-left"></i> Voltar ao Painel</a>
    </header>

    <main class="container" style="display: flex; margin-top: 20px; gap: 20px; padding: 0 20px;">
        <aside class="sidebar-controls" style="width: 300px; flex-shrink: 0;">

            <div class="card-box"
                style="background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="font-size: 1.1rem; margin-top: 0;"><i class="fa-solid fa-sliders"></i> Gerador de Plantas
                </h2>
                <p class="subtitle" style="font-size: 0.85rem; color: #666;">Layouts Regulamentares de Cozinha</p>
                <button type="button" class="btn btn-primary w-full" id="btn-alternar"
                    style="width: 100%; padding: 10px; background: #800000; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    <i class="fa-solid fa-rotate"></i> Alternar Layout Estratégico
                </button>
            </div>

            <div class="card-box"
                style="background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="font-size: 1.1rem; margin-top: 0;"><i class="fa-solid fa-cloud-arrow-up"></i> Gravar Planta
                    Atual</h2>
                <p class="subtitle" style="font-size: 0.85rem; color: #666;">Salva a configuração atual no sistema</p>
                <form action="layout.php" method="POST" class="form-save">
                    <input type="hidden" name="layout_id_tecnico" id="input-layout-id" value="ARQ-LINHA-01">

                    <label class="section-label"
                        style="font-size: 0.8rem; font-weight: bold; display: block; margin-bottom: 5px; color: #333; text-transform: uppercase;">Nome
                        Personalizado</label>
                    <input type="text" name="nome_layout" placeholder="Ex: Cozinha da Hamburgueria" required
                        style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">

                    <button type="submit" class="btn btn-success w-full"
                        style="width: 100%; padding: 10px; background: #800000; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                        <i class="fa-solid fa-cloud"></i> Salvar
                    </button>
                </form>
            </div>

            <div class="card-box"
                style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="font-size: 1.1rem; margin-top: 0;"><i class="fa-solid fa-clock-rotate-left"></i> Seus Layouts
                    Salvos</h2>
                <p class="subtitle" style="font-size: 0.85rem; color: #666; margin-bottom: 15px;">Clique para ver ou use
                    a lixeira para excluir</p>
                <div class="layouts-history-list">
                    <?php if (!empty($layouts_salvos)): ?>
                        <?php foreach ($layouts_salvos as $layout): ?>
                            <div class="history-item"
                                style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px; padding: 10px; background: #f9f9f9; border: 1px solid #eee; border-radius: 6px;">
                                <a href="layout.php?ver_id=<?php echo urlencode($layout['id_cenario']); ?>"
                                    style="text-decoration:none; color:inherit; text-align:left; flex-grow:1;">
                                    <div class="proj-name" style="font-weight:600; color:#333;">
                                        <i class="fa-regular fa-map" style="color: #800000; margin-right: 5px;"></i>
                                        <?php echo htmlspecialchars($layout['nome_layout']); ?>
                                    </div>
                                    <div class="proj-date" style="font-size:0.75rem; color:#777; margin-top:2px;">
                                        Modelo: <?php echo htmlspecialchars($layout['id_cenario']); ?>
                                    </div>
                                </a>
                                <a href="layout.php?deletar_id=<?php echo $layout['id_layout']; ?>"
                                    onclick="return confirm('Deseja realmente eliminar este layout?')"
                                    style="color:#ef4444; margin-left:10px; font-size:1.1rem;">
                                    <i class="fa-regular fa-trash-can"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="font-size: 0.85rem; color: #888; text-align: center; margin: 0;">Nenhum layout salvo
                            ainda.</p>
                    <?php endif; ?>
                </div>
            </div>

        </aside>

        <section class="main-content" style="flex-grow: 1;">
            <div class="card-box blueprint-card"
                style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
                    <span class="tech-version-badge" id="layout-id-badge"
                        style="background: #eee; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-family: monospace; font-weight: bold;">ID:
                        ...</span>
                </div>

                <div class="blueprint-canvas-wrapper" id="canvas-container"></div>
            </div>
<br>
            <div class="card-box analysis-card"
                style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div class="analysis-title-group"
                    style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; color: #800000;">
                    <i class="fa-solid fa-wand-magic-sparkles" style="font-size: 1.2rem;"></i>
                    <h3 style="margin: 0; font-size: 1.1rem;">Análise de Fluxo Operacional (Normas ANVISA)</h3>
                </div>
                <p class="analysis-text" id="layout-description"
                    style="font-size: 0.9rem; color: #444; line-height: 1.5; margin: 0;"></p>
            </div>
        </section>
    </main>

    <script src="layout.js"></script>
</body>

</html>
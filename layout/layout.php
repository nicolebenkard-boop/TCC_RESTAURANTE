<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../pag_login/index.php');
    exit();
}

$id_gestor = (int)$_SESSION['usuario_id'];

function respostaJson(array $dados): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit();
}

// API do próprio arquivo: salvar, listar e excluir layouts.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    try {
        if ($acao === 'salvar_layout') {
            $nome = trim($_POST['nome_layout'] ?? '');
            $largura = (float)($_POST['largura'] ?? 8);
            $comprimento = (float)($_POST['comprimento'] ?? 5.2);
            $dados_layout = $_POST['dados_layout'] ?? '[]';
            $id_cenario = trim($_POST['id_cenario'] ?? 'ARQ-CUSTOM');
            $id_layout = (int)($_POST['id_layout'] ?? 0);

            if ($nome === '') {
                respostaJson(['ok' => false, 'mensagem' => 'Informe um nome para o layout.']);
            }
            if ($largura <= 0 || $comprimento <= 0) {
                respostaJson(['ok' => false, 'mensagem' => 'As medidas do cômodo precisam ser maiores que zero.']);
            }
            json_decode($dados_layout, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                respostaJson(['ok' => false, 'mensagem' => 'Os dados do layout são inválidos.']);
            }

            if ($id_layout > 0) {
                $stmt = $pdo->prepare('UPDATE tb_layout
                    SET nome_layout = :nome, id_cenario = :cenario, largura_m = :largura,
                        comprimento_m = :comprimento, dados_layout = :dados
                    WHERE id_layout = :id_layout AND id_gestor = :id_gestor');
                $stmt->execute([
                    ':nome' => $nome, ':cenario' => $id_cenario, ':largura' => $largura,
                    ':comprimento' => $comprimento, ':dados' => $dados_layout,
                    ':id_layout' => $id_layout, ':id_gestor' => $id_gestor
                ]);
                if ($stmt->rowCount() === 0) {
                    respostaJson(['ok' => false, 'mensagem' => 'Layout não encontrado ou sem permissão.']);
                }
            } else {
                $stmt = $pdo->prepare('INSERT INTO tb_layout
                    (id_gestor, nome_layout, id_cenario, largura_m, comprimento_m, dados_layout)
                    VALUES (:id_gestor, :nome, :cenario, :largura, :comprimento, :dados)');
                $stmt->execute([
                    ':id_gestor' => $id_gestor, ':nome' => $nome, ':cenario' => $id_cenario,
                    ':largura' => $largura, ':comprimento' => $comprimento, ':dados' => $dados_layout
                ]);
                $id_layout = (int)$pdo->lastInsertId();
            }

            respostaJson(['ok' => true, 'id_layout' => $id_layout, 'mensagem' => 'Layout salvo no banco de dados com sucesso.']);
        }

        if ($acao === 'excluir_layout') {
            $id_layout = (int)($_POST['id_layout'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM tb_layout WHERE id_layout = :id_layout AND id_gestor = :id_gestor');
            $stmt->execute([':id_layout' => $id_layout, ':id_gestor' => $id_gestor]);
            respostaJson(['ok' => true, 'mensagem' => 'Layout excluído.']);
        }

        respostaJson(['ok' => false, 'mensagem' => 'Ação inválida.']);
    } catch (Throwable $e) {
        respostaJson(['ok' => false, 'mensagem' => 'Erro no banco: ' . $e->getMessage()]);
    }
}

$stmt = $pdo->prepare('SELECT id_layout, nome_layout, id_cenario, largura_m, comprimento_m, dados_layout, data_criacao
                       FROM tb_layout WHERE id_gestor = :id_gestor ORDER BY id_layout DESC');
$stmt->execute([':id_gestor' => $id_gestor]);
$layouts_salvos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Editor de Layout de Cozinha</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Geração do documento PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            700: '#8c1111',
                            800: '#750909',
                            900: '#520404'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            color: #1e293b;
        }

        /* Architectural Blueprint Canvas Grid */
        .blueprint-grid {
            background-color: #ffffff;
            background-image: 
                linear-gradient(to right, rgba(203, 213, 225, 0.4) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(203, 213, 225, 0.4) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        /* Kitchen Room Wall Boundary */
        .room-boundary {
            border: 8px solid #1e293b;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            transition: width 0.2s ease, height 0.2s ease;
        }

        /* Interactive Drag Elements */
        .kitchen-element {
            touch-action: none;
            user-select: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .kitchen-element:hover {
            box-shadow: 0 8px 16px -2px rgba(0, 0, 0, 0.15);
        }

        .kitchen-element.selected {
            outline: 3px solid #2563eb;
            outline-offset: 2px;
            z-index: 40 !important;
        }

        /* Custom scrollbar for sidebar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex flex-col text-slate-800">

    <header class="bg-black text-white px-6 py-3.5 flex items-center justify-between border-b border-slate-800 shadow-md">
        <div class="flex items-center space-x-3">
            <span class="font-extrabold text-xl tracking-tight text-white">RestControl</span>
            <span class="text-xs text-slate-400 font-medium px-2 py-0.5 bg-slate-800 rounded border border-slate-700">Blueprint Arch v4.0</span>
        </div>
        <button onclick="window.location.href='../home/home.php'" class="flex items-center gap-2 text-xs font-semibold text-slate-300 hover:text-white transition">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar ao Painel
        </button>
    </header>

    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-0 overflow-hidden">
        
        <!-- SIDEBAR CONTROLS (Left 3 columns) -->
        <aside class="lg:col-span-3 bg-white border-r border-slate-200 p-4 space-y-4 overflow-y-auto max-h-[calc(100vh-57px)]">
            
            <!-- 1. Room Dimensions Customization -->
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center gap-2 text-slate-800 font-bold mb-1 text-sm">
                    <i class="fa-solid fa-ruler-combined text-brand-800"></i>
                    <h2>Medidas do Cômodo</h2>
                </div>
                <p class="text-xs text-slate-500 mb-2.5">Ajuste as dimensões da cozinha em metros:</p>
                <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Largura (m)</label>
                        <input id="input-room-width" type="number" min="3" max="25" step="0.5" value="8.0" onchange="updateRoomDimensions()" oninput="updateRoomDimensions()" class="w-full mt-0.5 px-2.5 py-1.5 border border-slate-300 rounded-lg text-slate-800 font-semibold text-xs focus:ring-2 focus:ring-brand-800 outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Comprimento (m)</label>
                        <input id="input-room-height" type="number" min="3" max="25" step="0.5" value="5.2" onchange="updateRoomDimensions()" oninput="updateRoomDimensions()" class="w-full mt-0.5 px-2.5 py-1.5 border border-slate-300 rounded-lg text-slate-800 font-semibold text-xs focus:ring-2 focus:ring-brand-800 outline-none bg-white">
                    </div>
                </div>
                <div id="room-metrics-badge" class="text-[11px] text-center font-bold text-slate-700 bg-slate-200/80 rounded py-1 border border-slate-300">
                    Área Total: 8,0m × 5,2m (41,6 m²)
                </div>
            </div>

            <!-- 2. Equipment & Items Palette -->
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2 text-slate-800 font-bold text-sm">
                        <i class="fa-solid fa-plus-circle text-brand-800"></i>
                        <h2>Adicionar Módulos</h2>
                    </div>
                    <span class="text-[10px] bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded font-bold">Catálogo</span>
                </div>
                <p class="text-xs text-slate-500 mb-2.5">Clique para incluir itens no mapa:</p>
                
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button onclick="addNewElement('geladeira')" class="flex items-center justify-between bg-white border border-slate-200 hover:border-sky-500 hover:bg-sky-50 p-2 rounded-lg font-semibold text-slate-700 text-left transition shadow-xs group">
                        <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-snowflake text-sky-500 text-xs"></i> Geladeira</span>
                        <span id="cnt-geladeira" class="text-[10px] bg-slate-100 text-slate-500 group-hover:bg-sky-200 group-hover:text-sky-800 font-bold px-1.5 py-0.5 rounded">0</span>
                    </button>
                    <button onclick="addNewElement('fogao')" class="flex items-center justify-between bg-white border border-slate-200 hover:border-amber-500 hover:bg-amber-50 p-2 rounded-lg font-semibold text-slate-700 text-left transition shadow-xs group">
                        <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-fire text-amber-500 text-xs"></i> Fogão</span>
                        <span id="cnt-fogao" class="text-[10px] bg-slate-100 text-slate-500 group-hover:bg-amber-200 group-hover:text-amber-800 font-bold px-1.5 py-0.5 rounded">0</span>
                    </button>
                    <button onclick="addNewElement('pia')" class="flex items-center justify-between bg-white border border-slate-200 hover:border-blue-500 hover:bg-blue-50 p-2 rounded-lg font-semibold text-slate-700 text-left transition shadow-xs group">
                        <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-faucet-drip text-blue-500 text-xs"></i> Pia</span>
                        <span id="cnt-pia" class="text-[10px] bg-slate-100 text-slate-500 group-hover:bg-blue-200 group-hover:text-blue-800 font-bold px-1.5 py-0.5 rounded">0</span>
                    </button>
                    <button onclick="addNewElement('bancada')" class="flex items-center justify-between bg-white border border-slate-200 hover:border-slate-400 hover:bg-slate-100 p-2 rounded-lg font-semibold text-slate-700 text-left transition shadow-xs group">
                        <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-table-cells text-slate-500 text-xs"></i> Bancada</span>
                        <span id="cnt-bancada" class="text-[10px] bg-slate-100 text-slate-500 group-hover:bg-slate-300 group-hover:text-slate-800 font-bold px-1.5 py-0.5 rounded">0</span>
                    </button>
                    <button onclick="addNewElement('balcao')" class="flex items-center justify-between bg-white border border-slate-200 hover:border-slate-400 hover:bg-slate-100 p-2 rounded-lg font-semibold text-slate-700 text-left transition shadow-xs group">
                        <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-store text-slate-500 text-xs"></i> Balcão</span>
                        <span id="cnt-balcao" class="text-[10px] bg-slate-100 text-slate-500 group-hover:bg-slate-300 group-hover:text-slate-800 font-bold px-1.5 py-0.5 rounded">0</span>
                    </button>
                    <button onclick="addNewElement('armario')" class="flex items-center justify-between bg-white border border-slate-200 hover:border-amber-700 hover:bg-amber-50 p-2 rounded-lg font-semibold text-slate-700 text-left transition shadow-xs group">
                        <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-box text-amber-700 text-xs"></i> Armário</span>
                        <span id="cnt-armario" class="text-[10px] bg-slate-100 text-slate-500 group-hover:bg-amber-200 group-hover:text-amber-800 font-bold px-1.5 py-0.5 rounded">0</span>
                    </button>
                    <button onclick="addNewElement('fritadeira')" class="flex items-center justify-between bg-white border border-slate-200 hover:border-orange-500 hover:bg-orange-50 p-2 rounded-lg font-semibold text-slate-700 text-left transition shadow-xs group">
                        <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-temperature-arrow-up text-orange-500 text-xs"></i> Fritadeira</span>
                        <span id="cnt-fritadeira" class="text-[10px] bg-slate-100 text-slate-500 group-hover:bg-orange-200 group-hover:text-orange-800 font-bold px-1.5 py-0.5 rounded">0</span>
                    </button>
                    <button onclick="addNewElement('panelas')" class="flex items-center justify-between bg-white border border-slate-200 hover:border-zinc-500 hover:bg-zinc-100 p-2 rounded-lg font-semibold text-slate-700 text-left transition shadow-xs group">
                        <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-kitchen-set text-zinc-600 text-xs"></i> Panelas</span>
                        <span id="cnt-panelas" class="text-[10px] bg-slate-100 text-slate-500 group-hover:bg-zinc-300 group-hover:text-zinc-800 font-bold px-1.5 py-0.5 rounded">0</span>
                    </button>
                    <button onclick="addNewElement('talheres')" class="flex items-center justify-between bg-white border border-slate-200 hover:border-slate-500 hover:bg-slate-100 p-2 rounded-lg font-semibold text-slate-700 text-left transition shadow-xs group">
                        <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-utensils text-slate-600 text-xs"></i> Talheres</span>
                        <span id="cnt-talheres" class="text-[10px] bg-slate-100 text-slate-500 group-hover:bg-slate-300 group-hover:text-slate-800 font-bold px-1.5 py-0.5 rounded">0</span>
                    </button>
                    <button onclick="addNewElement('bebedouro')" class="flex items-center justify-between bg-white border border-slate-200 hover:border-cyan-500 hover:bg-cyan-50 p-2 rounded-lg font-semibold text-slate-700 text-left transition shadow-xs group">
                        <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-glass-water text-cyan-500 text-xs"></i> Bebedouro</span>
                        <span id="cnt-bebedouro" class="text-[10px] bg-slate-100 text-slate-500 group-hover:bg-cyan-200 group-hover:text-cyan-800 font-bold px-1.5 py-0.5 rounded">0</span>
                    </button>
                    
                    <!-- Structural Wall Elements -->
                    <button onclick="addNewElement('porta')" class="flex items-center justify-between bg-white border border-slate-200 hover:border-emerald-600 hover:bg-emerald-50 p-2 rounded-lg font-semibold text-slate-700 text-left transition shadow-xs group">
                        <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-door-open text-emerald-600 text-xs"></i> Porta</span>
                        <span id="cnt-porta" class="text-[10px] bg-slate-100 text-slate-500 group-hover:bg-emerald-200 group-hover:text-emerald-800 font-bold px-1.5 py-0.5 rounded">0</span>
                    </button>
                    <button onclick="addNewElement('janela')" class="flex items-center justify-between bg-white border border-slate-200 hover:border-teal-500 hover:bg-teal-50 p-2 rounded-lg font-semibold text-slate-700 text-left transition shadow-xs group">
                        <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-window-maximize text-teal-500 text-xs"></i> Janela</span>
                        <span id="cnt-janela" class="text-[10px] bg-slate-100 text-slate-500 group-hover:bg-teal-200 group-hover:text-teal-800 font-bold px-1.5 py-0.5 rounded">0</span>
                    </button>
                </div>
            </div>

            <!-- 3. Save Layout Form -->
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center gap-2 text-slate-800 font-bold mb-1 text-sm">
                    <i class="fa-solid fa-cloud-arrow-up text-brand-800"></i>
                    <h2>Gravar Planta Atual</h2>
                </div>
                <p class="text-xs text-slate-500 mb-2.5">Salva a configuração atual no sistema:</p>
                
                <div class="space-y-2">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Nome Personalizado</label>
                        <input id="layout-name-input" type="text" placeholder="Ex: Cozinha da Hamburgueria" class="w-full mt-1 px-3 py-1.5 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-800 outline-none">
                    </div>
                    <button onclick="saveCurrentLayout()" class="w-full bg-brand-800 hover:bg-brand-900 text-white font-bold py-2 px-3 rounded-lg text-xs transition flex items-center justify-center gap-2 shadow-sm">
                        <i class="fa-solid fa-cloud"></i>
                        Salvar Layout
                    </button>
                </div>
            </div>

            <!-- 5. Saved Layouts List -->
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center gap-2 text-slate-800 font-bold mb-1 text-sm">
                    <i class="fa-solid fa-clock-rotate-left text-brand-800"></i>
                    <h2>Seus Layouts Salvos</h2>
                </div>
                <p class="text-xs text-slate-500 mb-2">Clique para carregar ou use a lixeira:</p>
                <div id="saved-layouts-list" class="space-y-1.5 max-h-36 overflow-y-auto">
                    <!-- Dynamic rendering -->
                </div>
            </div>

        </aside>

        <!-- CANVAS VIEWPORT (Right 9 columns) -->
        <main class="lg:col-span-9 bg-slate-900 p-4 lg:p-6 flex flex-col justify-start items-center overflow-auto min-h-[calc(100vh-57px)]">
            
            <!-- Canvas Toolbar Header -->
            <div class="w-full max-w-5xl bg-white px-4 py-2.5 rounded-t-xl border border-slate-300 flex items-center justify-between shadow-xs">
                <div class="flex items-center space-x-2">
                    <span id="layout-id-tag" class="text-[11px] font-mono font-bold bg-rose-100 text-rose-800 px-2 py-0.5 rounded border border-rose-200">
                        ID: 01
                    </span>
                    <span id="active-preset-title" class="text-xs font-semibold text-slate-600 ml-2">
                        Planta Baixa Personalizada
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="duplicateSelectedElement()" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-2.5 py-1 rounded border border-slate-300 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-copy text-slate-500"></i> Duplicar
                    </button>
                    <button onclick="rotateSelectedElement(90)" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-2.5 py-1 rounded border border-slate-300 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-rotate-right text-slate-500"></i> Girar
                    </button>
                    <button onclick="deleteSelectedElement()" class="text-xs bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold px-2.5 py-1 rounded border border-rose-200 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-trash-can text-rose-600"></i> Remover
                    </button>
                    <button onclick="generateLayoutPDF()" class="text-xs bg-brand-800 hover:bg-brand-900 text-white font-semibold px-2.5 py-1 rounded border border-brand-900 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-file-pdf"></i> Gerar PDF
                    </button>
                    <div class="h-4 w-px bg-slate-300 mx-1"></div>
                    <button onclick="clearCanvas()" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-2.5 py-1 rounded border border-slate-300 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-eraser text-slate-500"></i> Limpar
                    </button>
                </div>
            </div>

            <!-- Interactive Blueprint Canvas Container -->
            <div id="canvas-wrapper" class="w-full max-w-5xl bg-slate-200 p-6 rounded-b-xl border-x border-b border-slate-300 shadow-xl flex items-center justify-center overflow-auto min-h-[580px]">
                
                <div id="room-boundary" class="room-boundary blueprint-grid relative rounded-lg" style="width: 800px; height: 520px;">
                    <!-- Draggable elements dynamically injected here -->
                    <div id="canvas-elements-container" class="w-full h-full relative"></div>
                </div>

            </div>

        </main>
    </div>

    <!-- Notification Toast Box -->
    <div id="toast" class="fixed bottom-5 right-5 bg-slate-900 text-white text-xs font-semibold px-4 py-3 rounded-lg shadow-2xl transition-all duration-300 opacity-0 pointer-events-none transform translate-y-3 flex items-center gap-2 border border-slate-700 z-50">
        <i class="fa-solid fa-circle-info text-sky-400"></i>
        <span id="toast-message">Notificação</span>
    </div>

    <script>
        // Constants & Canvas state
        const PIXELS_PER_METER = 100; // 1 meter = 100 pixels scale
        let canvasWidth = 800;  // Default 8.0 meters
        let canvasHeight = 520; // Default 5.2 meters

        // Module SVG configurations and dimensions
        const MODULE_TYPES = {
            geladeira: {
                name: 'Geladeira',
                w: 100, h: 80,
                color: '#e0f2fe', stroke: '#0284c7',
                customSvg: `
                    <div class="w-full h-full bg-sky-100 border-2 border-sky-600 rounded flex flex-col items-center justify-center p-1 relative shadow-sm">
                        <div class="absolute top-1 left-2 right-2 h-1 bg-sky-400 rounded-full"></div>
                        <i class="fa-solid fa-snowflake text-sky-600 text-lg mb-1"></i>
                        <span class="text-[11px] font-black tracking-tight text-sky-900 uppercase">GELADEIRA</span>
                    </div>`
            },
            fogao: {
                name: 'Fogão',
                w: 120, h: 80,
                color: '#f1f5f9', stroke: '#475569',
                customSvg: `
                    <div class="w-full h-full bg-slate-200 border-2 border-slate-600 rounded flex flex-col items-center justify-between p-1.5 relative shadow-sm">
                        <div class="grid grid-cols-4 gap-1.5 w-full my-auto">
                            <div class="w-4 h-4 rounded-full border-2 border-slate-700 bg-slate-400 mx-auto"></div>
                            <div class="w-4 h-4 rounded-full border-2 border-slate-700 bg-slate-400 mx-auto"></div>
                            <div class="w-4 h-4 rounded-full border-2 border-slate-700 bg-slate-400 mx-auto"></div>
                            <div class="w-4 h-4 rounded-full border-2 border-slate-700 bg-slate-400 mx-auto"></div>
                        </div>
                        <span class="text-[11px] font-black tracking-tight text-slate-800 uppercase">FOGÃO</span>
                    </div>`
            },
            pia: {
                name: 'Pia',
                w: 130, h: 80,
                color: '#e2e8f0', stroke: '#334155',
                customSvg: `
                    <div class="w-full h-full bg-slate-200 border-2 border-slate-500 rounded flex items-center justify-around p-1.5 relative shadow-sm">
                        <div class="w-10 h-10 bg-sky-400 border border-sky-600 rounded-sm"></div>
                        <span class="text-[11px] font-black tracking-tight text-slate-800 uppercase">PIA</span>
                        <div class="w-10 h-10 bg-sky-400 border border-sky-600 rounded-sm"></div>
                    </div>`
            },
            bancada: {
                name: 'Bancada',
                w: 110, h: 80,
                color: '#e2e8f0', stroke: '#475569',
                customSvg: `
                    <div class="w-full h-full bg-slate-200 border-2 border-slate-500 rounded flex items-center justify-center p-1 relative shadow-sm">
                        <span class="text-[11px] font-black tracking-tight text-slate-800 uppercase">BANCADA</span>
                    </div>`
            },
            balcao: {
                name: 'Balcão',
                w: 100, h: 80,
                color: '#e2e8f0', stroke: '#475569',
                customSvg: `
                    <div class="w-full h-full bg-slate-200 border-2 border-slate-500 rounded flex items-center justify-center p-1 relative shadow-sm">
                        <span class="text-[11px] font-black tracking-tight text-slate-800 uppercase">BALCÃO</span>
                    </div>`
            },
            armario: {
                name: 'Armário',
                w: 90, h: 70,
                color: '#fef3c7', stroke: '#b45309',
                customSvg: `
                    <div class="w-full h-full bg-amber-100 border-2 border-amber-700 rounded flex flex-col items-center justify-center p-1 relative shadow-sm">
                        <i class="fa-solid fa-box text-amber-800 text-sm mb-0.5"></i>
                        <span class="text-[10px] font-black tracking-tight text-amber-900 uppercase">ARMÁRIO</span>
                    </div>`
            },
            fritadeira: {
                name: 'Fritadeira',
                w: 80, h: 80,
                color: '#ffedd5', stroke: '#c2410c',
                customSvg: `
                    <div class="w-full h-full bg-orange-100 border-2 border-orange-600 rounded flex flex-col items-center justify-center p-1 relative shadow-sm">
                        <i class="fa-solid fa-fire-burner text-orange-600 text-sm mb-0.5"></i>
                        <span class="text-[10px] font-black tracking-tight text-orange-950 uppercase">FRITADEIRA</span>
                    </div>`
            },
            panelas: {
                name: 'Panelas',
                w: 80, h: 60,
                color: '#f4f4f5', stroke: '#52525b',
                customSvg: `
                    <div class="w-full h-full bg-zinc-100 border-2 border-zinc-600 rounded flex flex-col items-center justify-center p-1 relative shadow-sm">
                        <i class="fa-solid fa-kitchen-set text-zinc-700 text-sm mb-0.5"></i>
                        <span class="text-[10px] font-black tracking-tight text-zinc-900 uppercase">PANELAS</span>
                    </div>`
            },
            talheres: {
                name: 'Talheres',
                w: 70, h: 50,
                color: '#f8fafc', stroke: '#64748b',
                customSvg: `
                    <div class="w-full h-full bg-slate-100 border-2 border-slate-500 rounded flex flex-col items-center justify-center p-1 relative shadow-sm">
                        <i class="fa-solid fa-utensils text-slate-700 text-xs mb-0.5"></i>
                        <span class="text-[9px] font-black tracking-tight text-slate-900 uppercase">TALHERES</span>
                    </div>`
            },
            bebedouro: {
                name: 'Bebedouro',
                w: 60, h: 60,
                color: '#ecfeff', stroke: '#0891b2',
                customSvg: `
                    <div class="w-full h-full bg-cyan-100 border-2 border-cyan-600 rounded flex flex-col items-center justify-center p-1 relative shadow-sm">
                        <i class="fa-solid fa-glass-water text-cyan-600 text-sm mb-0.5"></i>
                        <span class="text-[9px] font-black tracking-tight text-cyan-950 uppercase">BEBEDOURO</span>
                    </div>`
            },
            porta: {
                name: 'Porta',
                w: 90, h: 25,
                color: '#d1fae5', stroke: '#059669',
                isWallElement: true,
                customSvg: `
                    <div class="w-full h-full bg-emerald-200 border-2 border-emerald-700 rounded-sm flex items-center justify-between px-2 relative shadow-sm">
                        <i class="fa-solid fa-door-open text-emerald-800 text-xs"></i>
                        <span class="text-[9px] font-black tracking-tight text-emerald-950 uppercase">PORTA</span>
                        <div class="w-2 h-2 bg-emerald-800 rounded-full"></div>
                    </div>`
            },
            janela: {
                name: 'Janela',
                w: 90, h: 20,
                color: '#ccfbf1', stroke: '#0d9488',
                isWallElement: true,
                customSvg: `
                    <div class="w-full h-full bg-teal-100 border-2 border-teal-600 rounded-sm flex items-center justify-center gap-1 relative shadow-sm">
                        <div class="w-full h-1 bg-teal-400"></div>
                        <span class="text-[9px] font-black tracking-tight text-teal-950 uppercase">JANELA</span>
                        <div class="w-full h-1 bg-teal-400"></div>
                    </div>`
            }
        };

        // Active Application State
        let currentElements = [
            { id: "el_1", type: "geladeira", x: 20, y: 30, rotation: 0 },
            { id: "el_2", type: "bancada", x: 130, y: 30, rotation: 0 },
            { id: "el_3", type: "pia", x: 250, y: 30, rotation: 0 },
            { id: "el_4", type: "fogao", x: 390, y: 30, rotation: 0 },
            { id: "el_5", type: "balcao", x: 520, y: 30, rotation: 0 },
            { id: "el_6", type: "porta", x: 0, y: 200, rotation: 0 }
        ];
        let selectedElementId = null;
        let activeDrag = null;
        let currentSavedLayoutId = null;
        const DB_LAYOUTS = <?php echo json_encode($layouts_salvos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        // Initialize on Load
        window.onload = function() {
            setupCanvasDragListeners();
            renderCanvas();
            loadSavedLayoutsFromDatabase();
            updateRoomDimensions();
        };

        /**
         * Update room boundary size based on width/height inputs in meters
         */
        function updateRoomDimensions() {
            const widthMeters = parseFloat(document.getElementById('input-room-width').value) || 8.0;
            const heightMeters = parseFloat(document.getElementById('input-room-height').value) || 5.2;

            canvasWidth = Math.round(widthMeters * PIXELS_PER_METER);
            canvasHeight = Math.round(heightMeters * PIXELS_PER_METER);

            const boundaryEl = document.getElementById('room-boundary');
            boundaryEl.style.width = `${canvasWidth}px`;
            boundaryEl.style.height = `${canvasHeight}px`;

            const areaM2 = (widthMeters * heightMeters).toFixed(1).replace('.', ',');
            document.getElementById('room-metrics-badge').innerText = 
                `Área Total: ${widthMeters.toString().replace('.', ',')}m × ${heightMeters.toString().replace('.', ',')}m (${areaM2} m²)`;

            clampElementsToBounds();
            renderCanvas();
        }

        /**
         * Clamp objects within current room boundaries
         */
        function clampElementsToBounds() {
            currentElements.forEach(item => {
                const config = MODULE_TYPES[item.type] || { w: 100, h: 80 };
                if (config.isWallElement) {
                    snapWallElement(item);
                } else {
                    item.x = Math.max(0, Math.min(canvasWidth - config.w, item.x));
                    item.y = Math.max(0, Math.min(canvasHeight - config.h, item.y));
                }
            });
        }

        /**
         * Snap doors/windows specifically to closest room perimeter wall
         */
        function snapWallElement(item) {
            const config = MODULE_TYPES[item.type] || { w: 80, h: 20 };
            const w = config.w;
            const h = config.h;

            const distTop = Math.abs(item.y);
            const distBottom = Math.abs(canvasHeight - (item.y + h));
            const distLeft = Math.abs(item.x);
            const distRight = Math.abs(canvasWidth - (item.x + w));

            const minDist = Math.min(distTop, distBottom, distLeft, distRight);

            if (minDist === distTop) {
                item.y = 0;
                item.x = Math.max(0, Math.min(canvasWidth - w, item.x));
            } else if (minDist === distBottom) {
                item.y = canvasHeight - h;
                item.x = Math.max(0, Math.min(canvasWidth - w, item.x));
            } else if (minDist === distLeft) {
                item.x = 0;
                item.y = Math.max(0, Math.min(canvasHeight - h, item.y));
            } else {
                item.x = canvasWidth - w;
                item.y = Math.max(0, Math.min(canvasHeight - h, item.y));
            }
        }

        /**
         * Render canvas elements to the UI
         */
        function renderCanvas() {
            const container = document.getElementById('canvas-elements-container');
            container.innerHTML = '';

            currentElements.forEach(item => {
                const config = MODULE_TYPES[item.type] || MODULE_TYPES.bancada;
                const el = document.createElement('div');
                const isWall = config.isWallElement;

                el.id = item.id;
                el.className = `kitchen-element absolute cursor-move select-none ${isWall ? 'wall-element' : ''} ${selectedElementId === item.id ? 'selected' : ''}`;
                el.style.left = `${item.x}px`;
                el.style.top = `${item.y}px`;
                el.style.width = `${config.w}px`;
                el.style.height = `${config.h}px`;
                el.style.transform = `rotate(${item.rotation || 0}deg)`;

                el.innerHTML = config.customSvg;

                // Selection highlight & Mouse / Touch event bindings
                el.addEventListener('mousedown', (e) => startDrag(e, item.id));
                el.addEventListener('touchstart', (e) => startDrag(e, item.id), { passive: false });

                container.appendChild(el);
            });

            updateModuleCounters();
        }

        /**
         * Update real-time counter badges in left sidebar catalog
         */
        function updateModuleCounters() {
            const counts = {};
            Object.keys(MODULE_TYPES).forEach(key => counts[key] = 0);

            currentElements.forEach(item => {
                if (counts[item.type] !== undefined) {
                    counts[item.type]++;
                }
            });

            Object.keys(counts).forEach(key => {
                const badge = document.getElementById(`cnt-${key}`);
                if (badge) badge.innerText = counts[key];
            });
        }

        /**
         * Add a new equipment or structural item to canvas
         */
        function addNewElement(type) {
            const config = MODULE_TYPES[type];
            if (!config) return;

            const existingSameType = currentElements.filter(i => i.type === type).length;
            const newId = 'item_' + Date.now();
            
            let x = 120 + (existingSameType * 15) % 150;
            let y = 140 + (existingSameType * 15) % 150;

            const newItem = {
                id: newId,
                type: type,
                x: x,
                y: y,
                rotation: 0
            };

            if (config.isWallElement) {
                newItem.x = 0;
                newItem.y = 100 + (existingSameType * 60) % (canvasHeight - 100);
                snapWallElement(newItem);
            }

            currentElements.push(newItem);
            selectedElementId = newId;
            renderCanvas();
            showToast(`Módulo ${config.name} adicionado ao layout.`);
        }

        /**
         * Duplicate currently selected element
         */
        function duplicateSelectedElement() {
            if (!selectedElementId) {
                showToast('Selecione um elemento para duplicar.');
                return;
            }

            const item = currentElements.find(i => i.id === selectedElementId);
            if (!item) return;

            const config = MODULE_TYPES[item.type];
            const newId = 'item_' + Date.now();
            const newItem = {
                id: newId,
                type: item.type,
                x: Math.min(canvasWidth - config.w, item.x + 20),
                y: Math.min(canvasHeight - config.h, item.y + 20),
                rotation: item.rotation || 0
            };

            if (config.isWallElement) {
                snapWallElement(newItem);
            }

            currentElements.push(newItem);
            selectedElementId = newId;
            renderCanvas();
            showToast(`Duplicado: ${config.name}`);
        }

        /**
         * Rotate currently selected element
         */
        function rotateSelectedElement(deg = 90) {
            if (!selectedElementId) {
                showToast('Selecione um elemento para girar.');
                return;
            }
            const item = currentElements.find(i => i.id === selectedElementId);
            if (item) {
                item.rotation = ((item.rotation || 0) + deg) % 360;
                renderCanvas();
                showToast(`Elemento rotacionado para ${item.rotation}°.`);
            }
        }

        /**
         * Delete currently selected element
         */
        function deleteSelectedElement() {
            if (!selectedElementId) {
                showToast('Selecione um elemento para remover.');
                return;
            }
            currentElements = currentElements.filter(i => i.id !== selectedElementId);
            selectedElementId = null;
            renderCanvas();
            showToast('Elemento removido.');
        }

        /**
         * Clear all items from current canvas
         */
        function clearCanvas() {
            currentElements = [];
            selectedElementId = null;
            currentSavedLayoutId = null;
            document.getElementById('layout-name-input').value = '';
            renderCanvas();
            showToast('Canvas limpo.');
        }

        /**
         * Drag Engine Handlers
         */
        function startDrag(e, id) {
            e.stopPropagation();
            selectedElementId = id;
            renderCanvas();

            const item = currentElements.find(i => i.id === id);
            if (!item) return;

            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;

            activeDrag = {
                id: id,
                startX: clientX,
                startY: clientY,
                origX: item.x,
                origY: item.y
            };
        }

        function setupCanvasDragListeners() {
            const handleMove = (e) => {
                if (!activeDrag) return;

                const item = currentElements.find(i => i.id === activeDrag.id);
                if (!item) return;

                const config = MODULE_TYPES[item.type] || { w: 100, h: 80 };
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;

                const deltaX = clientX - activeDrag.startX;
                const deltaY = clientY - activeDrag.startY;

                let newX = activeDrag.origX + deltaX;
                let newY = activeDrag.origY + deltaY;

                if (config.isWallElement) {
                    item.x = newX;
                    item.y = newY;
                    snapWallElement(item);
                } else {
                    item.x = Math.max(0, Math.min(canvasWidth - config.w, newX));
                    item.y = Math.max(0, Math.min(canvasHeight - config.h, newY));
                }

                const el = document.getElementById(item.id);
                if (el) {
                    el.style.left = `${item.x}px`;
                    el.style.top = `${item.y}px`;
                }
            };

            const handleEnd = () => {
                if (activeDrag) {
                    activeDrag = null;
                    renderCanvas();
                }
            };

            window.addEventListener('mousemove', handleMove);
            window.addEventListener('mouseup', handleEnd);
            window.addEventListener('touchmove', handleMove, { passive: false });
            window.addEventListener('touchend', handleEnd);

            // Deselect on empty canvas click
            document.getElementById('room-boundary').addEventListener('click', (e) => {
                if (e.target.id === 'room-boundary' || e.target.id === 'canvas-elements-container') {
                    selectedElementId = null;
                    renderCanvas();
                }
            });
        }

        /**
         * Persistência no banco de dados
         */
        function formatDateBR(value) {
            if (!value) return '';
            const d = new Date(value.replace(' ', 'T'));
            return Number.isNaN(d.getTime()) ? value : d.toLocaleDateString('pt-BR');
        }

        async function requestLayout(action, data = {}) {
            const form = new URLSearchParams({ acao: action, ...data });
            const response = await fetch('layout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: form.toString()
            });
            return response.json();
        }

        async function saveCurrentLayout() {
            const input = document.getElementById('layout-name-input');
            const name = input.value.trim() || `Layout Sem Nome (${new Date().toLocaleTimeString().slice(0,5)})`;

            try {
                const result = await requestLayout('salvar_layout', {
                    id_layout: currentSavedLayoutId || 0,
                    nome_layout: name,
                    id_cenario: 'ARQ-CUSTOM',
                    largura: document.getElementById('input-room-width').value,
                    comprimento: document.getElementById('input-room-height').value,
                    dados_layout: JSON.stringify(currentElements)
                });
                if (!result.ok) throw new Error(result.mensagem || 'Não foi possível salvar.');

                currentSavedLayoutId = Number(result.id_layout);
                const item = {
                    id_layout: currentSavedLayoutId,
                    nome_layout: name,
                    id_cenario: 'ARQ-CUSTOM',
                    largura_m: document.getElementById('input-room-width').value,
                    comprimento_m: document.getElementById('input-room-height').value,
                    dados_layout: JSON.stringify(currentElements),
                    data_criacao: new Date().toISOString()
                };
                const idx = DB_LAYOUTS.findIndex(x => Number(x.id_layout) === currentSavedLayoutId);
                if (idx >= 0) DB_LAYOUTS[idx] = item; else DB_LAYOUTS.unshift(item);

                input.value = '';
                document.getElementById('active-preset-title').innerText = name;
                document.getElementById('layout-id-tag').innerText = `ID: ${String(currentSavedLayoutId).padStart(2,'0')}`;
                loadSavedLayoutsFromDatabase();
                showToast(result.mensagem || `Layout "${name}" salvo no banco.`);
            } catch (error) {
                console.error(error);
                showToast(error.message || 'Erro ao salvar no banco de dados.');
            }
        }

        function loadSavedLayoutsFromDatabase() {
            const container = document.getElementById('saved-layouts-list');
            if (DB_LAYOUTS.length === 0) {
                container.innerHTML = `<p class="text-[11px] text-slate-400 italic text-center py-2">Nenhum layout salvo ainda.</p>`;
                return;
            }

            container.innerHTML = DB_LAYOUTS.map(item => `
                <div class="flex items-center justify-between bg-white border border-slate-200 p-2 rounded-lg text-xs shadow-2xs hover:border-slate-400 transition">
                    <button onclick="loadLayoutById(${Number(item.id_layout)})" class="truncate font-semibold text-slate-700 hover:text-brand-800 text-left flex-1">
                        <i class="fa-solid fa-map text-slate-400 mr-1 text-[10px]"></i> ${escapeHtml(item.nome_layout)}
                        <span class="block text-[10px] text-slate-400 font-normal mt-0.5">${formatDateBR(item.data_criacao)}</span>
                    </button>
                    <button onclick="deleteLayoutById(${Number(item.id_layout)})" class="text-slate-400 hover:text-rose-600 p-1 transition" title="Excluir">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                </div>
            `).join('');
        }

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value || '';
            return div.innerHTML;
        }

        function loadLayoutById(id) {
            const layout = DB_LAYOUTS.find(i => Number(i.id_layout) === Number(id));
            if (!layout) return;
            try {
                currentElements = JSON.parse(layout.dados_layout || '[]');
            } catch (_) {
                currentElements = [];
            }
            if (layout.largura_m) document.getElementById('input-room-width').value = layout.largura_m;
            if (layout.comprimento_m) document.getElementById('input-room-height').value = layout.comprimento_m;
            currentSavedLayoutId = Number(layout.id_layout);
            selectedElementId = null;
            updateRoomDimensions();
            document.getElementById('layout-name-input').value = layout.nome_layout || '';
            document.getElementById('active-preset-title').innerText = layout.nome_layout || 'Planta Baixa Personalizada';
            document.getElementById('layout-id-tag').innerText = `ID: ${String(currentSavedLayoutId).padStart(2,'0')}`;
            showToast(`Layout "${layout.nome_layout}" carregado do banco.`);
        }

        async function deleteLayoutById(id) {
            if (!confirm('Deseja realmente excluir este layout do banco de dados?')) return;
            try {
                const result = await requestLayout('excluir_layout', { id_layout: id });
                if (!result.ok) throw new Error(result.mensagem || 'Não foi possível excluir.');
                const idx = DB_LAYOUTS.findIndex(i => Number(i.id_layout) === Number(id));
                if (idx >= 0) DB_LAYOUTS.splice(idx, 1);
                if (Number(currentSavedLayoutId) === Number(id)) currentSavedLayoutId = null;
                loadSavedLayoutsFromDatabase();
                showToast(result.mensagem || 'Layout excluído.');
            } catch (error) {
                showToast(error.message || 'Erro ao excluir layout.');
            }
        }

        /**
         * Gera um documento PDF do layout atual.
         * O desenho é capturado exatamente como está na planta e acompanhado de um resumo técnico.
         */
        async function generateLayoutPDF() {
            const { jsPDF } = window.jspdf;
            const name = document.getElementById('layout-name-input').value.trim()
                || document.getElementById('active-preset-title').innerText.trim()
                || 'Layout de Cozinha';
            const width = document.getElementById('input-room-width').value;
            const height = document.getElementById('input-room-height').value;
            const area = (parseFloat(width || 0) * parseFloat(height || 0)).toFixed(1).replace('.', ',');

            try {
                showToast('Gerando documento PDF...');
                const room = document.getElementById('room-boundary');
                const canvas = await html2canvas(room, { scale: 1.5, backgroundColor: '#ffffff', useCORS: true });
                const imageData = canvas.toDataURL('image/png');
                const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

                pdf.setFillColor(117, 9, 9);
                pdf.rect(0, 0, 297, 18, 'F');
                pdf.setTextColor(255, 255, 255);
                pdf.setFontSize(18);
                pdf.text('RestControl — Documento de Layout da Cozinha', 14, 12);

                pdf.setTextColor(30, 41, 59);
                pdf.setFontSize(14);
                pdf.text(name, 14, 28);
                pdf.setFontSize(9);
                pdf.text(`Gestor: ${<?php echo json_encode((string)$id_gestor); ?>}   •   Data: ${new Date().toLocaleString('pt-BR')}`, 14, 34);
                pdf.text(`Medidas: ${String(width).replace('.', ',')} m × ${String(height).replace('.', ',')} m   •   Área: ${area} m²`, 14, 40);
                pdf.text(`Itens no layout: ${currentElements.length}`, 14, 46);

                const maxW = 270, maxH = 145;
                const ratio = canvas.width / canvas.height;
                let drawW = maxW, drawH = drawW / ratio;
                if (drawH > maxH) { drawH = maxH; drawW = drawH * ratio; }
                const x = (297 - drawW) / 2;
                pdf.addImage(imageData, 'PNG', x, 52, drawW, drawH);

                let y = 52 + drawH + 10;
                pdf.setFontSize(11);
                pdf.text('Resumo dos módulos', 14, y);
                y += 6;
                pdf.setFontSize(8);
                const counts = {};
                currentElements.forEach(item => counts[item.type] = (counts[item.type] || 0) + 1);
                const resumo = Object.entries(counts).map(([type, count]) => `${MODULE_TYPES[type]?.name || type}: ${count}`).join('   |   ') || 'Nenhum módulo adicionado.';
                const lines = pdf.splitTextToSize(resumo, 270);
                pdf.text(lines, 14, y);
                y += lines.length * 4 + 5;
                pdf.setTextColor(100, 116, 139);
                pdf.text('Documento gerado pelo RestControl. O desenho representa a configuração atual salva/visualizada no editor.', 14, y);

                const safeName = name.replace(/[\\/:*?"<>|]+/g, '-').trim() || 'layout';
                pdf.save(`${safeName}.pdf`);
                showToast('PDF gerado com sucesso.');
            } catch (error) {
                console.error(error);
                showToast('Não foi possível gerar o PDF.');
            }
        }

        /**
         * Notification Toast Display
         */
        function showToast(message) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-message').innerText = message;
            toast.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-3');
            toast.classList.add('opacity-100', 'translate-y-0');

            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'pointer-events-none', 'translate-y-3');
            }, 3000);
        }
    </script>
</body>
</html>
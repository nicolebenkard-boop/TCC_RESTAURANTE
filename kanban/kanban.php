<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanban Executivo, Planilha & IA</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        wine: {
                            50: '#fdf4f5',
                            100: '#fbe8eb',
                            200: '#f7d5db',
                            300: '#f0b3bf',
                            400: '#e58599',
                            500: '#d55772',
                            600: '#be3855',
                            700: '#a02842',
                            800: '#85243b',
                            900: '#702235',
                            950: '#3d0c19',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fdf4f5;
            color: #3d0c19;
        }

        /* Custom scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #fbe8eb;
        }

        ::-webkit-scrollbar-thumb {
            background: #a02842;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #702235;
        }

        /* Input highlight styles for editable table cells */
        .table-cell-input {
            width: 100%;
            background: transparent;
            border: 1px solid transparent;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .table-cell-input:hover {
            background: #ffffff;
            border-color: #f7d5db;
        }

        .table-cell-input:focus {
            background: #ffffff;
            border-color: #85243b;
            outline: none;
            box-shadow: 0 0 0 2px rgba(133, 36, 59, 0.15);
        }

        .drag-over {
            border-color: #85243b !important;
            background-color: #fbe8eb !important;
        }

        @keyframes pulseGlow {

            0%,
            100% {
                opacity: 0.8;
                transform: scale(1);
            }

            50% {
                opacity: 1;
                transform: scale(1.03);
            }
        }

        .ai-glow {
            animation: pulseGlow 2.5s infinite ease-in-out;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col bg-wine-50 text-wine-950 font-sans antialiased">

    <!-- NAVIGATION BAR -->




    <header class="bg-wine-950 text-white border-b border-wine-900 sticky top-0 z-30 shadow-md">
        <div class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

            <!-- View Selector Tabs -->
            <div class="flex items-center space-x-2 bg-wine-900/80 p-1 rounded-xl border border-wine-800">


                <button id="btn-view-kanban" onclick="switchView('kanban')" class="flex items-center space-x-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-wine-950 shadow-sm">
                    <i class="fa-solid fa-square-kanban text-wine-800"></i>
                    <span>Kanban</span>
                </button>
                <button id="btn-view-table" onclick="switchView('table')" class="flex items-center space-x-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-wine-200 hover:text-white hover:bg-wine-800/50">
                    <i class="fa-solid fa-table-cells"></i>
                    <span>Planilha</span>
                </button>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                <a href="../home/home.php" class="bg-wine-900 hover:bg-wine-800 text-wine-100 hover:text-white px-3.5 py-2 rounded-xl text-xs font-bold flex items-center space-x-1.5 border border-wine-800 transition-all">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Voltar para o Painel</span>
                </a>
                <button onclick="openTaskModal()" class="bg-white hover:bg-wine-100 text-wine-950 px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center space-x-1.5 shadow-sm transition-all hover:scale-105 active:scale-95">
                    <i class="fa-solid fa-plus text-wine-800"></i>
                    <span class="hidden sm:inline">Nova Tarefa</span>
                </button>

                <div class="h-6 w-px bg-wine-800 hidden sm:block"></div>

                <div class="flex items-center space-x-1">
                    <button onclick="exportJSON()" title="Exportar dados (Backup)" class="p-2 text-wine-200 hover:text-white hover:bg-wine-800 rounded-lg transition-colors">
                        <i class="fa-solid fa-download text-sm"></i>
                    </button>
                    <label title="Importar dados (JSON)" class="p-2 text-wine-200 hover:text-white hover:bg-wine-800 rounded-lg transition-colors cursor-pointer">
                        <i class="fa-solid fa-upload text-sm"></i>
                        <input type="file" accept=".json" onchange="importJSON(event)" class="hidden">
                    </label>
                </div>
            </div>

        </div>
    </header>

    <!-- EXECUTIVE METRICS BAR -->
    <section class="bg-white border-b border-wine-100 py-3 shadow-sm">
        <div class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                <!-- Total Tasks -->
                <div class="bg-wine-50/50 p-3 rounded-2xl border border-wine-100/80 flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-wine-950 text-white flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-wine-900/70">Minhas Tarefas</p>
                        <p id="stat-total" class="text-lg font-extrabold text-wine-950">0</p>
                    </div>
                </div>

                <!-- Completed Tasks -->
                <div class="bg-wine-50/50 p-3 rounded-2xl border border-wine-100/80 flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-wine-900/70">Concluídas</p>
                        <p id="stat-completed" class="text-lg font-extrabold text-emerald-800">0</p>
                    </div>
                </div>

                <!-- Overall Progress -->
                <div class="bg-wine-50/50 p-3 rounded-2xl border border-wine-100/80 flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-wine-800 text-white flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-percent"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-wine-900/70">Progresso Total</p>
                        <p id="stat-progress" class="text-lg font-extrabold text-wine-950">0%</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- SEARCH & FILTERS TOOLBAR -->
    <section class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        <div class="bg-white p-3 rounded-2xl border border-wine-100 shadow-sm flex flex-col sm:flex-row gap-3 items-center justify-between">

            <div class="relative w-full sm:w-80">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-wine-400 text-xs"></i>
                <input type="text" id="search-input" oninput="handleSearchFilter()" placeholder="Buscar tarefas, pessoas, notas ou tags..." class="w-full pl-9 pr-3 py-1.5 bg-wine-50/40 border border-wine-200 rounded-xl text-xs focus:outline-none focus:border-wine-800 focus:bg-white transition-all text-wine-950 placeholder-wine-400">
            </div>

            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-end">
                <select id="filter-priority" onchange="handleSearchFilter()" class="bg-wine-50/40 border border-wine-200 text-wine-950 text-xs rounded-xl px-2.5 py-1.5 font-medium focus:outline-none focus:border-wine-800">
                    <option value="">Todas as Prioridades</option>
                    <option value="Urgente">Urgente</option>
                    <option value="Alta">Alta</option>
                    <option value="Média">Média</option>
                    <option value="Baixa">Baixa</option>
                </select>

                <button onclick="openColumnModal()" class="text-xs bg-wine-50 hover:bg-wine-100 text-wine-950 px-3 py-1.5 rounded-xl font-semibold border border-wine-200 transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-sliders text-wine-800"></i>
                    Colunas
                </button>

                <button onclick="loadAppData()" title="Atualizar dados do Banco" class="text-xs text-wine-700 hover:text-wine-950 px-2 py-1.5 font-medium transition-all">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>

        </div>
    </section>

    <!-- MAIN WORKSPACE CONTAINER -->
    <main class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-8 py-5 flex-1 w-full overflow-y-auto">

        <!-- KANBAN BOARD VIEW (Vertical Grid Layout) -->
        <div id="kanban-view-container" class="w-full">
            <div id="kanban-board" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 w-full items-start pb-10">
                <!-- Columns dynamically injected here -->
            </div>
        </div>

        <!-- SPREADSHEET / TABLE VIEW -->
        <div id="table-view-container" class="hidden bg-white rounded-2xl border border-wine-200 shadow-sm overflow-hidden mb-10">
            <div class="p-3 bg-wine-50 border-b border-wine-100 flex justify-between items-center">
                <span class="text-xs font-bold text-wine-950 flex items-center gap-2">
                    <i class="fa-solid fa-table text-wine-800"></i> Planilha Interativa
                </span>
                <button onclick="openTaskModal()" class="bg-wine-950 hover:bg-wine-900 text-white text-xs px-3.5 py-1.5 rounded-xl font-semibold flex items-center gap-1.5 shadow-sm transition-all">
                    <i class="fa-solid fa-plus text-xs"></i> Nova Linha
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-wine-950 text-white font-semibold border-b border-wine-900">
                            <th class="p-3 w-10 text-center">#</th>
                            <th class="p-3 min-w-[220px]">Título / Nome da Tarefa</th>
                            <th class="p-3 w-40">Status (Coluna)</th>
                            <th class="p-3 w-40">Pessoa / Responsável</th>
                            <th class="p-3 w-28">Prioridade</th>
                            <th class="p-3 w-32">Prazo</th>
                            <th class="p-3 min-w-[150px]">Tags / Observações</th>
                            <th class="p-3 w-16 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="table-body" class="divide-y divide-wine-100 font-medium text-wine-950">
                        <!-- Spreadsheet rows dynamically rendered here -->
                    </tbody>
                </table>
            </div>
            <div id="table-empty" class="hidden p-10 text-center text-wine-400">
                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                <p class="font-medium">Nenhuma tarefa na planilha.</p>
                <button onclick="openTaskModal()" class="mt-3 bg-wine-800 hover:bg-wine-900 text-white text-xs px-4 py-2 rounded-xl font-bold transition-all">
                    + Adicionar Primeira Tarefa
                </button>
            </div>
        </div>

    </main>

    <!-- TASK FORM MODAL -->
    <div id="task-modal" class="fixed inset-0 bg-wine-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-2xl w-full border border-wine-200 shadow-2xl overflow-hidden transform transition-all">

            <div class="bg-wine-950 text-white px-6 py-4 flex items-center justify-between border-b border-wine-900">
                <h3 id="modal-title-text" class="text-lg font-bold flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-wine-300"></i> Editar Tarefa
                </h3>
                <button onclick="closeTaskModal()" class="text-wine-300 hover:text-white text-lg transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="task-form" onsubmit="saveTaskForm(event)" class="p-6 space-y-4">
                <input type="hidden" id="modal-task-id">

                <div>
                    <label class="block text-xs font-bold text-wine-950 uppercase tracking-wider mb-1">Título da Tarefa *</label>
                    <input type="text" id="modal-task-title" required placeholder="Digite o que precisa ser feito..." class="w-full px-3.5 py-2.5 border border-wine-200 rounded-xl text-sm font-semibold focus:outline-none focus:border-wine-800 focus:ring-1 focus:ring-wine-800">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-wine-950 uppercase tracking-wider mb-1">Status / Coluna</label>
                        <select id="modal-task-column" class="w-full px-3.5 py-2.5 border border-wine-200 rounded-xl text-sm font-medium focus:outline-none focus:border-wine-800">
                            <!-- Injected dynamically -->
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-wine-950 uppercase tracking-wider mb-1">Prioridade</label>
                        <select id="modal-task-priority" class="w-full px-3.5 py-2.5 border border-wine-200 rounded-xl text-sm font-medium focus:outline-none focus:border-wine-800">
                            <option value="Baixa">Baixa</option>
                            <option value="Média">Média</option>
                            <option value="Alta">Alta</option>
                            <option value="Urgente">Urgente</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-wine-950 uppercase tracking-wider mb-1">Pessoa / Responsável</label>
                        <input type="text" id="modal-task-assignee" placeholder="Quem vai fazer..." class="w-full px-3.5 py-2.5 border border-wine-200 rounded-xl text-sm font-medium focus:outline-none focus:border-wine-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-wine-950 uppercase tracking-wider mb-1">Data Limite / Prazo</label>
                        <input type="date" id="modal-task-date" class="w-full px-3.5 py-2.5 border border-wine-200 rounded-xl text-sm font-medium focus:outline-none focus:border-wine-800">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-wine-950 uppercase tracking-wider mb-1">Tags (separadas por vírgula)</label>
                    <input type="text" id="modal-task-tags" placeholder="Urgente, Projeto A, Ideia" class="w-full px-3.5 py-2.5 border border-wine-200 rounded-xl text-sm font-medium focus:outline-none focus:border-wine-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-wine-950 uppercase tracking-wider mb-1">Descrição Detalhada / Notas</label>
                    <textarea id="modal-task-desc" rows="3" placeholder="Escreva observações, detalhes ou notas da tarefa..." class="w-full px-3.5 py-2.5 border border-wine-200 rounded-xl text-sm font-medium focus:outline-none focus:border-wine-800"></textarea>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-wine-100">
                    <button type="button" id="modal-btn-delete" onclick="deleteCurrentTask()" class="text-xs font-bold text-red-600 hover:text-red-800 flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-red-50">
                        <i class="fa-solid fa-trash"></i> Excluir Tarefa
                    </button>

                    <div class="flex items-center gap-3">
                        <button type="button" onclick="closeTaskModal()" class="px-4 py-2 text-xs font-bold text-wine-800 hover:bg-wine-50 rounded-xl">Cancelar</button>
                        <button type="submit" class="bg-wine-950 hover:bg-wine-900 text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition-all">Salvar Alterações</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- COLUMN MANAGEMENT MODAL -->
    <div id="column-modal" class="fixed inset-0 bg-wine-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full border border-wine-200 shadow-2xl overflow-hidden">
            <div class="bg-wine-950 text-white px-6 py-4 flex items-center justify-between">
                <h3 class="text-base font-bold"><i class="fa-solid fa-columns mr-2"></i> Gerenciar Colunas</h3>
                <button onclick="closeColumnModal()" class="text-wine-300 hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex gap-2">
                    <input type="text" id="new-column-title" placeholder="Nome da nova coluna..." class="flex-1 px-3.5 py-2 border border-wine-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-wine-800">
                    <button onclick="addNewColumn()" class="bg-wine-800 hover:bg-wine-900 text-white px-4 py-2 rounded-xl text-xs font-bold">Adicionar</button>
                </div>
                <div id="column-list" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    <!-- Column list dynamically rendered -->
                </div>
            </div>
        </div>
    </div>

    <!-- NOTIFICATION / UI MESSAGE BOX -->
    <div id="message-box" class="fixed bottom-5 right-5 bg-wine-950 text-white text-xs px-4 py-3 rounded-2xl shadow-xl z-50 hidden flex items-center gap-3 border border-wine-800">
        <i id="msg-icon" class="fa-solid fa-circle-info text-wine-300"></i>
        <span id="msg-text">Mensagem...</span>
    </div>

    <script>
        // Default Column setup (Mapeadas diretamente para as opções ENUM do Banco de Dados)
        const DEFAULT_COLUMNS = [{
                id: 'ideias',
                title: 'Ideias / Backlog',
                color: '#821f35'
            },
            {
                id: 'a_fazer',
                title: 'A Fazer',
                color: '#ba2e4d'
            },
            {
                id: 'em_progresso',
                title: 'Em Progresso',
                color: '#d34b68'
            },
            {
                id: 'em_revisao',
                title: 'Em Revisão',
                color: '#9b223c'
            },
            {
                id: 'concluida',
                title: 'Concluído',
                color: '#10b981'
            }
        ];

        // Application State
        let appData = {
            columns: DEFAULT_COLUMNS,
            tasks: []
        };

        let activeView = 'kanban'; // 'kanban' or 'table'
        let draggedTaskId = null;

        window.addEventListener('DOMContentLoaded', () => {
            loadAppData();
        });

        // Load data from MySQL Database via PHP API
        async function loadAppData() {
            try {
                const response = await fetch('api_kanban.php?action=list');
                const result = await response.json();
                if (result.success) {
                    appData.tasks = result.tasks;
                    renderApp();
                } else {
                    showNotification('Erro ao carregar do banco: ' + result.error, 'fa-solid fa-triangle-exclamation');
                }
            } catch (err) {
                console.error('Erro ao conectar com a API PHP:', err);
                showNotification('Erro de conexão com o banco.', 'fa-solid fa-triangle-exclamation');
            }
        }

        // Master Render Function
        function renderApp() {
            renderMetrics();
            if (activeView === 'kanban') {
                renderKanban();
            } else {
                renderTable();
            }
        }

        // Custom Notification Box
        function showNotification(text, iconClass = 'fa-solid fa-circle-check') {
            const box = document.getElementById('message-box');
            const txt = document.getElementById('msg-text');
            const icon = document.getElementById('msg-icon');

            txt.innerText = text;
            icon.className = iconClass + ' text-wine-300';
            box.classList.remove('hidden');

            setTimeout(() => {
                box.classList.add('hidden');
            }, 3500);
        }

        // Switch between Kanban & Table views
        function switchView(view) {
            activeView = view;
            const btnKanban = document.getElementById('btn-view-kanban');
            const btnTable = document.getElementById('btn-view-table');
            const kanbanContainer = document.getElementById('kanban-view-container');
            const tableContainer = document.getElementById('table-view-container');

            if (view === 'kanban') {
                btnKanban.className = "flex items-center space-x-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-wine-950 shadow-sm";
                btnTable.className = "flex items-center space-x-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-wine-200 hover:text-white hover:bg-wine-800/50";
                kanbanContainer.classList.remove('hidden');
                tableContainer.classList.add('hidden');
            } else {
                btnTable.className = "flex items-center space-x-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-wine-950 shadow-sm";
                btnKanban.className = "flex items-center space-x-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-wine-200 hover:text-white hover:bg-wine-800/50";
                tableContainer.classList.remove('hidden');
                kanbanContainer.classList.add('hidden');
            }
            renderApp();
        }

        // Filter helper
        function getFilteredTasks() {
            const query = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
            const priority = document.getElementById('filter-priority')?.value || '';

            return appData.tasks.filter(t => {
                const matchesQuery = !query ||
                    (t.title && t.title.toLowerCase().includes(query)) ||
                    (t.assignee && t.assignee.toLowerCase().includes(query)) ||
                    (t.desc && t.desc.toLowerCase().includes(query)) ||
                    (t.tags && t.tags.some(tag => tag.toLowerCase().includes(query)));

                const matchesPriority = !priority || t.priority === priority;

                return matchesQuery && matchesPriority;
            });
        }

        function handleSearchFilter() {
            renderApp();
        }

        function renderMetrics() {
            const filteredTasks = getFilteredTasks();
            const total = filteredTasks.length;

            const completed = filteredTasks.filter(t => t.columnId === 'concluida').length;
            const progress = total > 0 ? Math.round((completed / total) * 100) : 0;

            document.getElementById('stat-total').innerText = total;
            document.getElementById('stat-completed').innerText = completed;
            document.getElementById('stat-progress').innerText = `${progress}%`;
        }

        function renderKanban() {
            const board = document.getElementById('kanban-board');
            const filteredTasks = getFilteredTasks();

            board.innerHTML = appData.columns.map(col => {
                const colTasks = filteredTasks.filter(t => t.columnId === col.id);

                return `
                    <div class="w-full bg-wine-50/70 border border-wine-200/80 rounded-2xl flex flex-col shadow-sm font-sans"
                         ondragover="handleDragOver(event)"
                         ondragleave="handleDragLeave(event)"
                         ondrop="handleDrop(event, '${col.id}')">
                        
                        <!-- Column Header -->
                        <div class="p-3 flex items-center justify-between border-b border-wine-100 bg-white rounded-t-2xl">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: ${col.color || '#821f35'}"></span>
                                <h3 class="font-bold text-xs text-wine-950">${escapeHtml(col.title)}</h3>
                                <span class="text-[10px] bg-wine-100 text-wine-900 font-extrabold px-1.5 py-0.5 rounded-full">${colTasks.length}</span>
                            </div>
                            <button onclick="openTaskModalForColumn('${col.id}')" class="bg-wine-100 hover:bg-wine-950 hover:text-white text-wine-900 text-xs px-2 py-1 rounded-lg transition-colors flex items-center gap-1 font-bold" title="Adicionar nesta coluna">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                            </button>
                        </div>

                        <!-- Column Card List -->
                        <div class="p-2.5 space-y-2.5 flex-1 min-h-[100px]">
                            ${colTasks.length === 0 ? `
                                <div onclick="openTaskModalForColumn('${col.id}')" class="h-20 flex flex-col items-center justify-center text-wine-400 hover:text-wine-800 border-2 border-dashed border-wine-200 hover:border-wine-500 rounded-xl cursor-pointer transition-all bg-white/40 group">
                                    <i class="fa-solid fa-plus text-lg mb-1 group-hover:scale-110 transition-transform"></i>
                                    <span class="text-[11px] font-semibold">Adicionar</span>
                                </div>
                            ` : colTasks.map(task => renderCardHTML(task)).join('')}
                        </div>

                        ${colTasks.length > 0 ? `
                            <div class="p-2 pt-0">
                                <button onclick="openTaskModalForColumn('${col.id}')" class="w-full py-1.5 px-2 bg-white hover:bg-wine-100 border border-wine-200 text-wine-900 rounded-xl text-[11px] font-bold flex items-center justify-center gap-1.5 transition-all shadow-sm">
                                    <i class="fa-solid fa-plus text-[10px]"></i> Adicionar
                                </button>
                            </div>
                        ` : ''}

                    </div>
                `;
            }).join('') + `
                <!-- Quick Add Column Button -->
                <button onclick="openColumnModal()" class="w-full h-20 border-2 border-dashed border-wine-200 hover:border-wine-800 rounded-2xl flex items-center justify-center text-wine-700 hover:text-wine-950 transition-all font-semibold text-xs gap-2 bg-white/50 hover:bg-white">
                    <i class="fa-solid fa-square-plus text-base text-wine-800"></i>
                    Nova Coluna
                </button>
            `;
        }

        // Render individual Kanban Card
        function renderCardHTML(task) {
            let priorityBg = 'bg-gray-100 text-gray-700';
            if (task.priority === 'Urgente') priorityBg = 'bg-red-100 text-red-800 font-extrabold';
            else if (task.priority === 'Alta') priorityBg = 'bg-wine-100 text-wine-900 font-bold';
            else if (task.priority === 'Média') priorityBg = 'bg-amber-100 text-amber-900 font-medium';

            return `
                <div draggable="true" 
                     ondragstart="handleDragStart(event, '${task.id}')"
                     ondragend="handleDragEnd(event)"
                     onclick="openTaskModal('${task.id}')"
                     class="bg-white p-3 rounded-xl border border-wine-100 shadow-sm hover:shadow-md transition-all cursor-grab active:cursor-grabbing group">
                    
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="text-xs ${priorityBg} px-2 py-0.5 rounded-md text-[10px] uppercase tracking-wider font-semibold">
                            ${escapeHtml(task.priority || 'Baixa')}
                        </span>
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                            <button onclick="event.stopPropagation(); openTaskModal('${task.id}')" class="text-wine-600 hover:text-wine-950 p-1">
                                <i class="fa-solid fa-pen text-[10px]"></i>
                            </button>
                            <button onclick="event.stopPropagation(); deleteTaskDirect('${task.id}')" class="text-red-500 hover:text-red-800 p-1">
                                <i class="fa-solid fa-trash text-[10px]"></i>
                            </button>
                        </div>
                    </div>

                    <h4 class="text-xs font-bold text-wine-950 mb-1.5 line-clamp-2">${escapeHtml(task.title)}</h4>
                    
                    ${task.desc ? `<p class="text-[11px] text-wine-800/80 mb-2 line-clamp-2 font-normal whitespace-pre-line">${escapeHtml(task.desc)}</p>` : ''}

                    ${task.tags && task.tags.length ? `
                        <div class="flex flex-wrap gap-1 mb-2">
                            ${task.tags.map(tag => `
                                <span class="text-[9px] bg-wine-50 text-wine-900 px-1.5 py-0.5 rounded border border-wine-100">${escapeHtml(tag)}</span>
                            `).join('')}
                        </div>
                    ` : ''}

                    <div class="flex items-center justify-between pt-2 border-t border-wine-50 text-[10px] text-wine-800/70 font-medium">
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-user text-[9px] text-wine-600"></i>
                            ${escapeHtml(task.assignee || 'Sem responsável')}
                        </span>
                        ${task.date ? `
                            <span class="flex items-center gap-1 font-semibold text-wine-900">
                                <i class="fa-regular fa-calendar text-[9px]"></i>
                                ${formatDate(task.date)}
                            </span>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        function renderTable() {
            const tableBody = document.getElementById('table-body');
            const tableEmpty = document.getElementById('table-empty');
            const filteredTasks = getFilteredTasks();

            if (filteredTasks.length === 0) {
                tableBody.innerHTML = '';
                tableEmpty.classList.remove('hidden');
                return;
            }

            tableEmpty.classList.add('hidden');

            tableBody.innerHTML = filteredTasks.map((task, idx) => {
                return `
                    <tr class="hover:bg-wine-50/40 transition-colors">
                        <td class="p-3 text-center text-wine-400 font-mono text-[10px]">${idx + 1}</td>
                        
                        <!-- Title Cell -->
                        <td class="p-2">
                            <input type="text" value="${escapeHtml(task.title)}" 
                                   placeholder="Nome da tarefa..."
                                   onchange="updateTaskField('${task.id}', 'title', this.value)" 
                                   class="table-cell-input font-bold text-wine-950">
                        </td>

                        <!-- Column / Status Select -->
                        <td class="p-2">
                            <select onchange="updateTaskField('${task.id}', 'columnId', this.value)" class="table-cell-input text-xs font-semibold text-wine-900">
                                ${appData.columns.map(col => `
                                    <option value="${col.id}" ${task.columnId === col.id ? 'selected' : ''}>
                                        ${escapeHtml(col.title)}
                                    </option>
                                `).join('')}
                            </select>
                        </td>

                        <!-- Assignee Cell -->
                        <td class="p-2">
                            <input type="text" value="${escapeHtml(task.assignee || '')}" 
                                   placeholder="Nome da pessoa..."
                                   onchange="updateTaskField('${task.id}', 'assignee', this.value)" 
                                   class="table-cell-input text-wine-800">
                        </td>

                        <!-- Priority Select -->
                        <td class="p-2">
                            <select onchange="updateTaskField('${task.id}', 'priority', this.value)" class="table-cell-input font-bold text-wine-900">
                                <option value="Baixa" ${task.priority === 'Baixa' ? 'selected' : ''}>Baixa</option>
                                <option value="Média" ${task.priority === 'Média' ? 'selected' : ''}>Média</option>
                                <option value="Alta" ${task.priority === 'Alta' ? 'selected' : ''}>Alta</option>
                                <option value="Urgente" ${task.priority === 'Urgente' ? 'selected' : ''}>Urgente</option>
                            </select>
                        </td>

                        <!-- Date Cell -->
                        <td class="p-2">
                            <input type="date" value="${task.date || ''}" 
                                   onchange="updateTaskField('${task.id}', 'date', this.value)" 
                                   class="table-cell-input text-wine-800">
                        </td>

                        <!-- Tags Cell -->
                        <td class="p-2">
                            <input type="text" value="${escapeHtml((task.tags || []).join(', '))}" 
                                   placeholder="tag1, tag2"
                                   onchange="updateTaskTags('${task.id}', this.value)" 
                                   class="table-cell-input text-wine-800">
                        </td>

                        <!-- Actions -->
                        <td class="p-2 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="openTaskModal('${task.id}')" title="Editar Detalhes" class="p-1.5 text-wine-700 hover:text-wine-950 rounded hover:bg-wine-100">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button onclick="deleteTaskDirect('${task.id}')" title="Excluir" class="p-1.5 text-red-600 hover:text-red-800 rounded hover:bg-red-50">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Direct field editing saved to Database
        async function updateTaskField(taskId, field, value) {
            const task = appData.tasks.find(t => t.id === taskId);
            if (task) {
                task[field] = value;

                try {
                    await fetch('api_kanban.php?action=update_field', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: taskId,
                            field: field,
                            value: value
                        })
                    });
                } catch (err) {
                    console.error('Erro ao atualizar campo no BD:', err);
                }

                renderMetrics();
                if (field === 'columnId' && activeView === 'kanban') {
                    renderKanban();
                }
            }
        }

        async function updateTaskTags(taskId, value) {
            const tags = value.split(',').map(s => s.trim()).filter(Boolean);
            const task = appData.tasks.find(t => t.id === taskId);
            if (task) {
                task.tags = tags;
                try {
                    await fetch('api_kanban.php?action=update_field', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: taskId,
                            field: 'tags',
                            value: tags
                        })
                    });
                } catch (err) {
                    console.error('Erro ao atualizar tags:', err);
                }
            }
        }

        // Drag and Drop Logic
        function handleDragStart(e, taskId) {
            draggedTaskId = taskId;
            e.dataTransfer.effectAllowed = 'move';
            e.target.classList.add('opacity-50');
        }

        function handleDragEnd(e) {
            e.target.classList.remove('opacity-50');
            draggedTaskId = null;
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.currentTarget.classList.add('drag-over');
        }

        function handleDragLeave(e) {
            e.currentTarget.classList.remove('drag-over');
        }

        async function handleDrop(e, targetColId) {
            e.preventDefault();
            e.currentTarget.classList.remove('drag-over');

            if (!draggedTaskId) return;

            const task = appData.tasks.find(t => t.id === draggedTaskId);
            if (task && task.columnId !== targetColId) {
                task.columnId = targetColId;
                renderApp();
                await updateTaskField(draggedTaskId, 'columnId', targetColId);
            }
        }

        // Open Modal for New or Existing Task
        function openTaskModal(taskId = null) {
            openTaskModalForColumn(null, taskId);
        }

        function openTaskModalForColumn(colId = null, taskId = null) {
            const modal = document.getElementById('task-modal');
            const colSelect = document.getElementById('modal-task-column');
            const btnDelete = document.getElementById('modal-btn-delete');
            const titleText = document.getElementById('modal-title-text');

            // Populate Column Options
            colSelect.innerHTML = appData.columns.map(c => `
                <option value="${c.id}">${escapeHtml(c.title)}</option>
            `).join('');

            if (taskId) {
                const task = appData.tasks.find(t => t.id === taskId);
                if (!task) return;

                document.getElementById('modal-task-id').value = task.id;
                document.getElementById('modal-task-title').value = task.title || '';
                document.getElementById('modal-task-column').value = task.columnId || appData.columns[0].id;
                document.getElementById('modal-task-priority').value = task.priority || 'Baixa';
                document.getElementById('modal-task-assignee').value = task.assignee || '';
                document.getElementById('modal-task-date').value = task.date || '';
                document.getElementById('modal-task-tags').value = (task.tags || []).join(', ');
                document.getElementById('modal-task-desc').value = task.desc || '';

                btnDelete.classList.remove('hidden');
                titleText.innerHTML = `<i class="fa-solid fa-pen-to-square text-wine-300"></i> Editar Tarefa`;
            } else {
                document.getElementById('task-form').reset();
                document.getElementById('modal-task-id').value = '';
                if (colId) {
                    colSelect.value = colId;
                } else if (appData.columns.length > 0) {
                    colSelect.value = appData.columns[0].id;
                }
                btnDelete.classList.add('hidden');
                titleText.innerHTML = `<i class="fa-solid fa-circle-plus text-wine-300"></i> Nova Tarefa`;
            }

            modal.classList.remove('hidden');
        }

        function closeTaskModal() {
            document.getElementById('task-modal').classList.add('hidden');
        }

        // Save Task Form to Database
        async function saveTaskForm(e) {
            e.preventDefault();

            const taskId = document.getElementById('modal-task-id').value;
            const title = document.getElementById('modal-task-title').value.trim();
            const columnId = document.getElementById('modal-task-column').value;
            const priority = document.getElementById('modal-task-priority').value;
            const assignee = document.getElementById('modal-task-assignee').value.trim();
            const date = document.getElementById('modal-task-date').value;
            const tagsStr = document.getElementById('modal-task-tags').value;
            const desc = document.getElementById('modal-task-desc').value.trim();

            const tags = tagsStr.split(',').map(s => s.trim()).filter(Boolean);

            const payload = {
                id: taskId || null,
                title,
                columnId,
                priority,
                assignee,
                date,
                tags,
                desc
            };

            try {
                const res = await fetch('api_kanban.php?action=save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();

                if (result.success) {
                    closeTaskModal();
                    await loadAppData();
                    showNotification('Tarefa salva no banco com sucesso!');
                } else {
                    showNotification('Erro ao salvar: ' + result.error, 'fa-solid fa-triangle-exclamation');
                }
            } catch (err) {
                console.error(err);
                showNotification('Erro ao conectar ao banco.', 'fa-solid fa-triangle-exclamation');
            }
        }

        function deleteCurrentTask() {
            const taskId = document.getElementById('modal-task-id').value;
            if (taskId) {
                deleteTaskDirect(taskId);
                closeTaskModal();
            }
        }

        // Delete Task from Database
        async function deleteTaskDirect(taskId) {
            try {
                const res = await fetch('api_kanban.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: taskId
                    })
                });
                const result = await res.json();
                if (result.success) {
                    await loadAppData();
                    showNotification('Tarefa excluída do banco.');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // Column Management
        function openColumnModal() {
            renderColumnList();
            document.getElementById('column-modal').classList.remove('hidden');
        }

        function closeColumnModal() {
            document.getElementById('column-modal').classList.add('hidden');
        }

        function renderColumnList() {
            const list = document.getElementById('column-list');
            list.innerHTML = appData.columns.map((col, idx) => `
                <div class="flex items-center justify-between p-2.5 bg-wine-50 rounded-xl border border-wine-100">
                    <span class="text-xs font-bold text-wine-950">${idx + 1}. ${escapeHtml(col.title)}</span>
                </div>
            `).join('');
        }

        function addNewColumn() {
            showNotification('Para adicionar colunas personalizadas no banco, adicione valores ao enum status.');
        }

        function exportJSON() {
            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(appData, null, 2));
            const downloadAnchor = document.createElement('a');
            downloadAnchor.setAttribute("href", dataStr);
            downloadAnchor.setAttribute("download", `vinho_kanban_backup_${new Date().toISOString().slice(0,10)}.json`);
            document.body.appendChild(downloadAnchor);
            downloadAnchor.click();
            downloadAnchor.remove();
            showNotification('Backup JSON exportado!');
        }

        function importJSON(event) {
            const fileReader = new FileReader();
            fileReader.onload = async function(e) {
                try {
                    const parsed = JSON.parse(e.target.result);
                    if (parsed && parsed.tasks) {
                        for (const task of parsed.tasks) {
                            await fetch('api_kanban.php?action=save', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(task)
                            });
                        }
                        await loadAppData();
                        showNotification('Dados importados com sucesso para o Banco!');
                    }
                } catch (err) {
                    showNotification('Erro ao importar JSON.', 'fa-solid fa-triangle-exclamation');
                }
            };
            if (event.target.files[0]) {
                fileReader.readAsText(event.target.files[0]);
            }
        }

        // Helpers
        function formatDate(dateString) {
            if (!dateString) return '';
            const parts = dateString.split('-');
            if (parts.length === 3) {
                return `${parts[2]}/${parts[1]}`;
            }
            return dateString;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>

</html>
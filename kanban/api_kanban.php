<?php
header('Content-Type: application/json; charset=utf-8');

// Configurações de Conexão com o Banco de Dados
$host = 'localhost';
$db   = 'restaurante';
$user = 'root';
$pass = ''; // Altere aqui se o seu MySQL tiver senha
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao conectar no banco: ' . $e->getMessage()]);
    exit;
}

// Inicia sessão ou define gestor padrão (exemplo: id_gestor = 5 do seu SQL)
session_start();
$id_gestor = $_SESSION['id_gestor'] ?? 5;

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Converte enums do BD para a interface
function mapPriorityToFrontend($p) {
    switch (strtolower($p)) {
        case 'baixa': return 'Baixa';
        case 'media': return 'Média';
        case 'alta': return 'Alta';
        case 'urgente': return 'Urgente';
        default: return 'Média';
    }
}

function mapPriorityToDb($p) {
    switch (mb_strtolower($p)) {
        case 'baixa': return 'baixa';
        case 'média':
        case 'media': return 'media';
        case 'alta': return 'alta';
        case 'urgente': return 'urgente';
        default: return 'media';
    }
}

// 1. LISTAR TAREFAS DO BANCO
if ($method === 'GET' && $action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM tb_kanban_tarefas WHERE id_gestor = ? ORDER BY id_tarefa DESC");
    $stmt->execute([$id_gestor]);
    $rows = $stmt->fetchAll();

    $tasks = [];
    foreach ($rows as $r) {
        $tags = [];
        if (!empty($r['tags'])) {
            $tags = array_map('trim', explode(',', $r['tags']));
        }

        $tasks[] = [
            'id' => (string)$r['id_tarefa'],
            'title' => $r['titulo'],
            'columnId' => $r['status'],
            'priority' => mapPriorityToFrontend($r['prioridade']),
            'assignee' => $r['responsavel'] ?? '',
            'date' => $r['data_limite'] ?? '',
            'tags' => $tags,
            'desc' => $r['descricao'] ?? ''
        ];
    }

    echo json_encode(['success' => true, 'tasks' => $tasks]);
    exit;
}

// 2. SALVAR / CRIAR OU EDITAR TAREFA COMPLETA
if ($method === 'POST' && $action === 'save') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'] ?? null;
    $title = $input['title'] ?? '';
    $status = $input['columnId'] ?? 'ideias';
    $priority = mapPriorityToDb($input['priority'] ?? 'Média');
    $assignee = $input['assignee'] ?? '';
    $date = !empty($input['date']) ? $input['date'] : null;
    $tags = is_array($input['tags']) ? implode(', ', $input['tags']) : ($input['tags'] ?? '');
    $desc = $input['desc'] ?? '';

    if (empty($title)) {
        echo json_encode(['success' => false, 'error' => 'Título é obrigatório']);
        exit;
    }

    if ($id && is_numeric($id)) {
        // Atualizar existente
        $stmt = $pdo->prepare("UPDATE tb_kanban_tarefas SET 
            titulo = ?, status = ?, prioridade = ?, responsavel = ?, data_limite = ?, tags = ?, descricao = ?
            WHERE id_tarefa = ? AND id_gestor = ?");
        $stmt->execute([$title, $status, $priority, $assignee, $date, $tags, $desc, $id, $id_gestor]);
        echo json_encode(['success' => true, 'id' => (string)$id]);
    } else {
        // Inserir nova
        $stmt = $pdo->prepare("INSERT INTO tb_kanban_tarefas 
            (id_gestor, titulo, status, prioridade, responsavel, data_limite, tags, descricao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_gestor, $title, $status, $priority, $assignee, $date, $tags, $desc]);
        $newId = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'id' => (string)$newId]);
    }
    exit;
}

// 3. ATUALIZAR CAMPO INDIVIDUAL (RÁPIDO / PLANILHA / DRAG & DROP)
if ($method === 'POST' && $action === 'update_field') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    $field = $input['field'] ?? '';
    $value = $input['value'] ?? '';

    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID inválido']);
        exit;
    }

    $colMap = [
        'title' => 'titulo',
        'columnId' => 'status',
        'priority' => 'prioridade',
        'assignee' => 'responsavel',
        'date' => 'data_limite',
        'tags' => 'tags',
        'desc' => 'descricao'
    ];

    if (!isset($colMap[$field])) {
        echo json_encode(['success' => false, 'error' => 'Campo inválido']);
        exit;
    }

    $dbCol = $colMap[$field];

    if ($field === 'priority') {
        $value = mapPriorityToDb($value);
    } elseif ($field === 'tags' && is_array($value)) {
        $value = implode(', ', $value);
    } elseif ($field === 'date' && empty($value)) {
        $value = null;
    }

    $stmt = $pdo->prepare("UPDATE tb_kanban_tarefas SET `$dbCol` = ? WHERE id_tarefa = ? AND id_gestor = ?");
    $stmt->execute([$value, $id, $id_gestor]);

    echo json_encode(['success' => true]);
    exit;
}

// 4. EXCLUIR TAREFA
if ($method === 'POST' && $action === 'delete') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM tb_kanban_tarefas WHERE id_tarefa = ? AND id_gestor = ?");
        $stmt->execute([$id, $id_gestor]);
    }

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Ação inválida']);
<?php
// api.php - IPMI Raw Command Dictionary API

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// 資料庫連線設定
$db_host = 'ipmi';
$db_name = 'ipmi_dictionary';
$db_user = 'one';
$db_pass = '1234';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
}

// 處理請求
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            listCommands();
            break;
        case 'get':
            getCommand($_GET['id'] ?? 0);
            break;
        case 'search':
            searchCommands($_GET['term'] ?? '', $_GET['category'] ?? '');
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            createCommand();
            break;
        case 'update':
            updateCommand($_POST['id'] ?? 0);
            break;
        case 'delete':
            deleteCommand($_POST['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

// 列出所有命令
function listCommands() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM ipmi_commands ORDER BY netfn, cmd");
        $commands = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($commands);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching commands: ' . $e->getMessage()]);
    }
}

// 取得單一命令
function getCommand($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM ipmi_commands WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $command = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($command) {
            echo json_encode($command);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Command not found']);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching command: ' . $e->getMessage()]);
    }
}

// 搜尋命令
function searchCommands($term, $category) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM ipmi_commands WHERE 1=1";
        $params = [];
        
        if (!empty($term)) {
            $sql .= " AND (command_name LIKE :term OR netfn LIKE :term OR cmd LIKE :term OR description LIKE :term)";
            $params['term'] = "%$term%";
        }
        
        if (!empty($category)) {
            $sql .= " AND category = :category";
            $params['category'] = $category;
        }
        
        $sql .= " ORDER BY netfn, cmd";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $commands = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($commands);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error searching commands: ' . $e->getMessage()]);
    }
}

// 建立新命令
function createCommand() {
    global $pdo;
    
    $required_fields = ['netfn', 'cmd', 'command_name', 'category'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            return;
        }
    }
    
    try {
        // 檢查是否已存在相同的 NetFn 和 Cmd
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ipmi_commands WHERE netfn = :netfn AND cmd = :cmd");
        $stmt->execute(['netfn' => $_POST['netfn'], 'cmd' => $_POST['cmd']]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Command with this NetFn and Cmd already exists']);
            return;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO ipmi_commands (netfn, cmd, command_name, category, description, request_format, response_format, example)
            VALUES (:netfn, :cmd, :command_name, :category, :description, :request_format, :response_format, :example)
        ");
        
        $stmt->execute([
            'netfn' => $_POST['netfn'],
            'cmd' => $_POST['cmd'],
            'command_name' => $_POST['command_name'],
            'category' => $_POST['category'],
            'description' => $_POST['description'] ?? null,
            'request_format' => $_POST['request_format'] ?? null,
            'response_format' => $_POST['response_format'] ?? null,
            'example' => $_POST['example'] ?? null
        ]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating command: ' . $e->getMessage()]);
    }
}

// 更新命令
function updateCommand($id) {
    global $pdo;
    
    $required_fields = ['netfn', 'cmd', 'command_name', 'category'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            return;
        }
    }
    
    try {
        // 檢查是否已存在相同的 NetFn 和 Cmd（排除自己）
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ipmi_commands WHERE netfn = :netfn AND cmd = :cmd AND id != :id");
        $stmt->execute(['netfn' => $_POST['netfn'], 'cmd' => $_POST['cmd'], 'id' => $id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Another command with this NetFn and Cmd already exists']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE ipmi_commands 
            SET netfn = :netfn, cmd = :cmd, command_name = :command_name, category = :category, 
                description = :description, request_format = :request_format, 
                response_format = :response_format, example = :example, updated_at = NOW()
            WHERE id = :id
        ");
        
        $stmt->execute([
            'id' => $id,
            'netfn' => $_POST['netfn'],
            'cmd' => $_POST['cmd'],
            'command_name' => $_POST['command_name'],
            'category' => $_POST['category'],
            'description' => $_POST['description'] ?? null,
            'request_format' => $_POST['request_format'] ?? null,
            'response_format' => $_POST['response_format'] ?? null,
            'example' => $_POST['example'] ?? null
        ]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Command not found or no changes made']);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating command: ' . $e->getMessage()]);
    }
}

// 刪除命令
function deleteCommand($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM ipmi_commands WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Command not found']);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting command: ' . $e->getMessage()]);
    }
}
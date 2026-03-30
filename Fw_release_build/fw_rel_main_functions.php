<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'update_jenkins_status':
        $script = __DIR__ . '/script/fw_rel_get_jenkins_status.sh';
        $output = shell_exec("bash " . escapeshellarg($script) . " 2>&1");
        echo json_encode(['success' => true, 'message' => 'Jenkins status updated', 'output' => $output]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
        break;
}

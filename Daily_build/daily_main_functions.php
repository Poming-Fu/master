<?php
require_once '../DB/db_operations_all.php';
require_once '../common/common.php';


if (isset($_GET['action'])) {
    $action = $_GET['action'];
    try {
        switch ($action) {
                case 'get_filter_data':
                    header('Content-Type: application/json');
                    
                    // 移除可能的破折號
                    $start_date = str_replace('-', '', $_POST['start_date'] ?? '');
                    $end_date = str_replace('-', '', $_POST['end_date'] ?? '');
                    $branch = $_POST['branch'] ?? '';
                    $status = $_POST['status'] ?? '';
            
                    // Debug 接收到的參數
                    error_log("Received parameters - Start: {$start_date}, End: {$end_date}, Branch: {$branch}, Status: {$status}");
            
                    $builds = daily_repository::scan_build_directories($branch, $start_date, $end_date, $status);
                    echo json_encode($builds);
                    break;

            case 'view_log':
                if (isset($_GET['path'])) {
                    $file_path = $_GET['path'];
                    
                    if (file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'txt') {
                        header('Content-Type: text/plain');
                        readfile($file_path);
                    } else {
                        throw new Exception("File not found or invalid file type: $file_path");
                    }
                }
                break;
            
            case 'download_file':
                if (isset($_GET['path'])) {
                    $file_path = $_GET['path'];
                    if (file_exists($file_path)) {
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                        header('Content-Length: ' . filesize($file_path));
                        readfile($file_path);
                    } else {
                        throw new Exception("File not found: $file_path");
                    }
                }
                break;

            // 其他 API...
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
 }
?>


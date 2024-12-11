<?php
require_once '../DB/db_operations_all.php';
require_once '../common/common.php';


if (isset($_GET['action'])) {
    $action = $_GET['action'];
    try {
        switch ($action) {
            // 返回 HTML
            case 'get_filter_data':
                $filters = [
                    'branch' => $_POST['branch'] ?? '',
                    'status' => $_POST['status'] ?? '',
                    'date' => $_POST['date'] ?? ''
                ];
                $data = daily_repository::query_daily_info($filters);
                require 'template/daily_template.php';
                break;
 
            // 查看日誌
            case 'view_log':
                if (isset($_GET['path'])) {
                    $base_path = '/mnt/DB/dailybuild_obmc/';
                    $file_path = $base_path . $_GET['path'];
                    
                    if (file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'txt') {
                        header('Content-Type: text/plain');
                        readfile($file_path);
                    } else {
                        throw new Exception("File not found or invalid file type: $file_path");
                    }
                }
                break;
 
            // 下載檔案
            case 'download_file':
                if (isset($_GET['path'])) {
                    $base_path = '/mnt/DB/dailybuild_obmc/';
                    $file_path = $base_path . $_GET['path'];
                    
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


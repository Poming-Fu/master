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

            case 'view_build_log':
                if (isset($_GET['path'])) {
                    $file_path = $_GET['path'];
                    
                    if (file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'txt') {
                        header('Content-Type: text/plain');
                        
                        // 完整讀取檔案 暫停用
                        //readfile($file_path);
                        
                        // 使用 tail 命令讀取最後 1000 行
                        $content = shell_exec("tail -n 1000 " . escapeshellarg($file_path));
                        echo "=== Showing last 1000 lines of the log ===\n";
                        echo $content;
                    } else {
                        throw new Exception("File not found or invalid file type: $file_path");
                    }
                }
                break;
                case 'view_git_log':
                    if (isset($_GET['path'])) {
                        $file_path = $_GET['path'];
                        
                        if (file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'txt') {
                            header('Content-Type: text/html; charset=utf-8');
                            
                            // 先輸出基本 HTML 結構
                            echo '<!DOCTYPE html>
                            <html>
                            <head>
                                <style>
                                    body { font-family: monospace; }
                                    .hash { color:rgb(123, 3, 153); }
                                    .date { color: #0066cc; }
                                    .author { color: #2da44e; }
                                    .message { color: #cf222e; }
                                </style>
                            </head>
                            <body><pre>';
                                                      
                            // 讀取檔案內容
                            $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                     
                            foreach ($lines as $line_number => $line) {
                                if (strpos($line, '|') !== false) {
                                    // 有水管符號的新格式
                                    $parts = explode("|", trim($line));
                                    if (count($parts) === 4) {
                                        $hash = trim($parts[0]);
                                        $date = trim($parts[1]);
                                        $author = trim($parts[2]);
                                        $message = trim($parts[3]);
                                        
                                        echo "<span class='hash'>$hash</span> <span class='date'><$date></span> <span class='author'>$author</span> <span class='message'>$message</span>\n";
                                    }
                                } else {
                                    // 沒有水管符號的舊格式，直接輸出
                                    echo htmlspecialchars($line) . "\n";
                                }
                            }
                            
                            echo '</pre></body></html>';
                        } else {
                            echo "File check failed: " . htmlspecialchars($file_path);
                            if (!file_exists($file_path)) {
                                echo " (File does not exist)";
                            }
                            if (pathinfo($file_path, PATHINFO_EXTENSION) !== 'txt') {
                                echo " (Not a .txt file)";
                            }
                            throw new Exception("File not found or invalid file type: $file_path");
                        }
                    } else {
                        echo "No path parameter provided";
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


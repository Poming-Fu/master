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
                $start_date = str_replace('-', '', $_GET['start_date'] ?? '');
                $end_date = str_replace('-', '', $_GET['end_date'] ?? '');
                $branch = $_GET['branch'] ?? '';
                $status = $_GET['status'] ?? '';
        
                // Debug 接收到的參數
                error_log("Received parameters - Start: {$start_date}, End: {$end_date}, Branch: {$branch}, Status: {$status}");
        
                $builds = daily_repository::scan_build_directories($branch, $start_date, $end_date, $status);
                echo json_encode($builds);
                break;

            case 'view_build_log':
                if (isset($_GET['path'])) {
                    $file_path = $_GET['path'];
                    $extension = pathinfo($file_path, PATHINFO_EXTENSION);
                    
                    // 允許 txt 和 log 檔案
                    if (file_exists($file_path) && in_array($extension, ['txt', 'log'])) {
                        header('Content-Type: text/plain');
                        
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

            // ========== Mail Report API ==========
            case 'list_mail_reports':
                header('Content-Type: application/json');
                $report_dir = '/mnt/DB/dailybuild_all_target/mail_report';
                $reports = [];

                // 支援日期範圍和筆數限制
                $start_date = $_GET['start_date'] ?? '';
                $end_date = $_GET['end_date'] ?? '';
                $limit = intval($_GET['limit'] ?? 0);

                if (is_dir($report_dir)) {
                    $files = glob($report_dir . '/*.html');
                    foreach ($files as $file) {
                        $filename = basename($file);
                        // 解析檔名: YYYYMMDD.html (只接受日期格式的檔名)
                        if (preg_match('/^(\d{8})\.html$/', $filename, $matches)) {
                            $file_date = $matches[1];

                            // 日期範圍篩選
                            if ($start_date && $file_date < $start_date) continue;
                            if ($end_date && $file_date > $end_date) continue;

                            $reports[] = [
                                'filename' => $filename,
                                'date' => $file_date,
                                'display_date' => substr($file_date, 0, 4) . '/' . substr($file_date, 4, 2) . '/' . substr($file_date, 6, 2),
                                'size' => filesize($file),
                                'mtime' => date('Y-m-d H:i:s', filemtime($file))
                            ];
                        }
                    }
                    // 按日期降序排列
                    usort($reports, function($a, $b) {
                        return strcmp($b['date'], $a['date']);
                    });

                    // 筆數限制
                    if ($limit > 0) {
                        $reports = array_slice($reports, 0, $limit);
                    }
                }
                echo json_encode($reports);
                break;

            case 'get_mail_report':
                $date = $_GET['date'] ?? '';
                $report_dir = '/mnt/DB/dailybuild_all_target/mail_report';
                $file_path = $report_dir . '/' . $date . '.html';

                if (file_exists($file_path) && preg_match('/^\d{8}$/', $date)) {
                    header('Content-Type: text/html; charset=utf-8');
                    readfile($file_path);
                } else {
                    header('Content-Type: text/html; charset=utf-8');
                    echo '<div style="padding: 20px; text-align: center; color: #666;">';
                    echo '<h3>Report not found</h3>';
                    echo '<p>File: ' . htmlspecialchars($date . '.html') . '</p>';
                    echo '<p>Please check if the report exists or try another date.</p>';
                    echo '</div>';
                }
                break;

            // ========== MR Check Report API ==========
            case 'list_mr_check_reports':
                header('Content-Type: application/json');
                $report_dir = '/mnt/DB/daily_MR_check_report';
                $reports = [];

                $start_date = $_GET['start_date'] ?? '';
                $end_date   = $_GET['end_date'] ?? '';
                $limit      = intval($_GET['limit'] ?? 0);

                if (is_dir($report_dir)) {
                    $files = glob($report_dir . '/*.html');
                    foreach ($files as $file) {
                        $filename = basename($file);
                        // 接受任意前綴 + YYYYMMDD.html
                        if (preg_match('/(\d{8})\.html$/', $filename, $matches)) {
                            $file_date = $matches[1];

                            if ($start_date && $file_date < $start_date) continue;
                            if ($end_date   && $file_date > $end_date)   continue;

                            $reports[] = [
                                'filename'     => $filename,
                                'date'         => $file_date,
                                'display_date' => substr($file_date, 0, 4) . '/' . substr($file_date, 4, 2) . '/' . substr($file_date, 6, 2),
                                'mtime'        => date('Y-m-d H:i:s', filemtime($file))
                            ];
                        }
                    }
                    usort($reports, function($a, $b) {
                        return strcmp($b['date'], $a['date']);
                    });
                    if ($limit > 0) {
                        $reports = array_slice($reports, 0, $limit);
                    }
                }
                echo json_encode($reports);
                break;

            case 'get_mr_check_report':
                $date       = $_GET['date'] ?? '';
                $report_dir = '/mnt/DB/daily_MR_check_report';

                if (preg_match('/^\d{8}$/', $date) && is_dir($report_dir)) {
                    $files = glob($report_dir . '/*' . $date . '.html');
                    if (!empty($files)) {
                        header('Content-Type: text/html; charset=utf-8');
                        readfile($files[0]);
                        break;
                    }
                }
                header('Content-Type: text/html; charset=utf-8');
                echo '<div style="padding: 20px; text-align: center; color: #666;">';
                echo '<h3>Report not found</h3>';
                echo '<p>File: *' . htmlspecialchars($date) . '.html</p>';
                echo '<p>Please check if the report exists or try another date.</p>';
                echo '</div>';
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


<?php
session_start();
require_once '../../DB/db_operations_all.php';
require_once '../common.php';

// 檢查用戶是否登入
common::check_login();
$username = $_SESSION['username'];

// 檢查用戶權限 - 只有 high 等級才能訪問
$user = users_repository::check_user_in_db($username);
if ($user['u_lev'] != 'high') {
    header('Location: /web1/index.php');
    exit;
}

// 取得統計資料
$conn = database_connection::get_connection();

// 總事件數
$total_events = $conn->query("SELECT COUNT(*) as count FROM users_action")->fetch_assoc()['count'];

// 今天的事件數
$today_events = $conn->query("SELECT COUNT(*) as count FROM users_action WHERE DATE(timestamp) = CURDATE()")->fetch_assoc()['count'];

// 活躍用戶數（今天）
$active_users = $conn->query("SELECT COUNT(DISTINCT u_acc) as count FROM users_action WHERE DATE(timestamp) = CURDATE()")->fetch_assoc()['count'];

// 瀏覽器分布
$browser_stats = $conn->query("SELECT browser, COUNT(*) as count FROM users_action WHERE browser IS NOT NULL GROUP BY browser ORDER BY count DESC LIMIT 5");

// 裝置類型分布
$device_stats = $conn->query("SELECT device_type, COUNT(*) as count FROM users_action WHERE device_type IS NOT NULL GROUP BY device_type ORDER BY count DESC");

// 熱門事件
$top_events = $conn->query("SELECT event_category, event_action, COUNT(*) as count FROM users_action WHERE event_category IS NOT NULL GROUP BY event_category, event_action ORDER BY count DESC LIMIT 10");

// 最近的事件（全部載入，由 DataTables 處理分頁）
$recent_events = $conn->query("SELECT * FROM users_action ORDER BY timestamp DESC LIMIT 1000");
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPMI web service - Analytics Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../../login_out/navbar.css" rel="stylesheet">
    <link href="../common.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        
        .page-header {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px 24px;
            margin-bottom: 24px;
        }
        
        .page-header h2 {
            font-size: 28px;
            font-weight: 600;
            color: var(--gray-700);
            margin: 0;
        }
        
        .stat-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card h5 {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .table-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .table-card h5 {
            padding: 16px 20px;
            margin: 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 18px;
            font-weight: 600;
        }
        
        .table-card table {
            margin: 0;
        }
        
        .table-card thead {
            background: #f9fafb;
        }
        
        .table-card th {
            font-size: 14px;
            font-weight: 600;
            padding: 12px 16px;
        }
        
        .table-card td {
            font-size: 14px;
            padding: 12px 16px;
        }
        
        .badge {
            font-size: 12px;
            padding: 4px 8px;
        }
        
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
        }

        /* DataTables 控制項位置調整 */
        .dataTables_length {
            margin-left: 20px; /* 往右移動 */
        }
    </style>
</head>
<body>

<?php include '../../login_out/navbar.php'; ?>

<div class="container-fluid px-3 py-3">
    <!-- 頁面標題 -->
    <div class="page-header">
        <h2><i class="bi bi-graph-up"></i> Analytics Dashboard</h2>
    </div>

    <!-- 統計卡片 -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="stat-card">
                <h5><i class="bi bi-activity"></i> 總事件數</h5>
                <div class="value"><?php echo number_format($total_events); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h5><i class="bi bi-calendar-day"></i> 今日事件</h5>
                <div class="value"><?php echo number_format($today_events); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h5><i class="bi bi-people"></i> 今日活躍用戶</h5>
                <div class="value"><?php echo number_format($active_users); ?></div>
            </div>
        </div>
    </div>

    <!-- 瀏覽器 & 裝置統計 -->
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="table-card">
                <h5><i class="bi bi-browser-chrome"></i> 瀏覽器分布</h5>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>瀏覽器</th>
                            <th class="text-end">次數</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $browser_stats->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['browser']); ?></td>
                            <td class="text-end"><strong><?php echo number_format($row['count']); ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="table-card">
                <h5><i class="bi bi-phone"></i> 裝置類型</h5>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>裝置</th>
                            <th class="text-end">次數</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $device_stats->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php
                                $icon = $row['device_type'] == 'mobile' ? 'phone' : ($row['device_type'] == 'tablet' ? 'tablet' : 'laptop');
                                echo "<i class='bi bi-{$icon}'></i> " . ucfirst($row['device_type']);
                                ?>
                            </td>
                            <td class="text-end"><strong><?php echo number_format($row['count']); ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 熱門事件 -->
    <div class="table-card mb-3">
        <h5><i class="bi bi-fire"></i> 熱門事件 Top 10</h5>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>類別</th>
                    <th>動作</th>
                    <th class="text-end">次數</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $top_events->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['event_category']); ?></td>
                    <td><?php echo htmlspecialchars($row['event_action']); ?></td>
                    <td class="text-end"><strong><?php echo number_format($row['count']); ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- 最近事件 -->
    <div class="table-card">
        <h5><i class="bi bi-clock-history"></i> 最近事件</h5>
        <div class="table-responsive">
            <table id="recentEventsTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 110px;">時間</th>
                        <th style="width: 90px;">用戶</th>
                        <th style="width: 120px;">類別</th>
                        <th style="width: 100px;">動作</th>
                        <th style="width: auto; min-width: 350px;">標籤</th>
                        <th style="width: 110px;">IP</th>
                        <th style="width: 90px;">瀏覽器</th>
                        <th style="width: 70px;">裝置</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $recent_events->fetch_assoc()):
                        $category = $row['event_category'] ?? $row['action'];
                        $action = $row['event_action'] ?? '';
                        $label = $row['event_label'] ?? '';
                    ?>
                    <tr>
                        <td style="white-space: nowrap;" data-order="<?php echo strtotime($row['timestamp']); ?>"><?php echo date('Y/m/d H:i:s', strtotime($row['timestamp'])); ?></td>
                        <td><?php echo htmlspecialchars($row['u_acc']); ?></td>
                        <td><?php echo htmlspecialchars($category); ?></td>
                        <td><?php echo htmlspecialchars($action); ?></td>
                        <td style="word-break: break-word;"><?php echo htmlspecialchars($label); ?></td>
                        <td><small><?php echo htmlspecialchars($row['ip_address'] ?? ''); ?></small></td>
                        <td><small><?php echo htmlspecialchars($row['browser'] ?? ''); ?></small></td>
                        <td><small><?php echo htmlspecialchars($row['device_type'] ?? ''); ?></small></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (DataTables 需要) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#recentEventsTable').DataTable({
        order: [[0, 'desc']], // 預設按時間降序排列
        pageLength: 50, // 每頁顯示 50 筆
        lengthMenu: [[25, 50, 100, 200], [25, 50, 100, 200]], // 可選擇的每頁筆數
        language: {
            search: "搜尋:",
            lengthMenu: "顯示 _MENU_ 筆",
            info: "顯示 _START_ 到 _END_ 筆，共 _TOTAL_ 筆",
            infoEmpty: "沒有資料",
            infoFiltered: "(從 _MAX_ 筆中篩選)",
            paginate: {
                first: "第一頁",
                last: "最後一頁",
                next: "下一頁",
                previous: "上一頁"
            },
            zeroRecords: "沒有符合的資料",
            emptyTable: "目前沒有資料"
        },
        columnDefs: [
            { orderable: false, targets: [5, 6, 7] } // IP、瀏覽器、裝置欄位不排序
        ]
    });
});
</script>
</body>
</html>

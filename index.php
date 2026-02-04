<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /web1/login_out/login.php');
    exit;
}
require_once 'DB/db_operations_all.php';
require_once 'common/common.php';
include 'login_out/navbar.php';
$conn = database_connection::get_connection();
$master_ip = mp510_repository::get_master_ip();
$current_ip = database_connection::get_server_ip();

// ============================================================
// Quick Links - 手動編輯
// ============================================================
$quick_links = [
    ['name' => 'GitLab - x12 x13',      'url' => 'https://gitlab.supermicro.com/ipmi/x12',      'icon' => 'bi-gitlab', 'color' => '#fc6d26'],
    ['name' => 'GitLab - x14',      'url' => 'https://gitlab.supermicro.com/openbmc_supermicro/aspeed-openbmc', 'icon' => 'bi-gitlab', 'color' => '#fc6d26'],
    ['name' => 'GitLab - x15',      'url' => 'https://gitlab.supermicro.com/onecodebase/openbmc_supermicro_gen_15/openbmc-two', 'icon' => 'bi-gitlab', 'color' => '#fc6d26'],
];

// ============================================================
// Branch Info - 從 common/rf_schedule.csv 讀取
// ============================================================
$rf_schedules = [];
$rf_csv_path = __DIR__ . '/common/rf_schedule.csv';
if (($handle = fopen($rf_csv_path, 'r')) !== false) {
    $header = fgetcsv($handle); // quarter,rf,gen12,gen13,gen13_hw1,gen14,lbmc,obmc
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) >= 8) {
            $rf_schedules[] = [
                'quarter'   => $row[0],
                'rf'        => $row[1],
                'gen12'     => $row[2],
                'gen13'     => $row[3],
                'gen13_hw1' => $row[4],
                'gen14'     => $row[5],
                'lbmc'      => $row[6],
                'obmc'      => $row[7],
            ];
        }
    }
    fclose($handle);
}

// ============================================================
// Team Members - 從 common/member.csv 讀取
// CSV 格式: id,name,email,highlight (highlight: 1=標註, 0=正常)
// ============================================================
$team_members = [];
$csv_path = __DIR__ . '/common/member.csv';
if (($handle = fopen($csv_path, 'r')) !== false) {
    $header = fgetcsv($handle); // 跳過標題列
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) >= 4) {
            $team_members[] = [
                'id'        => $row[0],
                'name'      => $row[1],
                'email'     => $row[2],
                'highlight' => (bool)$row[3],
            ];
        }
    }
    fclose($handle);
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IPMI web service - Index</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="index.css" rel="stylesheet">
    <link href="Lottery/lottery.css?v=<?= filemtime('Lottery/lottery.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="p-5 text-center border-bottom">
        <h1 class="text-center">IPMI Web Service</Search></h1>
        <div class="col-lg-6 mx-auto">
            <span>
                <strong>(Master: <?php echo $master_ip; ?>)</strong>
            </span>
            <span>
                <strong>(Current: <?php echo $current_ip ?>)</strong>
            </span>
        </div>

        <!-- Quick Links -->
        <?php if (!empty($quick_links)): ?>
        <div class="quick-links">
            <?php foreach ($quick_links as $link): ?>
            <a href="<?php echo $link['url']; ?>" target="_blank" class="quick-link">
                <i class="bi <?php echo $link['icon']; ?>" style="color: <?php echo $link['color']; ?>"></i>
                <?php echo $link['name']; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tab Navigation -->
    <div class="container py-4">
        <ul class="nav nav-tabs" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">
                    <i class="bi bi-calendar-event me-2"></i>Release Schedule
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button" role="tab">
                    <i class="bi bi-people me-2"></i>Team Members
                </button>
            </li>
        </ul>

        <div class="tab-content" id="mainTabsContent">
            <!-- Release Schedule Tab -->
            <div class="tab-pane fade show active" id="schedule" role="tabpanel">
                <!-- Branch Board -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="mb-4 fw-semibold">
                            <a href="https://mysupermicro-my.sharepoint.com/:x:/r/personal/jerry_wang_supermicro_com/_layouts/15/Doc.aspx?sourcedoc=%7BB1E28C19-B698-45D9-821E-379E81F6357A%7D&file=bmc-projects.xlsx&action=default&mobileredirect=true"
                            target="_blank"
                            class="text-decoration-none text-dark">
                                <i class="bi bi-calendar-event me-2"></i>RF Release Schedule
                            </a>
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>Quarter</th>
                                        <th>RF Version</th>
                                        <th>Gen12</th>
                                        <th>Gen13</th>
                                        <th>Gen13 HW1</th>
                                        <th>Gen14</th>
                                        <th>LBMC<br><small class="text-muted">(Gen14)</small></th>
                                        <th>OBMC<br><small class="text-muted">(Gen14)</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rf_schedules as $rf_schedule): ?>
                                    <tr>
                                        <td class="fw-semibold text-center"><?php echo $rf_schedule['quarter']; ?></td>
                                        <td><?php echo $rf_schedule['rf']; ?></td>
                                        <td><?php echo $rf_schedule['gen12'] ?: '<span class="text-muted">—</span>'; ?></td>
                                        <td><?php echo $rf_schedule['gen13'] ?: '<span class="text-muted">—</span>'; ?></td>
                                        <td><?php echo $rf_schedule['gen13_hw1'] ?: '<span class="text-muted">—</span>'; ?></td>
                                        <td class="text-center"><?php echo $rf_schedule['gen14'] ?: '<span class="text-muted">—</span>'; ?></td>
                                        <td><?php echo $rf_schedule['lbmc'] ?: '<span class="text-muted">—</span>'; ?></td>
                                        <td><?php echo $rf_schedule['obmc'] ?: '<span class="text-muted">—</span>'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Team Members Tab -->
            <div class="tab-pane fade" id="staff" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="mb-4 fw-semibold d-flex align-items-center">
                            <i class="bi bi-people me-2"></i>Team Members
                            <span class="badge bg-secondary ms-2"><?php echo count($team_members); ?> 人</span>
                            <button type="button" class="btn btn-outline-primary btn-sm ms-3" data-bs-toggle="modal" data-bs-target="#lotteryModal">
                                <i class="bi bi-dice-5 me-1"></i>抽籤
                            </button>
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="staffTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">#</th>
                                        <th style="width: 100px;">工號</th>
                                        <th style="width: 150px;">姓名</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($team_members as $index => $member): ?>
                                    <tr>
                                        <td class="text-center text-muted"><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($member['id']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($member['name']); ?>
                                            <?php if ($member['highlight']): ?>
                                            <span class="manager-badge" title="Manager">M</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" class="text-primary text-decoration-none">
                                                <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($member['email']); ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lottery Modal -->
    <div class="modal fade" id="lotteryModal" tabindex="-1" aria-labelledby="lotteryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lotteryModalLabel">
                        <i class="bi bi-dice-5 me-2"></i>抽籤系統
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- 狀態資訊 -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>剩餘人數: <strong id="remainingCount">0</strong> / <strong id="totalCount">0</strong></span>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="resetLotteryBtn">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>重置
                        </button>
                    </div>

                    <!-- 抽籤控制 -->
                    <div class="input-group mb-3">
                        <span class="input-group-text">抽取人數</span>
                        <input type="number" class="form-control" id="drawCount" value="1" min="1">
                        <button class="btn btn-primary" type="button" id="drawBtn">
                            <i class="bi bi-play-fill me-1"></i>開始抽籤
                        </button>
                    </div>

                    <!-- 本次抽中結果 -->
                    <div class="card mb-3" id="resultCard" style="display: none;">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-trophy me-2"></i>本次抽中</span>
                            <button type="button" class="btn btn-sm btn-light" id="copyWinnersBtn" title="複製名單">
                                <i class="bi bi-clipboard me-1"></i>複製
                            </button>
                        </div>
                        <ul class="list-group list-group-flush" id="resultList">
                        </ul>
                    </div>

                    <!-- 已抽過的人 -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <i class="bi bi-check2-square me-2"></i>已抽過 (<span id="drawnCount">0</span> 人)
                        </div>
                        <div class="card-body" style="max-height: 150px; overflow-y: auto;">
                            <div id="drawnList" class="d-flex flex-wrap gap-1">
                                <span class="text-muted">尚無</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <p class="mb-0">
                &copy; 2024 Baber. All rights reserved.
            </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Lottery/lottery.js?v=<?= filemtime('Lottery/lottery.js') ?>"></script>
    <script>
        // 初始化樂透系統
        document.addEventListener('DOMContentLoaded', function() {
            const teamMembers = <?php echo json_encode($team_members); ?>;
            LotterySystem.init(teamMembers);
        });
    </script>
</body>
</html>
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
// Branch Info - 手動編輯
// ============================================================
$rf_schedules = [
    [
        'quarter' => 'Q2 Jun/E',
        'rf' => 'RF1.21.1-00.01',
        'gen12' => 'master_x12_rel_1.07_20250818',
        'gen13' => 'master_rel_1.05_20250818',
        'gen13_hw1' => 'master_hw1_rel_1.05_20250818',
        'gen14' => '1.03',
        'lbmc' => 'master_rel_1.05_20250818',
        'obmc' => 'master_rel_1.03_20251031',
    ],
    [
        'quarter' => 'Q3 Sep/E',
        'rf' => 'RF1.21.1-00.02',
        'gen12' => 'master_x12_rel_1.08_20251027',
        'gen13' => 'master_rel_1.06_20251027',
        'gen13_hw1' => 'master_hw1_rel_1.06_20251027',
        'gen14' => '1.04',
        'lbmc' => 'master_rel_1.06_20251027',
        'obmc' => 'master_rel_1.04_20251120',
    ],
    [
        'quarter' => 'Q4 Oct/E',
        'rf' => 'RF1.21.1-00.02',
        'gen12' => 'master_x12_rel_1.09_20251125',
        'gen13' => 'master_rel_1.07_20251125',
        'gen13_hw1' => 'master_hw1_rel_1.07_20251125',
        'gen14' => '1.05',
        'lbmc' => 'master_rel_1.07_20251125',
        'obmc' => 'master_rel_1.05_20251209',
    ],
    [
        'quarter' => 'Q4 Nov/E',
        'rf' => 'RF_1_22_2-00_00_API_full_list_2025-Q4_Nov',
        'gen12' => 'master_x12_rel_1.10_2025',
        'gen13' => 'master_rel_1.08_20251125',
        'gen13_hw1' => 'master_hw1_rel_1.08_20251125',
        'gen14' => '1.06',
        'lbmc' => 'master_rel_1.08_20251125',
        'obmc' => 'master_rel_1.06_XX/aspeed_master',
    ],
    [
        'quarter' => 'Q4 Dec/E',
        'rf' => 'RF_1_22_2-00_01_API_full_list_2025-Q4_Dec',
        'gen12' => 'master_x12_rel_1.11_20260121',
        'gen13' => 'master_rel_1.09_20260121',
        'gen13_hw1' => 'master_hw1_rel_1.09_20260121',
        'gen14' => '1.07',
        'lbmc' => 'master_hw1_rel_1.09_20260121',
        'obmc' => 'master_rel_1.07_XX',
    ],
    [
        'quarter' => 'Q1 Jan/E',
        'rf' => 'RF_1_22_2-00_02_API_full_list_2026-Q1_Jan',
        'gen12' => 'master_x12',
        'gen13' => 'master',
        'gen13_hw1' => 'master_hw1',
        'gen14' => '',
        'lbmc' => '',
        'obmc' => '',
    ],
];

// ============================================================
// Announcements - 手動編輯 (支援 HTML)
// type: info / warning / success / danger
// ============================================================
$announcements = [
    // ['type' => 'warning', 'title' => 'Maintenance', 'content' => 'Build server down: 2025-02-01 10:00-12:00.', 'date' => '2025-01-28'],
];
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

    <!-- Announcements -->
    <?php if (!empty($announcements)): ?>
    <div class="container py-4">
        <div class="announcements">
            <?php foreach ($announcements as $ann): ?>
            <div class="announcement <?php echo $ann['type']; ?>">
                <i class="bi <?php
                    echo match($ann['type']) {
                        'info'    => 'bi-info-circle-fill',
                        'warning' => 'bi-exclamation-triangle-fill',
                        'success' => 'bi-check-circle-fill',
                        'danger'  => 'bi-x-circle-fill',
                        default   => 'bi-info-circle-fill'
                    };
                ?> announcement-icon"></i>
                <div class="announcement-content">
                    <div class="announcement-title"><?php echo $ann['title']; ?></div>
                    <div class="announcement-text"><?php echo $ann['content']; ?></div>
                </div>
                <div class="announcement-date"><?php echo $ann['date']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <!-- Branch Board -->
    <div class="container py-4">
        <div class="card">
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

    <footer class="py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <p class="mb-0">
                &copy; 2024 Baber. All rights reserved.
            </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
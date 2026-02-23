<?php
session_start();
require_once 'daily_main_functions.php';
//require_once '../DB/db_operations.php';
require_once '../DB/db_operations_all.php';
require_once '../common/common.php';
//require_once 'users_mgmt/log_user_action.php';

//檢查用戶是否登入
//common::check_login();
//$username = $_SESSION['username'];


//檢查用戶是否合法
//$conn = database_connection::get_connection();
//$user = users_repository::check_user_in_db($username);

$branch_names = daily_repository::get_branch_names();

?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPMI web service - Daily Build Status</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DateRangePicker CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <!-- Navbar CSS -->
    <link href="../login_out/navbar.css" rel="stylesheet">
    <!-- Common CSS -->
    <link href="../common/common.css" rel="stylesheet">
    <!-- Page CSS -->
    <link href="daily_main.css" rel="stylesheet">
</head>

<body>

<?php include '../login_out/navbar.php'; ?>

    <div id="dailyBuildPage" class="container-fluid px-3 py-3">
        <!-- 頁面標題 -->
        <div class="page-header">
            <h2>Daily Build Results</h2>
        </div>

        <!-- Tab 導航 -->
        <ul class="nav nav-tabs mb-3" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="build-tab" data-bs-toggle="tab" data-bs-target="#build-content" type="button" role="tab" aria-controls="build-content" aria-selected="true">
                    <i class="bi bi-hammer"></i> Common Std Sign Image
                </button>
            </li>
            <?php if ($user_level == 'high'): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="report-tab" data-bs-toggle="tab" data-bs-target="#report-content" type="button" role="tab" aria-controls="report-content" aria-selected="false">
                    <i class="bi bi-envelope-paper"></i> All Targets Mail Reports
                    <span class="badge rounded-pill text-bg-warning ms-1" style="font-size: 10px;">beta!</span>
                </button>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Tab 內容 -->
        <div class="tab-content" id="mainTabsContent">
            <!-- Tab 1: Build Results (原有內容) -->
            <div class="tab-pane fade show active" id="build-content" role="tabpanel" aria-labelledby="build-tab">
                <!-- 篩選卡片 -->
                <div class="filter-card">
                    <div class="filter-title">Filter Options</div>
                    <div class="row g-3">
                    <div class="col-md-3">
                        <label for="branchFilter" class="form-label">Branch</label>
                        <select class="form-select" id="branchFilter">
                            <option value="">All Branches</option>
                            <?php foreach ($branch_names as $branch_name): ?>
                                <option value="<?php echo htmlspecialchars($branch_name); ?>">
                                    <?php echo htmlspecialchars($branch_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="statusFilter" class="form-label">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="PASS">PASS</option>
                            <option value="FAIL">FAIL</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="dateFilter" class="form-label">Date Range</label>
                        <input type="text" class="form-control" id="dateFilter" placeholder="Select date range">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary w-100" id="searchFilter">Search</button>
                    </div>
                    </div>
                </div>

                <!-- 結果區域 -->
                <div class="accordion" id="buildResults">
                    <!-- 由 JS 動態渲染 -->
                </div>
            </div>

            <!-- Tab 2: Mail Reports (僅 high 權限) -->
            <?php if ($user_level == 'high'): ?>
            <div class="tab-pane fade" id="report-content" role="tabpanel" aria-labelledby="report-tab">
                <!-- 報告篩選 -->
                <div class="filter-card">
                    <div class="filter-title">Select Report</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="reportDateRange" class="form-label">Date Range</label>
                            <input type="text" class="form-control" id="reportDateRange" placeholder="Select date range">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary w-100" id="loadReport"><i class="bi bi-search me-1"></i>Load Reports</button>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-outline-secondary w-100" id="listReports"><i class="bi bi-clock-history me-1"></i>List Recent</button>
                        </div>
                    </div>
                </div>

                <!-- 報告列表 -->
                <div id="reportList" class="mb-3" style="display: none;">
                    <div class="card">
                        <div class="card-header">Available Reports</div>
                        <div class="card-body" id="reportListContent">
                            <!-- 由 JS 動態渲染 -->
                        </div>
                    </div>
                </div>

                <!-- 報告內容顯示區 -->
                <div id="reportDisplay" class="card">
                    <div class="card-header" id="reportHeader">
                        <i class="bi bi-file-earmark-text"></i> Mail Report Content
                    </div>
                    <div class="card-body p-0">
                        <iframe id="reportFrame" style="width: 100%; height: 600px; border: none;"></iframe>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript 庫 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <!-- 自定義 JavaScript -->
    <script src="daily_main.js"></script>
</body>
</html>
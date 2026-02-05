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
    <!-- Page CSS -->
    <link href="daily_main.css" rel="stylesheet">
</head>

<body>

<?php include '../login_out/navbar.php'; ?>

    <div class="container-fluid px-3 py-3">
        <!-- 頁面標題 -->
        <div class="page-header">
            <h2>Daily Build Results</h2>
        </div>

        <!-- 篩選卡片 -->
        <div class="filter-card">
            <div class="filter-title">Filter Options</div>
            <div class="row g-3">
            <div class="col-md-3">
                <label for="branchFilter" class="form-label">Branch</label>
                <select class="form-select" id="branchFilter">
                    <option value="">All Branches</option>
                    <?php 
                    // 分離普通分支和BR分支
                    $normal_branches = [];
                    $br_branches = [];
                    // BR開頭的是特別分支
                    foreach ($branch_names as $branch_name) {
                        if (strpos($branch_name, 'BR_') === 0) {
                            $br_branches[] = $branch_name;
                        } else {
                            $normal_branches[] = $branch_name;
                        }
                    }
                    ?>
                    
                    <!-- 主要 Daily build 群組 -->
                    <?php if (!empty($normal_branches)): ?>
                        <optgroup label="Active Branches">
                            <?php foreach ($normal_branches as $branch_name): ?>
                                <option value="<?php echo htmlspecialchars($branch_name); ?>">
                                    <?php echo htmlspecialchars($branch_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    
                    <!-- BR Daily build 群組 -->
                    <?php if (!empty($br_branches)): ?>
                        <optgroup label="Develop Branches">
                            <?php foreach ($br_branches as $branch_name): ?>
                                <option value="<?php echo htmlspecialchars($branch_name); ?>">
                                    <?php echo htmlspecialchars($branch_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
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

    <!-- JavaScript 庫 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <!-- 自定義 JavaScript -->
    <script src="daily_main.js"></script>
</body>
</html>
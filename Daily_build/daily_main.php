<?php
session_start();
require_once 'daily_main_functions.php';
//require_once '../DB/db_operations.php';
require_once '../DB/db_operations_all.php';
require_once '../common/common.php';
//require_once 'users_mgmt/log_user_action.php';

include '../login_out/navbar.php';

//檢查用戶是否登入
//common::check_login();
//$username = $_SESSION['username'];


//檢查用戶是否合法
//$conn = database_connection::get_connection();
//$user = users_repository::check_user_in_db($username);

?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPMI web service - Daily Build Status</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap Datepicker CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <style>
        body {
            overflow-y: scroll; /* 永遠顯示垂直滾動條 */
        }

        /* 表格容器樣式 */
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        /* 確保表格列寬度固定 */
        .table th {
            white-space: nowrap;
        }

        /* 使用 flexbox 確保內容對齊 */
        .d-flex {
            min-width: 0;
        }
        /* 移除點擊後的背景色變化 */
        .accordion-button:not(.collapsed) {
            background-color: white;  
            color: black;            /* 保持文字顏色 */
            box-shadow: none;        /* 移除陰影效果 */
        }
    </style>
</head>

<body>
    <!-- JavaScript 庫 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <!-- 自定義 JavaScript -->
    <script src="daily_main.js"></script>

    <div class="container mt-4">
        <h2 class="mb-4">Daily Build Results</h2>
        
        <!-- 過濾器保持不變 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <select class="form-select" id="branchFilter">
                    <option value="">All Branches</option>
                    <option value="master">master</option>
                    <option value="master_rel_1.03_20240715">master_rel_1.03_20240715</option>
                    <option value="master_x12_rel_1.05_20240715">master_x12_rel_1.05_20240715</option>
                    <option value="aspeed-master">aspeed-master</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="PASS">PASS</option>
                    <option value="FAIL">FAIL</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="input-group" class="form-control" id="dateFilter">
                <!--
                    在 js 用 datarangepicker 渲染
                -->
            </div>

            <div class="col-md-3">
                <input type="button" class="btn btn-primary" id="searchFilter" value="search">
            </div>
        </div>
        <div class="accordion" id="buildResults">
            <!--
                在 js 用 {#buildResults} 渲染版面
            -->
        </div>
    </div>
</body>
</html>
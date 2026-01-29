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
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IPMI web service - Index</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
        }

        /* 頂部歡迎區 */
        .p-5.text-center.border-bottom {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 35px 24px !important;
        }

        .p-5.text-center.border-bottom h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .p-5.text-center.border-bottom .lead {
            color: #6b7280;
            font-size: 0.95rem;
            margin-bottom: 16px !important;
        }

        /* Server Info 標籤 */
        .p-5.text-center.border-bottom span {
            display: inline-block;
            margin: 4px;
        }

        .p-5.text-center.border-bottom span strong {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
            background: #f3f4f6;
            color: #6b7280;
        }

        /* 卡片區域 */
        .p-5:not(.text-center) {
            flex: 1;
            padding: 40px 24px !important;
        }

        .card {
            border: none;
            border-radius: 12px;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: box-shadow 0.25s ease, border-color 0.25s ease;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-color: #d1d5db;
        }

        .card-body {
            padding: 32px 24px;
        }

        .card-icon {
            font-size: 2.5rem;
            width: 70px;
            height: 70px;
            line-height: 70px;
            display: inline-block;
            border-radius: 12px;
            color: white;
            margin-bottom: 16px;
        }

        /* 不同卡片配色 */
        .col-md-3:nth-child(1) .card-icon {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .col-md-3:nth-child(2) .card-icon {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }

        .col-md-3:nth-child(3) .card-icon {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .col-md-3:nth-child(4) .card-icon {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .card-text {
            font-size: 0.85rem;
            color: #9ca3af;
        }

        .card.disabled {
            opacity: 0.6;
            pointer-events: none;
        }

        .card.disabled::after {
            content: 'Coming Soon';
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 600;
            background: #fbbf24;
            color: white;
        }

        .col-md-3:nth-child(4) .card {
            position: relative;
        }

        /* Footer */
        footer {
            background: white;
            border-top: 1px solid #e5e7eb;
            padding: 20px 0;
        }

        footer p {
            color: #9ca3af;
            font-size: 0.875rem;
        }

        footer a {
            color: #667eea !important;
            font-weight: 500;
            transition: color 0.2s;
        }

        footer a:hover {
            color: #764ba2 !important;
        }

        /* 響應式 */
        @media (max-width: 992px) {
            .col-md-3 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (max-width: 576px) {
            .col-md-3 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .p-5.text-center.border-bottom h1 {
                font-size: 1.4rem;
            }

            .card-icon {
                width: 60px;
                height: 60px;
                line-height: 60px;
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="p-5 text-center border-bottom">
        <h1 class="text-center">IPMI Web Control Panel</h1>
        <div class="col-lg-6 mx-auto">
            <span>
                <strong>(Master: <?php echo $master_ip; ?>)</strong>
            </span>
            <span>
                <strong>(Current: <?php echo $current_ip ?>)</strong>
            </span>
        </div>
    </div>

    <div class="p-5">
        <div class="container">
            <div class="row mt-4">
                <div class="col-md-3 mb-4">
                    <a href="/web1/Device_control/dev_ctrl_main.php" class="text-decoration-none">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-sliders card-icon"></i>
                                <h5 class="card-title">Device Control</h5>
                                <p class="card-text">Manage boards & devices</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-4">
                    <a href="/web1/Fw_release_build/fw_rel_main.php" class="text-decoration-none">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-cpu card-icon"></i>
                                <h5 class="card-title">FW Release Build</h5>
                                <p class="card-text">Compile firmware</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-4">
                    <a href="/web1/Daily_build/daily_main.php" class="text-decoration-none">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-calendar3 card-icon"></i>
                                <h5 class="card-title">Daily Build</h5>
                                <p class="card-text">Build status & reports</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center disabled">
                        <div class="card-body">
                            <i class="bi bi-gear card-icon"></i>
                            <h5 class="card-title">Automation</h5>
                            <p class="card-text">Coming soon</p>
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
            <a href="mailto:BaberF@xxx.com" class="text-decoration-none">
                Contact
            </a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
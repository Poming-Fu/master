<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /web1/login_out/login.php');
    exit;
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
    <style>
        body {
            padding-top: 60px;
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card-icon {
            font-size: 6rem;
            margin: 4rem 0;
        }
        footer {
            margin-top: auto; /* 頁面始終於底部 */
        }
        footer a:hover {
            color: #0056b3 !important; /* 懸停變色 */
        }
    </style>
</head>
<body>
    <div class="p-5 text-center border-bottom">
        <h1 class="text-center mb-4">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>! ~</h1>
        <div class="col-lg-6 mx-auto">
            <p class="lead mb-4">
                This is ipmi web site for testing.
            </p>
            <div class="row">
    </div>
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
                                    <p class="card-text">Include some boards info</p>
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
                                    <p class="card-text">Compile FW tool</p>
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
                                    <p class="card-text">view daily build status</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-center disabled" style="opacity: 0.5; pointer-events: none;">
                            <div class="card-body">
                                <i class="bi bi-stop-circle-fill card-icon"></i>
                                <h5 class="card-title">Automation</h5>
                                <p class="card-text">(Planning)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="bg-light py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <p class="mb-0 text-muted">
                &copy; 2024 Baber. All rights reserved.
            </p>
            <a href="mailto:BaberF@xxx.com" class="text-decoration-none text-muted">
                寄信給我
            </a>
         </div>
        </footer>


    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
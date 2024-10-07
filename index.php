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
    <title>Index</title>
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
            font-size: 4rem;
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Index intro</h1>
        <h2 class="text-center mb-4">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>! ~</h2>

        <div class="row">
            <div class="col-md-6 mb-4">
                <a href="/web1/Device_control/dev_ctrl_main.php" class="text-decoration-none">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-hdd-rack card-icon"></i>
                            <h5 class="card-title">Device Control</h5>
                            <p class="card-text">Include some boards</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="/web1/Fw_release_build/fw_rel_main.php" class="text-decoration-none">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-cpu card-icon"></i>
                            <h5 class="card-title">FW Release Form</h5>
                            <p class="card-text">Compile FW tool</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="#" class="text-decoration-none">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-pause-circle-fill card-icon"></i>
                            <h5 class="card-title">test</h5>
                            <p class="card-text">test</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="#" class="text-decoration-none">
                    <div class="card text-center">
                        <div class="card-body">
                        <i class="bi bi-pause-circle-fill card-icon"></i>
                            <h5 class="card-title">test</h5>
                            <p class="card-text">test</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
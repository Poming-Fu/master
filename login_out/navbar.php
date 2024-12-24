<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        /* 重置和固定導航欄樣式 */
        .navbar-custom {
            background-color: #2c3e50;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
            height: 60px;
            padding: 0 !important;
        }

        .navbar-custom .container-fluid {
            height: 100%;
            padding: 0 1rem;
        }

        .navbar-custom .navbar-nav {
            height: 100%;
            margin: 0 !important;
        }

        .navbar-custom .nav-item {
            height: 100%;
            display: flex;
            align-items: center;
            margin: 0 !important;
        }

        .navbar-custom .nav-link {
            color: rgba(255,255,255,.9) !important;
            padding: 0 1rem !important;
            height: 100%;
            display: flex;
            align-items: center;
            transition: color 0.3s ease;
        }

        .navbar-custom .nav-link:hover {
            color: #ffffff !important;
            background-color: rgba(255,255,255,.1);
        }

        /* 修改漢堡選單按鈕樣式 */
        .navbar-toggler {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.7%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }

        @media (max-width: 991.98px) {
            .navbar-custom {
                height: auto;
                padding: 0.5rem 0 !important;
            }

            .navbar-custom .nav-item {
                height: 45px;
            }

            .navbar-collapse {
                background-color: #2c3e50;
                padding: 0.5rem 0;
                position: absolute;
                top: 60px;
                left: 0;
                right: 0;
                z-index: 1000;
                box-shadow: 0 2px 4px rgba(0,0,0,.1);
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/web1/index.php">
                            <i class="bi bi-house-door me-1"></i>Index
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/web1/Device_control/dev_ctrl_main.php">
                            <i class="bi bi-sliders me-1"></i>Device Control
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/web1/Fw_release_build/fw_rel_main.php">
                            <i class="bi bi-cpu-fill me-1"></i>FW Release Build
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/web1/Daily_build/daily_main.php">
                            <i class="bi bi-calendar3 me-1"></i>Daily Build
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/web1/login_out/logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
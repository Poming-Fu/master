<?php
// 取得用戶等級
require_once __DIR__ . '/../DB/db_operations_all.php';
$conn = database_connection::get_connection();
$user = users_repository::check_user_in_db($_SESSION['username']);
$user_level = $user['u_lev'] ?? 'low';
?>
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
                    <li class="nav-item">
                        <a class="nav-link" href="/web1/common/boards_tmp/boards_tmp_mgmt.php">
                            <i class="bi bi-motherboard me-1"></i>Boards Tmp
                            <span class="badge rounded-pill text-bg-warning ms-1" style="font-size: 10px;">beta!</span>
                        </a>
                    </li>
                    <?php if ($user_level == 'high'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/web1/common/analytics/analytics_dashboard.php">
                            <i class="bi bi-graph-up me-1"></i>Analytics
                            <span class="badge rounded-pill text-bg-warning ms-1" style="font-size: 10px;">beta!</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#" style="pointer-events: none; cursor: default;">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/web1/login_out/logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
</nav>
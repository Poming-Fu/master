<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /web1/login_out/login.php');
    exit;
}

require_once '../Device_control/db/db_operations.php';
$conn = connect_to_db();

?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fw_release_build</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>        
        .form-label {
            border: 1px solid #007bff;
            color: #007bff;
            font-weight: bold;
            font-size: 1.2rem;
            padding: 0.25rem 0.5rem;
            display: inline-block;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include '../login_out/navbar.php'; ?>
    
    <div class="container-fluid py-5">
        <h1 class="text-center mb-4">FW release build</h1>
        
        <!-- 頁內導航 -->
        <nav class="nav justify-content-center mb-4">
            <a class="nav-link" href="#build-form">Build Form</a>
            <a class="nav-link" href="#history">Build History</a>
        </nav>
        
        <div class="row g-4">
        <div class="col-12 col-lg-6 order-1">
                <div id="build-form" class="card h-100">
                    <div class="card-body">
                        <h2 class="card-title">Build Form</h2>
                        <p class="card-text">請填寫必要的參數。</p>
                        
                        <form method="post" action="/web1/Fw_release_build/fw_r_form.php" target="_blank">
                            <div class="mb-3">
                                <label for="username" class="form-label">User：<?= htmlspecialchars($_SESSION['username']) ?></label>
                            </div>
                            
                            <div class="mb-3">
                                <label for="branch" class="form-label">branch name or tag：</label>
                                <input type="text" class="form-control" id="branch" name="branch" required>
                                <div class="form-text">ex：x12 : master_x12_rel_1.05_20240715 , x13 : master_rel_1.03_20240715, x14 : aspeed-master</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="platform" class="form-label">Platform：</label>
                                <input type="text" class="form-control" id="platform" name="platform" required>
                                <div class="form-text">ex：x12 : sx12_rot_ast25_p , x13 : sx13_rot2hw2_ast26_p , x14 : x14-ast2600-rot</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ver" class="form-label">Version：</label>
                                <input type="text" class="form-control" id="ver" name="ver" required>
                                <div class="form-text">ex : legacybmc : 9.09.09 & openbmc: 09.09.09.09 注意三碼四碼rule</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="option" class="form-label">Option：</label>
                                <input type="text" class="form-control" id="option" name="option" value="core=8" required>
                                <div class="form-text">default please</div>
                            </div>

                            <div>
                                <label for="oemname" class="form-label">OEM name：</label>
                            </div>
                            <div class="input-group">
                                <input type="text" class="form-control" id="oemname" name="oemname">
                                <span class="input-group-text">.bin</span>
                            </div>
                            <div class="form-text mb-4">STD ignore this, if OEM you can fill up your OEM fw filename</div>

                            <div class="mb-3">
                                <button id="build-form-submit-btn" type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- 新增的排程表卡片 -->
            <div class="col-12 col-lg-2 order-2 order-lg-3">
            <div id="build-status" class="card h-100">
                <div class="card-body">
                    <h2 class="card-title">Build Status</h2>
                    <?php
                    $builds = get_schedule_builds();
                    if (!empty($builds)): ?>
                        <ul class="list-group">
                        <?php foreach ($builds as $build): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($build['u_acc']) ?> -
                                <?= htmlspecialchars($build['submit_time']) ?> - 
                                <?= htmlspecialchars($build['branch']) ?> 
                                (<?= htmlspecialchars($build['platform']) ?>)
                                <span class="badge 
                                <?php
                                    switch($build['status']) {
                                        case 'in_progress':
                                            echo 'bg-primary d-flex align-items-center justify-content-center';
                                            $spinner = '<span class="spinner-border spinner-border-sm me-2"></span>';//
                                            break;
                                        case 'pending':
                                            echo 'bg-secondary';
                                            $spinner = '';
                                            break;
                                        default:
                                            echo 'bg-info';
                                            $spinner = '';
                                    }
                                    ?>">
                                    <?= $spinner ?><?= htmlspecialchars($build['id']) ?>
                                    <?= htmlspecialchars($build['status']) ?>..
                                </span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No pending builds.</p>
                    <?php endif; ?>
                </div>
            </div>
            </div>
            <div class="col-12 col-lg-4 order-3 order-lg-2">
                <div id="history" class="card h-100">
                    <div class="card-body">
                        <h2 class="card-title">Build history 
                            <img
                            id="history_reload"
                            src="/web1/web_picture/reload.png" 
                            style="width: 20px; height: 20px; cursor: pointer;"
                            title="refresh status">
                        </h2>
                        <?php
                        $historys = get_history_builds();
                        if (!empty($historys)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>status</th>
                                            <th>id</th>
                                            <th>Time</th>
                                            <th>User</th>
                                            <th>Branch</th>
                                            <th>Platform</th>
                                            <th>Version</th>
                                            <th>Option</th>
                                            <th>OEM name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historys as $history): ?>
                                        <tr>
                                            <td class="<?php
                                            switch($history['status']) {
                                                case 'completed':
                                                    echo 'table-success';
                                                    break;
                                                case 'failed':
                                                    echo 'table-danger';
                                                    break;
                                            }
                                            ?>"><?= htmlspecialchars($history['status']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($history['id']) ?></td>
                                            <td><?= $history['submit_time'] ?></td>
                                            <td><?= htmlspecialchars($history['u_acc']) ?></td>
                                            <td><?= htmlspecialchars($history['branch']) ?></td>
                                            <td><?= htmlspecialchars($history['platform']) ?></td>
                                            <td><?= htmlspecialchars($history['ver']) ?></td>
                                            <td><?= htmlspecialchars($history['option']) ?></td>
                                            <td><?= htmlspecialchars($history['oemname'] ?: 'N/A') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="card-text">No history available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="fw_r_main.js"></script>
</body>
</html>
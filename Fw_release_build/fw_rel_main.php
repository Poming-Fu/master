<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /web1/login_out/login.php');
    exit;
}

require_once __DIR__ . '/../DB/db_operations_all.php';
//require_once __DIR__ . '/../DB/db_operations.php';
$conn = database_connection::get_connection();
$who  = htmlspecialchars($_SESSION['username']) . ":" . htmlspecialchars($_SESSION['password']);

?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPMI web service - Fw_release_build</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="fw_rel_main.css" rel="stylesheet">
</head>
<body>
    <?php include '../login_out/navbar.php'; ?>
    
    <div class="container-fluid py-5">
        <h4 class="text mb-4" style="display: flex; gap: 20px; align-items: center;">
            <span>FW release build</span>
            <span><i class="bi bi-check-circle-fill"></i> x12 x13 x14 codebase</span>
            <span><i class="bi bi-x-circle-fill"></i> hw1</span>
        </h4>
        <!-- 頁內導航 
        <nav class="nav justify-content-center mb-4">
            <a class="nav-link" href="#build-form">Build Form</a>
            <a class="nav-link" href="#history">Build History</a>
        </nav> -->
        
        <div class="row g-4 mb-2">
            <div class="col-12">
                <div id="build-form" class="card h-100">
                    <div class="card-body">                      
                        <form>
                            <div class="mb-3 d-flex">
                                <label for="username" class="form-label">User：<?= htmlspecialchars($_SESSION['username']) ?></label>
                                <input type="hidden" id="who" name="who" value="<?= htmlspecialchars($who) ?>">
                            </div>
                            
                            <div class="mb-3 row">
                                <label for="branch" class="col-sm-3 col-form-label form-label">Branch name or tag or commit id：</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="branch" name="branch" placeholder="ex : master_rel_1.04_20250513, aspeed-master" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="platform" class="col-sm-3 col-form-label form-label">Platform：</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="platform" name="platform" placeholder="ex : sx13_rot2hw2_ast26_p, x14-ast2600-rot" required>
                                </div>
                            </div>


                            <div class="mb-3 row">
                                <label for="ver" class="col-sm-3 col-form-label form-label">Version：</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="ver" name="ver" placeholder="ex : legacybmc : 1.01.01 & openbmc: 01.02.03.01" required>
                                </div>
                            </div>
                            
                            <div class="mb-3 row">
                                <label for="option" class="col-sm-3 col-form-label form-label">Option：</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="option" name="option" value="core=12" readonly required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="oemname" class="col-sm-3 col-form-label form-label">OEM name：</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="oemname" name="oemname" placeholder="STD ignored this">
                                        <span class="input-group-text">.bin</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 d-grid">
                                <button id="build-form-submit-btn" type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-2">
            <!-- 新增的排程表卡片 -->
            <div class="col-12">
            <div id="build-status" class="card h-100">
                <div class="card-body">
                    <h2 class="card-title">Build Status</h2>
                    <?php
                    $builds = firmware_repository::get_schedule_builds();
                    if (!empty($builds)): ?>
                        <ul class="list-group">
                        <?php foreach ($builds as $build): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($build['u_acc']) ?> -
                                <?= htmlspecialchars($build['submit_time']) ?> - 
                                <?= htmlspecialchars($build['branch']) ?> 
                                (<?= htmlspecialchars($build['platform']) ?>)
                                <span class="badge d-flex align-items-center justify-content-center
                                <?php
                                    switch($build['status']) {
                                        case 'in_progress':
                                            echo 'bg-primary';
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
        </div>
        <div class="row g-4 mb-2">
            <div class="col-12">
                <div id="history" class="card h-100">
                    <div class="card-body">
                        <h2 class="card-title">Build history </h2>
                        <?php
                        $historys = firmware_repository::get_history_builds(10);
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
    <script src="fw_rel_main.js"></script>
</body>
</html>
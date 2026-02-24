<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /web1/login_out/login.php');
    exit;
}

require_once __DIR__ . '/../DB/db_operations_all.php';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="../login_out/navbar.css?v=<?= filemtime('../login_out/navbar.css') ?>" rel="stylesheet">
    <link href="../common/common.css?v=<?= filemtime('../common/common.css') ?>" rel="stylesheet">
    <link href="fw_rel_main.css?v=<?= filemtime('fw_rel_main.css') ?>" rel="stylesheet">
    <!-- Loading overlay -->
    <style id="loading-style">
        .page-loading-overlay { position:fixed; inset:0; background:rgba(255,255,255,0.8); z-index:9999; display:flex; align-items:center; justify-content:center; transition:opacity 0.3s; }
        .page-loading-spinner { width:2.5rem; height:2.5rem; border:3px solid rgba(102,126,234,0.25); border-top-color:#667eea; border-radius:50%; animation:spin .7s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }
    </style>
    <!-- JS：defer 放 head，與 HTML 解析同步下載 -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>
    <link rel="preconnect" href="https://cdn.datatables.net" crossorigin>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js" defer></script>
    <script src="fw_rel_main.js?v=<?= filemtime('fw_rel_main.js') ?>" defer></script>
</head>
<body>
<div class="page-loading-overlay" id="pageLoadingOverlay"><div class="page-loading-spinner"></div></div>
<script>
(function(){
    function removeOverlay(){
        var o=document.getElementById('pageLoadingOverlay');
        if(o){o.style.opacity='0';setTimeout(function(){o.remove();},300);}
        var s=document.getElementById('loading-style');
        if(s)s.remove();
    }
    document.addEventListener('DOMContentLoaded',removeOverlay);
    window.addEventListener('pageshow',function(e){if(e.persisted)removeOverlay();});
    setTimeout(removeOverlay,8000);
})();
</script>
    <?php include '../login_out/navbar.php'; ?>
    
    <div id="fwReleasePage" class="container-fluid py-3">

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div id="build-form" class="card h-100">
                    <div class="card-body">
                        <h2 class="card-title">
                            Build Form 
                            <span class="ms-3 fs-6">
                                <i class="bi bi-check-circle-fill text-success"></i> x12 x13 x14 codebase
                            </span>
                            <span class="ms-3 fs-6">
                                <i class="bi bi-x-circle-fill text-danger"></i> hw1
                            </span>
                        </h2>
                        <form>
                            <div class="mb-4">
                                <div class="form-info-display">
                                    <span>User:</span>
                                    <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                                </div>
                                <input type="hidden" id="who" name="who" value="<?= htmlspecialchars($who) ?>">
                            </div>

                            <div class="mb-3">
                                <label for="branch" class="form-label">Branch name or tag or commit id</label>
                                <input type="text" class="form-control" id="branch" name="branch" placeholder="ex: master_rel_1.04_20250513, aspeed-master" required>
                            </div>

                            <div class="mb-3">
                                <label for="platform" class="form-label">Platform</label>
                                <input type="text" class="form-control" id="platform" name="platform" placeholder="ex: sx13_rot2hw2_ast26_p, x14-ast2600-rot" required>
                            </div>

                            <div class="mb-3">
                                <label for="ver" class="form-label">Version</label>
                                <input type="text" class="form-control" id="ver" name="ver" placeholder="ex: legacybmc: 1.01.01 & openbmc: 01.02.03.01" required>
                            </div>

                            <div class="mb-3">
                                <label for="option" class="form-label">Option</label>
                                <input type="text" class="form-control" id="option" name="option" value="core=12" readonly required>
                            </div>

                            <div class="mb-4">
                                <label for="oemname" class="form-label">OEM name</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="oemname" name="oemname" placeholder="STD ignored this">
                                    <span class="input-group-text">.bin</span>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button id="build-form-submit-btn" type="submit" class="btn btn-primary btn-lg">
                                    Submit Build
                                </button>
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
                        $historys = firmware_repository::get_history_builds(100);
                        if (!empty($historys)): ?>
                            <div class="table-responsive">
                                <table id="historyTable" class="table table-striped table-bordered">
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

</body>
</html>
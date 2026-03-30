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
    <!-- CSS -->
    <link href="../common/src/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../common/src/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../common/src/datatables/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="../login_out/navbar.css?v=<?= filemtime('../login_out/navbar.css') ?>" rel="stylesheet">
    <link href="../common/common.css?v=<?= filemtime('../common/common.css') ?>" rel="stylesheet">
    <link href="fw_rel_main.css?v=<?= filemtime('fw_rel_main.css') ?>" rel="stylesheet">
    <!-- JS -->
    <script src="../common/src/jquery-3.7.1.min.js"></script>
    <script src="../common/src/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../common/src/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../common/src/datatables/js/dataTables.bootstrap5.min.js"></script>
    <script src="fw_rel_main.js?v=<?= filemtime('fw_rel_main.js') ?>"></script>
</head>
<body>
    <?php include '../login_out/navbar.php'; ?>
    
    <div id="fwReleasePage" class="container-fluid py-3">

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div id="build-form" class="card h-100">
                    <div class="card-body">
                        <h2 class="card-title">
                            Build Form 
                            <span class="ms-3 fs-6">
                                <i class="bi bi-check-circle-fill text-success"></i> AST2600(x12, x13, x13_hw1, x14)
                            </span>
                            <span class="ms-3 fs-6">
                                <i class="bi bi-x-circle-fill text-danger"></i> AST2500(x11,x12), AST2700(x15)
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

                            <!-- STD / OEM 切換 -->
                            <div class="mb-3">
                                <label class="form-label">Build Type</label>
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="build_type" id="build_type_std" value="std" checked>
                                    <label class="btn btn-outline-primary" for="build_type_std">STD</label>
                                    <input type="radio" class="btn-check" name="build_type" id="build_type_oem" value="oem">
                                    <label class="btn btn-outline-primary" for="build_type_oem">OEM</label>
                                </div>
                            </div>

                            <!-- Release / Only Build 切換 -->
                            <div class="mb-3">
                                <label class="form-label">Mode</label>
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="build_mode" id="build_mode_only" value="only_build" checked>
                                    <label class="btn btn-outline-success" for="build_mode_only">Only Build</label>
                                    <input type="radio" class="btn-check" name="build_mode" id="build_mode_release" value="release">
                                    <label class="btn btn-outline-success" for="build_mode_release">Release</label>
                                </div>
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
                                <input type="text" class="form-control" id="ver" name="ver" placeholder="ex: legacybmc: 01.01.01 & openbmc: 01.02.03.01" required>
                            </div>

                            <div class="mb-3">
                                <label for="option" class="form-label">Option</label>
                                <input type="text" class="form-control" id="option" name="option" value="core=12" readonly required>
                            </div>

                            <!-- OEM name - 只在 OEM 模式顯示 -->
                            <div class="mb-4" id="oemname-group" style="display: none;">
                                <label for="oemname" class="form-label">OEM name</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="oemname" name="oemname" placeholder="OEM firmware name">
                                    <span class="input-group-text">.bin</span>
                                </div>
                            </div>

                            <!-- Release 板子選擇 - 只在 Release 模式顯示 -->
                            <div class="mb-4" id="release-board-group" style="display: none;">
                                <label class="form-label">選擇板子 (依 Platform 對應)</label>
                                <select class="form-select" id="release-board-select">
                                    <option value="" disabled selected>請先填入 Platform 並按 Tab 觸發搜尋</option>
                                </select>
                                <small class="text-muted">請選擇一個板子</small>
                            </div>

                            <!-- Release 資訊 - 選完板子後顯示 -->
                            <div id="release-board-info" style="display: none;">
                                <hr>
                                <h5 class="mb-3"><i class="bi bi-motherboard"></i> Release Info</h5>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Board Name</label>
                                        <input type="text" class="form-control form-control-sm" id="release-board-name" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">BMC Type</label>
                                        <input type="text" class="form-control form-control-sm" id="release-bmc-type" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">GUID</label>
                                        <input type="text" class="form-control form-control-sm" id="release-guid" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">PBID</label>
                                        <input type="text" class="form-control form-control-sm" id="release-pbid" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Gitlab Type</label>
                                        <input type="text" class="form-control form-control-sm" id="release-gitlab-type" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Gitlab ID</label>
                                        <input type="text" class="form-control form-control-sm" id="release-gitlab-id" readonly>
                                    </div>
                                </div>
                                <button id="gen-release-note-btn" type="button" class="btn btn-warning btn-lg w-100 mb-3">
                                    <i class="bi bi-file-earmark-text"></i> Gen Release Note
                                </button>
                                <!-- Release Note 顯示區 -->
                                <div id="release-note-output" style="display: none;">
                                    <label class="form-label">Release Note</label>
                                    <pre id="release-note-content" class="form-control" style="height: auto; white-space: pre-wrap; background: var(--gray-50); font-size: 13px;"></pre>
                                    <small class="text-muted" id="release-note-paths"></small>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button id="build-form-submit-btn" type="submit" class="btn btn-primary btn-lg flex-fill">
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
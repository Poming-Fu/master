<?php
session_start();
require_once 'dev_ctrl_main_functions.php';
require_once '../DB/db_operations_all.php';
require_once '../common/common.php';

//檢查用戶是否登入
common::check_login();
$username = $_SESSION['username'];


//檢查用戶是否合法
$conn = database_connection::get_connection();
$user = users_repository::check_user_in_db($username);

// 使用query_boards_info 獲取板子info (傳入用戶等級)
$boards_info  = boards_repository::query_boards_info($user['u_lev']);
$ip_list      = $boards_info['ip_list'];
$mp510_groups = $boards_info['mp510_groups'];

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IPMI web service - Device Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../login_out/navbar.css" rel="stylesheet">
    <link href="../common/common.css" rel="stylesheet">
    <link href="dev_ctrl_main.css" rel="stylesheet">
    <link href="boards_mgmt/boards_mgmt.css" rel="stylesheet">
</head>
<body>

<?php include '../login_out/navbar.php'; ?>

<div class="container-fluid px-3 py-3">
    <!-- 狀態列 -->
    <div class="status-bar">
        <div class="status-info">
            <div class="status-item">
                <i class="bi bi-person-circle"></i>
                <span>User:</span>
                <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
            </div>
            <div class="status-item">
                <i class="bi bi-shield-check"></i>
                <span>Level:</span>
                <strong><?php echo htmlspecialchars($user['u_lev']); ?></strong>
            </div>
            <div class="status-item">
                <i class="bi bi-hdd-stack"></i>
                <span>Alive / Total:</span>
                <strong class="text-success" id="alive_count">-</strong>
                <span>/</span>
                <strong class="text-primary" id="total_count">-</strong>
            </div>
        </div>
        <?php if ($user['u_lev'] == 'high'): ?>
        <div class="status-actions">
            <a href="users_mgmt/add_new_user.php" target="_blank" class="btn btn-sm btn-primary">
                <i class="bi bi-person-plus"></i> Add User
            </a>
            <a href="users_mgmt/update_new_user.php" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i> Update User
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- 工具箱 -->
    <div class="tool-box">
        <h5>Board Raw 小工具</h5>
        <form id="rawForm">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">IP Address</label>
                    <input autocomplete="off" list="boardList" id="board_number" name="board_number" class="form-control" placeholder="選擇或輸入 IP">
                    <datalist id="boardList">
                        <?php foreach ($ip_list as $i => $board): ?>
                            <option value="<?php echo htmlspecialchars($board['IP']); ?>"
                                    data-current_pw="<?php echo htmlspecialchars($board['current_pw']); ?>">
                                <?php echo htmlspecialchars($board['B_Name']); ?> - <?php echo htmlspecialchars($board['IP']); ?>
                            </option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Username</label>
                    <input type="text" name="user_value" id="user_value" value="ADMIN" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Password</label>
                    <input type="text" name="pass_value" id="pass_value" value="ADMIN" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Raw Command</label>
                    <input type="text" name="raw_value" id="raw_value" value="0x6 0x1" class="form-control" placeholder="Enter raw command">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-send"></i> Execute
                    </button>
                </div>
            </div>
        </form>
        <div class="result-area">
            <div><strong>Command:</strong> <span id="cmd">-</span></div>
            <div><strong>Result:</strong> <span id="result">-</span></div>
        </div>
    </div>

    <!-- MP510 選擇器 -->
    <div class="mp510-selector">
        <select id="mp510Dropdown" class="form-select">
            <option value="">快速跳轉至 MP510...</option>
            <?php foreach ($mp510_groups as $mp_num => $boards): ?>
                <option value="mp510_group_<?php echo $mp_num; ?>">
                    MP510: <?php echo htmlspecialchars($boards[0]['mp_ip']); ?> (<?php echo htmlspecialchars($boards[0]['Locate']); ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- 所有主板區塊 -->
    <?php foreach ($mp510_groups as $mp_num => $boards):
        $mp_id = 'mp510_group_' . $mp_num;
    ?>
    <div class="mp510-group" id="<?php echo $mp_id; ?>">
        <!-- MP510 容器 -->
        <div class="mp510-box">
            <!-- MP510 標題列 -->
            <div class="mp510-header">
                <div class="mp510-title">
                    <i class="bi bi-pc-display-horizontal"></i>
                    MP510 - <span class="mp510-ip"><?php echo htmlspecialchars($boards[0]['mp_ip']); ?></span>
                    <span class="mp510-location"><?php echo htmlspecialchars($boards[0]['Locate']); ?></span>
                </div>
                <div class="mp510-actions">
                    <button class="mp510-btn action-icon insert-board-btn"
                            title="新增主板"
                            data-mp_num="<?php echo $mp_num; ?>"
                            data-mp_ip="<?php echo htmlspecialchars($boards[0]['mp_ip']); ?>"
                            data-locate="<?php echo htmlspecialchars($boards[0]['Locate']); ?>">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button class="mp510-btn resetMP510ser2net-icon"
                            data-mp_ip="<?php echo htmlspecialchars($boards[0]['mp_ip']); ?>"
                            title="Reset ser2net">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>

            <!-- 板子卡片網格 -->
            <div class="board-grid">
            <?php foreach ($boards as $board): ?>
            <div class="board-card" id="board_<?php echo htmlspecialchars($board['id']); ?>">
                <!-- 卡片標題 -->
                <div class="card-header">
                    <div class="board-name">
                        <?php if ($board['status'] == 'online'): ?>
                            <i class="bi bi-wifi text-success" title="Online"></i>
                        <?php else: ?>
                            <i class="bi bi-wifi-off text-danger" title="Offline"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($board['B_Name']); ?>
                    </div>
                    <div class="card-actions">
                        <!-- Copy All Info -->
                        <button class="icon-btn copy-all-btn"
                                data-name="<?php echo htmlspecialchars($board['B_Name']); ?>"
                                data-ip="<?php echo htmlspecialchars($board['IP']); ?>"
                                data-bid="<?php echo htmlspecialchars($board['B_id']); ?>"
                                data-version="<?php echo htmlspecialchars($board['version']); ?>"
                                data-mac="<?php echo htmlspecialchars($board['bmc_nc_mac']); ?>"
                                data-unique_pw="<?php echo htmlspecialchars($board['unique_pw']); ?>"
                                data-current_pw="<?php echo htmlspecialchars($board['current_pw']); ?>"
                                data-mp_ip="<?php echo htmlspecialchars($board['mp_ip']); ?>"
                                data-mp_com="<?php echo htmlspecialchars($board['mp_com']); ?>"
                                title="Copy All Info">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <!-- Reload -->
                        <button class="icon-btn reload-icon"
                                data-ip="<?php echo htmlspecialchars($board['IP']); ?>"
                                data-unique_pw="<?php echo htmlspecialchars($board['unique_pw']); ?>"
                                data-current_pw="<?php echo htmlspecialchars($board['current_pw']); ?>"
                                title="Reload status">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        <!-- Recovery -->
                        <form class="d-inline">
                            <input type="hidden" name="ip" value="<?php echo htmlspecialchars($board['IP']); ?>">
                            <input type="hidden" name="B_id" value="<?php echo htmlspecialchars($board['B_id']); ?>">
                            <input type="hidden" name="FW_type" value="BMC">
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($board['status']); ?>">
                            <input type="hidden" name="unique_pw" value="<?php echo htmlspecialchars($board['unique_pw']); ?>">
                            <input type="hidden" name="current_pw" value="<?php echo htmlspecialchars($board['current_pw']); ?>">
                            <button type="button" class="icon-btn fw-button" name="RF_recovery" title="Recovery BMC">
                                <i class="bi bi-bootstrap"></i>
                            </button>
                        </form>
                        <!-- Modify -->
                        <button class="icon-btn modify-board-btn"
                                title="修改"
                                data-board_id="<?php echo htmlspecialchars($board['id']); ?>"
                                data-ip="<?php echo htmlspecialchars($board['IP']); ?>"
                                data-name="<?php echo htmlspecialchars($board['B_Name']); ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <!-- Delete -->
                        <?php if (isset($user['u_lev']) && $user['u_lev'] == 'high'): ?>
                        <button class="icon-btn delete-board-btn"
                                title="刪除"
                                data-board_id="<?php echo htmlspecialchars($board['id']); ?>"
                                data-ip="<?php echo htmlspecialchars($board['IP']); ?>"
                                data-name="<?php echo htmlspecialchars($board['B_Name']); ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 卡片內容 -->
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>BMC IP</label>
                            <div class="value">
                                <a href="http://<?php echo htmlspecialchars($board['IP']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($board['IP']); ?>
                                </a>
                            </div>
                        </div>
                        <div class="info-item">
                            <label>Board ID</label>
                            <div class="value mono"><?php echo htmlspecialchars($board['B_id']) ?: '-'; ?></div>
                        </div>
                        <div class="info-item">
                            <label>BMC Version</label>
                            <div class="value mono"><?php echo htmlspecialchars($board['version']) ?: '-'; ?></div>
                        </div>
                        <div class="info-item">
                            <label>BMC MAC</label>
                            <div class="value mono"><?php echo htmlspecialchars($board['bmc_nc_mac']) ?: '-'; ?></div>
                        </div>
                        <div class="info-item">
                            <label>Unique Password</label>
                            <div class="value password-field">
                                <span class="mono"><?php echo htmlspecialchars($board['unique_pw']) ?: '-'; ?></span>
                                <?php if ($board['unique_pw']): ?>
                                <button class="copy-btn copy-button" data-unique_pw="<?php echo htmlspecialchars($board['unique_pw']); ?>" title="Copy">
                                    <i class="bi bi-copy"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <label>Current Password</label>
                            <div class="value password-field">
                                <span class="mono"><?php echo htmlspecialchars($board['current_pw']) ?: '-'; ?></span>
                                <?php if ($board['current_pw']): ?>
                                <button class="copy-btn copy-button" data-unique_pw="<?php echo htmlspecialchars($board['current_pw']); ?>" title="Copy">
                                    <i class="bi bi-copy"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <label>PowerBox</label>
                            <div class="value powerbox-status">
                                <?php if ($board['pw_num'] != 0): ?>
                                <button class="power-toggle AC-button on"
                                        id="pb<?php echo htmlspecialchars($board['pw_num']); ?>_port_<?php echo htmlspecialchars($board['pw_port']); ?>_closeButton"
                                        data-target="<?php echo htmlspecialchars($board['pw_port']); ?>"
                                        data-control="2"
                                        data-pw_ip="<?php echo $board['pw_ip']; ?>"
                                        style="display: none;">
                                    <i class="bi bi-power"></i> ON
                                </button>
                                <button class="power-toggle AC-button off"
                                        id="pb<?php echo htmlspecialchars($board['pw_num']); ?>_port_<?php echo htmlspecialchars($board['pw_port']); ?>_openButton"
                                        data-target="<?php echo htmlspecialchars($board['pw_port']); ?>"
                                        data-control="1"
                                        data-pw_ip="<?php echo $board['pw_ip']; ?>"
                                        style="display: none;">
                                    <i class="bi bi-power"></i> OFF
                                </button>
                                <?php else: ?>
                                <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Note -->
                    <?php if (!empty($board['note'])): ?>
                        <div class="note-area">
                            <i class="bi bi-card-text"></i>
                            <span><?php echo htmlspecialchars($board['note']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 卡片底部操作區 -->
                <div class="card-footer">
                    <div class="action-row">
                        <!-- Console -->
                        <div class="telnet-console console-icon <?php echo $board['console_status'] != 'enable' ? 'disabled' : ''; ?>"
                             id="telnet-console_<?php echo htmlspecialchars($board['IP']); ?>"
                             <?php if ($board['console_status'] == 'enable'): ?>
                             onclick="openTelnetSession('<?php echo htmlspecialchars($board['mp_ip']); ?>', '<?php echo htmlspecialchars($board['mp_com']); ?>', '<?php echo htmlspecialchars($board['IP']); ?>')"
                             <?php endif; ?>
                             title="<?php echo $board['console_status'] == 'enable' ? 'Open Console' : 'Console not prepared'; ?>">
                            <i class="bi bi-terminal"></i>
                        </div>

                        <!-- Enable Console -->
                        <form class="enableForm" style="flex: 1;">
                            <input type="hidden" name="ip" value="<?php echo htmlspecialchars($board['IP']); ?>">
                            <input type="hidden" name="current_pw" value="<?php echo htmlspecialchars($board['current_pw']); ?>">
                            <button type="submit" name="BMC_enable_console_btn" class="action-btn">
                                <i class="bi bi-unlock"></i> Enable
                            </button>
                        </form>

                        <!-- Power Actions -->
                        <form class="actionForm" style="flex: 2; display: flex; gap: 8px;">
                            <input type="hidden" name="ip" value="<?php echo htmlspecialchars($board['IP']); ?>">
                            <input type="hidden" name="current_pw" value="<?php echo htmlspecialchars($board['current_pw']); ?>">
                            <select name="action" class="action-select">
                                <option value="NA">Power Action...</option>
                                <option value="bmc_default_uni_ADMIN">BMC Default</option>
                                <option value="reset">Power Reset</option>
                                <option value="on">Power On</option>
                                <option value="off">Power Off</option>
                            </select>
                            <button type="submit" name="System_operation_btn" class="action-btn primary">
                                <i class="bi bi-play-fill"></i> Run
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<button id="backToTopBtn" class="backToTopBtn" title="回到頂部">
    <i class="bi bi-chevron-up"></i>
</button>

<!-- Board Management Modal -->
<div class="modal fade" id="boardManagementModal" tabindex="-1" aria-labelledby="boardManagementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Custom Form Header -->
            <div class="form-header">
                <i class="bi bi-plus-circle" id="boardManagementModalIcon"></i>
                <div class="form-header-text">
                    <h1 id="boardManagementModalLabel">板子管理</h1>
                    <p id="boardManagementModalSubtitle"></p>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="boardManagementModalBody">
                <div class="text-center" style="padding: 32px;">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>window.LOG_USER_ACC = '<?php echo htmlspecialchars($_SESSION['username']); ?>';</script>
<!-- Analytics SDK -->
<script src="../common/analytics/analytics.js"></script>
<script>
// 初始化 Analytics
Analytics.init('../common/analytics/analytics.php', {
    debug: false,
    autoTrackPageView: false  // 關閉自動追蹤頁面瀏覽
});
</script>
<script src="dev_ctrl_main.js"></script>
<script src="powerbox/dev_ctrl_power_fetch.js"></script>
<script>
$(document).ready(function() {
    fetchPb1Status();
    setInterval(fetchPb1Status, 5000);

    fetchPb2Status();
    setInterval(fetchPb2Status, 5000);

    fetchBoardAliveData();
    setInterval(fetchBoardAliveData, 5000);
});
</script>

</body>
</html>
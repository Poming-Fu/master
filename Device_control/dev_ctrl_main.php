<?php
session_start();
require_once 'dev_ctrl_main_functions.php';
//require_once '../DB/db_operations.php';
require_once '../DB/db_operations_all.php';
require_once '../common/common.php';
require_once 'users_mgmt/log_user_action.php';

include '../login_out/navbar.php';

//檢查用戶是否登入
common::check_login();
$username = $_SESSION['username'];


//檢查用戶是否合法
$conn = database_connection::get_connection();
$user = users_repository::check_user_in_db($username);

// 使用query_boards_info 獲取板子info
$boards_info  = boards_repository::query_boards_info();
$ip_list      = $boards_info['ip_list'];
$mp510_groups = $boards_info['mp510_groups'];

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IPMI web service - control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="dev_ctrl_main.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid px-2">
    <!-- 狀態&資訊列 -->
    <div class="card mb-3">
        <div class="card-body">
            <span>
                User: <?php echo htmlspecialchars($_SESSION['username']); ?> &nbsp; 
                Level: <?php echo htmlspecialchars($user['u_lev']); ?> &nbsp; 
                Alive / Total: 
                <strong class="text-success" id="alive_count"></strong> / 
                <strong class="text-primary" id="total_count"></strong> &nbsp;
                <?php if ($user['u_lev'] == 'high'): ?>
                    <a href="users_mgmt/add_new_user.php" target="_blank" class="btn btn-sm btn-primary">Add User</a>
                    <a href="users_mgmt/update_new_user.php" target="_blank" class="btn btn-sm btn-primary">Update User</a>
                <?php endif; ?>
            </span>
        </div>
    </div>
    <!-- board raw rool -->
    <div class="tool-column">
        <h5>Board Raw小工具</h5>
        <form id="rawForm" class="row">
            <div class="col-md-3">
                <input autocomplete="off" list="boardList" id="board_number" name="board_number" class="form-control" placeholder="選擇或輸入 IP">
                    <datalist id="boardList">
                        <?php
                        foreach ($ip_list as $i => $board) {
                            echo "<option value='" . htmlspecialchars($board['IP']) . "'>Board " . ($i + 1) . " - " . htmlspecialchars($board['IP']) . "</option>";
                        }
                        ?>
                    </datalist>
                <input type="text" id="custom_ip" class="form-control mt-2" placeholder="輸入 IP" style="display: none;">
            </div>
            <div class="col-md-2">
                <input type="text" name="user_value" id="user_value" value="ADMIN" class="form-control">
            </div>
            <div class="col-md-2">
                <input type="text" name="pass_value" id="pass_value" value="ADMIN" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="text" name="raw_value" id="raw_value" value="0x6 0x1" class="form-control" placeholder="Enter raw">
            </div>
            <div class="col-md-2">
                <input type="submit" name="raw_submit" value="Submit" class="btn btn-primary">
            </div>
        </form>  
        <p class="mt-3">cmd = <span id="cmd"></span></p>
        <p>result = <span id="result"></span></p>
    </div>
    <!-- 下拉選擇MP510 -->
    <div class="mb-3">
        <select id="mp510Dropdown" class="form-select">
            <option value="">選擇MP510</option>
            <?php
            foreach ($mp510_groups as $mp_num => $boards) {
                $mp_ip = htmlspecialchars($boards[0]['mp_ip']);
                $location = htmlspecialchars($boards[0]['Locate']);
                echo "<option value='mp510_group_$mp_num'>MP510：$mp_ip ($location)</option>";
            }
            ?>
        </select>
    </div>
    <!-- 所有主板區塊 -->
    <div class="mb-3">
        <?php foreach ($mp510_groups as $mp_num => $boards): 
            $mp_id = 'mp510_group_' . $mp_num;
        ?>
            <div class="mp510-container" style="display: flex; align-items: center; gap: 10px;">
                <h2 class="mp510-title" data-bs-target="#<?php echo $mp_id; ?>" style="font-weight: bold; text-decoration: underline; padding-bottom: 5px; margin: 0;">
                    MP510：<?php echo htmlspecialchars($boards[0]['mp_ip']); ?>&nbsp;(<?php echo htmlspecialchars($boards[0]['Locate']); ?>)
                </h2>
                <a href="boards_mgmt/insert.php?mp_num=<?php echo $mp_num; ?>&mp_ip=<?php echo htmlspecialchars($boards[0]['mp_ip']); ?>&Locate=<?php echo urlencode($boards[0]['Locate']); ?>">
                    <img src="/web1/web_picture/insert.png" style="width: 30px; height: 30px; cursor: pointer; vertical-align: middle;" alt="Insert">
                </a>
            </div>

            <div id="<?php echo $mp_id; ?>">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 5%;">Ping</th>
                                <th style="width: 25%;">MB & BMC</th>
                                <th style="width: 10%;">PowerBox 狀態</th>
                                <th style="width: 10%;">BMC Console</th>
                                <th style="width: 10%;">BMC Console Btn</th>
                                <th style="width: 20%;">ipmitool action(ADMIN)</th>
                                <th style="width: 5%;">刪除</th>
                                <th style="width: 5%;">修改</th>
                                <th style="width: 10%;">note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($boards as $board): ?>
                            <tr id="board_<?php echo htmlspecialchars($board['id']); ?>"> <!-- 這裡加入 id -->
                                <td>
                                    <?php if ($board['status'] == "online"): ?>
                                        <img src="/web1/web_picture/O.png" style="width: 48px; height: 48px;">
                                    <?php else: ?>
                                        <img src="/web1/web_picture/X.png" style="width: 48px; height: 48px;">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-danger fs-6">
                                        Board name = <?php echo htmlspecialchars($board['B_Name']); ?>
                                        <form class="d-inline">
                                            <input type="hidden" name="ip" value="<?php echo htmlspecialchars($board['IP']); ?>">
                                            <input type="hidden" name="B_id" value="<?php echo htmlspecialchars($board['B_id']); ?>">
                                            <input type="hidden" name="FW_type" value="BMC">
                                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($board['status']); ?>">
                                            <input type="hidden" name="unique_pw" value="<?php echo htmlspecialchars($board['unique_pw']); ?>">
                                            <img src="/web1/web_picture/recovery_BMC.png" class="fw-button" name="RF_recovery" style="width: 23px; height: 23px; cursor: pointer;" title="recover fw" alt="RF_recovery_BMC">
                                        </form>
                                    </div>
                                    <div>Board id = <?php echo htmlspecialchars($board['B_id']); ?></div>
                                    <div>
                                        BMC ip = 
                                        <a href="http://<?php echo htmlspecialchars($board['IP']); ?>" target="_blank"><?php echo htmlspecialchars($board['IP']); ?></a>&nbsp;
                                        <img src="/web1/web_picture/reload.png" 
                                            alt="Reload" 
                                            class="reload-icon" 
                                            data-ip="<?php echo htmlspecialchars($board['IP']); ?>"
                                            data-unique_pw="<?php echo htmlspecialchars($board['unique_pw']); ?>"
                                            style="width: 20px; height: 20px; cursor: pointer;" 
                                            title="reload status">
                                    </div>
                                    <div>BMC ver = <?php echo htmlspecialchars($board['version']); ?></div>
                                    <div>BMC MAC = <?php echo htmlspecialchars($board['bmc_nc_mac']); ?></div>
                                    <div>Unique pw = <?php echo htmlspecialchars($board['unique_pw']); ?></div>
                                </td>
                                <td>
                                    <?php if ($board['pw_num'] != 0): ?>
                                        <div class="AC-button" id="pb<?php echo htmlspecialchars($board['pw_num']); ?>_port_<?php echo htmlspecialchars($board['pw_port']); ?>_openButton" 
                                            data-target="<?php echo htmlspecialchars($board['pw_port']); ?>" 
                                            data-control="1" 
                                            data-pw_ip="<?php echo $board['pw_ip']; ?>" 
                                            style="cursor: pointer; display: none;">
                                            <img src="/web1/web_picture/off.png" alt="off" style="width: 65px; height: 30px;">
                                        </div>
                                        <div class="AC-button" id="pb<?php echo htmlspecialchars($board['pw_num']); ?>_port_<?php echo htmlspecialchars($board['pw_port']); ?>_closeButton" 
                                            data-target="<?php echo htmlspecialchars($board['pw_port']); ?>" 
                                            data-control="2" 
                                            data-pw_ip="<?php echo $board['pw_ip']; ?>" 
                                            style="cursor: pointer; display: none;">
                                            <img src="/web1/web_picture/on.png" alt="on" style="width: 65px; height: 30px;">
                                        </div>
                                    <?php else: ?>
                                        <img src="/web1/web_picture/na.png" alt="na" style="width: 65px; height: 30px;">
                                    <?php endif; ?>
                                </td>
                                <td>
                                <div class="telnet-console" id="telnet-console_<?php echo htmlspecialchars($board['IP']); ?>" 
                                    <?php if ($board['console_status'] == 'enable'): ?>
                                        onclick="openTelnetSession('<?php echo htmlspecialchars($board['mp_ip']); ?>', '<?php echo htmlspecialchars($board['mp_com']); ?>', '<?php echo htmlspecialchars($board['IP']); ?>')" 
                                        style="cursor: pointer;"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="Click to open console"
                                    <?php else: ?>
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="Console not prepared"
                                    <?php endif; ?>
                                >
                                    <img src="/web1/web_picture/icon.png" 
                                        alt="Console" 
                                        style="width: 60px; height: 60px; 
                                            <?php if ($board['console_status'] != 'enable'): ?>
                                                opacity: 0.3;
                                            <?php endif; ?>"
                                    >
                                </div>
                                </td>
                                <td>
                                    <form class="enableForm">
                                        <input type="hidden" name="ip" value="<?php echo htmlspecialchars($board['IP']); ?>">
                                        <input type="submit" name="BMC_enable_console_btn" value="Console Enabled" class="btn btn-sm btn-primary">
                                    </form>
                                </td>
                                <td>
                                    <form class="actionForm">
                                        <input type="hidden" name="ip" value="<?php echo htmlspecialchars($board['IP']); ?>">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-auto flex-grow-1">
                                                <select name="action" class="form-select form-select-sm">
                                                    <option value="NA">Choose option</option>
                                                    <option value="bmc_default_uni_ADMIN">raw 0x30 0x48 0x1</option>
                                                    <option value="reset">System power reset</option>
                                                    <option value="on">System power on</option>
                                                    <option value="off">System power off</option>
                                                </select>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" name="System_operation_btn" class="btn btn-sm btn-primary">Submit</button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <?php
                                    if (isset($user['u_lev']) && $user['u_lev'] == 'high'): ?>
                                        <a href="boards_mgmt/delete.php?id=<?php echo htmlspecialchars($board['id']); ?>">
                                            <img src="/web1/web_picture/bin.png" alt="Delete" style="width: 30px; height: 30px;">
                                        </a>
                                    <?php else: ?>
                                        <img src="/web1/web_picture/bin.png" alt="Delete" style="width: 30px; height: 30px; opacity: 0.5;" title="Not allow">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="boards_mgmt/modify.php?id=<?php echo htmlspecialchars($board['id']); ?>">
                                        <img src="/web1/web_picture/modify.png" alt="modify" style="width: 30px; height: 30px;">
                                    </a>
                                </td>
                                <td class="note"><?php echo htmlspecialchars($board['note']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
<button id="backToTopBtn" class="backToTopBtn" title="回到頂部">↑</button>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="dev_ctrl_main.js"></script>
<script src="powerbox/dev_ctrl_power_fetch.js"></script>
<script>


$(document).ready(function() {
    fetchPb1Status(); // powerbox 1 調用
    setInterval(fetchPb1Status, 5000); // 每 5 秒調用一次

    fetchPb2Status(); // powerbox 2 調用
    setInterval(fetchPb2Status, 5000); // 每 5 秒調用一次

    fetchBoardAliveData(); // 獲取當前存活板子
    setInterval(fetchBoardAliveData, 5000); // 每 5 秒調用一次
});


</script>

</body>
</html>
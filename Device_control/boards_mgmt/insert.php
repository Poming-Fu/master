<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Board</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="boards_mgmt.css" rel="stylesheet">
</head>
<body>
<?php
session_start();
require_once '../../DB/db_operations_all.php';
$conn     = database_connection::get_connection();
$username = $_SESSION['username'];
$user     = users_repository::check_user_in_db($username);

if (isset($_POST['InsertBtn'])) {
    $B_Name     = !empty($_POST['B_Name']) ? $_POST['B_Name'] : NULL;
    $IP         = !empty($_POST['IP']) ? $_POST['IP'] : NULL;
    $BMC_MAC    = !empty($_POST['bmc_nc_mac']) ? $_POST['bmc_nc_mac'] : NULL;
    $L1_MAC     = !empty($_POST['L1_MAC']) ? $_POST['L1_MAC'] : NULL;
    $Unique_pw  = !empty($_POST['unique_pw']) ? $_POST['unique_pw'] : NULL;
    $current_pw = !empty($_POST['current_pw']) ? $_POST['current_pw'] : NULL;
    $Locate     = !empty($_POST['Locate']) ? $_POST['Locate'] : NULL;
    $pw_ip      = !empty($_POST['pw_ip']) ? $_POST['pw_ip'] : NULL;
    $pw_num     = ($_POST['pw_num'] !== '' && $_POST['pw_num'] !== '0') ? $_POST['pw_num'] : NULL;
    $pw_port    = ($_POST['pw_port'] !== '' && $_POST['pw_port'] !== '0') ? $_POST['pw_port'] : NULL;
    $mp_ip      = !empty($_POST['mp_ip']) ? $_POST['mp_ip'] : NULL;
    $mp_num     = ($_POST['mp_num'] !== '' && $_POST['mp_num'] !== '0') ? $_POST['mp_num'] : NULL;
    $mp_com     = ($_POST['mp_com'] !== '' && $_POST['mp_com'] !== '0') ? $_POST['mp_com'] : NULL;
    $note       = !empty($_POST['note']) ? $_POST['note'] : NULL;

    $new_id = boards_repository::insert_boards_info($B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $current_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note);
    if ($new_id) {
        echo "<script>
                alert('Insert Success! ID: ' + " . $new_id . ");
                window.location.href = '../dev_ctrl_main.php#board_" . $new_id . "';
              </script>";
        exit;
    } else {
        echo "<script>alert('Insert Failed');</script>";
        exit;
    }
}

if (isset($_GET['mp_num']) && isset($_GET['mp_ip']) && isset($_GET['Locate'])) {
    $MP_NUM = $_GET['mp_num'];
    $MP_IP  = $_GET['mp_ip'];
    $LOCATE = urldecode($_GET['Locate']);
}
?>

<div class="form-container">
    <div class="form-header">
        <i class="bi bi-plus-circle"></i>
        <div class="form-header-text">
            <h1>New Board</h1>
            <p>Add new board to <?php echo htmlspecialchars($MP_IP ?? 'MP510'); ?></p>
        </div>
    </div>

    <form class="form-body" method="post">
        <!-- Basic Info -->
        <div class="form-section">
            <div class="section-title">
                <i class="bi bi-motherboard"></i>
                Basic Info
            </div>
            <div class="form-grid">
                <div class="form-field">
                    <label>Board Name <span class="required">*</span></label>
                    <input type="text" name="B_Name" placeholder="ex: X13SEW-TF" required>
                </div>
                <div class="form-field">
                    <label>BMC IP <span class="required">*</span></label>
                    <input type="text" name="IP" placeholder="10.148.xx.xx" required>
                </div>
                <div class="form-field">
                    <label>BMC MAC</label>
                    <input type="text" name="bmc_nc_mac" placeholder="AA:BB:CC:DD:EE:FF">
                </div>
                <div class="form-field">
                    <label>L1 MAC</label>
                    <input type="text" name="L1_MAC" placeholder="option">
                </div>
            </div>
        </div>

        <!-- Password -->
        <div class="form-section">
            <div class="section-title">
                <i class="bi bi-key"></i>
                Password
            </div>
            <div class="form-grid">
                <div class="form-field">
                    <label>Unique Password</label>
                    <input type="text" name="unique_pw" placeholder="Factory password">
                </div>
                <div class="form-field">
                    <label>Current Password</label>
                    <input type="text" name="current_pw" placeholder="Current BMC password">
                </div>
            </div>
        </div>

        <!-- Location -->
        <div class="form-section">
            <div class="section-title">
                <i class="bi bi-geo-alt"></i>
                Location
            </div>
            <div class="form-grid single">
                <div class="form-field">
                    <label>Location <span class="required">*</span></label>
                    <input type="text" name="Locate" value="<?php echo htmlspecialchars($LOCATE ?? ''); ?>" required <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>>
                </div>
            </div>
        </div>

        <!-- PowerBox -->
        <div class="form-section">
            <div class="section-title">
                <i class="bi bi-plug"></i>
                PowerBox
            </div>
            <div class="form-grid">
                <div class="form-field">
                    <label>PowerBox IP</label>
                    <input type="text" name="pw_ip" placeholder="option">
                </div>
                <div class="form-field">
                    <label>PowerBox Number</label>
                    <input type="number" name="pw_num" placeholder="option">
                </div>
                <div class="form-field">
                    <label>PowerBox Port</label>
                    <input type="number" name="pw_port" placeholder="option">
                </div>
            </div>
        </div>

        <!-- MP510 -->
        <div class="form-section">
            <div class="section-title">
                <i class="bi bi-hdd-rack"></i>
                MP510 Console
            </div>
            <div class="form-grid">
                <div class="form-field">
                    <label>MP510 IP <span class="required">*</span></label>
                    <input type="text" name="mp_ip" value="<?php echo htmlspecialchars($MP_IP ?? ''); ?>" required <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>>
                </div>
                <div class="form-field">
                    <label>MP510 Number</label>
                    <input type="number" name="mp_num" value="<?php echo htmlspecialchars($MP_NUM ?? ''); ?>" <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>>
                </div>
                <div class="form-field">
                    <label>MP510 COM Port</label>
                    <input type="number" name="mp_com" placeholder="3000~300x">
                </div>
            </div>
        </div>

        <!-- Note -->
        <div class="form-section">
            <div class="section-title">
                <i class="bi bi-sticky"></i>
                Note
            </div>
            <div class="form-grid single">
                <div class="form-field">
                    <label>Note</label>
                    <input type="text" name="note" placeholder="Who is using? Any remarks...">
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="history.back()">
                <i class="bi bi-arrow-left"></i> Back
            </button>
            <button type="submit" name="InsertBtn" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> Add Board
            </button>
        </div>
    </form>
</div>
</body>
</html>

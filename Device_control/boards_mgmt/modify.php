<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Board</title>
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

if (isset($_POST['EditBtn'])) {
    $id         = $_POST['id'];
    $B_Name     = !empty($_POST['B_Name']) ? $_POST['B_Name'] : NULL;
    $IP         = !empty($_POST['IP']) ? $_POST['IP'] : NULL;
    $BMC_MAC    = !empty($_POST['bmc_nc_mac']) ? $_POST['bmc_nc_mac'] : NULL;
    $L1_MAC     = !empty($_POST['L1_MAC']) ? $_POST['L1_MAC'] : NULL;
    $Unique_pw  = !empty($_POST['unique_pw']) ? $_POST['unique_pw'] : NULL;
    $Locate     = !empty($_POST['Locate']) ? $_POST['Locate'] : NULL;
    $pw_ip      = !empty($_POST['pw_ip']) ? $_POST['pw_ip'] : NULL;
    $pw_num     = ($_POST['pw_num'] !== '' && $_POST['pw_num'] !== '0') ? $_POST['pw_num'] : NULL;
    $pw_port    = ($_POST['pw_port'] !== '' && $_POST['pw_port'] !== '0') ? $_POST['pw_port'] : NULL;
    $mp_ip      = !empty($_POST['mp_ip']) ? $_POST['mp_ip'] : NULL;
    $mp_num     = ($_POST['mp_num'] !== '' && $_POST['mp_num'] !== '0') ? $_POST['mp_num'] : NULL;
    $mp_com     = ($_POST['mp_com'] !== '' && $_POST['mp_com'] !== '0') ? $_POST['mp_com'] : NULL;
    $note       = !empty($_POST['note']) ? $_POST['note'] : NULL;
    $current_pw = !empty($_POST['current_pw']) ? $_POST['current_pw'] : NULL;

    if (boards_repository::modify_boards_info($B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $current_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note, $id)) {
        echo "<script>
                alert('Update Success!');
                window.location.href = '../dev_ctrl_main.php#board_" . $id . "';
              </script>";
        exit;
    } else {
        echo "<script>alert('Update Failed');</script>";
        exit;
    }
}

if (isset($_GET['id'])) {
    $id    = $_GET['id'];
    $board = boards_repository::query_boards_info_by_id($id);
}
?>

<?php if ($board): ?>
<div class="form-container">
    <div class="form-header">
        <i class="bi bi-pencil-square"></i>
        <div class="form-header-text">
            <h1>Edit Board</h1>
            <p><?php echo htmlspecialchars($board['B_Name']); ?> - <?php echo htmlspecialchars($board['IP']); ?></p>
        </div>
    </div>

    <form class="form-body" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

        <!-- Basic Info -->
        <div class="form-section">
            <div class="section-title">
                <i class="bi bi-motherboard"></i>
                Basic Info
            </div>
            <div class="form-grid">
                <div class="form-field">
                    <label>Board Name <span class="required">*</span></label>
                    <input type="text" name="B_Name" value="<?php echo htmlspecialchars($board['B_Name']); ?>" required>
                </div>
                <div class="form-field">
                    <label>BMC IP <span class="required">*</span></label>
                    <input type="text" name="IP" value="<?php echo htmlspecialchars($board['IP']); ?>" required>
                </div>
                <div class="form-field">
                    <label>BMC MAC</label>
                    <input type="text" name="bmc_nc_mac" value="<?php echo htmlspecialchars($board['bmc_nc_mac']); ?>" placeholder="AA:BB:CC:DD:EE:FF">
                </div>
                <div class="form-field">
                    <label>L1 MAC</label>
                    <input type="text" name="L1_MAC" value="<?php echo htmlspecialchars($board['L1_MAC']); ?>" placeholder="Optional">
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
                    <input type="text" name="unique_pw" value="<?php echo htmlspecialchars($board['unique_pw']); ?>" placeholder="Factory password">
                </div>
                <div class="form-field">
                    <label>Current Password</label>
                    <input type="text" name="current_pw" value="<?php echo htmlspecialchars($board['current_pw']); ?>" placeholder="Current BMC password">
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
                    <input type="text" name="Locate" value="<?php echo htmlspecialchars($board['Locate']); ?>" required <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>>
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
                    <input type="text" name="pw_ip" value="<?php echo htmlspecialchars($board['pw_ip']); ?>" <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>>
                </div>
                <div class="form-field">
                    <label>PowerBox Number</label>
                    <input type="number" name="pw_num" value="<?php echo htmlspecialchars($board['pw_num']); ?>" <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>>
                </div>
                <div class="form-field">
                    <label>PowerBox Port</label>
                    <input type="number" name="pw_port" value="<?php echo htmlspecialchars($board['pw_port']); ?>" <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>>
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
                    <input type="text" name="mp_ip" value="<?php echo htmlspecialchars($board['mp_ip']); ?>" required <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>>
                </div>
                <div class="form-field">
                    <label>MP510 Number</label>
                    <input type="number" name="mp_num" value="<?php echo htmlspecialchars($board['mp_num']); ?>" <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>>
                </div>
                <div class="form-field">
                    <label>MP510 COM Port</label>
                    <input type="number" name="mp_com" value="<?php echo htmlspecialchars($board['mp_com']); ?>">
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
                    <input type="text" name="note" value="<?php echo htmlspecialchars($board['note']); ?>" placeholder="Who is using? Any remarks...">
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="history.back()">
                <i class="bi bi-arrow-left"></i> Back
            </button>
            <button type="submit" name="EditBtn" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Save Changes
            </button>
        </div>
    </form>
</div>
<?php endif; ?>
</body>
</html>

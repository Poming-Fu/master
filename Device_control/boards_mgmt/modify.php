<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯表單</title>
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .UpdateForm table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .UpdateForm th, .UpdateForm td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .UpdateForm th {
            background-color: #f2f2f2;
        }
        .UpdateForm input[type="text"],
        .UpdateForm input[type="number"] {
            width: 100%;
            padding: 8px;
            margin: 4px 0;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
		.UpdateForm input[readonly] {
            background-color: #ffe6e6; /* 淡粉色 */
        }
        .UpdateForm button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .UpdateForm button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
    <?php
    session_start();
    //require_once '../../DB/db_operations.php';
    require_once '../../DB/db_operations_all.php';
    $conn           = database_connection::get_connection();
    $username       = $_SESSION['username'];
    $user           = users_repository::check_user_in_db($username);

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


        if (boards_repository::modify_boards_info($B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note, $id)) {
            $_SESSION['message'] = "記錄更新成功";
        } else {
            $_SESSION['message'] = "更新記錄時發生錯誤";
        }
        header("Location: ../dev_ctrl_main.php");
        exit;
    }

    if (isset($_GET['id'])) {
        $id    = $_GET['id'];
        $board = boards_repository::query_boards_info_by_id($id);
    }
    ?>
    <?php if ($board): ?>
        <form class="UpdateForm" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
            <table>
                <thead>
                <tr>
                    <th>欄位名稱</th>
                    <th>資料輸入</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>MB</td><td><input type="text" name="B_Name" value="<?php echo htmlspecialchars($board['B_Name']); ?>" required></td></tr>
                <tr><td>IP</td><td><input type="text" name="IP" value="<?php echo htmlspecialchars($board['IP']); ?>" required></td></tr>
                <tr><td>BMC_MAC</td><td><input type="text" name="bmc_nc_mac" value="<?php echo htmlspecialchars($board['bmc_nc_mac']); ?>" placeholder="選填"></td></tr>
                <tr><td>L1_MAC</td><td><input type="text" name="L1_MAC" value="<?php echo htmlspecialchars($board['L1_MAC']); ?>" placeholder="選填"></td></tr>
                <tr><td>Unique_pw</td><td><input type="text" name="unique_pw" value="<?php echo htmlspecialchars($board['unique_pw']); ?>" placeholder="選填"></td></tr>
                <tr><td>位置</td><td><input type="text" name="Locate" value="<?php echo htmlspecialchars($board['Locate']); ?>" required <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>></td></tr>
                <tr><td>電源IP</td><td><input type="text" name="pw_ip" value="<?php echo htmlspecialchars($board['pw_ip']); ?>" <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>></td></tr>
                <tr><td>電源編號</td><td><input type="number" name="pw_num" value="<?php echo htmlspecialchars($board['pw_num']); ?>" <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>></td></tr>
                <tr><td>電源端口</td><td><input type="number" name="pw_port" value="<?php echo htmlspecialchars($board['pw_port']); ?>" <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>></td></tr>
                <tr><td>MP510 IP</td><td><input type="text" name="mp_ip" value="<?php echo htmlspecialchars($board['mp_ip']); ?>" required <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>></td></tr>
                <tr><td>MP510 編號</td><td><input type="number" name="mp_num" value="<?php echo htmlspecialchars($board['mp_num']); ?>" <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>></td></tr>
                <tr><td>MP510 COM</td><td><input type="number" name="mp_com" value="<?php echo htmlspecialchars($board['mp_com']); ?>"></td></tr>
                <tr><td>備註</td><td><input type="text" name="note" value="<?php echo htmlspecialchars($board['note']); ?>"></td></tr>
                </tbody>
            </table>
            <button type="submit" name="EditBtn">保存修改</button>
            <button type="button" class="back-button" onclick="goBack()">上一頁</button>
        </form>
    <?php endif; ?>

</div>
</body>
<script>
function goBack() 
{
    window.history.back();
}
</script>
</html>

<!DOCTYPE html>
<html lang="TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增表單</title>
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .InsertForm table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .InsertForm th, .InsertForm td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .InsertForm th {
            background-color: #f2f2f2;
        }
        .InsertForm input[type="text"],
        .InsertForm input[type="number"] {
            width: 100%;
            padding: 8px;
            margin: 4px 0;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .InsertForm input[readonly] {
            background-color: #ffe6e6; /* 淡粉色 */
        }
        .InsertForm button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .InsertForm button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
    <?php
    session_start();
    require_once '../../DB/db_operations.php';
    require_once '../../DB/db_operations_all.php';
    $conn           = database_connection::get_connection();
    $username       = $_SESSION['username'];
    $user           = users_repository::check_user_in_db($username);

    if (isset($_POST['InsertBtn'])) {
        // 表單數據庫，帶入檢查機制
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

        // prepare bind 語法
        if (boards_repository::insert_boards_info($B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note)) {       
            $_SESSION['message'] = "插入資料成功";
        } else {
            $_SESSION['message'] = "插入資料失敗";
        }
        header("Location: ../dev_ctrl_main.php");
        exit;
    }


    //顯示表格基本訊息
    if (isset($_GET['mp_num']) && isset($_GET['mp_ip']) && isset($_GET['Locate'])) {
        $MP_NUM = $_GET['mp_num'];
        $MP_IP  = $_GET['mp_ip'];
        $LOCATE = urldecode($_GET['Locate']);
    }
    ?>

    <form class="InsertForm" method="post">
        <!-- 開始表格 -->
        <table>
            <!-- 表頭 -->
            <thead>
            <tr>
                <th>欄位名稱</th>
                <th>資料輸入</th>
            </tr>
            </thead>
            <!-- 表格內容 -->
            <tbody>
            <tr>
                <td>MB</td>
                <td><input type="text" name="B_Name" placeholder="ex:X13SEW-TF" required></td>
            </tr>
            <tr>
                <td>IP</td>
                <td><input type="text" name="IP" placeholder="XX.XX.XX.XX" required></td>
            </tr>
            <tr>
                <td>BMC_MAC</td>
                <td><input type="text" name="bmc_nc_mac" placeholder="選填"></td>
            </tr>
            <tr>
                <td>L1_MAC</td>
                <td><input type="text" name="L1_MAC" placeholder="選填"></td>
            </tr>
            <tr>
                <td>Unique_pw</td>
                <td><input type="text" name="unique_pw" placeholder="選填"></td>
            </tr>
            <tr>
                <td>位置</td>
                <td><input type="text" name="Locate" value="<?php echo htmlspecialchars($LOCATE); ?>" required <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>></td>
            </tr>
            <tr>
                <td>電源IP</td>
                <td><input type="text" name="pw_ip" placeholder="選填"></td>
            </tr>
            <tr>
                <td>電源編號</td>
                <td><input type="number" name="pw_num" placeholder="選填"></td>
            </tr>
            <tr>
                <td>電源端口</td>
                <td><input type="number" name="pw_port" placeholder="選填"></td>
            </tr>
            <tr>
                <td>MP510 IP</td>
                <td><input type="text" name="mp_ip" value="<?php echo htmlspecialchars($MP_IP); ?>" required <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>></td>
            </tr>
            <tr>
                <td>MP510 編號</td>
                <td><input type="number" name="mp_num" value="<?php echo htmlspecialchars($MP_NUM); ?>" <?php if ($user['u_lev'] !== 'high') echo 'readonly'; ?>></td>
            </tr>
            <tr>
                <td>MP510 COM</td>
                <td><input type="number" name="mp_com" placeholder="選填"></td>
            </tr>
            <tr>
                <td>note</td>
                <td><input type="text" name="note" placeholder="填狀況或誰在用"></td>
            </tr>
            </tbody>
            <!-- 結束表格 -->
        </table>
        <button type="submit" name="InsertBtn">新增</button>
		<button type="button" class="back-button" onclick="goBack()">上一頁</button>
    </form>
</div>
</body>
<script>
function goBack() 
{
    window.history.back();
}
</script>
</html>

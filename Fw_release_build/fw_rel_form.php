<?php
session_start();
require_once '../../DB/db_operations.php';
require_once '../../DB/db_operations_all.php';
require_once 'fw_rel_main_function.php';
$conn       = database_connection::get_connection();
$u_acc      = htmlspecialchars($_SESSION['username']);
$who        = htmlspecialchars($_SESSION['username']) . ":" . htmlspecialchars($_SESSION['password']);



//htmlspecialchars 是 PHP 中的一個內建函數，用於將字串中的特殊字符轉換為 HTML 實體
//isset 檢查變數是否有值

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch      = $_POST['branch'];
    $platform    = $_POST['platform'];
    $ver         = $_POST['ver'];
    $option      = $_POST['option'];
    $oemname     = $_POST['oemname'];
    
    if (!empty($oemname) && !str_ends_with($oemname, '.bin')) {
        $oemname .= '.bin';
    }

    //gen uuid
    $UUID        = generate_uuid(num: 4);
    // submit time
    $submit_time = date("Y-m-d H:i:s");
    
    $result      = execute_api($who, $branch, $platform, $ver, $option, $oemname, $UUID);
    $api_command = $result['api_command'];
    $output      = $result['output'];



    if ($output === null || trim($output) === '') {
        $output = "not expected error happened ~";
        alert($output);
        exit;
    }
    
    // record db
    firmware_repository::fw_r_form_record_history($u_acc, $branch, $platform, $ver, $option, $oemname, $status = 'pending', $UUID);
}
?>


<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>handle_build_code</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;

        }
		/* 設置第一列的寬度 */
		th:nth-child(1), td:nth-child(1) {
        width: 20%;
		}
		/* 設置第二列的寬度 */
		th:nth-child(2), td:nth-child(2) {
        width: 80%;
		}
        th {
            background-color: #f0f0f0;
        }
        input[type="text"], input[type="submit"] {
            width: 95%;
            padding: 5px;
            margin: 5px 0;
            border: 1px solid #ddd;
        }
        input[type="submit"] {
            background-color: #5cb85c;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #4cae4c;
        }
		.centered-text {
            text-align: center;
        }

    </style>
</head>
<body>
	<table>
			<th colspan="2">handle_build_code form</th>
			<tr>
				<td>who</td>
				<td><?= $u_acc ?></td>
			</tr>
			<tr>
				<td>branch</td>
				<td><?= htmlspecialchars($branch) ?></td>
			</tr>
			<tr>
				<td>platform</td>
				<td><?= htmlspecialchars($platform) ?></td>
			</tr>
			<tr>
				<td>ver</td>
				<td><?= htmlspecialchars($ver) ?></td>
			</tr>
			<tr>
				<td>option</td>
				<td><?= htmlspecialchars($option) ?></td>
			</tr>
			<tr>
				<td>oemname</td>
				<td><?= htmlspecialchars($oemname) ?></td>
			</tr>
			<tr>
				<td>submit start time</td>
				<td><?= htmlspecialchars($submit_time) ?></td>
			</tr>
            <tr>
				<td>UUID</td>
				<td><?= htmlspecialchars($UUID) ?></td>
			</tr>
			<tr>
				<td>result cmd </td>
				<td><?= htmlspecialchars($api_command) ?></td>
			</tr>			
			<tr>
				<td>output </td>
				<td><?= htmlspecialchars($output) ?></td>
			</tr>
	</table>
</body>
</html>

<script>

</script>
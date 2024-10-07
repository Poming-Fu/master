<?php
session_start();
require_once '../Device_control/db/db_operations.php';
$conn       = connect_to_db();
$u_acc      = htmlspecialchars($_SESSION['username']);
$who        = htmlspecialchars($_SESSION['username']) . ":" . htmlspecialchars($_SESSION['password']);

function generate_uuid($num) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $UUID = '';
    for ($i = 0; $i < $num; $i++) {
        $UUID .= $characters[rand(0, strlen($characters) - 1)];// - 1 為索引 ex 英文有26位，但位元從0開始，所以要0~25 => 0~(26-1)
    }
    return $UUID;
}


function execute_api($who, $branch, $platform, $ver, $option, $oemname, $UUID) {
    $script_path  = __DIR__ . "/fw_rel_form_api.sh";
    $args         = escapeshellarg($who) . ' ' . 
                    escapeshellarg($branch) . ' ' . 
                    escapeshellarg($platform) . ' ' . 
                    escapeshellarg($ver) . ' ' . 
                    escapeshellarg($option) . ' ' . 
                    escapeshellarg($oemname) . ' ' . 
                    escapeshellarg($UUID);

    $api_command  = "$script_path $args";
    $output       = shell_exec($api_command);

    return [
        'api_command'   => $api_command,
        'output'        => $output
    ];
}


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
    
    $result = execute_api($who, $branch, $platform, $ver, $option, $oemname, $UUID);
    $api_command = $result['api_command'];
    $output      = $result['output'];

    function alert($msg) {
        echo "<script type='text/javascript'>alert('$msg');</script>";
    }

    if ($output === null || trim($output) === '') {
        $output = "not expected error happened ~";
        alert($output);
        exit;
    }


    
    // record db
    fw_r_form_record_history($u_acc, $branch, $platform, $ver, $option, $oemname, $status = 'pending', $UUID);
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
				<td><?= $who ?></td>
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
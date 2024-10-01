<?php
require_once '../Device_control/db/db_operations.php';
$conn = connect_to_db();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ip         = htmlspecialchars($_POST['ip'], ENT_QUOTES, 'UTF-8');
    $table      = "boards";//DB 
    $account    = $_POST['account'];
    $password   = $_POST['password'];
    $unique_pw  = $_POST['unique_pw'];
    $custom_pw  = isset($_POST['custom_pw']) ? $_POST['custom_pw'] : null;
    
    function ping_and_get_ip_status($conn, $table, $ip) {
        // 檢查 IP 是否在線
        $ping_result   = shell_exec("ping -c 1 -W 2 $ip");
        $status        = (strpos($ping_result, '1 received') !== false) ? 'online' : 'offline';

        // 更新資料庫中的狀態
        $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE IP = ?");
        $stmt->bind_param("ss", $status, $ip);
        $stmt->execute();
        $stmt->close();
    }

    function get_board_id_and_ver($conn, $table, $ip, $account, $password, $unique_pw, $custom_pw) {
        // 初始化狀態變量
        $status = null;

        // 獲取板子的狀態
        $stmt = $conn->prepare("SELECT status FROM $table WHERE IP = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();

        if ($status == "online") {
            $passwords = [$password, $unique_pw];
            //$passwords = [$password];
            if ($custom_pw) {
                $passwords[] = $custom_pw;
            }

            $success = false;
            foreach ($passwords as $pw) {
                $get_bmc_info = shell_exec("ipmitool -I lanplus -H $ip -U $account -P $pw raw 0x6 0x1 2>&1");
                $get_bmc_info = trim($get_bmc_info);
                //如果沒有error 就success = true
                if (strpos($get_bmc_info, 'Error') === false) {
                    $success  = true;
                    $password = $pw;
                    break;
                }
            }

            if (!$success) {
                if (!$custom_pw) {
                    echo json_encode(["success" => false, "needCustomPassword" => true, "message" => "Both password \"ADMIN\" and \"$unique_pw\" are login failed.\nPlease enter your custom password."]);
                } else {
                    echo json_encode(["success" => false, "message" => "custom password: $custom_pw login failed."]);
                }
                $stmt = $conn->prepare("UPDATE $table SET B_id = NULL, version = NULL WHERE IP = ?");
                $stmt->bind_param("s", $ip);
                $stmt->execute();
                $stmt->close();
                exit;
            }

            // 拆 BMC info
            $bmc_info_parts = explode(" ", $get_bmc_info);
            $board_id       = $bmc_info_parts[10] . $bmc_info_parts[9];
            $version        = $bmc_info_parts[2] . "." . $bmc_info_parts[3] . "." . $bmc_info_parts[11];

            echo json_encode(["success" => true, "message" => "Reload & Ping pass\nCurrent password: $password\nRequest: $get_bmc_info"]);

            // 更新 DB BMC info
            $stmt = $conn->prepare("UPDATE $table SET B_id = ?, version = ? WHERE IP = ?");
            $stmt->bind_param("sss", $board_id, $version, $ip);
            $stmt->execute();
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "ping $ip fail, please check network"]);

            // 清除DB BMC info
            $stmt = $conn->prepare("UPDATE $table SET B_id = NULL, version = NULL WHERE IP = ?");
            $stmt->bind_param("s", $ip);
            $stmt->execute();
            $stmt->close();
        }
    }

    // 執行函數
    ping_and_get_ip_status($conn, $table, $ip);
    get_board_id_and_ver($conn, $table, $ip, $account, $password, $unique_pw, $custom_pw);

    // 關閉 MySQL 連接
    $conn->close();
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>

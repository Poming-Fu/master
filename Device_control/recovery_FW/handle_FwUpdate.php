<?php
//require_once '../../DB/db_operations.php';
require_once '../../DB/db_operations_all.php';
$conn       = database_connection::get_connection();

//BMC BIOS CPLD 要不要做一起
function check_password($ip, $account, $password, $unique_pw, $custom_pw) {
    $passwords = [$password, $unique_pw];
    //$passwords = [$password];
    if ($custom_pw) {
        $passwords[] = $custom_pw;
    }

    foreach ($passwords as $pw) {
        $get_bmc_info = shell_exec("ipmitool -I lanplus -H $ip -U $account -P $pw raw 0x6 0x1 2>&1");
        $get_bmc_info = trim($get_bmc_info);
        
        if (strpos($get_bmc_info, 'Error') === false) {
            return ["success" => true, "message" => "Password verified successfully", "current_password" => $pw];
            
        }
    }

    if (!$custom_pw) {
        return ["success" => false, "needCustomPassword" => true, "message" => "Both password \"ADMIN\" and \"$unique_pw\" are login failed.\nPlease enter your custom password."];
    } else {
        return ["success" => false, "message" => "custom password: $custom_pw login failed."];
    }
}


function get_latest_FW_bin($directory, $FW_type, $GUID) {
    if ($FW_type == "BMC") {
        $FW_directory = "$directory/$FW_type";
    }
    //以後BIOS 加在這裡

    if (!is_dir($FW_directory)) {
        return null;
    }

    $files = glob($FW_directory . '/*' . $GUID . '*.bin');
    
    if (!$files) {
        return null;
    }

    $latest_file = null;
    $latest_date = 0;

    foreach ($files as $file) {
        $filename = basename($file);
        $token = strtok($filename, '_');
        
        while ($token !== false) {
            if (preg_match('/^\d{8}$/', $token)) {
                $date = (int)$token;
                if ($date > $latest_date) {
                    $latest_date = $date;
                    $latest_file = $file;
                }
                break;
            }
            $token = strtok('_');
        }
    }

    return $latest_file;
}

function get_latest_FW_name($directory, $FW_type, $GUID) {
    $latest_file = get_latest_FW_bin($directory, $FW_type, $GUID);
    return $latest_file ? basename($latest_file) : null;
}
function FW_update_operation($ip, $B_id, $FW_type, $GUID, $BMC_bin_path, $current_password)
{ 
    // 使用相對於當前 PHP 腳本的路徑
    $FW_update_type = strtolower($FW_type);
    $command = __DIR__ . "/FwUpdate.sh $FW_update_type $ip $BMC_bin_path $current_password";
    
    // 執行命令並捕獲輸出和錯誤信息
    $output = shell_exec($command . " 2>&1");

    // 刷新緩衝區
    ob_flush();
    flush();

    // 命令執行成功
    echo json_encode(['success' => true, 'message' => "current_password " . $current_password]);
    exit;
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ip         = htmlspecialchars($_POST['ip'], ENT_QUOTES, 'UTF-8');
    $table      = "boards";//DB 
    $account    = $_POST['account'];
    $password   = $_POST['password'];
    $unique_pw  = $_POST['unique_pw'];
    $custom_pw  = isset($_POST['custom_pw']) ? $_POST['custom_pw'] : null;



    if (isset($_POST['action']) && $_POST['action'] === 'get_latest_FW') {
        $B_id    = $_POST['B_id'];
        $FW_type = $_POST['FW_type'];


        $GUID = boards_repository::get_GUID_by_B_id($B_id);
        if (!$GUID) {
            echo json_encode(["success" => false, "message" => "GUID not found , check the board id"]);
            exit;
        }
        //在web server先做sudo mount -o username=sam,password=sam,iocharset=utf8 //10.148.165.16/Golden_FW /mnt
        $directory = "/mnt";
        if (!is_dir($directory)) {
            echo json_encode(["success" => false, "message" => "NAS directory not found"]);
            exit;
        }

        $latest_FW_name = get_latest_FW_name($directory, $FW_type, $GUID);
        
        if ($latest_FW_name) {
            echo json_encode(["success" => true, "FW_name" => $latest_FW_name]);
        } else {
            echo json_encode(["success" => false, "message" => "No firmware found"]);
        }
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'RF_recovery') {
        $B_id      = $_POST['B_id'];
        $FW_type   = $_POST['FW_type'];    
        $GUID      = boards_repository::get_GUID_by_B_id($B_id);
        $directory = "/mnt";
        
        // 先檢查密碼
        $password_check = check_password($ip, $account, $password, $unique_pw, $custom_pw);
        if (!$password_check['success']) {
            echo json_encode($password_check);
            exit;
        }
        $current_password = $password_check['current_password'];//這邊從return的值拿current_password ， 可以從function check_password找到

        $BMC_bin_path = get_latest_FW_bin($directory, $FW_type, $GUID);
        if (!$BMC_bin_path) {
            echo json_encode(["success" => false, "message" => "BMC_bin not found."]);
            exit;
        }

        // 開始更新操作
        
        FW_update_operation($ip, $B_id, $FW_type, $GUID, $BMC_bin_path, $current_password);
    } else {
        echo json_encode(["success" => false, 'message' => 'Invalid parameter request.']);
    }
}
?>

<?php
//require_once '../DB/db_operations.php';
require_once '../DB/db_operations_all.php';
require_once '../common/common.php';

class device_controller {
    private $username = "ADMIN";
    private $password = "ADMIN";
    private $conn;

    public function __construct() {
        //$this->conn = connect_to_db();
        $this->conn = database_connection::get_connection();
    }

    public function execute_raw_command($board_number, $user_value, $pass_value, $raw_value) {
        $command = "ipmitool -I lanplus -H $board_number -U $user_value -P $pass_value raw $raw_value";
        $result = shell_exec($command);
        return json_encode([
            'command' => $command,
            'result' => $result
        ]);
    }

    public function perform_action($ip, $action) {
        switch ($action) {
            case 'bmc_default_uni_ADMIN':
                $command = "ipmitool -I lanplus -H $ip -U $this->username -P $this->password raw 0x06 0x1";
                break;
            case 'reset':
                $command = "ipmitool -I lanplus -H $ip -U $this->username -P $this->password power reset";
                break;
            case 'on':
                $command = "ipmitool -I lanplus -H $ip -U $this->username -P $this->password power on";
                break;
            case 'off':
                $command = "ipmitool -I lanplus -H $ip -U $this->username -P $this->password power off";
                break;
            case 'NA':
                return json_encode(['success' => false, 'message' => 'Choose an option.']);
            default:
                return json_encode(['success' => false, 'message' => 'Unknown operation.']);
        }

        $output = shell_exec($command . " 2>&1");
        return json_encode(['success' => true, 'message' => $output]);
    }

    public function reload_status($ip, $unique_pw, $custom_pw = null) {
        $this->ping_and_get_ip_status($ip);
        return $this->get_board_id_and_ver($ip, $this->username, $this->password, $unique_pw, $custom_pw);
    }

    private function ping_and_get_ip_status($ip) {
        $ping_result = shell_exec("ping -c 1 -W 2 $ip");
        $status      = (strpos($ping_result, '1 received') !== false) ? 'online' : 'offline';
        boards_repository::update_boards_status($status, $ip);
    }

    private function get_board_id_and_ver($ip, $account, $password, $unique_pw, $custom_pw) {
        $status = boards_repository::query_boards_status($ip);
        if ($status == "online") {
            $passwords = [$password, $unique_pw];
            if ($custom_pw) {
                $passwords[] = $custom_pw;
            }

            $success = false;
            foreach ($passwords as $pw) {
                $get_bmc_info = shell_exec("ipmitool -I lanplus -H $ip -U $account -P $pw raw 0x6 0x1 2>&1");
                $get_bmc_info = trim($get_bmc_info);
                if (strpos($get_bmc_info, 'Error') === false) {
                    $success = true;
                    $password = $pw;
                    break;
                }
            }

            if (!$success) {
                if (!$custom_pw) {
                    return json_encode(["success" => false, "need_custom_password" => true, "message" => "Both password \"ADMIN\" and \"$unique_pw\" are login failed.\nPlease enter your custom password."]);
                } else {
                    return json_encode(["success" => false, "message" => "custom password: $custom_pw login failed."]);
                }
            }

            $bmc_info_parts = explode(" ", $get_bmc_info);
            $board_id = $bmc_info_parts[10] . $bmc_info_parts[9];
            $version = $bmc_info_parts[2] . "." . $bmc_info_parts[3] . "." . $bmc_info_parts[11];

            $stmt = $this->conn->prepare("UPDATE boards SET B_id = ?, version = ? WHERE IP = ?");
            $stmt->bind_param("sss", $board_id, $version, $ip);
            $stmt->execute();
            $stmt->close();

            return json_encode(["success" => true, "message" => "Reload & Ping pass\nCurrent password: $password\nRequest: $get_bmc_info"]);
        } else {
            $stmt = $this->conn->prepare("UPDATE boards SET B_id = NULL, version = NULL WHERE IP = ?");
            $stmt->bind_param("s", $ip);
            $stmt->execute();
            $stmt->close();

            return json_encode(["success" => false, "message" => "ping $ip fail, please check network"]);
        }
    }

    public function enable_console($ip) {
        $command1 = "ipmitool -I lanplus -H $ip -U $this->username -P $this->password raw 0x30 0x70 0x49 0x01 0x01";
        $command2 = "ipmitool -I lanplus -H $ip -U $this->username -P $this->password raw 0x06 0x02";

        $result1 = shell_exec($command1 . " 2>&1");
        $result2 = shell_exec($command2 . " 2>&1");

        return json_encode([
            'success' => true, 
            'message' => $result1 . "\n" . $result2
        ]);
    }

    public function get_boards_alive() {
        return json_encode(boards_repository::query_boards_alive());
    }

    public function upload_boards_FW_file() {
        
    }
}


//call api area 
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $controller = new device_controller();
    $action     = $_GET['action'];

    try {
        switch ($action) {
            case 'execute_raw_command':
                $response = $controller->execute_raw_command(
                    $_POST['board_number'],
                    $_POST['user_value'],
                    $_POST['pass_value'],
                    $_POST['raw_value']
                );
                break;
            case 'perform_action':
                $response = $controller->perform_action($_POST['ip'], $_POST['action']);
                break;
            case 'reload_status':
                $response = $controller->reload_status(
                    $_POST['ip'],
                    $_POST['unique_pw'],
                    $_POST['custom_pw'] ?? null
                );
                break;
            case 'enable_console':
                $response = $controller->enable_console($_POST['ip']);
                break;
            case 'get_boards_alive':
                $response = $controller->get_boards_alive();
                break;
            default:
                throw new Exception('Invalid action');
        }

        echo $response;
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
?>
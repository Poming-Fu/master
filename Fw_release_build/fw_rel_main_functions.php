<?php
session_start();
require_once '../DB/db_operations_all.php';
require_once '../common/common.php';
$u_acc      = htmlspecialchars($_SESSION['username']);
$conn       = database_connection::get_connection();
class firmware_release_controller {

    public function update_jenkins_status() {
    $output = shell_exec(__DIR__ . "/script/fw_rel_get_jenkins_status.sh");
    return json_encode([
        'success' => true,
        'message' => $output
    ]);
    }

    private function execute_form_api($who, $branch, $platform, $ver, $option, $oemname, $UUID) {
        $script_path  = __DIR__ . "/script/fw_rel_form_api.sh";
        $args         = escapeshellarg($who) . ' ' . 
                    escapeshellarg($branch) . ' ' . 
                    escapeshellarg($platform) . ' ' . 
                    escapeshellarg($ver) . ' ' . 
                    escapeshellarg($option) . ' ' . 
                    escapeshellarg($oemname) . ' ' . 
                    escapeshellarg($UUID);
    
        $api_command  = "$script_path $args";
        $output       = shell_exec($api_command);
    
        // 返回陣列而不是 JSON 字串
        return [
            'success' => true,
            'api_command' => $api_command,
            'message' => $output
        ];
        }
    public function submit_fw_rel_form(){
        $u_acc       = htmlspecialchars($_SESSION['username']); //login acc like : ipmi
        $who         = $_POST['who']; //jenkins acc format like : ipmi:ipmi
        $branch      = $_POST['branch'];
        $platform    = $_POST['platform'];
        $ver         = $_POST['ver'];
        $option      = $_POST['option'];
        $oemname     = $_POST['oemname'];
        
        if (!empty($oemname) && !str_ends_with($oemname, '.bin')) {
            $oemname .= '.bin';
        }
    
        //gen uuid
        $UUID        = common::generate_uuid(4);
        // submit time
        $submit_time = date("Y-m-d H:i:s");
        
        $result      = $this->execute_form_api($who, $branch, $platform, $ver, $option, $oemname, $UUID);
        if ($result['message'] == null || trim($result['message']) == '') {
            $response = [
                'success' => false,
                'message' => '請填寫缺少參數'
            ];
            return json_encode($response);
        }
        // record db
        firmware_repository::fw_r_form_record_history($u_acc, $branch, $platform, $ver, $option, $oemname, $status = 'pending', $UUID);
        $response = [
            'success' => true,
            'UUID' => $UUID,
            'message' => $result['message'],
            'api_command' => $result['api_command']
        ];
        return json_encode($response);
    }


}

if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $fw_rel_controller = new firmware_release_controller();
    $action            = $_GET['action'];
    
    try {
        switch ($action) {
            case 'update_jenkins_status':
                $response = $fw_rel_controller->update_jenkins_status();
                break;
            case 'submit_fw_rel_form':
                $response = $fw_rel_controller->submit_fw_rel_form();
                break;
            default:
            throw new Exception('Invalid action');
            
        } 
        echo $response;
    } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
    }

}
?>
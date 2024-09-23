<?php
header('Content-Type: application/json');

function executeBMCOperation($ip, $username, $password, $operation) {
    switch ($operation) {
        case 'bmc_default_uni_ADMIN':
            $command = "ipmitool -I lanplus -H $ip -U $username -P $password raw 0x30 0x48 01";
            break;
        case 'reset':
            $command = "ipmitool -I lanplus -H $ip -U $username -P $password power reset";
            break;
        case 'on':
            $command = "ipmitool -I lanplus -H $ip -U $username -P $password power on";
            break;
        case 'off':
            $command = "ipmitool -I lanplus -H $ip -U $username -P $password power off";
            break;
        case 'NA':
            echo json_encode(['success' => false, 'message' => 'Choose an option.']);
            exit;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown operation.']);
            exit;
    }

    $output = shell_exec($command . " 2>&1");
    echo json_encode(['success' => true, 'message' => $output]);
    exit; // Ensure no further output
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedAction = htmlspecialchars($_POST['action']);

    
    $ip = htmlspecialchars($_POST['ip']);
    $username = 'ADMIN';
    $password = 'ADMIN';

    executeBMCOperation($ip, $username, $password, $selectedAction);
}
?>

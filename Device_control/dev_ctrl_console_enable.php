<?php
header('Content-Type: application/json');

$username = "ADMIN";
$password = "ADMIN";

if (isset($_POST['ip']) && !empty($_POST['ip'])) {
    $ip = htmlspecialchars($_POST['ip'], ENT_QUOTES, 'UTF-8');

    $command1 = "ipmitool -I lanplus -H $ip -U $username -P $password raw 0x30 0x70 0x49 0x01 0x01";
    $command2 = "ipmitool -I lanplus -H $ip -U $username -P $password raw 0x06 0x02";

    $result1 = shell_exec($command1 . " 2>&1");
    $result2 = shell_exec($command2 . " 2>&1");

    echo json_encode([
        'success' => true, 
        'message' => $result1 . "\n" . $result2
    ]);

} else {
    //這裡檢查ip有沒有打過來
    echo json_encode([
        'success' => false,
        'message' => 'IP address is missing or invalid.'
    ]);
}


?>

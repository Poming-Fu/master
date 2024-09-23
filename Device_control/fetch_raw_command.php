<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $board_number = $_POST['board_number'];
    $user_value = $_POST['user_value'];
    $pass_value = $_POST['pass_value'];
    $raw_value = $_POST['raw_value'];

    // 假設命令格式是 "some_command" 並且需要傳遞這些參數
    $command = "ipmitool -I lanplus -H $board_number -U $user_value -P $pass_value raw $raw_value";
    
    // 使用 shell_exec 執行命令並獲取結果
    $result = shell_exec($command);

    // 返回 JSON 格式的結果
    echo json_encode([
        'command' => $command,
        'result' => $result
    ]);
}
?>
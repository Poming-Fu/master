<?php
function update_jenkins_status() {
    $output = shell_exec(__DIR__ . "/fw_r_get_jenkins_status.sh");
    return $output;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = update_jenkins_status();
    echo json_encode(['success' => true, 'message' => 'status updated!!', 'detail' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'POST fail, check PHP & script']);
}
?>
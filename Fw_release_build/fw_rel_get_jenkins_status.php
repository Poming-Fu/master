<?php
session_start();
require_once '../../DB/db_operations.php';
require_once '../../DB/db_operations_all.php';
require_once 'fw_rel_main_function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = update_jenkins_status();
    echo json_encode(['success' => true, 'message' => 'status updated!!', 'detail' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'POST fail, check PHP & script']);
}


?>
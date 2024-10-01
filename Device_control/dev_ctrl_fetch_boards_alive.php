<?php
require_once '../Device_control/db/db_operations.php';
$boards_alive_row_data = query_boards_alive(); 
echo json_encode($boards_alive_row_data);
?>

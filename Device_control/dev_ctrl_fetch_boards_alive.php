<?php
require_once '../DB/db_operations.php';
$boards_alive_row_data = query_boards_alive(); 
echo json_encode($boards_alive_row_data);
?>

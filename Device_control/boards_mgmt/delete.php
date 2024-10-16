<?php
session_start();
//require_once '../../DB/db_operations.php';
require_once '../../DB/db_operations_all.php';
$conn       = database_connection::get_connection();
$username   = $_SESSION['username'];
$user       = users_repository::check_user_in_db($username);
$id         = $_GET['id'];
if (boards_repository::delete_boards_info($id)) {
    $_SESSION['success_message'] = "板子已成功刪除";
} else {
    $_SESSION['error_message'] = "刪除板子時發生錯誤";
}
header("Location: ../dev_ctrl_main.php");
exit;
?>

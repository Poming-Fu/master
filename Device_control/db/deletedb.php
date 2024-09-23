<?php
session_start();
require_once 'db_operations.php';
$conn = connect_to_db();
$username = $_SESSION['username'];
$user = check_user_in_db($conn, $username);


// 如果用戶等級不是 'high'，則拒絕操作
if ($user['u_lev'] != 'high') {
    $_SESSION['del_message'] = "等級為: {$user['u_lev']}，無權限操作";
    header("Location: ../button3.php");
    exit;
} else {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM boards WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['del_message'] = "Record Deleted Successfully";
    } else {
        $_SESSION['del_message'] = "Error Deleting Record";
    }
    
    $stmt->close();
    $conn->close();
    header("Location: ../button3.php");
    exit;
}
?>

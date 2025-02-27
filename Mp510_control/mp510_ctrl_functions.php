<?php
require_once '../DB/db_operations_all.php';
require_once '../common/common.php';

// 確認是 POST 請求且有 action 參數
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // 處理設置 master 的請求
    if ($_POST['action'] === 'set_master' && isset($_POST['new_master_ip'])) {
        $conn = database_connection::get_connection();
        
        try {
            // 開始交易
            $conn->begin_transaction();
            
            // 先將所有節點設為 slave
            $sql1 = "UPDATE mp510 SET node_type = 'slave' WHERE node_type = 'master'";
            $conn->query($sql1);
            
            // 設置新的 master
            $sql2 = "UPDATE mp510 SET node_type = 'master' WHERE mp_ip = ?";
            $stmt = $conn->prepare($sql2);
            $stmt->bind_param("s", $_POST['new_master_ip']);
            $stmt->execute();
            
            // 提交交易
            $conn->commit();
            
            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            // 發生錯誤時回滾
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
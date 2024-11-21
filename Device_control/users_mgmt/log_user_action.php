<?php
require_once '../DB/db_operations_all.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   // header('Content-Type: application/json');
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'log_user_actions') {
        
        $u_acc          = $_POST['u_acc'];
        $action         = $_POST['action'];
        $element_id     = $_POST['element_id'];
        $element_type   = $_POST['element_type'];
        $page_url       = $_POST['page_url'];

        users_repository::log_user_actions($u_acc, $action, $element_id, $element_type, $page_url);

        $response = ['status' => 'success'];
    } else {
        $response = ['status' => 'error', 'message' => 'Missing required parameters'];
    }
    echo json_encode($response);
}
?>


<script>
const u_acc     = "<?php echo htmlspecialchars($_SESSION['username']); ?>"; // 從 session 得到用戶名
const page_url  = window.location.href;

function log_user_actions_collect(action, element, element_type) {
    const element_id = element.attr('id') || element.parent().attr('id') || element.parent().attr('href') || 'undefined';
    
    $.ajax({
        url: window.location.href, // 確保這是正確的 URL
        type: 'POST',
        data: {
            action_type: 'log_user_actions',
            u_acc: u_acc,
            action: action,
            element_id: element_id,
            element_type: element_type,
            page_url: page_url
        },
        success: function(response) {
            console.log('行為已記錄', response.status);
        },
        error: function(xhr, status, error) {
            console.error('記錄行為失敗:', status, error);
        }
    });
}

</script>
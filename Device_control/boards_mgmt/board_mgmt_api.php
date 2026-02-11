<?php
/**
 * Board Management API
 * 統一處理板子管理的所有 API 請求
 * - get_form: 取得新增/修改表單 HTML
 * - insert_board: 新增板子
 * - modify_board: 修改板子
 * - delete_board: 刪除板子
 */

session_start();
require_once '../../DB/db_operations_all.php';
require_once '../../common/analytics/analytics.php';

header('Content-Type: application/json');

$conn     = database_connection::get_connection();
$username = $_SESSION['username'] ?? null;

if (!$username) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit;
}

$user = users_repository::check_user_in_db($username);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ==================== 取得表單 HTML ====================
if ($action === 'get_form') {
    $type = $_GET['type'] ?? ''; // 'insert' or 'modify'

    if ($type === 'insert') {
        $mp_num = $_GET['mp_num'] ?? '';
        $mp_ip = $_GET['mp_ip'] ?? '';
        $locate = $_GET['locate'] ?? '';

        ob_start();
        include 'board_mgmt.php';
        $html = ob_get_clean();

        echo json_encode(['success' => true, 'html' => $html]);

    } elseif ($type === 'modify') {
        $id = $_GET['id'] ?? '';
        $board = boards_repository::query_boards_info_by_id($id);

        if (!$board) {
            echo json_encode(['success' => false, 'message' => '找不到板子資訊']);
            exit;
        }

        ob_start();
        include 'board_mgmt.php';
        $html = ob_get_clean();

        echo json_encode(['success' => true, 'html' => $html]);
    }
    exit;
}

// ==================== 新增板子 ====================
if ($action === 'insert_board') {
    $B_Name     = !empty($_POST['B_Name']) ? $_POST['B_Name'] : NULL;
    $IP         = !empty($_POST['IP']) ? $_POST['IP'] : NULL;
    $BMC_MAC    = !empty($_POST['bmc_nc_mac']) ? $_POST['bmc_nc_mac'] : NULL;
    $L1_MAC     = !empty($_POST['L1_MAC']) ? $_POST['L1_MAC'] : NULL;
    $Unique_pw  = !empty($_POST['unique_pw']) ? $_POST['unique_pw'] : NULL;
    $current_pw = !empty($_POST['current_pw']) ? $_POST['current_pw'] : NULL;
    $Locate     = !empty($_POST['Locate']) ? $_POST['Locate'] : NULL;
    $pw_ip      = !empty($_POST['pw_ip']) ? $_POST['pw_ip'] : NULL;
    $pw_num     = ($_POST['pw_num'] !== '' && $_POST['pw_num'] !== '0') ? $_POST['pw_num'] : NULL;
    $pw_port    = ($_POST['pw_port'] !== '' && $_POST['pw_port'] !== '0') ? $_POST['pw_port'] : NULL;
    $mp_ip      = !empty($_POST['mp_ip']) ? $_POST['mp_ip'] : NULL;
    $mp_num     = ($_POST['mp_num'] !== '' && $_POST['mp_num'] !== '0') ? $_POST['mp_num'] : NULL;
    $mp_com     = ($_POST['mp_com'] !== '' && $_POST['mp_com'] !== '0') ? $_POST['mp_com'] : NULL;
    $note       = !empty($_POST['note']) ? $_POST['note'] : NULL;

    $board_identifier = $IP ?: $B_Name ?: "New Board";

    $new_id = boards_repository::insert_boards_info($B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $current_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note);
    
    if ($new_id) {
        Analytics::track_event('form', 'success', "insert board - $board_identifier");
        echo json_encode(['success' => true, 'message' => '新增成功！', 'board_id' => $new_id]);
    } else {
        Analytics::track_event('form', 'failed', "insert board - $board_identifier");
        echo json_encode(['success' => false, 'message' => '新增失敗']);
    }
    exit;
}

// ==================== 修改板子 ====================
if ($action === 'modify_board') {
    $id         = $_POST['id'];
    $B_Name     = !empty($_POST['B_Name']) ? $_POST['B_Name'] : NULL;
    $IP         = !empty($_POST['IP']) ? $_POST['IP'] : NULL;
    $BMC_MAC    = !empty($_POST['bmc_nc_mac']) ? $_POST['bmc_nc_mac'] : NULL;
    $L1_MAC     = !empty($_POST['L1_MAC']) ? $_POST['L1_MAC'] : NULL;
    $Unique_pw  = !empty($_POST['unique_pw']) ? $_POST['unique_pw'] : NULL;
    $Locate     = !empty($_POST['Locate']) ? $_POST['Locate'] : NULL;
    $pw_ip      = !empty($_POST['pw_ip']) ? $_POST['pw_ip'] : NULL;
    $pw_num     = ($_POST['pw_num'] !== '' && $_POST['pw_num'] !== '0') ? $_POST['pw_num'] : NULL;
    $pw_port    = ($_POST['pw_port'] !== '' && $_POST['pw_port'] !== '0') ? $_POST['pw_port'] : NULL;
    $mp_ip      = !empty($_POST['mp_ip']) ? $_POST['mp_ip'] : NULL;
    $mp_num     = ($_POST['mp_num'] !== '' && $_POST['mp_num'] !== '0') ? $_POST['mp_num'] : NULL;
    $mp_com     = ($_POST['mp_com'] !== '' && $_POST['mp_com'] !== '0') ? $_POST['mp_com'] : NULL;
    $note       = !empty($_POST['note']) ? $_POST['note'] : NULL;
    $current_pw = !empty($_POST['current_pw']) ? $_POST['current_pw'] : NULL;

    $board_identifier = $IP ?: $B_Name ?: "ID:$id";
    
    if (boards_repository::modify_boards_info($B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $current_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note, $id)) {
        Analytics::track_event('form', 'success', "modify board - $board_identifier");
        echo json_encode(['success' => true, 'message' => '更新成功！', 'board_id' => $id]);
    } else {
        Analytics::track_event('form', 'failed', "modify board - $board_identifier");
        echo json_encode(['success' => false, 'message' => '更新失敗']);
    }
    exit;
}

// ==================== 刪除板子 ====================
if ($action === 'delete_board') {
    $id = $_POST['id'] ?? $_GET['id'] ?? '';

    if (!$id) {
        echo json_encode(['success' => false, 'message' => '缺少板子 ID']);
        exit;
    }

    // 先取得板子資訊用於 Analytics
    $board = boards_repository::query_boards_info_by_id($id);
    $board_identifier = $board['IP'] ?: $board['B_Name'] ?: "ID:$id";

    if (boards_repository::delete_boards_info($id)) {
        Analytics::track_event('form', 'success', "delete board - $board_identifier");
        echo json_encode(['success' => true, 'message' => '板子已成功刪除']);
    } else {
        Analytics::track_event('form', 'failed', "delete board - $board_identifier");
        echo json_encode(['success' => false, 'message' => '刪除板子時發生錯誤']);
    }
    exit;
}

// ==================== 無效操作 ====================
echo json_encode(['success' => false, 'message' => '無效的操作']);


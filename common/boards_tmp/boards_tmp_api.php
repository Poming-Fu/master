<?php
/**
 * Boards Tmp API
 * 統一處理 boards_tmp 管理的所有 API 請求
 * - get_board: 取得單筆板子資訊
 * - insert_board: 新增板子
 * - modify_board: 修改板子
 * - delete_board: 刪除板子
 */

session_start();
require_once '../../DB/db_operations_all.php';

header('Content-Type: application/json');

$username = $_SESSION['username'] ?? null;

if (!$username) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ==================== 取得單筆板子資訊 ====================
if ($action === 'get_board') {
    $b_id = $_GET['b_id'] ?? '';

    if (empty($b_id)) {
        echo json_encode(['success' => false, 'message' => '缺少 b_id']);
        exit;
    }

    $board = boards_tmp_repository::query_boards_tmp_by_id($b_id);

    if ($board) {
        echo json_encode(['success' => true, 'data' => $board]);
    } else {
        echo json_encode(['success' => false, 'message' => '找不到板子資訊']);
    }
    exit;
}

// ==================== 新增板子 ====================
if ($action === 'insert_board') {
    $b_id        = !empty($_POST['b_id']) ? $_POST['b_id'] : NULL;
    $b_name      = !empty($_POST['b_name']) ? $_POST['b_name'] : NULL;
    $guid        = !empty($_POST['guid']) ? $_POST['guid'] : NULL;
    $pbid        = ($_POST['pbid'] !== '' && $_POST['pbid'] !== null) ? intval($_POST['pbid']) : NULL;
    $pbid_oem    = ($_POST['pbid_oem'] !== '' && $_POST['pbid_oem'] !== null) ? intval($_POST['pbid_oem']) : NULL;
    $bmc_chip    = !empty($_POST['bmc_chip']) ? $_POST['bmc_chip'] : NULL;
    $bmc_type    = !empty($_POST['bmc_type']) ? $_POST['bmc_type'] : NULL;
    $rot_pfr     = !empty($_POST['rot_pfr']) ? $_POST['rot_pfr'] : NULL;
    $redfish     = !empty($_POST['redfish']) ? $_POST['redfish'] : NULL;
    $target      = !empty($_POST['target']) ? $_POST['target'] : NULL;
    $fw_size     = !empty($_POST['fw_size']) ? $_POST['fw_size'] : NULL;
    $owner       = !empty($_POST['owner']) ? $_POST['owner'] : NULL;
    $gitlab_type = !empty($_POST['gitlab_type']) ? $_POST['gitlab_type'] : NULL;
    $gitlab_id   = ($_POST['gitlab_id'] !== '' && $_POST['gitlab_id'] !== null) ? intval($_POST['gitlab_id']) : NULL;
    $notes       = !empty($_POST['notes']) ? $_POST['notes'] : NULL;
    $branch      = !empty($_POST['branch']) ? $_POST['branch'] : 'master';

    if (empty($b_id)) {
        echo json_encode(['success' => false, 'message' => 'Board ID 為必填']);
        exit;
    }

    // 檢查 b_id 是否已存在
    $existing = boards_tmp_repository::query_boards_tmp_by_id($b_id);
    if ($existing) {
        echo json_encode(['success' => false, 'message' => "Board ID '$b_id' 已存在"]);
        exit;
    }

    $result = boards_tmp_repository::insert_boards_tmp_info($b_id, $b_name, $guid, $pbid, $pbid_oem, $bmc_chip, $bmc_type, $rot_pfr, $redfish, $target, $fw_size, $owner, $gitlab_type, $gitlab_id, $notes, $branch);

    if ($result) {
        echo json_encode(['success' => true, 'message' => '新增成功！']);
    } else {
        echo json_encode(['success' => false, 'message' => '新增失敗']);
    }
    exit;
}

// ==================== 修改板子 ====================
if ($action === 'modify_board') {
    $b_id        = $_POST['b_id'] ?? '';
    $b_name      = !empty($_POST['b_name']) ? $_POST['b_name'] : NULL;
    $guid        = !empty($_POST['guid']) ? $_POST['guid'] : NULL;
    $pbid        = ($_POST['pbid'] !== '' && $_POST['pbid'] !== null) ? intval($_POST['pbid']) : NULL;
    $pbid_oem    = ($_POST['pbid_oem'] !== '' && $_POST['pbid_oem'] !== null) ? intval($_POST['pbid_oem']) : NULL;
    $bmc_chip    = !empty($_POST['bmc_chip']) ? $_POST['bmc_chip'] : NULL;
    $bmc_type    = !empty($_POST['bmc_type']) ? $_POST['bmc_type'] : NULL;
    $rot_pfr     = !empty($_POST['rot_pfr']) ? $_POST['rot_pfr'] : NULL;
    $redfish     = !empty($_POST['redfish']) ? $_POST['redfish'] : NULL;
    $target      = !empty($_POST['target']) ? $_POST['target'] : NULL;
    $fw_size     = !empty($_POST['fw_size']) ? $_POST['fw_size'] : NULL;
    $owner       = !empty($_POST['owner']) ? $_POST['owner'] : NULL;
    $gitlab_type = !empty($_POST['gitlab_type']) ? $_POST['gitlab_type'] : NULL;
    $gitlab_id   = ($_POST['gitlab_id'] !== '' && $_POST['gitlab_id'] !== null) ? intval($_POST['gitlab_id']) : NULL;
    $notes       = !empty($_POST['notes']) ? $_POST['notes'] : NULL;
    $branch      = !empty($_POST['branch']) ? $_POST['branch'] : 'master';

    if (empty($b_id)) {
        echo json_encode(['success' => false, 'message' => '缺少 Board ID']);
        exit;
    }

    if (boards_tmp_repository::modify_boards_tmp_info($b_name, $guid, $pbid, $pbid_oem, $bmc_chip, $bmc_type, $rot_pfr, $redfish, $target, $fw_size, $owner, $gitlab_type, $gitlab_id, $notes, $branch, $b_id)) {
        echo json_encode(['success' => true, 'message' => '更新成功！']);
    } else {
        echo json_encode(['success' => false, 'message' => '更新失敗']);
    }
    exit;
}

// ==================== 刪除板子 ====================
if ($action === 'delete_board') {
    $b_id = $_POST['b_id'] ?? $_GET['b_id'] ?? '';

    if (empty($b_id)) {
        echo json_encode(['success' => false, 'message' => '缺少 Board ID']);
        exit;
    }

    if (boards_tmp_repository::delete_boards_tmp_info($b_id)) {
        echo json_encode(['success' => true, 'message' => '刪除成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '刪除失敗']);
    }
    exit;
}

// ==================== 無效操作 ====================
echo json_encode(['success' => false, 'message' => '無效的操作']);

<?php
/**
 * RF Schedule API
 * 處理 rf_schedule 的 CRUD 請求
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

// ==================== 取得單筆 ====================
if ($action === 'get') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => '缺少 id']);
        exit;
    }
    $row = rf_schedule_repository::query_by_id($id);
    if ($row) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => '找不到資料']);
    }
    exit;
}

// ==================== 新增 ====================
if ($action === 'insert') {
    $quarter    = !empty($_POST['quarter']) ? $_POST['quarter'] : NULL;
    $rf_version = !empty($_POST['rf_version']) ? $_POST['rf_version'] : NULL;
    $gen12      = !empty($_POST['gen12']) ? $_POST['gen12'] : NULL;
    $gen13      = !empty($_POST['gen13']) ? $_POST['gen13'] : NULL;
    $gen14      = !empty($_POST['gen14']) ? $_POST['gen14'] : NULL;
    $lbmc       = !empty($_POST['lbmc']) ? $_POST['lbmc'] : NULL;
    $obmc       = !empty($_POST['obmc']) ? $_POST['obmc'] : NULL;
    $sort_order = intval($_POST['sort_order'] ?? 0);

    if (empty($quarter)) {
        echo json_encode(['success' => false, 'message' => 'Quarter 為必填']);
        exit;
    }

    if (rf_schedule_repository::insert($quarter, $rf_version, $gen12, $gen13, $gen14, $lbmc, $obmc, $sort_order)) {
        echo json_encode(['success' => true, 'message' => '新增成功！']);
    } else {
        echo json_encode(['success' => false, 'message' => '新增失敗']);
    }
    exit;
}

// ==================== 修改 ====================
if ($action === 'modify') {
    $id         = intval($_POST['id'] ?? 0);
    $quarter    = !empty($_POST['quarter']) ? $_POST['quarter'] : NULL;
    $rf_version = !empty($_POST['rf_version']) ? $_POST['rf_version'] : NULL;
    $gen12      = !empty($_POST['gen12']) ? $_POST['gen12'] : NULL;
    $gen13      = !empty($_POST['gen13']) ? $_POST['gen13'] : NULL;
    $gen14      = !empty($_POST['gen14']) ? $_POST['gen14'] : NULL;
    $lbmc       = !empty($_POST['lbmc']) ? $_POST['lbmc'] : NULL;
    $obmc       = !empty($_POST['obmc']) ? $_POST['obmc'] : NULL;
    $sort_order = intval($_POST['sort_order'] ?? 0);

    if (!$id) {
        echo json_encode(['success' => false, 'message' => '缺少 id']);
        exit;
    }

    if (rf_schedule_repository::modify($quarter, $rf_version, $gen12, $gen13, $gen14, $lbmc, $obmc, $sort_order, $id)) {
        echo json_encode(['success' => true, 'message' => '更新成功！']);
    } else {
        echo json_encode(['success' => false, 'message' => '更新失敗']);
    }
    exit;
}

// ==================== 刪除 ====================
if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => '缺少 id']);
        exit;
    }

    if (rf_schedule_repository::delete($id)) {
        echo json_encode(['success' => true, 'message' => '刪除成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '刪除失敗']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => '無效的操作']);

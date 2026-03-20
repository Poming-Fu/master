<?php
/**
 * Boards Tmp 管理頁面
 * 提供 boards_tmp 的 CRUD 管理介面
 */

session_start();
require_once '../../DB/db_operations_all.php';
require_once '../common.php';

// 檢查用戶是否登入
common::check_login();
$username = $_SESSION['username'];

// 取得所有 boards_tmp 資料
$boards = boards_tmp_repository::query_boards_tmp_info();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPMI web service - Boards Tmp 管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../../login_out/navbar.css" rel="stylesheet">
    <link href="../common.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .table-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .table-card .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
        }

        .table-card .card-header h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--gray-800);
        }

        .table-card table {
            margin: 0;
            font-size: 13px;
        }

        .table-card thead {
            background: var(--gray-50);
        }

        .table-card th {
            font-size: 13px;
            font-weight: 600;
            padding: 10px 12px;
            white-space: nowrap;
        }

        .table-card td {
            padding: 8px 12px;
            vertical-align: middle;
        }

        .action-btns {
            display: flex;
            gap: 4px;
        }

        .btn-icon {
            border: none;
            background: none;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-icon.edit {
            color: var(--primary-color);
        }

        .btn-icon.edit:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        .btn-icon.delete {
            color: var(--danger-color);
        }

        .btn-icon.delete:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        /* Modal 表單樣式 */
        .form-body .form-section {
            margin-bottom: 20px;
        }

        .form-body .section-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-body .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .form-body .form-grid.single {
            grid-template-columns: 1fr;
        }

        .form-body .form-grid.triple {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .form-body .form-field label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-600);
            margin-bottom: 4px;
        }

        .form-body .form-field input,
        .form-body .form-field select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-body .form-field input:focus,
        .form-body .form-field select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-body .required {
            color: var(--danger-color);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding-top: 16px;
            border-top: 1px solid var(--gray-200);
        }

        .badge-chip {
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* DataTables 控制項位置調整 */
        .dataTables_length {
            margin-left: 20px;
        }

        .dataTables_length select {
            padding: 6px 30px 6px 12px !important;
            min-width: 70px;
        }
    </style>
</head>
<body>

<?php include '../../login_out/navbar.php'; ?>

<div class="container-fluid px-3 py-3">
    <!-- 頁面標題 -->
    <div class="page-header">
        <h2><i class="bi bi-motherboard"></i> Boards Tmp 管理</h2>
    </div>

    <!-- 狀態列 -->
    <div class="status-bar">
        <div class="status-info">
            <div class="status-item">
                <i class="bi bi-list-ul" style="color: var(--primary-color);"></i>
                <span>總數: <strong><?php echo count($boards); ?></strong></span>
            </div>
        </div>
        <div class="status-actions">
            <button class="btn btn-success btn-sm" onclick="openInsertModal()">
                <i class="bi bi-plus-lg"></i> 新增板子
            </button>
        </div>
    </div>

    <!-- 資料表格 -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="boardsTmpTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>B_ID</th>
                        <th>Board Name</th>
                        <th>GUID</th>
                        <th>PBID</th>
                        <th>PBID OEM</th>
                        <th>BMC Chip</th>
                        <th>BMC Type</th>
                        <th>ROT/PFR</th>
                        <th>Redfish</th>
                        <th>Target</th>
                        <th>FW Size</th>
                        <th>Owner</th>
                        <th>GitLab</th>
                        <th>Notes</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($boards as $board): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($board['b_id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($board['b_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($board['guid'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($board['pbid'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($board['pbid_oem'] ?? ''); ?></td>
                        <td>
                            <?php if ($board['bmc_chip']):
                                $chip_color = match($board['bmc_chip']) {
                                    'AST2500' => 'bg-secondary',
                                    'AST2600' => 'bg-warning text-dark',
                                    'AST2700' => 'bg-danger',
                                    default   => 'bg-info',
                                };
                            ?>
                            <span class="badge <?php echo $chip_color; ?> badge-chip"><?php echo htmlspecialchars($board['bmc_chip']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($board['bmc_type']): ?>
                            <span class="badge <?php echo $board['bmc_type'] === 'openbmc' ? 'bg-success' : 'bg-primary'; ?> badge-chip">
                                <?php echo htmlspecialchars($board['bmc_type']); ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($board['rot_pfr'] ?? ''); ?></td>
                        <td><small><?php echo htmlspecialchars($board['redfish'] ?? ''); ?></small></td>
                        <td><small><?php echo htmlspecialchars($board['target'] ?? ''); ?></small></td>
                        <td><?php echo htmlspecialchars($board['fw_size'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($board['owner'] ?? ''); ?></td>
                        <td>
                            <?php if ($board['gitlab_id']): ?>
                            <small><?php echo htmlspecialchars($board['gitlab_type'] ?? ''); ?> #<?php echo htmlspecialchars($board['gitlab_id']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><small><?php echo htmlspecialchars($board['notes'] ?? ''); ?></small></td>
                        <td>
                            <div class="action-btns">
                                <button type="button" class="btn-icon edit"
                                        onclick="openModifyModal('<?php echo htmlspecialchars($board['b_id']); ?>')"
                                        title="編輯">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn-icon delete"
                                        onclick="deleteBoard('<?php echo htmlspecialchars($board['b_id']); ?>', '<?php echo htmlspecialchars($board['b_name'] ?? $board['b_id']); ?>')"
                                        title="刪除">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 新增/修改 Modal -->
<div class="modal fade" id="boardModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="boardModalTitle">新增板子</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="boardForm" class="form-body">
                    <input type="hidden" name="action" id="formAction" value="insert_board">

                    <!-- Board ID & Name -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-motherboard"></i>
                            基本資訊
                        </div>
                        <div class="form-grid">
                            <div class="form-field">
                                <label>Board ID <span class="required">*</span></label>
                                <input type="text" name="b_id" id="f_b_id" placeholder="如 1D1A" required>
                            </div>
                            <div class="form-field">
                                <label>Board Name</label>
                                <input type="text" name="b_name" id="f_b_name" placeholder="如 X13SCL_F">
                            </div>
                            <div class="form-field">
                                <label>GUID</label>
                                <input type="text" name="guid" id="f_guid" placeholder="如 5201MS">
                            </div>
                            <div class="form-field">
                                <label>Owner</label>
                                <input type="text" name="owner" id="f_owner" placeholder="如 Miko / Ivan">
                            </div>
                        </div>
                    </div>

                    <!-- PBID -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-hash"></i>
                            PBID
                        </div>
                        <div class="form-grid">
                            <div class="form-field">
                                <label>PBID (ATEN)</label>
                                <input type="number" name="pbid" id="f_pbid" placeholder="如 20899">
                            </div>
                            <div class="form-field">
                                <label>PBID OEM (INTERNAL)</label>
                                <input type="number" name="pbid_oem" id="f_pbid_oem" placeholder="如 21992">
                            </div>
                        </div>
                    </div>

                    <!-- BMC Info -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-cpu"></i>
                            BMC 資訊
                        </div>
                        <div class="form-grid triple">
                            <div class="form-field">
                                <label>BMC Chip</label>
                                <select name="bmc_chip" id="f_bmc_chip">
                                    <option value="">-- 選擇 --</option>
                                    <option value="AST2500">AST2500</option>
                                    <option value="AST2600">AST2600</option>
                                    <option value="AST2700">AST2700</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>BMC Type</label>
                                <select name="bmc_type" id="f_bmc_type">
                                    <option value="">-- 選擇 --</option>
                                    <option value="legacybmc">legacybmc</option>
                                    <option value="openbmc">openbmc</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>ROT / PFR</label>
                                <select name="rot_pfr" id="f_rot_pfr">
                                    <option value="">-- 選擇 --</option>
                                    <option value="ROT1.0">ROT1.0</option>
                                    <option value="ROT2">ROT2</option>
                                    <option value="nonROT">nonROT</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Build Info -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-gear"></i>
                            建置資訊
                        </div>
                        <div class="form-grid">
                            <div class="form-field">
                                <label>Target</label>
                                <input type="text" name="target" id="f_target" placeholder="如 x12_rot2_ast26_p">
                            </div>
                            <div class="form-field">
                                <label>FW Size</label>
                                <select name="fw_size" id="f_fw_size">
                                    <option value="">-- 選擇 --</option>
                                    <option value="32MB">32MB</option>
                                    <option value="64MB">64MB</option>
                                    <option value="128MB">128MB</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>Redfish</label>
                                <input type="text" name="redfish" id="f_redfish" placeholder="如 redfish_x11_2500">
                            </div>
                        </div>
                    </div>

                    <!-- GitLab -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-git"></i>
                            GitLab
                        </div>
                        <div class="form-grid">
                            <div class="form-field">
                                <label>GitLab Type</label>
                                <input type="text" name="gitlab_type" id="f_gitlab_type" placeholder="如 GitLab US" value="GitLab US">
                            </div>
                            <div class="form-field">
                                <label>GitLab ID</label>
                                <input type="number" name="gitlab_id" id="f_gitlab_id" placeholder="如 2150">
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-sticky"></i>
                            備註
                        </div>
                        <div class="form-grid single">
                            <div class="form-field">
                                <label>Notes</label>
                                <input type="text" name="notes" id="f_notes" placeholder="備註，如 EOL">
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-left"></i> 取消
                        </button>
                        <button type="submit" class="btn btn-success" id="formSubmitBtn">
                            <i class="bi bi-plus-lg"></i> 新增
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
// DataTable 初始化
$(document).ready(function() {
    $('#boardsTmpTable').DataTable({
        order: [[1, 'asc']], // 按 Board Name 排序
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "全部"]],
        language: {
            search: "搜尋:",
            lengthMenu: "顯示 _MENU_ 筆",
            info: "顯示 _START_ 到 _END_ 筆，共 _TOTAL_ 筆",
            infoEmpty: "沒有資料",
            infoFiltered: "(從 _MAX_ 筆中篩選)",
            paginate: {
                first: "第一頁",
                last: "最後一頁",
                next: "下一頁",
                previous: "上一頁"
            },
            zeroRecords: "沒有符合的資料",
            emptyTable: "目前沒有資料"
        },
        columnDefs: [
            { orderable: false, targets: [14] } // "操作"欄位不排序
        ]
    });
});

// 開啟新增 Modal
function openInsertModal() {
    $('#boardModalTitle').text('新增板子');
    $('#formAction').val('insert_board');
    $('#formSubmitBtn').removeClass('btn-primary').addClass('btn-success')
        .html('<i class="bi bi-plus-lg"></i> 新增');
    $('#f_b_id').prop('readonly', false);
    $('#boardForm')[0].reset();
    // 預設值
    $('#f_gitlab_type').val('GitLab US');

    let modal = new bootstrap.Modal(document.getElementById('boardModal'));
    modal.show();
}

// 開啟修改 Modal
function openModifyModal(b_id) {
    $.ajax({
        url: 'boards_tmp_api.php?action=get_board&b_id=' + encodeURIComponent(b_id),
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let board = response.data;

                $('#boardModalTitle').text('修改板子 - ' + b_id);
                $('#formAction').val('modify_board');
                $('#formSubmitBtn').removeClass('btn-success').addClass('btn-primary')
                    .html('<i class="bi bi-check-lg"></i> 儲存變更');
                $('#f_b_id').val(board.b_id).prop('readonly', true);
                $('#f_b_name').val(board.b_name || '');
                $('#f_guid').val(board.guid || '');
                $('#f_pbid').val(board.pbid || '');
                $('#f_pbid_oem').val(board.pbid_oem || '');
                $('#f_bmc_chip').val(board.bmc_chip || '');
                $('#f_bmc_type').val(board.bmc_type || '');
                $('#f_rot_pfr').val(board.rot_pfr || '');
                $('#f_redfish').val(board.redfish || '');
                $('#f_target').val(board.target || '');
                $('#f_fw_size').val(board.fw_size || '');
                $('#f_owner').val(board.owner || '');
                $('#f_gitlab_type').val(board.gitlab_type || '');
                $('#f_gitlab_id').val(board.gitlab_id || '');
                $('#f_notes').val(board.notes || '');

                let modal = new bootstrap.Modal(document.getElementById('boardModal'));
                modal.show();
            } else {
                alert('取得板子資訊失敗: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('AJAX 請求失敗: ' + error);
        }
    });
}

// 表單送出
$('#boardForm').on('submit', function(e) {
    e.preventDefault();

    let formDOM = this;
    formDOM.classList.add('was-validated');

    if (!formDOM.checkValidity()) {
        return;
    }

    let formData = $(this).serialize();
    let action = $('#formAction').val();

    $.ajax({
        url: 'boards_tmp_api.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('失敗: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('AJAX 請求失敗: ' + error);
        }
    });
});

// 刪除板子
function deleteBoard(b_id, name) {
    if (!confirm('確定要刪除 ' + name + ' (' + b_id + ') 嗎？')) {
        return;
    }

    $.ajax({
        url: 'boards_tmp_api.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'delete_board', b_id: b_id },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('刪除失敗: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('AJAX 請求失敗: ' + error);
        }
    });
}
</script>
</body>
</html>

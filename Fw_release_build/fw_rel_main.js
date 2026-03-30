
// DataTable 實例
let historyTable;
// Release 模式下查到的板子資料暫存
let matchedBoards = [];

function update_jenkins_status() {
    $.ajax({
        url: 'fw_rel_main_functions.php?action=update_jenkins_status',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                //console.log(response.message);
                // 重新載入 history 區塊並重新初始化 DataTable
                $('#history').load(location.href + ' #history>*', function() {
                    initDataTable();
                });
                $('#build-status').load(location.href + ' #build-status>*');
            } else {
                console.error('Failed:', response.message);
                location.reload();
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX failed:', error);
        }
    });
}

function initDataTable() {
    //Fix「Cannot reinitialise DataTable」
    if ($.fn.DataTable.isDataTable('#historyTable')) {
        $('#historyTable').DataTable().destroy();
    }

    historyTable = $('#historyTable').DataTable({
        order: [[2, 'desc']], // 按時間(第三格 0 1 2)降序排列
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            search: "搜尋:",
            lengthMenu: "顯示 _MENU_ 筆記錄",
            info: "顯示 _START_ 到 _END_ 筆，共 _TOTAL_ 筆",
            infoEmpty: "沒有資料",
            infoFiltered: "(從 _MAX_ 筆記錄中篩選)",
            zeroRecords: "沒有符合的記錄",
            paginate: {
                first: "首頁",
                last: "末頁",
                next: "下一頁",
                previous: "上一頁"
            }
        },
        columnDefs: [
            { orderable: false, targets: 0 } // Status 欄位不排序
        ]
    });
}

// ==================== STD / OEM 切換 ====================
function toggleBuildType() {
    let isOem = $('input[name="build_type"]:checked').val() === 'oem';
    if (isOem) {
        $('#oemname-group').slideDown();
    } else {
        $('#oemname-group').slideUp();
        $('#oemname').val('');
    }
}

// ==================== Release / Only Build 切換 ====================
function toggleBuildMode() {
    let isRelease = $('input[name="build_mode"]:checked').val() === 'release';
    if (isRelease) {
        $('#release-board-group').slideDown();
        $('#build-form-submit-btn').hide();
    } else {
        $('#release-board-group').slideUp();
        $('#release-board-info').slideUp();
        $('#build-form-submit-btn').show();
        matchedBoards = [];
    }
}

// ==================== 根據 platform 查詢對應板子 ====================
function fetchBoardsByPlatform() {
    let platform = $('#platform').val().trim();
    if (!platform) return;

    let isRelease = $('input[name="build_mode"]:checked').val() === 'release';
    if (!isRelease) return;

    $.ajax({
        url: 'fw_rel_main_functions.php?action=get_boards_by_target&target=' + encodeURIComponent(platform),
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            let $select = $('#release-board-select');
            $select.empty();
            matchedBoards = [];
            $('#release-board-info').slideUp();

            if (response.success && response.data.length > 0) {
                matchedBoards = response.data;
                $select.append('<option value="" disabled selected>請選擇板子</option>');
                response.data.forEach(function(board) {
                    $select.append(
                        $('<option>').val(board.b_id).text(
                            '[' + board.b_id + '] ' + board.b_name + ' | GUID: ' + (board.guid || 'N/A') + ' | PBID: ' + (board.pbid || 'N/A')
                        )
                    );
                });
            } else {
                $select.append('<option disabled>沒有找到對應的板子</option>');
            }
        },
        error: function() {
            console.error('查詢板子失敗');
        }
    });
}

// ==================== 選擇板子後顯示 Board Info ====================
function showBoardInfo() {
    let selectedId = $('#release-board-select').val();
    if (!selectedId) {
        $('#release-board-info').slideUp();
        return;
    }

    let board = matchedBoards.find(b => b.b_id === selectedId);
    if (!board) {
        $('#release-board-info').slideUp();
        return;
    }

    let buildType = $('input[name="build_type"]:checked').val();
    let pbid = (buildType === 'oem') ? (board.pbid_oem || '') : (board.pbid || '');

    $('#release-bmc-type').val(board.bmc_type || '');
    $('#release-gitlab-type').val(board.gitlab_type || '');
    $('#release-gitlab-id').val(board.gitlab_id || '');
    $('#release-guid').val(board.guid || '');
    $('#release-pbid').val(pbid);
    $('#release-board-name').val(board.b_name || '');

    $('#release-board-info').slideDown();
}


$(document).ready(function() {
    // 初始化 DataTable
    initDataTable();

    // STD / OEM 切換
    $('input[name="build_type"]').on('change', toggleBuildType);

    // Release / Only Build 切換
    $('input[name="build_mode"]').on('change', toggleBuildMode);

    // Platform 欄位 blur 時查詢板子
    $('#platform').on('blur', fetchBoardsByPlatform);

    // 選擇板子後顯示 Board Info
    $('#release-board-select').on('change', showBoardInfo);

    // STD/OEM 切換時更新 PBID
    $('input[name="build_type"]').on('change', showBoardInfo);

    // Gen Release Note 按鈕
    $('#gen-release-note-btn').on('click', function() {
        let $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Generating...');

        $.ajax({
            type: 'POST',
            url: 'fw_rel_main_functions.php?action=gen_release_note',
            data: {
                build_type:  $('input[name="build_type"]:checked').val(),
                build_mode:  $('input[name="build_mode"]:checked').val(),
                branch:      $('#branch').val(),
                platform:    $('#platform').val(),
                ver:         $('#ver').val(),
                option:      $('#option').val(),
                oemname:     $('#oemname').val() || 'N/A',
                b_id:        $('#release-board-select').val(),
                board_name:  $('#release-board-name').val(),
                bmc_type:    $('#release-bmc-type').val(),
                guid:        $('#release-guid').val(),
                pbid:        $('#release-pbid').val(),
                gitlab_type: $('#release-gitlab-type').val(),
                gitlab_id:   $('#release-gitlab-id').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#release-note-content').text(response.content);
                    $('#release-note-paths').text('Saved: ' + response.tmp_path + ' & ' + response.release_path);
                    $('#release-note-output').slideDown();
                    $('#build-form-submit-btn').show();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Gen Release Note failed:', error);
                alert('Gen Release Note 失敗');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-file-earmark-text"></i> Gen Release Note');
            }
        });
    });

    $('#build-form-submit-btn').click(function(e) {
        e.preventDefault();
        let formData = $(this).closest('form').serialize(); //表單值 who, branch, target...
        let formDOM  = $(this).closest('form')[0]; //元素
        //document.getElementById("myDIV").classList.add("mystyle");
        formDOM.classList.add('was-validated');  // 加 Bootstrap 驗證樣式

        if (!formDOM.checkValidity()) {
            return;
        }

        // 驗證 version 格式: legacy xx.xx.xx 或 openbmc xx.xx.xx.xx
        let verVal = $('#ver').val().trim();
        let verRegex = /^\d{1,2}\.\d{1,2}\.\d{1,2}(\.\d{1,2})?$/;
        if (!verRegex.test(verVal)) {
            alert('Version 格式不正確！\n請輸入：\n  Legacy BMC: xx.xx.xx (例: 01.01.01)\n  OpenBMC: xx.xx.xx.xx (例: 01.02.03.01)');
            //滑鼠會移到這裡
            $('#ver').focus();
            return;
        }

        if(confirm('確定要送出表單嗎?')) {
            $.ajax({
                type: "POST",
                url: "fw_rel_main_functions.php?action=submit_fw_rel_form",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // 創建彈出視窗的 HTML 內容
                        let popupContent = `
                            <!DOCTYPE html>
                            <html lang="zh-TW">
                            <head>
                                <meta charset="UTF-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                <title>Build Result - Success</title>
                                <style>
                                    :root {
                                        --primary-color: #3b82f6;
                                        --success-color: #22c55e;
                                        --gray-50: #f9fafb;
                                        --gray-100: #f3f4f6;
                                        --gray-200: #e5e7eb;
                                        --gray-300: #d1d5db;
                                        --gray-600: #4b5563;
                                        --gray-700: #374151;
                                        --gray-800: #1f2937;
                                        --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
                                        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                                        --radius: 12px;
                                        --radius-sm: 8px;
                                    }

                                    * {
                                        margin: 0;
                                        padding: 0;
                                        box-sizing: border-box;
                                    }

                                    body {
                                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                                        background: var(--gray-100);
                                        padding: 24px;
                                        line-height: 1.6;
                                    }

                                    .container {
                                        max-width: 700px;
                                        margin: 0 auto;
                                    }

                                    .header {
                                        background: white;
                                        border: 2px solid var(--success-color);
                                        border-radius: var(--radius);
                                        padding: 24px;
                                        margin-bottom: 20px;
                                        box-shadow: var(--shadow);
                                        text-align: center;
                                    }

                                    .header h1 {
                                        font-size: 24px;
                                        color: var(--gray-800);
                                        margin-bottom: 8px;
                                        font-weight: 600;
                                    }

                                    .header p {
                                        color: var(--gray-600);
                                        font-size: 14px;
                                        margin: 0;
                                    }

                                    .card {
                                        background: white;
                                        border: 1px solid var(--gray-200);
                                        border-radius: var(--radius);
                                        padding: 24px;
                                        box-shadow: var(--shadow);
                                    }

                                    .info-row {
                                        display: flex;
                                        padding: 14px 0;
                                        border-bottom: 1px solid var(--gray-200);
                                    }

                                    .info-row:last-child {
                                        border-bottom: none;
                                    }

                                    .info-label {
                                        flex: 0 0 120px;
                                        font-weight: 600;
                                        color: var(--gray-700);
                                        font-size: 14px;
                                    }

                                    .info-value {
                                        flex: 1;
                                        color: var(--gray-800);
                                        font-size: 14px;
                                        word-break: break-all;
                                    }

                                    .uuid-value {
                                        font-family: 'Courier New', monospace;
                                        background: var(--gray-50);
                                        padding: 4px 8px;
                                        border-radius: 4px;
                                        font-size: 13px;
                                    }

                                    .output-value {
                                        background: var(--gray-50);
                                        padding: 12px;
                                        border-radius: var(--radius-sm);
                                        font-family: 'Courier New', monospace;
                                        font-size: 13px;
                                        white-space: pre-wrap;
                                        word-break: break-word;
                                    }
                                </style>
                            </head>
                            <body>
                                <div class="container">
                                    <div class="header">
                                        <h1>Build Submitted Successfully</h1>
                                        <p>Your build request has been submitted to the queue</p>
                                    </div>

                                    <div class="card">
                                        <div class="info-row">
                                            <div class="info-label">Branch</div>
                                            <div class="info-value">${$('#branch').val()}</div>
                                        </div>

                                        <div class="info-row">
                                            <div class="info-label">Platform</div>
                                            <div class="info-value">${$('#platform').val()}</div>
                                        </div>

                                        <div class="info-row">
                                            <div class="info-label">Version</div>
                                            <div class="info-value">${$('#ver').val()}</div>
                                        </div>

                                        <div class="info-row">
                                            <div class="info-label">Option</div>
                                            <div class="info-value">${$('#option').val()}</div>
                                        </div>

                                        <div class="info-row">
                                            <div class="info-label">OEM Name</div>
                                            <div class="info-value">${$('#oemname').val() || 'N/A'}</div>
                                        </div>

                                        <div class="info-row">
                                            <div class="info-label">UUID</div>
                                            <div class="info-value">
                                                <span class="uuid-value">${response.UUID}</span>
                                            </div>
                                        </div>

                                        <div class="info-row">
                                            <div class="info-label">Output</div>
                                            <div class="info-value">
                                                <div class="output-value">${response.message}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </body>
                            </html>
                        `;

                        // 開啟新視窗
                        let popup = window.open('', 'BuildResult', 'width=800,height=600');
                        popup.document.write(popupContent);

                        // 延遲重整主頁面
                        setTimeout(function() {
                            location.reload();
                        }, 5000);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('An error occurred');
                }
            });
        }
    });

    update_jenkins_status();
});

setInterval(update_jenkins_status, 30000);

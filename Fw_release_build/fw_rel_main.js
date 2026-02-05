
// DataTable 實例
let historyTable;

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




$(document).ready(function() {
    // 初始化 DataTable
    initDataTable();

    $('#build-form-submit-btn').click(function(e) {
        e.preventDefault();
        let formData = $(this).closest('form').serialize(); //表單值 who, branch, target...
        let formDOM  = $(this).closest('form')[0]; //元素
        //document.getElementById("myDIV").classList.add("mystyle");
        formDOM.classList.add('was-validated');  // 加 Bootstrap 驗證樣式
        
        if (!formDOM.checkValidity()) { 
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
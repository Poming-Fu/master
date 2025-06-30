<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPMI Raw Command Dictionary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .command-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .command-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .hex-input {
            font-family: 'Courier New', monospace;
            text-transform: uppercase;
        }
        .copy-btn {
            cursor: pointer;
        }
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1050;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-terminal"></i> IPMI Raw Command Dictionary
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showAddCommandModal()">
                            <i class="bi bi-plus-circle"></i> 新增命令
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- 搜尋區塊 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="搜尋命令名稱、NetFn、Cmd 或描述...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="categoryFilter">
                    <option value="">所有類別</option>
                    <option value="chassis">Chassis</option>
                    <option value="bridge">Bridge</option>
                    <option value="sensor">Sensor/Event</option>
                    <option value="app">App</option>
                    <option value="firmware">Firmware</option>
                    <option value="storage">Storage</option>
                    <option value="transport">Transport</option>
                    <option value="oem">OEM</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100" onclick="searchCommands()">
                    <i class="bi bi-search"></i> 搜尋
                </button>
            </div>
        </div>

        <!-- 命令列表 -->
        <div class="row" id="commandList">
            <!-- 命令卡片會動態插入這裡 -->
        </div>
    </div>

    <!-- 新增/編輯命令 Modal -->
    <div class="modal fade" id="commandModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">新增 IPMI Raw Command</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="commandForm">
                        <input type="hidden" id="commandId">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">NetFn (Network Function)</label>
                                    <input type="text" class="form-control hex-input" id="netfn" placeholder="0x06" maxlength="4" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Cmd (Command)</label>
                                    <input type="text" class="form-control hex-input" id="cmd" placeholder="0x01" maxlength="4" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">命令名稱</label>
                            <input type="text" class="form-control" id="commandName" placeholder="例如: Get Device ID" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">類別</label>
                            <select class="form-select" id="category" required>
                                <option value="">選擇類別</option>
                                <option value="chassis">Chassis</option>
                                <option value="bridge">Bridge</option>
                                <option value="sensor">Sensor/Event</option>
                                <option value="app">App</option>
                                <option value="firmware">Firmware</option>
                                <option value="storage">Storage</option>
                                <option value="transport">Transport</option>
                                <option value="oem">OEM</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">描述</label>
                            <textarea class="form-control" id="description" rows="3" placeholder="詳細描述此命令的功能..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">請求資料格式</label>
                            <textarea class="form-control hex-input" id="requestFormat" rows="2" placeholder="例如: [NetFn] [Cmd] [Data1] [Data2]..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">回應資料格式</label>
                            <textarea class="form-control hex-input" id="responseFormat" rows="2" placeholder="例如: [Completion Code] [Data1] [Data2]..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">範例</label>
                            <textarea class="form-control hex-input" id="example" rows="2" placeholder="例如: ipmitool raw 0x06 0x01"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" onclick="saveCommand()">儲存</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 命令詳情 Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailTitle">命令詳情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <!-- 詳情內容會動態插入這裡 -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" onclick="editCommand()">編輯</button>
                    <button type="button" class="btn btn-danger" onclick="deleteCommand()">刪除</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast 通知 -->
    <div class="toast-container">
        <div class="toast" id="notificationToast" role="alert">
            <div class="toast-header">
                <strong class="me-auto">系統通知</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage">
                <!-- 訊息內容 -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // API 端點
        const API_URL = 'api.php';
        let currentCommandId = null;

        // 頁面載入時獲取所有命令
        document.addEventListener('DOMContentLoaded', function() {
            loadCommands();
        });

        // 載入命令列表
        function loadCommands() {
            fetch(API_URL + '?action=list')
                .then(response => response.json())
                .then(data => {
                    displayCommands(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('無法載入命令列表', 'danger');
                });
        }

        // 顯示命令列表
        function displayCommands(commands) {
            const commandList = document.getElementById('commandList');
            commandList.innerHTML = '';

            commands.forEach(command => {
                const card = `
                    <div class="col-md-4 mb-3">
                        <div class="card command-card" onclick="showCommandDetail(${command.id})">
                            <div class="card-body">
                                <h5 class="card-title">${command.command_name}</h5>
                                <p class="card-text">
                                    <span class="badge bg-primary">NetFn: ${command.netfn}</span>
                                    <span class="badge bg-success">Cmd: ${command.cmd}</span>
                                    <span class="badge bg-info">${command.category}</span>
                                </p>
                                <p class="card-text text-truncate">${command.description || '無描述'}</p>
                            </div>
                        </div>
                    </div>
                `;
                commandList.innerHTML += card;
            });
        }

        // 搜尋命令
        function searchCommands() {
            const searchTerm = document.getElementById('searchInput').value;
            const category = document.getElementById('categoryFilter').value;
            
            const url = `${API_URL}?action=search&term=${encodeURIComponent(searchTerm)}&category=${category}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    displayCommands(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('搜尋失敗', 'danger');
                });
        }

        // 顯示新增命令 Modal
        function showAddCommandModal() {
            document.getElementById('commandForm').reset();
            document.getElementById('commandId').value = '';
            document.getElementById('modalTitle').textContent = '新增 IPMI Raw Command';
            const modal = new bootstrap.Modal(document.getElementById('commandModal'));
            modal.show();
        }

        // 儲存命令
        function saveCommand() {
            const formData = new FormData();
            const commandId = document.getElementById('commandId').value;
            
            formData.append('action', commandId ? 'update' : 'create');
            if (commandId) {
                formData.append('id', commandId);
            }
            
            formData.append('netfn', document.getElementById('netfn').value);
            formData.append('cmd', document.getElementById('cmd').value);
            formData.append('command_name', document.getElementById('commandName').value);
            formData.append('category', document.getElementById('category').value);
            formData.append('description', document.getElementById('description').value);
            formData.append('request_format', document.getElementById('requestFormat').value);
            formData.append('response_format', document.getElementById('responseFormat').value);
            formData.append('example', document.getElementById('example').value);

            fetch(API_URL, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('命令儲存成功', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('commandModal')).hide();
                    loadCommands();
                } else {
                    showToast('儲存失敗: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('儲存失敗', 'danger');
            });
        }

        // 顯示命令詳情
        function showCommandDetail(id) {
            currentCommandId = id;
            fetch(`${API_URL}?action=get&id=${id}`)
                .then(response => response.json())
                .then(command => {
                    const content = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>NetFn:</strong> ${command.netfn}</p>
                                <p><strong>Command:</strong> ${command.cmd}</p>
                                <p><strong>類別:</strong> ${command.category}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>建立時間:</strong> ${new Date(command.created_at).toLocaleString()}</p>
                                <p><strong>更新時間:</strong> ${new Date(command.updated_at).toLocaleString()}</p>
                            </div>
                        </div>
                        <hr>
                        <p><strong>描述:</strong><br>${command.description || '無描述'}</p>
                        <p><strong>請求格式:</strong><br><code>${command.request_format || '無'}</code></p>
                        <p><strong>回應格式:</strong><br><code>${command.response_format || '無'}</code></p>
                        <p><strong>範例:</strong><br><code>${command.example || '無'}</code>
                            ${command.example ? `<i class="bi bi-clipboard copy-btn ms-2" onclick="copyToClipboard('${command.example}')"></i>` : ''}
                        </p>
                    `;
                    
                    document.getElementById('detailTitle').textContent = command.command_name;
                    document.getElementById('detailContent').innerHTML = content;
                    
                    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('無法載入命令詳情', 'danger');
                });
        }

        // 編輯命令
        function editCommand() {
            fetch(`${API_URL}?action=get&id=${currentCommandId}`)
                .then(response => response.json())
                .then(command => {
                    document.getElementById('commandId').value = command.id;
                    document.getElementById('netfn').value = command.netfn;
                    document.getElementById('cmd').value = command.cmd;
                    document.getElementById('commandName').value = command.command_name;
                    document.getElementById('category').value = command.category;
                    document.getElementById('description').value = command.description || '';
                    document.getElementById('requestFormat').value = command.request_format || '';
                    document.getElementById('responseFormat').value = command.response_format || '';
                    document.getElementById('example').value = command.example || '';
                    
                    document.getElementById('modalTitle').textContent = '編輯 IPMI Raw Command';
                    
                    bootstrap.Modal.getInstance(document.getElementById('detailModal')).hide();
                    const modal = new bootstrap.Modal(document.getElementById('commandModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('無法載入命令資料', 'danger');
                });
        }

        // 刪除命令
        function deleteCommand() {
            if (confirm('確定要刪除這個命令嗎？')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', currentCommandId);

                fetch(API_URL, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('命令已刪除', 'success');
                        bootstrap.Modal.getInstance(document.getElementById('detailModal')).hide();
                        loadCommands();
                    } else {
                        showToast('刪除失敗: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('刪除失敗', 'danger');
                });
            }
        }

        // 複製到剪貼簿
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('已複製到剪貼簿', 'success');
            }).catch(err => {
                console.error('Error copying text: ', err);
                showToast('複製失敗', 'danger');
            });
        }

        // 顯示 Toast 通知
        function showToast(message, type = 'info') {
            const toastElement = document.getElementById('notificationToast');
            const toastMessage = document.getElementById('toastMessage');
            
            toastMessage.textContent = message;
            toastElement.className = `toast align-items-center text-white bg-${type}`;
            
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }

        // 限制輸入只能是 hex 格式
        document.querySelectorAll('.hex-input').forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase();
                // 移除非 hex 字符
                value = value.replace(/[^0-9A-FX\s]/g, '');
                e.target.value = value;
            });
        });
    </script>
</body>
</html>
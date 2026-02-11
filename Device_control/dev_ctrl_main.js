// dev_ctrl_main.js

// ===== Analytics Helper Functions =====
// 向後相容的包裝函數
function log_user_actions_collect(action, element, element_type) {
    Analytics.track_button_click(element, action);
}

// 取得板子識別資訊（IP 或名稱）
function get_board_identifier(element) {
    // 優先順序：IP > Board Name > MP IP
    let ip = element.data('ip') || element.closest('.board-card').find('[data-ip]').first().data('ip');
    let name = element.data('name') || element.closest('.board-card').find('[data-name]').first().data('name');
    let mp_ip = element.data('mp_ip');

    return ip || name || mp_ip || 'unknown';
}

// ===== Event Handlers =====
$(document).ready(function() {

    // 捕捉 insert 按鈕事件 - 開啟 Modal
    $(document).on('click', '.insert-board-btn', function() {
        const btn = $(this);
        const mp_num = btn.data('mp_num');
        const mp_ip = btn.data('mp_ip');
        const locate = btn.data('locate');

        Analytics.track_button_click(btn, `open insert form - MP510: ${mp_ip}`);

        // 開啟 Modal
        const modal = new bootstrap.Modal(document.getElementById('boardManagementModal'));

        // 更新標題樣式
        $('#boardManagementModalIcon').removeClass('bi-pencil-square').addClass('bi-plus-circle');
        $('#boardManagementModalLabel').text('New Board');
        $('#boardManagementModalSubtitle').text(`Add new board to ${mp_ip}`);
        $('#boardManagementModalBody').html('<div class="text-center" style="padding: 32px;"><div class="spinner-border" role="status"></div></div>');
        modal.show();

        // 載入表單
        $.get('boards_mgmt/board_mgmt_api.php', {
            action: 'get_form',
            type: 'insert',
            mp_num: mp_num,
            mp_ip: mp_ip,
            locate: locate
        }, function(response) {
            if (response.success) {
                $('#boardManagementModalBody').html(response.html);
            } else {
                $('#boardManagementModalBody').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        }, 'json');
    });

    // 捕捉AC button click事件 (PowerBox 控制)
    $('.AC-button').click(function() {
        const element     = $(this);
        const element_id  = element.attr('id') || 'undefined';

        Analytics.track_button_click(element, `power control - ${element_id}`);

        const confirmed   = confirm(`你確定要執行 ${element_id} 嗎？`);

        Analytics.track_confirm('power_control', confirmed, element_id);

        if (confirmed) {
            const pw_ip   = element.data('pw_ip');
            const target  = element.data('target');
            const control = element.data('control');
            const url = `http://${pw_ip}/cgi-bin/control2.cgi?user=one&passwd=1234&target=${target}&control=${control}`;
            $.get(url);
        }
    });

    // 捕捉BMC Console按钮click事件
    $('.telnet-console').click(function() {
        const element = $(this);
        if (!element.hasClass('disabled')) {
            const board_ip = get_board_identifier(element);
            Analytics.track_button_click(element, `bmc console - ${board_ip}`);
        }
    });

    // 捕捉 delete 按鈕事件 - 使用 AJAX
    $(document).on('click', '.delete-board-btn', function(event) {
        event.preventDefault();
        const btn = $(this);
        const board_id = btn.data('board_id');
        const board_ip = btn.data('ip');
        const board_name = btn.data('name');
        const board_identifier = board_ip || board_name;

        Analytics.track_button_click(btn, `delete board - ${board_identifier}`);

        const confirmed = confirm(`確定要刪除這個主板嗎？\n${board_name} - ${board_ip}`);

        Analytics.track_confirm('delete_board', confirmed, board_identifier);

        if (confirmed) {
            // 禁用按鈕
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            // 發送 AJAX 請求
            $.post('boards_mgmt/board_mgmt_api.php', {
                action: 'delete_board',
                id: board_id
            }, function(response) {
                if (response.success) {
                    Analytics.track_event('form_submit', 'success', `Delete Board - ${board_identifier}`);
                    alert(response.message);
                    location.reload();
                } else {
                    Analytics.track_event('form_submit', 'failed', `Delete Board - ${board_identifier}`);
                    alert(response.message);
                    btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                }
            }, 'json').fail(function() {
                Analytics.track_event('form_submit', 'error', `Delete Board - ${board_identifier}`);
                alert('發生錯誤，請稍後再試');
                btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
            });
        }
    });

    // 捕捉 modify 按鈕事件 - 開啟 Modal
    $(document).on('click', '.modify-board-btn', function() {
        const btn = $(this);
        const board_id = btn.data('board_id');
        const board_ip = btn.data('ip');
        const board_name = btn.data('name');

        Analytics.track_button_click(btn, `open modify form - ${board_ip || board_name}`);

        // 開啟 Modal
        const modal = new bootstrap.Modal(document.getElementById('boardManagementModal'));

        // 更新標題樣式
        $('#boardManagementModalIcon').removeClass('bi-plus-circle').addClass('bi-pencil-square');
        $('#boardManagementModalLabel').text('Edit Board');
        $('#boardManagementModalSubtitle').text(`${board_name} - ${board_ip}`);
        $('#boardManagementModalBody').html('<div class="text-center" style="padding: 32px;"><div class="spinner-border" role="status"></div></div>');
        modal.show();

        // 載入表單
        $.get('boards_mgmt/board_mgmt_api.php', {
            action: 'get_form',
            type: 'modify',
            id: board_id
        }, function(response) {
            if (response.success) {
                $('#boardManagementModalBody').html(response.html);
            } else {
                $('#boardManagementModalBody').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        }, 'json');
    });

    // 處理新增板子表單提交
    $(document).on('submit', '#boardInsertForm', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const formData = form.serialize();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> 新增中...');

        Analytics.track_form_submit(form, 'Insert Board Form');

        $.post('boards_mgmt/board_mgmt_api.php', formData, function(response) {
            if (response.success) {
                Analytics.track_event('form_submit', 'success', 'Insert Board Form');
                alert(response.message);
                $('#boardManagementModal').modal('hide');
                location.reload();
            } else {
                Analytics.track_event('form_submit', 'failed', 'Insert Board Form');
                alert(response.message);
                submitBtn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> 新增板子');
            }
        }, 'json').fail(function() {
            Analytics.track_event('form_submit', 'error', 'Insert Board Form');
            alert('發生錯誤，請稍後再試');
            submitBtn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> 新增板子');
        });
    });

    // 處理修改板子表單提交
    $(document).on('submit', '#boardModifyForm', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const formData = form.serialize();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> 儲存中...');

        Analytics.track_form_submit(form, 'Modify Board Form');

        $.post('boards_mgmt/board_mgmt_api.php', formData, function(response) {
            if (response.success) {
                Analytics.track_event('form_submit', 'success', 'Modify Board Form');
                alert(response.message);
                $('#boardManagementModal').modal('hide');
                location.reload();
            } else {
                Analytics.track_event('form_submit', 'failed', 'Modify Board Form');
                alert(response.message);
                submitBtn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> 儲存變更');
            }
        }, 'json').fail(function() {
            Analytics.track_event('form_submit', 'error', 'Modify Board Form');
            alert('發生錯誤，請稍後再試');
            submitBtn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> 儲存變更');
        });
    });

    // 捕捉 Reset ser2net 事件
    $('.resetMP510ser2net-icon').click(function() {
        const element = $(this);
        const mp_ip = element.data('mp_ip');

        Analytics.track_button_click(element, `reset ser2net - MP510: ${mp_ip}`);

        const confirmed = confirm(`確定要重啟 MP510 (${mp_ip}) ser2net service？`);

        Analytics.track_confirm('reset_ser2net', confirmed, `MP510: ${mp_ip}`);

        if (confirmed) {

            // 顯示載入中
            element.prop('disabled', true);
            element.html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: 'dev_ctrl_main_functions.php?action=reset_ser2net',
                type: 'POST',
                data: { mp_ip: mp_ip },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Success:\n' + response.message);
                    } else {
                        alert('Failed: \n' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: Server not disconnected\n' + textStatus);
                },
                complete: function() {
                    // 恢復按鈕
                    element.prop('disabled', false);
                    element.html('<i class="bi bi-arrow-clockwise"></i>');
                }
            });
        }
    });
});

//工具箱 or 元素功能
$(document).ready(function() {
    // 回到頂部按鈕功能
    let backToTopBtn = $('#backToTopBtn');

    $(window).scroll(function() {
        if ($(window).scrollTop() > 300) {
            backToTopBtn.fadeIn();
        } else {
            backToTopBtn.fadeOut();
        }
    });

    backToTopBtn.click(function() {
        $(window).scrollTop(0);
    });

    $('#mp510Dropdown').change(function() {
        let selectedMP510 = $(this).val();
        if (selectedMP510) {
            let targetElement = $('#' + selectedMP510);
            //沒有元素 length = 0 不動
            if (targetElement.length) {
                // 頂部對齊標籤 ， 40這個偏移量剛好看到MP510
                $(window).scrollTop(targetElement.offset().top - 40);
            }
        }
    });

    // Reload button click event
    $('.reload-icon').click(function() {
        let ip         = $(this).data('ip');
        let unique_pw  = $(this).data('unique_pw');
        let current_pw = $(this).data('current_pw');
        let account    = "ADMIN";
        let password   = "ADMIN";

        Analytics.track_button_click($(this), `reload status - ${ip}`);

        function sendRequest(customPassword = null) {
            let data = {
                ip: ip,
                unique_pw: unique_pw,
                current_pw: current_pw,
                account: account,
                password: password
            };
            if (customPassword) {
                data.custom_pw = customPassword;
            }

            $.ajax({
                url: 'dev_ctrl_main_functions.php?action=reload_status',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Success: \n' + response.message);
                        location.reload();
                    } else if (response.needCustomPassword) {
                        let customPassword = prompt(response.message);
                        if (customPassword) {
                            sendRequest(customPassword);
                        }
                    } else {
                        alert('Failed: \n' + response.message);
                        location.reload();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error occurred while sending reload command.');
                }
            });
        }
        sendRequest();
    });

    $('.fw-button').click(function(event) {
        event.preventDefault();
        let name       = $(this).attr('name');
        let form       = $(this).closest('form');
        let ip         = form.find('input[name="ip"]').val();
        let B_id       = form.find('input[name="B_id"]').val();
        let FW_type    = form.find('input[name="FW_type"]').val();
        let unique_pw  = form.find('input[name="unique_pw"]').val();
        let current_pw = form.find('input[name="current_pw"]').val();
        let account    = "ADMIN";
        let password   = "ADMIN";

        Analytics.track_button_click($(this), `FW Recovery - ${FW_type} - ${ip}`);

        // 首先獲取最新的韌體名稱
        $.ajax({
            type: 'POST',
            url: '/web1/Device_control/recovery_FW/handle_FwUpdate.php',
            data: { 
                action: 'get_latest_FW',
                B_id: B_id,
                FW_type: FW_type,
            },
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    alert("Error: " + response.message);
                    return;
                }
                
                let latest_FW_name = response.FW_name;
                
                let checkMessage = [
                    `====  ${name} mode ====`,
                    'Check info :',
                    `IP: ${ip}`,
                    `Board_id: ${B_id}`,
                    `FW_type: ${FW_type}`,
                    `Latest Firmware: ${latest_FW_name}`,
                    '======================',
                    `Are you sure to recover ${FW_type} ?`
                ].join('\n');

                let confirmation = confirm(checkMessage);

                Analytics.track_confirm('fw_recovery', confirmation, `${FW_type} - ${ip}`);

                if (confirmation) {
                    function sendUpdateRequest(customPassword = null) {
                        let data = {
                            action: 'RF_recovery',
                            ip: ip,
                            B_id: B_id,
                            FW_type: FW_type,
                            account: account,
                            password: password,
                            unique_pw: unique_pw,
                            current_pw: current_pw
                        };

                        if (customPassword) {
                            data.custom_pw = customPassword;
                        }

                        $.ajax({
                            type: 'POST',
                            url: '/web1/Device_control/recovery_FW/handle_FwUpdate.php',
                            data: data,
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    alert("Success: \n" + response.message);
                                } else if (response.needCustomPassword) {
                                    let customPassword = prompt(response.message);
                                    if (customPassword) {
                                        sendUpdateRequest(customPassword);
                                    }
                                } else {
                                    alert('Failed: ' + response.message);
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.error("Error: " + textStatus + " - " + errorThrown);
                                console.error("Response Text: " + jqXHR.responseText);
                            }
                        });
                    }

                    sendUpdateRequest();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error: " + textStatus + " - " + errorThrown);
                console.error("Response Text: " + jqXHR.responseText);
            }
        });
    });
    $('.copy-button').click(function() {
        let password = $(this).data('unique_pw');
        let button = $(this);
        const board_ip = get_board_identifier(button);

        Analytics.track_button_click(button, `copy password - ${board_ip}`);

        // HTTPS 使用現代 API (HTTPS)
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(password).then(function() {
                button.find('i').removeClass('bi-copy').addClass('bi-check-lg');
                button.addClass('copied');
                // 1.5秒後恢復
                setTimeout(function() {
                    button.find('i').removeClass('bi-check-lg').addClass('bi-copy');
                    button.removeClass('copied');
                }, 1500);
            });
        } else {
            // HTTP 環境或不支援,直接用傳統方法
            let $temp = $('<textarea>').val(password).appendTo('body');
            $temp[0].select();
            document.execCommand('copy'); // 老方法還能再戰十年
            $temp.remove();
            
            button.find('i').removeClass('bi-copy').addClass('bi-check-lg');
            button.addClass('copied');
            // 1.5秒後恢復
            setTimeout(function() {
            button.find('i').removeClass('bi-check-lg').addClass('bi-copy');
            button.removeClass('copied');
        }, 1500);
        }
    });

    // 一鍵複製板子所有資訊
    $('.copy-all-btn').click(function() {
        let btn = $(this);
        let ip = btn.data('ip');
        let mp_ip = btn.data('mp_ip');
        let mp_com = btn.data('mp_com');
        let bmcLink = `http://${ip}`;
        let consoleLink = `http://${window.location.hostname}/web1/Device_control/websocket-terminal/bmc-console.html?host=${mp_ip}&port=${mp_com}&IP=${ip}`;

        Analytics.track_button_click(btn, `copy all board info - ${ip}`);

        let info = [
            `Board Name: ${btn.data('name')}`,
            `BMC IP: ${ip}`,
            `BMC Link: ${bmcLink}`,
            `Board ID: ${btn.data('bid') || '-'}`,
            `BMC Version: ${btn.data('version') || '-'}`,
            `BMC MAC: ${btn.data('mac') || '-'}`,
            `Unique PW: ${btn.data('unique_pw') || '-'}`,
            `Current PW: ${btn.data('current_pw') || '-'}`,
            `Console: ${consoleLink}`
        ].join('\n');

        copyToClipboard(info, btn);
    });

    // Board Raw 小工具 - 選擇 IP 時自動帶入密碼
    $('#board_number').on('input', function() {
        let selectedIP = $(this).val();
        let option = $('#boardList option[value="' + selectedIP + '"]');
        if (option.length) {
            let currentPw = option.data('current_pw');
            if (currentPw) {
                $('#pass_value').val(currentPw);
            } else {
                $('#pass_value').val('ADMIN');
            }
        }
    });

    $('#rawForm').on('submit', function(event) {
        event.preventDefault();

        const ip = $('#board_number').val();
        const command = $('#raw_value').val();

        Analytics.track_form_submit($(this), `raw command - ${ip} - ${command}`);

        $.ajax({
            url: 'dev_ctrl_main_functions.php?action=execute_raw_command',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                $('#cmd').text(response.command);
                $('#result').text(response.result);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
            }
        });
    });
    
    $(document).on('submit', '.enableForm', function(event) {
        //動態生成表格用 class=enableForm去指定比較好，用id=會bug
        event.preventDefault(); // Prevent default form submission

        const ip = $(this).find('input[name="ip"]').val();
        const confirmed = confirm('確定要enable console嗎？');
        Analytics.track_confirm('enable_console', confirmed, ip);

        if (confirmed) {
            $.ajax({
                url: 'dev_ctrl_main_functions.php?action=enable_console',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    //console.log('Full response:', response);
                    if (response.message && response.message.includes('Error')) {
                            // function => success = true，but includes('Error')
                            alert('enable console action failed: \n' + response.message);
                    } else {
                            alert('enable console action success \n' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error:', textStatus, errorThrown);
                    console.error('Response text:', jqXHR.responseText);
                    alert('Error occurred while enabling console.');
                }
            });
        }
    });

    $(document).on('submit', '.actionForm', function(event) {
        event.preventDefault(); // Prevent default form submission
        let action = $(this).find('select[name="action"]').val();
        if (action === 'NA') {
            alert('Please choose an option.');
            return;//js語法不執行後面
        }

        const confirmed = confirm('Are you sure you want to perform this action?');
        Analytics.track_confirm('board_action', confirmed, action);

        if (confirmed) {
            $.ajax({
                url: 'dev_ctrl_main_functions.php?action=perform_action',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (response.message && response.message.includes('Error')) {
                            // function => success = true，but includes('Error')
                            alert('Action failed: ' + response.message);
                        } else {
                            
                            alert('Action completed ' + response.message);
                        }
                    } else {
                        // 明確的失敗情況
                        alert('Action failed: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error:', textStatus, errorThrown);
                    console.error('Response text:', jqXHR.responseText);
                    alert('Error occurred while performing the action.');
                }
            });
        }
    });
});

//function區

// 通用複製到剪貼簿函數
function copyToClipboard(text, btn) {
    let icon = btn.find('i');
    let originalClass = icon.attr('class');

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showCopySuccess(btn, icon, originalClass);
        });
    } else {
        let $temp = $('<textarea>').val(text).appendTo('body');
        $temp[0].select();
        document.execCommand('copy');
        $temp.remove();
        showCopySuccess(btn, icon, originalClass);
    }
}

function showCopySuccess(btn, icon, originalClass) {
    icon.attr('class', 'bi bi-check-lg');
    btn.addClass('copied');
    setTimeout(function() {
        icon.attr('class', originalClass);
        btn.removeClass('copied');
    }, 1500);
}

//獲取板子存活狀態
function fetchBoardAliveData() {
    $.ajax({
        url: 'dev_ctrl_main_functions.php?action=get_boards_alive',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#alive_count').text(data.alive_count);
            $('#total_count').text(data.total_count);
        },
        error: function(error) {
            console.log("Error fetching data:", error);
        }
    });
}

//BMC console function -> telnet
function openTelnetSession(host, port, IP) {
    const url = `http://${window.location.hostname}/web1/Device_control/websocket-terminal/bmc-console.html?host=${host}&port=${port}&IP=${IP}`;
    window.open(url, '_blank');
}


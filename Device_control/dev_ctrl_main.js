// control.js
//捕捉事件
$(document).ready(function() {

    // 捕捉insert事件
    $('a img[src*="insert.png"]').click(function() {
        log_user_actions_collect('click_insert_image', $(this), 'image');
    });

    // 捕捉AC button click事件
    $('.AC-button img').click(function() {
        const element     = $(this).closest('.AC-button');
        const element_id  = element.attr('id') || 'undefined';
        
        log_user_actions_collect('click_power_control', element, 'button');

        const confirmed   = confirm(`你確定要執行${element_id} 嗎？`);
        if (confirmed) {
            log_user_actions_collect('click_power_control', element, 'confirm');

            const pw_ip   = element.data('pw_ip');
            const target  = element.data('target');
            const control = element.data('control');
            const url = `http://${pw_ip}/cgi-bin/control2.cgi?user=one&passwd=1234&target=${target}&control=${control}`;
            $.get(url);
        } else {
            log_user_actions_collect('click_power_control', element, 'cancel');
        }
    });

    // 捕捉BMC Console按钮click事件
    $('.telnet-console img').click(function() {
        const element_img = $(this);
        log_user_actions_collect('click_bmc_console', element_img, 'button');
    });

    // 捕捉delete事件
    $('a img[src*="bin.png"]').click(function(event) {
        event.preventDefault(); // 防默認提交
        const element_img = $(this);
        const element     = element_img.closest('a'); // 找最靠近 a 的元素
        const confirmed   = confirm('確定要刪除表單嗎？');
        log_user_actions_collect('click_delete_image', element_img, 'button');
        
        if (confirmed) {
            log_user_actions_collect('click_delete_image', element_img, 'confirm');
            window.location.href = element.attr('href'); // 導航到 a 的href屬性
        } else {
            log_user_actions_collect('click_delete_image', element_img, 'cancel');
            
        }
    });

    // 捕捉update事件
    $('a img[src*="modify.png"]').click(function() {
        const element_img = $(this);
        log_user_actions_collect('click_modify', element_img, 'button');
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
        let ip        = $(this).data('ip');
        let unique_pw = $(this).data('unique_pw');
        let account   = "ADMIN";
        let password  = "ADMIN";
        function sendRequest(customPassword = null) {
            let data = { 
                ip: ip,
                unique_pw: unique_pw, 
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
        let name      = $(this).attr('name');
        let form      = $(this).closest('form');
        let ip        = form.find('input[name="ip"]').val();
        let B_id      = form.find('input[name="B_id"]').val();
        let FW_type   = form.find('input[name="FW_type"]').val();
        let unique_pw = form.find('input[name="unique_pw"]').val();
        let account   = "ADMIN";
        let password  = "ADMIN";
        
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

                if (confirmation) {
                    function sendUpdateRequest(customPassword = null) {
                        let data = { 
                            action: 'RF_recovery',
                            ip: ip,
                            B_id: B_id,
                            FW_type: FW_type,
                            account: account,
                            password: password,
                            unique_pw: unique_pw
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

    $('#rawForm').on('submit', function(event) {
        event.preventDefault();

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

    $('.resetMP510ser2net-icon').click(function() {
        let mp_ip        = $(this).data('mp_ip');
        if (!confirm("MP510 : " + mp_ip + "\nAre you sure to reset ser2net.service ?")) {
            return;
        }
        let data = {
            mp_ip: mp_ip
        };
        $.ajax({
            url: 'dev_ctrl_main_functions.php?action=reset_ser2net_service',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Success: \n' + response.message);
                } else {
                    alert('Failed: \n' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error occurred while sending reload command.');
            }
        });
    });

    $('.copy-button').click(function() {
        let password = $(this).data('unique_pw');
        let button = $(this);
        
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
    $(document).on('submit', '.enableForm', function(event) {
        //動態生成表格用 class=enableForm去指定比較好，用id=會bug
        event.preventDefault(); // Prevent default form submission
        if (confirm('確定要enable console嗎？')) {
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
        if (confirm('Are you sure you want to perform this action?')) {
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
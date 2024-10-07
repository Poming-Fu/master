
function fetchPb1Status() {
    $.ajax({
        url: '/web1/Device_control/powerbox/dev_ctrl_power_fetch.php?get-pb1_status=true',
        type: 'GET',
        success: function(response) {
            const data = JSON.parse(response);
            $('#pb1_port1_status').html(data.pb1_output1).css('color', data.pb1_output1 === 'On' ? 'green' : 'red');
            $('#pb1_port2_status').html(data.pb1_output2).css('color', data.pb1_output2 === 'On' ? 'green' : 'red');
            // 根據status判斷隱藏按鈕 實現單一按鈕
            if (data.pb1_output1 === 'On') {
                $('#pb1_port_1_openButton').hide(); 
                $('#pb1_port_1_closeButton').show(); 
            } else {
                $('#pb1_port_1_openButton').show(); 
                $('#pb1_port_1_closeButton').hide(); 
            }
            // 根據status判斷隱藏按鈕 實現單一按鈕
            if (data.pb1_output2 === 'On') {
                $('#pb1_port_2_openButton').hide(); 
                $('#pb1_port_2_closeButton').show(); 
            } else {
                $('#pb1_port_2_openButton').show(); 
                $('#pb1_port_2_closeButton').hide(); 
            }				
        },
        error: function(error) {
            console.error("Error fetching pb1 status: ", error);
        }
    });
}

function fetchPb2Status() {
    $.ajax({
        url: '/web1/Device_control/powerbox/dev_ctrl_power_fetch.php?get-pb2_status=true',
        type: 'GET',
        dataType: 'json',
            success: function(data) {
                // 更新頁面上的狀態顯示
                $('#pb2_port1_status').html(data.pb2_output1).css('color', data.pb2_output1 === 'On' ? 'green' : 'red');
                $('#pb2_port2_status').html(data.pb2_output2).css('color', data.pb2_output2 === 'On' ? 'green' : 'red');

			// 根據status判斷隱藏按鈕 實現單一按鈕
			if (data.pb2_output1 === 'On') {
                $('#pb2_port_1_openButton').hide(); 
                $('#pb2_port_1_closeButton').show(); 
            } else {
                $('#pb2_port_1_openButton').show(); 
                $('#pb2_port_1_closeButton').hide(); 
            }
            // 根據status判斷隱藏按鈕 實現單一按鈕
            if (data.pb2_output2 === 'On') {
                $('#pb2_port_2_openButton').hide(); 
                $('#pb2_port_2_closeButton').show(); 
            } else {
                $('#pb2_port_2_openButton').show(); 
                $('#pb2_port_2_closeButton').hide(); 
            }
        },
        error: function(error) {
            console.error("Error fetching pb2 status: ", error);
        }
    });
}


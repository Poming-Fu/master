
function update_jenkins_status() {
    $.ajax({
        url: 'fw_rel_get_jenkins_status.php',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                //console.log(response.message);
                $('#history').load(location.href + ' #history>*');
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


$(document).ready(function() {
    $('#history_reload').click(function() {
        $('#history').load(location.href + ' #history>*');    
    });



    $('#build-form-submit-btn').click(function() {
        // 使用 setTimeout 來確保表單提交後再重新載入頁面
        setTimeout(function() {
            location.reload();
        }, 100);
    });
    
    update_jenkins_status();
});

setInterval(update_jenkins_status, 30000);

$(document).ready(function() {
    // 初始化日期選擇器
    $('#dateFilter').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        clearBtn: true
    });

    // 過濾器變更事件
    function updateResults() {
        $.ajax({
            url: 'daily_main_functions.php?action=get_filter_data',
            type: 'POST',
            data: {
                branch: $('#branchFilter').val(),
                status: $('#statusFilter').val(),
                date: $('#dateFilter').val()
            },
            success: function(response) {
                $('#buildResults').html(response);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    }

    // 綁定事件
    $('#branchFilter').on('change', updateResults);
    $('#dateFilter').on('change', updateResults);
    $('#statusFilter').on('change', updateResults);




    $(selector).click(function (e) { 
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "daily_main_functions.php?action=view_log",
            data: "data",
            dataType: "dataType",
            success: function (response) {
                
            }
        });
    });
});

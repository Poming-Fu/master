
function update_jenkins_status() {
    $.ajax({
        url: 'fw_rel_main_functions.php?action=update_jenkins_status',
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
                        // 調適用<tr><td>Command</td><td>${response.api_command}</td></tr>
                        let popupContent = `
                            <html>
                            <head>
                                <title>Build Result</title>
                                <style>
                                    body { font-family: Arial; padding: 20px; }
                                    table { width: 100%; border-collapse: collapse; }
                                    th, td { padding: 8px; border: 1px solid #ddd; }
                                    th { background-color: #f5f5f5; }
                                </style>
                            </head>
                            <body>
                                <table>
                                    <tr><th colspan="2">Build Result</th></tr>
                                    <tr><td>Branch</td><td>${$('#branch').val()}</td></tr>
                                    <tr><td>Platform</td><td>${$('#platform').val()}</td></tr>
                                    <tr><td>Version</td><td>${$('#ver').val()}</td></tr>
                                    <tr><td>Option</td><td>${$('#option').val()}</td></tr>
                                    <tr><td>OEM Name</td><td>${$('#oemname').val()}</td></tr>
                                    <tr><td>UUID</td><td>${response.UUID}</td></tr>
                                    <tr><td>Output</td><td>${response.message}</td></tr>
                                </table>
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

$(document).ready(function() {
    // 初始化日期選擇器
    $('#dateFilter').daterangepicker({
        autoUpdateInput: true, //初始值在這邊設定
        startDate: moment().subtract(3, 'days'),  // 預設開始日期
        endDate: moment(),  // 預設結束日期
        locale: {
            format: 'YYYYMMDD',
            separator: ' - ',
            applyLabel: '確定',
            cancelLabel: '清除',
            customRangeLabel: '自定義範圍'
        },
        ranges: {
            '今天': [moment(), moment()],
            '昨天': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '最近7天': [moment().subtract(6, 'days'), moment()],
            '最近30天': [moment().subtract(29, 'days'), moment()]
        }
    });

    // 處理日期選擇事件
    $('#dateFilter').on('apply.daterangepicker', function(e, picker) {
        e.preventDefault();
        $(this).val(picker.startDate.format('YYYYMMDD') + ' - ' + picker.endDate.format('YYYYMMDD'));
    });

    $('#dateFilter').on('cancel.daterangepicker', function(e, picker) {
        e.preventDefault();
        $(this).val('');
    });

    // filter event
    function updateResults() {
        let dates = $('#dateFilter').val().split(' - ');
        let startDate = dates[0] || '';
        let endDate = dates[1] || '';
        
        $.ajax({
            url: 'daily_main_functions.php?action=get_filter_data',
            type: 'GET',
            data: {
                branch: $('#branchFilter').val(),
                status: $('#statusFilter').val(),
                start_date: startDate,
                end_date: endDate
            },
            beforeSend: function() {
                $('#buildResults').html(`
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary"></div>
                        <div class="mt-2">Loading builds...</div>
                    </div>
                `);
            },
            success: function(response) {
                $('#buildResults').empty();
    
                if (!Array.isArray(response) || response.length === 0) {
                    $('#buildResults').append(`
                        <div class="alert alert-info m-3">
                            <i class="fas fa-info-circle me-2"></i>No builds found matching your criteria.
                        </div>
                    `);
                    return;
                }
    
                // response 第一層就是掃多個 target 當group，隨意取名為targetGroup
                // 回傳
                // 'target_id' => $target_id,
                // 'target_name' => $target_info['name'],
                // 'builds' => $target_builds 
                response.forEach(function(targetGroup) {
                    const targetSection = $(`
                        <div class="target-section mb-4">
                            <h3 class="mb-3 border-bottom pb-2">
                            ${targetGroup.branch_name} - ${targetGroup.target_name} - ${targetGroup.target_type} - ${targetGroup.target_GUID} 
                            </h3>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 12%">Build Date</th>
                                            <th style="width: 20%">Branch Version</th>
                                            <th style="width: 12%">Status</th>
                                            <th style="width: 40%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `);
                
                    targetGroup.builds.forEach(function(build) {
                        const row = `
                            <tr>
                                <td class="fw-bold">${build.build_date}</td>
                                <td><code>${build.branch_version}</code></td>
                                <td>
                                    <span class="badge ${build.build_status === 'PASS' ? 'bg-success' : 'bg-danger'} fs-6">
                                        Compile: ${build.build_status}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <div title="${!build.build_file_path ? 'If compile pass, no build log' : 'View build log'}" 
                                             class="flex-fill">
                                            <a href="${build.build_file_path ? 'daily_main_functions.php?action=view_build_log&path=' + encodeURIComponent(build.build_file_path) : '#'}" 
                                               target="_blank" 
                                               class="btn btn-info btn-sm ${!build.build_file_path ? 'disabled' : ''} w-100">
                                               <i class="fas fa-file-alt me-2"></i>Build Log
                                            </a>
                                        </div>
                                        
                                        <div title="${!build.log_file_path ? 'Git log not available' : 'View git log'}"
                                             class="flex-fill">
                                            <a href="${build.log_file_path ? 'daily_main_functions.php?action=view_git_log&path=' + encodeURIComponent(build.log_file_path) : '#'}" 
                                               target="_blank" 
                                               class="btn btn-info btn-sm ${!build.log_file_path ? 'disabled' : ''} w-100">
                                               <i class="fas fa-code-branch me-2"></i>Git Log
                                            </a>
                                        </div>
                                        
                                        <div title="${!build.bin_file_path ? 'Binary file not available' : 'Download binary file'}"
                                             class="flex-fill">
                                            <a href="${build.bin_file_path ? 'daily_main_functions.php?action=download_file&path=' + encodeURIComponent(build.bin_file_path) : '#'}" 
                                               class="btn btn-primary btn-sm ${!build.bin_file_path ? 'disabled' : ''} w-100">
                                               <i class="fas fa-download me-2"></i>Download Binary
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        `;
                        
                        targetSection.find('tbody').append(row);
                    });
                    
                    $('#buildResults').append(targetSection);
                });
            },
            error: function(xhr, status, error) {
                $('#buildResults').html(`
                    <div class="alert alert-danger m-3">
                        <i class="fas fa-exclamation-circle me-2"></i>Error loading builds: ${error}
                    </div>
                `);
                console.error('Error:', error);
            }
        });
    }
    // 綁定事件
    $('#searchFilter').click(function (e) {
        e.preventDefault();
        updateResults();
    });

    // ========== Mail Reports Tab ==========

    // 初始化報告日期範圍選擇器（預設最近 7 天）
    $('#reportDateRange').daterangepicker({
        autoUpdateInput: true,
        startDate: moment().subtract(6, 'days'),
        endDate: moment(),
        locale: {
            format: 'YYYYMMDD',
            separator: ' - ',
            applyLabel: '確定',
            cancelLabel: '清除',
            customRangeLabel: '自定義範圍'
        },
        ranges: {
            '今天': [moment(), moment()],
            '昨天': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '最近7天': [moment().subtract(6, 'days'), moment()],
            '最近30天': [moment().subtract(29, 'days'), moment()]
        }
    });

    // 共用：載入報告列表
    function loadReportList(params, headerText) {
        $.ajax({
            url: 'daily_main_functions.php?action=list_mail_reports',
            type: 'GET',
            data: params,
            dataType: 'json',
            beforeSend: function() {
                $('#reportListContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Loading...</div>');
                $('#reportList').show();
            },
            success: function(reports) {
                if (reports.length === 0) {
                    $('#reportListContent').html('<p class="text-muted mb-0 py-2">No reports found in this range.</p>');
                    return;
                }

                let html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0">';
                html += '<thead><tr><th>Date</th><th>Modified</th><th>Action</th></tr></thead><tbody>';

                reports.forEach(function(report) {
                    html += '<tr>';
                    html += '<td>' + report.display_date + '</td>';
                    html += '<td><small class="text-muted">' + report.mtime + '</small></td>';
                    html += '<td><button class="btn btn-sm btn-outline-primary view-report" data-date="' + report.date + '">View</button></td>';
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
                $('#reportListContent').html(html);

                // 更新 card header
                $('.card-header', '#reportList').text(headerText + ' (' + reports.length + ' reports)');
            },
            error: function() {
                $('#reportListContent').html('<p class="text-danger mb-0">Error loading report list</p>');
            }
        });
    }

    // Load Reports - 按日期範圍搜尋
    $('#loadReport').click(function() {
        const picker = $('#reportDateRange').data('daterangepicker');
        const startDate = picker.startDate.format('YYYYMMDD');
        const endDate = picker.endDate.format('YYYYMMDD');
        const displayStart = picker.startDate.format('YYYY/MM/DD');
        const displayEnd = picker.endDate.format('YYYY/MM/DD');

        loadReportList(
            { start_date: startDate, end_date: endDate },
            displayStart + ' ~ ' + displayEnd
        );
    });

    // List Recent - 列出最近 50 筆
    $('#listReports').click(function() {
        loadReportList(
            { limit: 50 },
            'Recent 50'
        );
    });

    // 點擊報告列表中的 View 按鈕
    $(document).on('click', '.view-report', function() {
        const date = String($(this).data('date'));
        const displayDate = date.substring(0, 4) + '/' + date.substring(4, 6) + '/' + date.substring(6, 8);

        $(this).closest('tr').addClass('table-active').siblings().removeClass('table-active');
        $('#reportHeader').html('<i class="bi bi-hourglass-split"></i> Loading report...');
        $('#reportFrame').attr('src', 'daily_main_functions.php?action=get_mail_report&date=' + date);
        $('#reportHeader').html('<i class="bi bi-file-earmark-text"></i> Daily Report: ' + displayDate);
    });

    // ========== MR Check Reports Tab ==========

    $('#mrDateRange').daterangepicker({
        autoUpdateInput: true,
        startDate: moment().subtract(6, 'days'),
        endDate: moment(),
        locale: {
            format: 'YYYYMMDD',
            separator: ' - ',
            applyLabel: '確定',
            cancelLabel: '清除',
            customRangeLabel: '自定義範圍'
        },
        ranges: {
            '今天': [moment(), moment()],
            '昨天': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '最近7天': [moment().subtract(6, 'days'), moment()],
            '最近30天': [moment().subtract(29, 'days'), moment()]
        }
    });

    function loadMrReportList(params, headerText) {
        $.ajax({
            url: 'daily_main_functions.php?action=list_mr_check_reports',
            type: 'GET',
            data: params,
            dataType: 'json',
            beforeSend: function() {
                $('#mrReportListContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Loading...</div>');
                $('#mrReportList').show();
            },
            success: function(reports) {
                if (reports.length === 0) {
                    $('#mrReportListContent').html('<p class="text-muted mb-0 py-2">No reports found in this range.</p>');
                    return;
                }

                let html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0">';
                html += '<thead><tr><th>Date</th><th>Modified</th><th>Action</th></tr></thead><tbody>';

                reports.forEach(function(report) {
                    html += '<tr>';
                    html += '<td>' + report.display_date + '</td>';
                    html += '<td><small class="text-muted">' + report.mtime + '</small></td>';
                    html += '<td><button class="btn btn-sm btn-outline-primary view-mr-report" data-date="' + report.date + '">View</button></td>';
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
                $('#mrReportListContent').html(html);
                $('.card-header', '#mrReportList').text(headerText + ' (' + reports.length + ' reports)');
            },
            error: function() {
                $('#mrReportListContent').html('<p class="text-danger mb-0">Error loading report list</p>');
            }
        });
    }

    $('#loadMrReport').click(function() {
        const picker = $('#mrDateRange').data('daterangepicker');
        const startDate = picker.startDate.format('YYYYMMDD');
        const endDate = picker.endDate.format('YYYYMMDD');
        const displayStart = picker.startDate.format('YYYY/MM/DD');
        const displayEnd = picker.endDate.format('YYYY/MM/DD');

        loadMrReportList(
            { start_date: startDate, end_date: endDate },
            displayStart + ' ~ ' + displayEnd
        );
    });

    $('#listMrReports').click(function() {
        loadMrReportList(
            { limit: 50 },
            'Recent 50'
        );
    });

    $(document).on('click', '.view-mr-report', function() {
        const date = String($(this).data('date'));
        const displayDate = date.substring(0, 4) + '/' + date.substring(4, 6) + '/' + date.substring(6, 8);

        $(this).closest('tr').addClass('table-active').siblings().removeClass('table-active');
        $('#mrReportHeader').html('<i class="bi bi-hourglass-split"></i> Loading report...');
        $('#mrReportFrame').attr('src', 'daily_main_functions.php?action=get_mr_check_report&date=' + date);
        $('#mrReportHeader').html('<i class="bi bi-file-earmark-check"></i> MR Check Report: ' + displayDate);
    });

});

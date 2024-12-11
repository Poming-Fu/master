
<?php foreach ($data['branch_list'] as $branch_name => $branch_data): ?>
<div class="accordion-item mt-3 mb-3">
    <!-- branch 層級 -->
    <h2 class="accordion-header" id="heading-<?php echo $branch_name; ?>">
        <!-- Target 的子手風琴 收合按鈕 -->
        <button class="accordion-button collapsed" type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#collapse-<?php echo $branch_name; ?>">
            <div class="d-flex justify-content-between w-100">
                <h4><span class="badge bg-primary"><strong><?php echo $branch_name; ?></strong></span></h4>
            </div>
        </button>
    </h2>
    <!-- Target 層級的子手風琴 收合內容 -->
    <div id="collapse-<?php echo $branch_name; ?>" class="accordion-collapse collapse show">
        <div class="accordion-body p-0">
            <!-- Target 層級的子手風琴 內容-->
            <div class="accordion" id="targetAccordion-<?php echo $branch_name; ?>">
                <?php foreach ($branch_data['targets'] as $target_name => $target_data): ?>
                <div class="accordion-item border-0 ps-4 ">
                    <h3 class="accordion-header" id="target-heading-<?php echo $branch_name; ?>-<?php echo $target_name; ?>">
                        <!-- 表格收合按鈕 -->
                        <button class="accordion-button collapsed" type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#target-collapse-<?php echo $branch_name; ?>-<?php echo $target_name; ?>">
                            <!-- Target 層級的標題 -->
                            <div class="d-flex justify-content-between w-100">
                                <h5><span class="badge bg-secondary">Target: <?php echo $target_name; ?></span></h5>
                                <h5><span class="badge <?php echo $target_data['latest_status'] == 'PASS' ? 'bg-success' : 'bg-danger'; ?>">
                                    Latest Status: <?php echo $target_data['latest_status']; ?>
                                </span></h5>
                            </div>
                        </button>
                    </h3>
                    <!-- 表格收合內容 -->
                    <div id="target-collapse-<?php echo $branch_name; ?>-<?php echo $target_name; ?>" 
                        class="accordion-collapse collapse show">
                        <div class="accordion-body p-0">
                            <div class="table-responsive ps-4">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 15%;">Date</th>
                                            <th style="width: 10%;">Status</th>
                                            <th style="width: 35%;">Message</th>
                                            <th style="width: 20%;">log path</th>
                                            <th style="width: 20%;">download</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($target_data['builds'] as $build): ?>
                                        <tr>
                                            <td><?php echo $build['build_date']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $build['status'] == 'PASS' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $build['status']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $build['message']; ?></td>
                                            <td>
                                                <a href="daily_main_functions.php?action=view_log&path=<?php echo urlencode($build['log_path']); ?>" 
                                                target="_blank" 
                                                class="btn btn-sm btn-info">
                                                    查看日誌
                                                </a>
                                            </td>
                                            <td>
                                                <a href="daily_main_functions.php?action=download_file&path=<?php echo urlencode($build['binary_path']); ?>" 
                                                class="btn btn-sm btn-primary">
                                                    下載檔案
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
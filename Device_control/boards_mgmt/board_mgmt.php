<?php
/**
 * Board Management Forms
 * 統一的表單模板，根據 $type 變數顯示不同表單
 * $type = 'insert' or 'modify'
 */

$is_insert = ($type === 'insert');
$is_modify = ($type === 'modify');
?>

<form id="<?php echo $is_insert ? 'boardInsertForm' : 'boardModifyForm'; ?>" class="form-body">
    <input type="hidden" name="action" value="<?php echo $is_insert ? 'insert_board' : 'modify_board'; ?>">

    <?php if ($is_modify): ?>
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($board['id'] ?? ''); ?>">
    <?php endif; ?>

    <?php if ($is_insert): ?>
    <input type="hidden" name="mp_num" value="<?php echo htmlspecialchars($mp_num ?? ''); ?>">
    <input type="hidden" name="mp_ip" value="<?php echo htmlspecialchars($mp_ip ?? ''); ?>">
    <input type="hidden" name="Locate" value="<?php echo htmlspecialchars($locate ?? ''); ?>">
    <?php endif; ?>

    <!-- Basic Info -->
    <div class="form-section">
        <div class="section-title">
            <i class="bi bi-motherboard"></i>
            Basic Info
        </div>
        <div class="form-grid">
            <div class="form-field">
                <label>Board Name <?php if ($is_insert) echo '<span class="required">*</span>'; ?></label>
                <input type="text" name="B_Name"
                       placeholder="ex: X13SEW-TF"
                       value="<?php echo htmlspecialchars($board['B_Name'] ?? ''); ?>"
                       <?php if ($is_insert) echo 'required'; ?>>
            </div>
            <div class="form-field">
                <label>BMC IP <?php if ($is_insert) echo '<span class="required">*</span>'; ?></label>
                <input type="text" name="IP"
                       placeholder="10.148.xx.xx"
                       value="<?php echo htmlspecialchars($board['IP'] ?? ''); ?>"
                       <?php if ($is_insert) echo 'required'; ?>>
            </div>
            <div class="form-field">
                <label>BMC MAC</label>
                <input type="text" name="bmc_nc_mac"
                       placeholder="AA:BB:CC:DD:EE:FF"
                       value="<?php echo htmlspecialchars($board['bmc_nc_mac'] ?? ''); ?>">
            </div>
            <div class="form-field">
                <label>L1 MAC</label>
                <input type="text" name="L1_MAC"
                       placeholder="option"
                       value="<?php echo htmlspecialchars($board['L1_MAC'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <!-- Password -->
    <div class="form-section">
        <div class="section-title">
            <i class="bi bi-key"></i>
            Password
        </div>
        <div class="form-grid">
            <div class="form-field">
                <label>Unique Password</label>
                <input type="text" name="unique_pw"
                       placeholder="Factory password"
                       value="<?php echo htmlspecialchars($board['unique_pw'] ?? ''); ?>">
            </div>
            <div class="form-field">
                <label>Current Password</label>
                <input type="text" name="current_pw"
                       placeholder="Current BMC password"
                       value="<?php echo htmlspecialchars($board['current_pw'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <?php if ($is_modify): ?>
    <!-- Location -->
    <div class="form-section">
        <div class="section-title">
            <i class="bi bi-geo-alt"></i>
            Location
        </div>
        <div class="form-grid single">
            <div class="form-field">
                <label>Location</label>
                <input type="text" name="Locate"
                       value="<?php echo htmlspecialchars($board['Locate'] ?? ''); ?>">
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- PowerBox -->
    <div class="form-section">
        <div class="section-title">
            <i class="bi bi-plug"></i>
            PowerBox
        </div>
        <div class="form-grid">
            <div class="form-field">
                <label>PowerBox IP</label>
                <input type="text" name="pw_ip"
                       placeholder="option"
                       value="<?php echo htmlspecialchars($board['pw_ip'] ?? ''); ?>">
            </div>
            <div class="form-field">
                <label>PowerBox Number</label>
                <input type="number" name="pw_num"
                       placeholder="option"
                       value="<?php echo htmlspecialchars($board['pw_num'] ?? ''); ?>">
            </div>
            <div class="form-field">
                <label>PowerBox Port</label>
                <input type="number" name="pw_port"
                       placeholder="option"
                       value="<?php echo htmlspecialchars($board['pw_port'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <!-- MP510 -->
    <div class="form-section">
        <div class="section-title">
            <i class="bi bi-hdd-rack"></i>
            MP510 Console
        </div>
        <div class="form-grid">
            <div class="form-field">
                <label>MP510 IP <?php if ($is_insert) echo '<span class="required">*</span>'; ?></label>
                <input type="text" name="mp_ip"
                       value="<?php echo htmlspecialchars($is_insert ? ($mp_ip ?? '') : ($board['mp_ip'] ?? '')); ?>"
                       <?php if ($is_insert) echo 'readonly'; ?>>
            </div>
            <div class="form-field">
                <label>MP510 Number</label>
                <input type="number" name="mp_num"
                       value="<?php echo htmlspecialchars($is_insert ? ($mp_num ?? '') : ($board['mp_num'] ?? '')); ?>"
                       <?php if ($is_insert) echo 'readonly'; ?>>
            </div>
            <div class="form-field">
                <label>MP510 COM Port</label>
                <input type="number" name="mp_com"
                       placeholder="3000~300x"
                       value="<?php echo htmlspecialchars($board['mp_com'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <!-- Note -->
    <div class="form-section">
        <div class="section-title">
            <i class="bi bi-sticky"></i>
            Note
        </div>
        <div class="form-grid single">
            <div class="form-field">
                <label>Note</label>
                <input type="text" name="note"
                       placeholder="Who is using? Any remarks..."
                       value="<?php echo htmlspecialchars($board['note'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <!-- Buttons -->
    <div class="form-actions">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-arrow-left"></i> Back
        </button>
        <button type="submit" class="btn <?php echo $is_insert ? 'btn-success' : 'btn-primary'; ?>">
            <i class="bi bi-<?php echo $is_insert ? 'plus-lg' : 'check-lg'; ?>"></i>
            <?php echo $is_insert ? 'Add Board' : 'Save Changes'; ?>
        </button>
    </div>
</form>


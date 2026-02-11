# Device Control Analytics Events

## 📊 已追蹤的事件列表

### 1. **板子管理操作**

| 事件 | Category | Action | Label | 觸發元素 |
|------|----------|--------|-------|----------|
| 新增主板（按鈕） | button_click | click | Insert Board - MP510: {mp_ip} | `.mp510-actions .action-icon[title="新增主板"]` |
| 新增主板（提交） | form_submit | success/failed | Insert Board - {board_ip} | insert.php |
| 修改主板（按鈕） | button_click | click | Modify Board - {board_ip} | `.icon-btn[title="修改"]` |
| 修改主板（提交） | form_submit | success/failed | Modify Board - {board_ip} | modify.php |
| 刪除主板（按鈕） | button_click | click | Delete Board - {board_ip} | `.delete-btn` |
| 刪除主板（執行） | form_submit | success/failed | Delete Board - {board_ip} | delete.php |
| 刪除確認 | confirm_dialog | confirmed/cancelled | delete_board: {board_ip} | 確認對話框 |

---

### 2. **電源控制**

| 事件 | Category | Action | Label | 觸發元素 |
|------|----------|--------|-------|----------|
| 電源控制按鈕 | button_click | click | Power Control | `.AC-button` |
| 電源控制確認 | confirm_dialog | confirmed/cancelled | power_control: {element_id} | 確認對話框 |

---

### 3. **BMC 操作**

| 事件 | Category | Action | Label | 觸發元素 |
|------|----------|--------|-------|----------|
| BMC Console | button_click | click | BMC Console | `.telnet-console` |
| Reload Status | button_click | click | Reload Status | `.reload-icon` |
| Enable Console | confirm_dialog | confirmed/cancelled | enable_console: Enable Console | `.enableForm` |
| Board Action | confirm_dialog | confirmed/cancelled | board_action: {action} | `.actionForm` |

---

### 4. **韌體更新**

| 事件 | Category | Action | Label | 觸發元素 |
|------|----------|--------|-------|----------|
| FW Recovery 按鈕 | button_click | click | FW Recovery - {FW_type} | `.fw-button` |
| FW Recovery 確認 | confirm_dialog | confirmed/cancelled | fw_recovery: {FW_type} - {ip} | 確認對話框 |

---

### 5. **MP510 操作**

| 事件 | Category | Action | Label | 觸發元素 |
|------|----------|--------|-------|----------|
| Reset ser2net | button_click | click | Reset ser2net | `.resetMP510ser2net-icon` |
| Reset ser2net 確認 | confirm_dialog | confirmed/cancelled | reset_ser2net: {mp_ip} | 確認對話框 |

---

### 6. **複製操作**

| 事件 | Category | Action | Label | 觸發元素 |
|------|----------|--------|-------|----------|
| 複製密碼 | button_click | click | Copy Password | `.copy-button` |
| 複製所有資訊 | button_click | click | Copy All Board Info | `.copy-all-btn` |

---

### 7. **Raw Command**

| 事件 | Category | Action | Label | 觸發元素 |
|------|----------|--------|-------|----------|
| 提交 Raw Command | form_submit | submit | Raw Command | `#rawForm` |

---

## 📈 自動追蹤

### **頁面瀏覽**
- **Event Category**: `page_view`
- **Event Action**: `view`
- **Event Label**: 頁面標題
- **自動觸發**: 頁面載入時（`autoTrackPageView: true`）

---

## 🔍 查詢範例

### 查看所有按鈕點擊
```sql
SELECT event_label, COUNT(*) as count
FROM users_action
WHERE event_category = 'button_click'
GROUP BY event_label
ORDER BY count DESC;
```

### 查看確認對話框的確認率
```sql
SELECT 
    SUBSTRING_INDEX(event_label, ':', 1) as action_type,
    event_action,
    COUNT(*) as count
FROM users_action
WHERE event_category = 'confirm_dialog'
GROUP BY action_type, event_action
ORDER BY action_type, event_action;
```

### 查看最常用的功能
```sql
SELECT 
    event_category,
    event_label,
    COUNT(*) as usage_count
FROM users_action
WHERE event_category IN ('button_click', 'form_submit')
GROUP BY event_category, event_label
ORDER BY usage_count DESC
LIMIT 10;
```

---

## 📝 向後相容

舊的 `log_user_actions_collect()` 函數仍然可用，會自動轉換為新的 Analytics 追蹤：

```javascript
// 舊寫法（仍可用）
log_user_actions_collect('click_button', $(this), 'button');

// 新寫法（推薦）
Analytics.track_button_click($(this), 'Button Description');
```

---

## 🎯 下一步

可以在 **Analytics Dashboard** 查看所有追蹤數據：
- URL: `http://localhost/web1/common/analytics_dashboard.php`
- 包含統計、圖表、事件列表等


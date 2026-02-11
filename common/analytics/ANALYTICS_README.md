# Analytics System 使用說明

## 📊 概述

全新的 Analytics 系統，類似 Google Analytics 的事件追蹤模式，自動收集完整的訪客資訊。

---

## 🎯 功能特色

✅ **自動收集訪客資訊**
- Session ID（追蹤用戶會話）
- IP Address（真實 IP，支援代理）
- User Agent（完整瀏覽器資訊）
- Browser（Chrome, Firefox, Safari, etc.）
- OS（Windows, macOS, Linux, Android, iOS）
- Device Type（desktop, mobile, tablet）
- Referrer（來源頁面）

✅ **結構化事件追蹤**
- Event Category（事件類別）
- Event Action（事件動作）
- Event Label（事件標籤）
- Element ID & Type（元素資訊）

✅ **向後相容**
- 保留舊的 `action`, `element_id`, `element_type` 欄位
- 舊的 `log_user_actions_collect()` 函數仍可使用

---

## 🚀 快速開始

### 1. 在 PHP 頁面中引入

```php
<?php
session_start();
// ... 其他 require
?>
<!DOCTYPE html>
<html>
<head>
    <!-- ... -->
</head>
<body>
    <!-- 頁面內容 -->
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- 引入 Analytics SDK -->
    <script src="../common/analytics/analytics.js"></script>
    <script>
    // 初始化 Analytics
    Analytics.init('../common/analytics/analytics.php', {
        debug: false,              // 開發時設為 true
        autoTrackPageView: true    // 自動追蹤頁面瀏覽
    });
    </script>
    <!-- 其他 JS -->
</body>
</html>
```

### 2. 追蹤事件

```javascript
// 追蹤按鈕點擊
$('.my-button').click(function() {
    Analytics.track_button_click($(this), '按鈕描述');
});

// 追蹤連結點擊
$('.my-link').click(function() {
    Analytics.track_link_click($(this), '連結描述');
});

// 追蹤表單提交
$('form').submit(function() {
    Analytics.track_form_submit($(this), '表單名稱');
});

// 追蹤確認對話框
const confirmed = confirm('確定要執行嗎？');
Analytics.track_confirm('delete_action', confirmed, '刪除項目');

// 自訂事件
Analytics.track_event('category', 'action', 'label', {
    element_id: 'my-element',
    element_type: 'button'
});
```

---

## 📁 檔案結構

```
web1/common/analytics/
├── analytics.php          # PHP Analytics 類別 + API Endpoint
├── analytics.js           # JavaScript SDK
├── analytics_dashboard.php # Analytics Dashboard
└── ANALYTICS_README.md    # 本文檔
```

---

## 🗄️ 資料庫結構

```sql
users_action 表：
- id (自動遞增)
- u_acc (用戶帳號)
- session_id (會話 ID)
- ip_address (IP 地址)
- user_agent (User Agent)
- browser (瀏覽器)
- os (作業系統)
- device_type (裝置類型)
- action (動作，向後相容)
- element_id (元素 ID)
- element_type (元素類型)
- page_url (頁面 URL)
- referrer (來源頁面)
- event_category (事件類別)
- event_action (事件動作)
- event_label (事件標籤)
- timestamp (時間戳記)
```

---

## 🔍 查詢範例

```sql
-- 查看最近的事件
SELECT * FROM users_action ORDER BY timestamp DESC LIMIT 10;

-- 統計各類事件數量
SELECT event_category, event_action, COUNT(*) as count
FROM users_action
GROUP BY event_category, event_action
ORDER BY count DESC;

-- 查看特定用戶的行為
SELECT * FROM users_action
WHERE u_acc = 'baber'
ORDER BY timestamp DESC;

-- 統計瀏覽器分布
SELECT browser, COUNT(*) as count
FROM users_action
GROUP BY browser
ORDER BY count DESC;

-- 統計裝置類型
SELECT device_type, COUNT(*) as count
FROM users_action
GROUP BY device_type;
```

---

## ⚠️ 注意事項

1. **Session 必須啟動**：頁面開頭必須有 `session_start()`
2. **Guest 用戶**：未登入用戶會記錄為 'guest'
3. **效能考量**：使用 `navigator.sendBeacon` 非阻塞式發送
4. **Debug 模式**：開發時可設定 `debug: true` 查看 console 訊息

---

## 🔄 遷移指南

### 舊的寫法（仍可使用）：
```javascript
log_user_actions_collect('click_button', $(this), 'button');
```

### 新的寫法（推薦）：
```javascript
Analytics.track_button_click($(this), 'Button Description');
```

---

## 📞 支援

如有問題，請查看：
- `web1/common/analytics/analytics_dashboard.php` - Analytics Dashboard
- Console 訊息（debug 模式）
- PHP error log


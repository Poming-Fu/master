# CSS 重構計劃與影響分析

## 📋 目標
將重複的 CSS 樣式統一到 `common/common.css`，減少冗餘，提升維護性。

---

## 📊 影響分析表

### **檔案依賴關係**

| PHP 頁面 | 目前使用的 CSS | 將新增 | 影響範圍 |
|----------|---------------|--------|----------|
| `Device_control/dev_ctrl_main.php` | navbar.css<br>dev_ctrl_main.css<br>boards_mgmt.css | **common.css** | 🔴 高 - 主要頁面 |
| `Fw_release_build/fw_rel_main.php` | navbar.css<br>fw_rel_main.css | **common.css** | 🔴 高 - 主要頁面 |
| `Daily_build/daily_main.php` | navbar.css<br>daily_main.css | **common.css** | 🔴 高 - 主要頁面 |
| `common/analytics/analytics_dashboard.php` | navbar.css<br>(內嵌 CSS) | **common.css** | 🟡 中 - 新頁面 |
| `index.php` | navbar.css<br>lottery.css | **common.css** | 🟢 低 - 簡單頁面 |

---

## 🔧 修改計劃

### **階段 1: 建立 common.css** ✅ 已完成
- [x] 建立 `web1/common/common.css`
- [x] 包含所有共用的 CSS 變數和樣式

### **階段 2: 引入 common.css 到各頁面**
需要在以下檔案的 `<head>` 中加入：
```html
<link href="/web1/common/common.css" rel="stylesheet">
```

**修改順序（由低到高風險）：**
1. ✅ `common/analytics/analytics_dashboard.php` - 新頁面，影響最小
2. ⏳ `Daily_build/daily_main.php` - 獨立頁面
3. ⏳ `Fw_release_build/fw_rel_main.php` - 獨立頁面
4. ⏳ `Device_control/dev_ctrl_main.php` - 主要頁面
5. ⏳ `index.php` - 首頁

### **階段 3: 清理各頁面 CSS 檔案**
移除已在 `common.css` 中定義的重複樣式。

---

## 📝 詳細修改清單

### **1. Device_control/dev_ctrl_main.css**

**可刪除的部分（已在 common.css）：**
- ❌ 第 3-23 行：`:root` CSS 變數定義
- ❌ 第 26-28 行：`body` 字體設定
- ❌ 第 31-36 行：`.container-fluid` 設定
- ❌ 第 38-74 行：`.status-bar` 樣式（已在 common.css）

**保留的部分（頁面專屬）：**
- ✅ 第 76-612 行：工具箱、MP510 群組、表格等專屬樣式

**預計減少：** ~74 行 → 剩餘 ~538 行

---

### **2. Fw_release_build/fw_rel_main.css**

**可刪除的部分：**
- ❌ 第 3-24 行：`:root` CSS 變數定義
- ❌ 第 27-29 行：`body` 字體設定
- ❌ 第 32-37 行：`.container-fluid` 設定
- ❌ 第 39-75 行：`.status-bar` 樣式
- ❌ 第 77-96 行：`.card` 樣式（已在 common.css）
- ❌ 第 98-117 行：`.form-label`, `.form-control` 樣式

**保留的部分：**
- ✅ 第 119+ 行：Build Form 專屬樣式、History Table 等

**預計減少：** ~117 行 → 剩餘 ~331 行

---

### **3. Daily_build/daily_main.css**

**可刪除的部分：**
- ❌ 第 3-24 行：`:root` CSS 變數定義
- ❌ 第 27-30 行：`body` 字體設定
- ❌ 第 33-38 行：`.container-fluid` 設定
- ❌ 第 40-56 行：`.page-header` 樣式（已在 common.css）
- ❌ 第 76-98 行：`.form-label`, `.form-control` 樣式

**保留的部分：**
- ✅ 第 58-75 行：`.filter-card` 專屬樣式
- ✅ 第 100+ 行：按鈕、表格、Tab 等專屬樣式

**預計減少：** ~98 行 → 剩餘 ~162 行

---

### **4. Device_control/boards_mgmt/boards_mgmt.css**

**可刪除的部分：**
- ❌ 第 4-22 行：`#boardManagementModal` 內的 CSS 變數定義

**保留的部分：**
- ✅ 所有 Modal 專屬樣式（已用 `#boardManagementModal` 限定作用域）

**預計減少：** ~19 行 → 剩餘 ~241 行

---

### **5. common/analytics/analytics_dashboard.php**

**可刪除的部分（內嵌 CSS）：**
- ❌ 第 53-62 行：`:root` CSS 變數定義
- ❌ 第 64-67 行：`body` 設定

**保留的部分：**
- ✅ 第 69+ 行：Dashboard 專屬樣式（stat-card, table-card 等）

**預計減少：** ~15 行內嵌 CSS

---

## 📊 重構前後對比

| 檔案 | 重構前 | 重構後 | 減少 | 減少率 |
|------|--------|--------|------|--------|
| `dev_ctrl_main.css` | 612 行 | ~538 行 | ~74 行 | 12% |
| `fw_rel_main.css` | 448 行 | ~331 行 | ~117 行 | 26% |
| `daily_main.css` | 260 行 | ~162 行 | ~98 行 | 38% |
| `boards_mgmt.css` | 260 行 | ~241 行 | ~19 行 | 7% |
| `analytics (內嵌)` | ~150 行 | ~135 行 | ~15 行 | 10% |
| **總計** | **1,730 行** | **~1,407 行** | **~323 行** | **19%** |

**新增：** `common.css` 145 行

**淨減少：** ~178 行（10%）

---

## ⚠️ 風險評估

### **低風險 ✅**
- CSS 變數定義移除（完全相同）
- body/container 基礎設定（所有頁面一致）

### **中風險 ⚠️**
- `.status-bar` 樣式（Device_control 和 Fw_release_build 略有差異）
- `.card` 樣式（需確認所有頁面使用一致）

### **需要測試的頁面**
1. Device Control - 狀態列、工具箱、MP510 群組
2. Fw Release Build - Build Form、History Table
3. Daily Build - 篩選卡片、Tab 導航
4. Analytics Dashboard - 統計卡片、表格
5. Index - Lottery 功能

---

## 🚀 執行步驟

1. **備份** - 確保可以回滾
2. **引入 common.css** - 在各頁面 `<head>` 加入
3. **清理重複樣式** - 從各 CSS 檔案移除
4. **測試** - 逐頁檢查樣式是否正常
5. **Commit** - 提交到 beta 分支

---

## 📌 注意事項

1. **CSS 載入順序很重要：**
   ```html
   <link href="navbar.css">           <!-- 1. Navbar -->
   <link href="common.css">            <!-- 2. 共用樣式 -->
   <link href="page-specific.css">    <!-- 3. 頁面專屬 -->
   ```

2. **CSS 變數繼承：**
   - `common.css` 定義在 `:root`
   - 頁面專屬 CSS 可以覆蓋（如需要）

3. **作用域隔離：**
   - `boards_mgmt.css` 使用 `#boardManagementModal` 前綴
   - 不會與全域樣式衝突



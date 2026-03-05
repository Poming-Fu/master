# BMC Console (bmc-console.html) 改動說明

> 檔案：`Device_control/websocket-terminal/bmc-console.html`
> 分支：beta（已 git stash）
> stash：`bmc-console: control panel UI + real-time highlight + font size + checkall`

---

## 一、Bug 修復

### 1. 斷線重連失敗
- **問題**：`socket.onclose` 裡呼叫 `setTimeout(connect, 3000)`，但 `connect` 函式不存在
- **修復**：改為 `setTimeout(main, RECONNECT_DELAY)`，用已定義的 `main()` 重新建立 WebSocket

### 2. stopHeartbeat() 未定義
- **問題**：`onclose` / `onerror` 呼叫 `stopHeartbeat()` 但沒有這個函式，heartbeat timer 不會被清掉
- **修復**：新增 `stopHeartbeat()` → `clearInterval(heartbeatInterval)`

### 3. HTML 結構
- **問題**：`<style>` 寫在 `</head>` 之後
- **修復**：移進 `<head>` 內

### 4. 死碼清理
- 移除被註解的舊 timestamp 邏輯、highlight on Enter、重複 resize listener
- 精簡冗餘註解

---

## 二、即時關鍵字上色（Real-time Highlight）

### 原理
在資料寫入 terminal **之前**先過濾上色，不需要手動按按鈕。

```
WebSocket 資料進來 → timestamp（optional）→ apply_highlight() → term.write()
```

### 新增函式

#### `escape_regex(str)`
跳脫 regex 特殊字元（`.` `*` `(` `[` 等），避免使用者輸入的 keyword 含特殊字元時 RegExp 報錯。

```js
function escape_regex(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
```

#### `apply_highlight(text)`
讀取三個 keyword 輸入框的值 + checkbox 狀態，對文字套用 ANSI escape code 上色：
- 紅色：`\u001b[1;31m`
- 黃色：`\u001b[1;33m`
- 藍色：`\u001b[1;34m`
- 重置：`\u001b[0m`

使用 `new RegExp(escape_regex(keyword), 'gi')` 做全域不分大小寫匹配。

#### `socket.onmessage` 修改
```js
socket.onmessage = function(event) {
    let data = event.data;
    if (addTimestamp) {
        data = data.replace(/(\r\n|\r)/g, function(match) {
            return match + `[${getTermTimestamp()}] `;
        });
    }
    data = apply_highlight(data);   // ← 新增：寫入前即時上色
    term.write(data);
};
```

### 即時 debounce 監聽
在 keyword 輸入框加 `input` 事件、checkbox 加 `change` 事件，停止操作 300ms 後自動對全 buffer 重新上色：

```js
let highlightDebounceTimer;
function debounce_highlight() {
    clearTimeout(highlightDebounceTimer);
    highlightDebounceTimer = setTimeout(highlight, 300);
}
['redKeyword', 'yellowKeyword', 'blueKeyword'].forEach(id => {
    document.getElementById(id).addEventListener('input', debounce_highlight);
});
['redKeywordCheckbox', 'yellowKeywordCheckbox', 'blueKeywordCheckbox'].forEach(id => {
    document.getElementById(id).addEventListener('change', debounce_highlight);
});
```

### 限制
客戶端輸入的指令是逐字元 echo 回來的，所以多字元 keyword 無法在打字當下即時匹配。
但改變 keyword 或 checkbox 時會觸發 debounce，300ms 後重掃整個 buffer，屆時會上色。

---

## 三、字體大小控制

### UI
工具列新增 `A-` / `16px` / `A+` 按鈕組。

### JS
```js
let currentFontSize = 16;

function change_font_size(delta) {
    currentFontSize = Math.max(8, Math.min(40, currentFontSize + delta));
    term.options.fontSize = currentFontSize;
    document.getElementById('fontSizeDisplay').textContent = currentFontSize + 'px';
    fitAddon.fit();
}
```
- 每次 ±2px，範圍 8px ~ 40px
- `term.options.fontSize` 是 xterm.js 支援的動態屬性，設定後立刻重新渲染
- `fitAddon.fit()` 根據新字體重算 cols/rows，自動調整排版

---

## 四、Check All / Uncheck All 按鈕

### ☑ Check All
勾選全部三個 keyword checkbox 並立刻觸發 `highlight()` 重掃全 buffer：

```js
document.getElementById('checkall').addEventListener('click', function() {
    document.getElementById('redKeywordCheckbox').checked = true;
    document.getElementById('yellowKeywordCheckbox').checked = true;
    document.getElementById('blueKeywordCheckbox').checked = true;
    highlight();
});
```

### ☐ Uncheck All（原 Clean All）
取消全部 checkbox、清空 keyword 輸入框、reset terminal 並重寫純文字（去色）。
- 按鈕 id 由 `cleanall` 改為 `uncheckall`
- 圖示由 🗑 改為 ☐（&#9744;）

---

## 五、Control Panel UI 現代化

### 設計風格
GitHub Dark 風格深色主題，玻璃質感（glassmorphism）。

### CSS 重點

| 項目 | 做法 |
|---|---|
| 背景 | `linear-gradient(180deg, #161b22, #0d1117)` |
| 群組邊框 | `rgba(255,255,255,0.1)` 半透明 + `backdrop-filter: blur(4px)` |
| 按鈕 hover | `translateY(-1px)` 浮起 + `box-shadow: 0 2px 8px rgba(0,0,0,0.3)` |
| 圓角 | 群組 `8px`、按鈕/輸入框 `6px` |
| Checkbox | 自訂外觀：`appearance: none`、深色底、勾選時綠色 `#238636` + CSS 偽元素打勾 |
| Keyword 輸入框 | 帶透明度底色 `rgba(…, 0.85)`、focus 時色彩匹配的 ring `box-shadow` |
| 字體 | 系統字型 stack：`-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen` |
| 動畫 | 統一 `transition: all 0.15s ease` |

### HTML 結構
工具列分為 5 個 `ctrl-group`，用 `ctrl-divider` 分隔功能區域：

```
[ File: 💾 Save ] [ Font: A- 16px A+ ] [ Time: ⏱ Timestamp ☐ ]
  |
[ Highlight: [Red]☑ [Yellow]☑ [Blue]☑ ] [ 🎨 Highlight | ☑ Check All | ☐ Uncheck All ]
```

每個群組有 `ctrl-group-label`（灰色大寫小字）標示用途。


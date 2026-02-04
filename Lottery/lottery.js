/**
 * 樂透抽籤系統
 * 功能：從團隊成員中隨機抽取人員，支援多人抽取、歷史記錄、重置等功能
 */
(function() {
    'use strict';

    // ============================================================
    // 常數定義
    // ============================================================
    const STORAGE_KEY = 'lottery_drawn_ids';
    const HISTORY_KEY = 'lottery_history';
    
    // ============================================================
    // 全域變數
    // ============================================================
    let allMembers = [];
    
    // DOM 元素
    let totalCountEl, remainingCountEl, drawnCountEl;
    let drawnListEl, resultCard, resultList;
    let drawCountInput, drawBtn, resetBtn, copyWinnersBtn;

    // 儲存當前中獎者
    let currentWinners = [];

    // ============================================================
    // 初始化函式
    // ============================================================
    function init(members) {
        allMembers = members;
        
        // 取得 DOM 元素
        totalCountEl = document.getElementById('totalCount');
        remainingCountEl = document.getElementById('remainingCount');
        drawnCountEl = document.getElementById('drawnCount');
        drawnListEl = document.getElementById('drawnList');
        resultCard = document.getElementById('resultCard');
        resultList = document.getElementById('resultList');
        drawCountInput = document.getElementById('drawCount');
        drawBtn = document.getElementById('drawBtn');
        resetBtn = document.getElementById('resetLotteryBtn');
        copyWinnersBtn = document.getElementById('copyWinnersBtn');

        // 綁定事件
        drawBtn.addEventListener('click', draw);
        resetBtn.addEventListener('click', reset);
        copyWinnersBtn.addEventListener('click', copyWinners);
        
        // Modal 開啟時更新 UI
        const modal = document.getElementById('lotteryModal');
        if (modal) {
            modal.addEventListener('show.bs.modal', updateUI);
        }

        // 初始化 UI
        updateUI();
    }

    // ============================================================
    // LocalStorage 操作
    // ============================================================
    
    /**
     * 從 LocalStorage 讀取已抽過的 ID
     */
    function getDrawnIds() {
        const stored = localStorage.getItem(STORAGE_KEY);
        return stored ? JSON.parse(stored) : [];
    }

    /**
     * 儲存已抽過的 ID 到 LocalStorage
     */
    function saveDrawnIds(ids) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
    }

    /**
     * 取得抽籤歷史記錄
     */
    function getHistory() {
        const stored = localStorage.getItem(HISTORY_KEY);
        return stored ? JSON.parse(stored) : [];
    }

    /**
     * 儲存抽籤歷史記錄
     */
    function saveHistory(history) {
        localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
    }

    // ============================================================
    // 核心邏輯
    // ============================================================
    
    /**
     * 取得剩餘可抽取的人員
     */
    function getRemainingMembers() {
        const drawnIds = getDrawnIds();
        return allMembers.filter(m => !drawnIds.includes(m.id));
    }

    /**
     * Fisher-Yates 洗牌演算法（真正的均勻隨機）
     */
    function shuffleArray(array) {
        const shuffled = [...array];
        for (let i = shuffled.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }
        return shuffled;
    }

    /**
     * 更新 UI 顯示
     */
    function updateUI() {
        const drawnIds = getDrawnIds();
        const remaining = getRemainingMembers();

        // 更新統計數字
        totalCountEl.textContent = allMembers.length;
        remainingCountEl.textContent = remaining.length;
        drawnCountEl.textContent = drawnIds.length;

        // 更新已抽過列表
        if (drawnIds.length === 0) {
            drawnListEl.innerHTML = '<span class="text-muted">尚無</span>';
        } else {
            const drawnMembers = allMembers.filter(m => drawnIds.includes(m.id));
            drawnListEl.innerHTML = drawnMembers.map(m =>
                `<span class="badge bg-secondary">${m.name}</span>`
            ).join('');
        }

        // 更新抽取人數上限
        drawCountInput.max = remaining.length;
        if (parseInt(drawCountInput.value) > remaining.length) {
            drawCountInput.value = remaining.length || 1;
        }

        // 禁用按鈕如果沒有剩餘人員
        drawBtn.disabled = remaining.length === 0;
    }

    /**
     * 執行抽籤
     */
    function draw() {
        const remaining = getRemainingMembers();
        let count = parseInt(drawCountInput.value) || 1;

        if (count > remaining.length) {
            count = remaining.length;
        }

        if (count === 0 || remaining.length === 0) {
            alert('沒有可抽取的人員了！');
            return;
        }

        // 使用 Fisher-Yates 演算法隨機抽取
        const shuffled = shuffleArray(remaining);
        const winners = shuffled.slice(0, count);

        // 儲存當前中獎者
        currentWinners = winners;

        // 更新已抽過的 ID
        const drawnIds = getDrawnIds();
        winners.forEach(w => drawnIds.push(w.id));
        saveDrawnIds(drawnIds);

        // 記錄到歷史
        const history = getHistory();
        history.push({
            timestamp: new Date().toISOString(),
            winners: winners.map(w => ({ id: w.id, name: w.name })),
            count: count
        });
        saveHistory(history);

        // 顯示結果（加入動畫效果）
        resultCard.style.display = 'block';
        resultList.innerHTML = winners.map((w, i) =>
            `<li class="list-group-item d-flex justify-content-between align-items-center">
                <span><strong>${i + 1}.</strong> ${w.name}</span>
                <span class="text-muted">${w.id}</span>
            </li>`
        ).join('');

        // 更新 UI
        updateUI();
    }

    /**
     * 複製中獎名單到剪貼簿
     */
    function copyWinners() {
        if (currentWinners.length === 0) {
            alert('目前沒有抽中的名單！');
            return;
        }

        // 格式化名單
        const text = currentWinners.map((w, i) =>
            `${i + 1}. ${w.name} (${w.id})`
        ).join('\n');

        // 嘗試使用現代 Clipboard API
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                showCopySuccess();
            }).catch(err => {
                console.error('Clipboard API 失敗:', err);
                fallbackCopy(text);
            });
        } else {
            // 使用舊方法作為備用方案
            fallbackCopy(text);
        }
    }

    /**
     * 備用複製方法（使用 textarea + execCommand）
     */
    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess();
            } else {
                alert('複製失敗，請手動複製');
            }
        } catch (err) {
            console.error('複製失敗:', err);
            alert('複製失敗，請手動複製');
        } finally {
            document.body.removeChild(textarea);
        }
    }

    /**
     * 顯示複製成功提示
     */
    function showCopySuccess() {
        const originalText = copyWinnersBtn.innerHTML;
        copyWinnersBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>已複製';
        copyWinnersBtn.classList.remove('btn-light');
        copyWinnersBtn.classList.add('btn-success');

        // 2秒後恢復
        setTimeout(() => {
            copyWinnersBtn.innerHTML = originalText;
            copyWinnersBtn.classList.remove('btn-success');
            copyWinnersBtn.classList.add('btn-light');
        }, 2000);
    }

    /**
     * 重置抽籤記錄
     */
    function reset() {
        if (confirm('確定要重置嗎？所有抽籤紀錄將被清除。')) {
            localStorage.removeItem(STORAGE_KEY);
            localStorage.removeItem(HISTORY_KEY);
            resultCard.style.display = 'none';
            resultList.innerHTML = '';
            currentWinners = [];
            updateUI();
        }
    }

    // ============================================================
    // 匯出到全域
    // ============================================================
    window.LotterySystem = {
        init: init
    };

})();


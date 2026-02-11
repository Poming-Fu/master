/**
 * Analytics SDK - JavaScript 端
 * 統一的用戶行為追蹤系統
 */

const Analytics = {
    // 配置
    config: {
        endpoint: null,  // 將由各頁面設定
        debug: false
    },

    /**
     * 初始化 Analytics
     * @param {string} endpoint - API endpoint URL
     * @param {object} options - 配置選項
     */
    init: function(endpoint, options = {}) {
        this.config.endpoint = endpoint;
        this.config.debug = options.debug || false;
        
        // 自動追蹤頁面瀏覽
        if (options.autoTrackPageView !== false) {
            this.track_page_view();
        }
        
        if (this.config.debug) {
            console.log('[Analytics] Initialized with endpoint:', endpoint);
        }
    },

    /**
     * 追蹤事件
     * @param {string} category - 事件類別 (button_click, form_submit, etc.)
     * @param {string} action - 事件動作 (click, submit, etc.)
     * @param {string} label - 事件標籤
     * @param {object} options - 額外選項
     */
    track_event: function(category, action, label = '', options = {}) {
        if (!this.config.endpoint) {
            console.error('[Analytics] Endpoint not configured');
            return;
        }

        const data = {
            action: 'track_event',
            event_category: category,
            event_action: action,
            event_label: label,
            element_id: options.element_id || null,
            element_type: options.element_type || null
        };

        if (this.config.debug) {
            console.log('[Analytics] Tracking event:', data);
        }

        // 使用 sendBeacon 或 AJAX
        if (navigator.sendBeacon && !this.config.debug) {
            const formData = new FormData();
            for (const key in data) {
                formData.append(key, data[key]);
            }
            navigator.sendBeacon(this.config.endpoint, formData);
        } else {
            $.post(this.config.endpoint, data).fail(function(xhr, status, error) {
                if (this.config.debug) {
                    console.error('[Analytics] Tracking failed:', error);
                }
            }.bind(this));
        }
    },

    /**
     * 追蹤頁面瀏覽
     */
    track_page_view: function() {
        const page_title = document.title;
        this.track_event('page', 'view', page_title);
    },

    /**
     * 追蹤按鈕點擊
     * @param {jQuery} element - jQuery 元素
     * @param {string} label - 描述
     */
    track_button_click: function(element, label = '') {
        const element_id = this._get_element_id(element);
        const element_label = label || element.text().trim() || element.attr('title') || element_id;

        this.track_event('button', 'click', element_label, {
            element_id: element_id,
            element_type: 'button'
        });
    },

    /**
     * 追蹤連結點擊
     * @param {jQuery} element - jQuery 元素
     * @param {string} label - 描述
     */
    track_link_click: function(element, label = '') {
        const element_id = this._get_element_id(element);
        const href = element.attr('href') || '';
        const element_label = label || element.text().trim() || href;

        this.track_event('link', 'click', element_label, {
            element_id: element_id,
            element_type: 'link'
        });
    },

    /**
     * 追蹤表單提交
     * @param {jQuery} element - jQuery 元素
     * @param {string} label - 描述
     */
    track_form_submit: function(element, label = '') {
        const element_id = this._get_element_id(element);
        const element_label = label || element_id;

        this.track_event('form', 'submit', element_label, {
            element_id: element_id,
            element_type: 'form'
        });
    },

    /**
     * 追蹤確認對話框
     * @param {string} action_type - 動作類型
     * @param {boolean} confirmed - 是否確認
     * @param {string} label - 描述
     */
    track_confirm: function(action_type, confirmed, label = '') {
        const result = confirmed ? 'confirmed' : 'cancelled';
        this.track_event('confirm', result, `${action_type}: ${label}`);
    },

    /**
     * 取得元素 ID（智能判斷）
     * @private
     */
    _get_element_id: function(element) {
        return element.attr('id')
            || element.data('id')
            || element.attr('name')
            || element.parent().attr('id')
            || element.attr('class')
            || 'unknown';
    }
};

// 向後相容：保留舊的函數名稱
function log_user_actions_collect(action, element, element_type) {
    console.warn('[Analytics] log_user_actions_collect is deprecated. Use Analytics.track_event instead.');
    Analytics.track_button_click(element, action);
}


<?php
/**
 * Analytics SDK - 統一的用戶行為追蹤系統
 * 類似 Google Analytics 的事件追蹤模式
 */

require_once __DIR__ . '/../../DB/db_operations_all.php';

class Analytics {
    
    /**
     * 記錄事件
     *
     * @param string $event_category 事件類別 (page, button, form, confirm, etc.)
     * @param string $event_action 事件動作 (click, submit, view, confirmed, cancelled, etc.)
     * @param string $event_label 事件標籤 (具體描述)
     * @param array $options 額外選項 (element_id, element_type, etc.)
     */
    public static function track_event($event_category, $event_action, $event_label = '', $options = []) {
        try {
            $conn = database_connection::get_connection();
            
            // 取得用戶資訊
            $u_acc = $_SESSION['username'] ?? 'guest';
            
            // 取得或建立 session_id
            $session_id = self::get_session_id();
            
            // 取得訪客資訊
            $ip_address = self::get_client_ip();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $browser_info = self::parse_user_agent($user_agent);
            
            // 取得頁面資訊
            $page_url = $_SERVER['REQUEST_URI'] ?? '';
            $referrer = $_SERVER['HTTP_REFERER'] ?? '';
            
            // 從 options 取得額外資訊
            $element_id = $options['element_id'] ?? null;
            $element_type = $options['element_type'] ?? null;
            
            // 為了向後相容，保留 action 欄位
            $action = $event_category . '_' . $event_action;
            
            // 插入資料
            $sql = "INSERT INTO users_action (
                u_acc, session_id, ip_address, user_agent, browser, os, device_type,
                action, element_id, element_type, page_url, referrer,
                event_category, event_action, event_label
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssssssssss",
                $u_acc,
                $session_id,
                $ip_address,
                $user_agent,
                $browser_info['browser'],
                $browser_info['os'],
                $browser_info['device_type'],
                $action,
                $element_id,
                $element_type,
                $page_url,
                $referrer,
                $event_category,
                $event_action,
                $event_label
            );
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Analytics tracking error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 記錄頁面瀏覽
     */
    public static function track_page_view($page_title = '') {
        return self::track_event('page', 'view', $page_title);
    }
    
    /**
     * 取得或建立 session_id
     */
    private static function get_session_id() {
        if (!isset($_SESSION['analytics_session_id'])) {
            $_SESSION['analytics_session_id'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['analytics_session_id'];
    }
    
    /**
     * 取得客戶端真實 IP
     */
    private static function get_client_ip() {
        $ip_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $ip = $_SERVER[$key];
                // 處理多個 IP 的情況（取第一個）
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return 'unknown';
    }
    
    /**
     * 解析 User Agent
     */
    private static function parse_user_agent($user_agent) {
        $browser = 'Unknown';
        $os = 'Unknown';
        $device_type = 'desktop';
        
        // 偵測瀏覽器（順序重要：Edge/Edg 必須在 Chrome 之前，Chrome 必須在 Safari 之前）
        if (preg_match('/MSIE|Trident/i', $user_agent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Edg/i', $user_agent)) {
            $browser = 'Edge';
        } elseif (preg_match('/Firefox/i', $user_agent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $user_agent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $user_agent)) {
            $browser = 'Safari';
        }

        // 偵測作業系統
        if (preg_match('/Windows/i', $user_agent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac OS X/i', $user_agent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $user_agent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $user_agent)) {
            $os = 'Android';
        } elseif (preg_match('/iOS|iPhone|iPad/i', $user_agent)) {
            $os = 'iOS';
        }

        // 偵測裝置類型
        if (preg_match('/Mobile|Android|iPhone/i', $user_agent)) {
            $device_type = 'mobile';
        } elseif (preg_match('/iPad|Tablet/i', $user_agent)) {
            $device_type = 'tablet';
        }

        return [
            'browser' => $browser,
            'os' => $os,
            'device_type' => $device_type
        ];
    }

    /**
     * 處理 API 請求
     * 作為 API endpoint 使用
     */
    public static function handle_api_request() {
        // 只接受 POST 請求
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        try {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'track_event':
                    $event_category = $_POST['event_category'] ?? '';
                    $event_action = $_POST['event_action'] ?? '';
                    $event_label = $_POST['event_label'] ?? '';

                    $options = [
                        'element_id' => $_POST['element_id'] ?? null,
                        'element_type' => $_POST['element_type'] ?? null
                    ];

                    $result = self::track_event($event_category, $event_action, $event_label, $options);

                    echo json_encode([
                        'success' => $result,
                        'message' => $result ? 'Event tracked' : 'Failed to track event'
                    ]);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action']);
                    break;
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

// 如果是直接被呼叫（作為 API endpoint），則處理 API 請求
if (basename($_SERVER['PHP_SELF']) === 'analytics.php') {
    session_start();
    Analytics::handle_api_request();
}


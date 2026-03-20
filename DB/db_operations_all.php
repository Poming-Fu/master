<?php
/*class database_connection {
    private static $conn = null;

    public static function get_connection() {
        if (self::$conn === null) {
            $db_server = "10.148.175.12"; #main server
            $db_user = "one";
            $db_password = "1234";
            $database = "ipmi";

            self::$conn = new mysqli($db_server, $db_user, $db_password, $database);

            if (self::$conn->connect_error) {
                die("連接失敗: " . self::$conn->connect_error);
            }
        }
        return self::$conn;
    }
}*/
class database_connection {
    private static $conn = null;
    private static $server_hostname = null;
    private static $server_ip = null;

    // 初始化伺服器資訊
    private static function init_server_info() {
        if (self::$server_hostname === null) {
            self::$server_hostname = gethostname();
            
            // 直接獲取第一個非本地 IP 地址
            $ip = trim(shell_exec("hostname -I | awk '{print $1}'"));
            
            
            // 確保獲取的是有效 IP
            if (filter_var($ip, FILTER_VALIDATE_IP) && $ip != '127.0.0.1' && $ip != '127.0.1.1') {
                self::$server_ip = $ip;
            } else {
                // 回退到原始方法
                self::$server_ip = gethostbyname(self::$server_hostname);
            }
        }
    }

    public static function get_connection() {
        self::init_server_info();
        if (self::$conn === null) {
            $db_server = self::$server_ip; #main server
            $db_user = "one";
            $db_password = "1234";
            $database = "ipmi";

            self::$conn = new mysqli($db_server, $db_user, $db_password, $database);

            if (self::$conn->connect_error) {
                die("連接失敗: " . self::$conn->connect_error);
            }
        }
        return self::$conn;
    }

    // 新增取得伺服器 IP 的方法
    public static function get_server_ip() {
        self::init_server_info();
        return self::$server_ip;
    }
}

 class mp510_repository {
    public static function get_master_ip() {
        $conn   = database_connection::get_connection();
        $sql    = "SELECT mp_ip FROM mp510 WHERE node_type = 'master'";
        $stmt   = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $current_master = null; //只有一個master
        if ($row = $result->fetch_assoc()) {
            $current_master = $row['mp_ip'];  // 從 DB 獲取 master IP
        } else {
            $current_master = "NA";
        }
        $stmt->close();
        return $current_master;
    }

    public static function get_mp510_by_ip($mp_ip) {
        $conn = database_connection::get_connection();
        $sql  = "SELECT * FROM mp510 WHERE mp_ip = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $mp_ip);
        $stmt->execute();
        $result = $stmt->get_result();

        $mp510_info = null;
        if ($row = $result->fetch_assoc()) {
            $mp510_info = $row;
        }
        $stmt->close();
        return $mp510_info;
    }
 }

class users_repository {
    public static function query_users_info() {
        $conn   = database_connection::get_connection();
        // 獲取用戶列表
        $sql    = "SELECT * FROM users";
        $stmt   = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $users  = []; // 空的數組來存用戶信息
        while ($row = $result->fetch_assoc()) {
            $users[] = $row; // 直接將$row賦值給$users數組
        }
        return $users;
    }
    public static function check_user_in_db($username) {
        $conn   = database_connection::get_connection();
        $sql    = "SELECT * FROM users WHERE u_acc = ?";
        $stmt   = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    public static function update_user_last_login($username) {
        $conn = database_connection::get_connection();
        $sql  = "UPDATE users SET last_login = NOW() WHERE u_acc = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();
    }

    public static function add_new_user($u_acc, $u_lev) {
        $conn = database_connection::get_connection();
        $sql  = "INSERT INTO users (u_acc, u_lev) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $u_acc, $u_lev);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function update_new_user($u_acc, $u_lev, $id) {
        $conn = database_connection::get_connection();
        $sql  = "UPDATE users SET u_acc=?, u_lev=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $u_acc, $u_lev, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function delete_new_user($id) {
        $conn = database_connection::get_connection();
        $sql  = "DELETE FROM users WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function log_user_actions($u_acc, $action, $element_id, $element_type, $page_url) {
        $conn = database_connection::get_connection();
        $sql  = "INSERT INTO users_action (u_acc, action, element_id, element_type, page_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $u_acc, $action, $element_id, $element_type, $page_url);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

class daily_repository {

    // 修改 query_daily_info 函數來分組數據 暫時用不到
    /* public static function query_daily_info($filters = []) {
        $conn = database_connection::get_connection();
        $sql = "SELECT * FROM daily_builds WHERE 1=1"; //使用 WHERE 1=1 後，可以統一使用 AND
        $params = [];
        $types = "";

        // 處理過濾條件
        if (!empty($filters['branch'])) {
            $sql .= " AND branch = ?";
            $params[] = $filters['branch'];
            $types .= "s";
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = strtoupper($filters['status']); // 轉大寫確保匹配
            $types .= "s";
        }

        if (!empty($filters['date'])) {
            $sql .= " AND DATE(build_date) = ?";
            $params[] = $filters['date'];
            $types .= "s";
        }

        $sql .= " ORDER BY branch, build_date DESC";
        
        $stmt = $conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
            // 使用展開運算符
            //$stmt->bind_param($types, ...$params);
            // 相當於
            //$stmt->bind_param($types, $params[0], $params[1]);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $branch_list = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $branch = $row['branch'];
                $target = $row['target'];
                
                // 初始化分支
                if (!isset($branch_list[$branch])) {
                    $branch_list[$branch] = [
                        'branch' => $branch,
                        'latest_status' => $row['status'],
                        'targets' => []
                    ];
                }
                
                // 初始化目標
                if (!isset($branch_list[$branch]['targets'][$target])) {
                    $branch_list[$branch]['targets'][$target] = [
                        'target' => $target,
                        'latest_status' => $row['status'],
                        'builds' => []
                    ];
                }
                
                // 添加建構記錄
                $branch_list[$branch]['targets'][$target]['builds'][] = $row;
            }
        }
        
        return ['branch_list' => $branch_list];
    } */

    // 從 CSV 載入 branch mapping（帶靜態快取，只讀一次）
    private static $branch_maps_cache = null;

    private static function load_branch_maps() {
        if (self::$branch_maps_cache !== null) {
            return self::$branch_maps_cache;
        }

        $csv_path = __DIR__ . '/../Daily_build/Common_std_sign_image.csv';
        $maps = [];

        if (($handle = fopen($csv_path, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                // 跳過註解和標題列
                if (empty($row[0]) || $row[0][0] === '#' || $row[0] === 'branch') continue;
                if (count($row) < 6) continue;

                $branch    = trim($row[0]);
                $target_id = trim($row[1]);

                if (!isset($maps[$branch])) {
                    $maps[$branch] = [];
                }

                $maps[$branch][$target_id] = [
                    'path' => trim($row[2]),
                    'type' => trim($row[3]),
                    'name' => trim($row[4]),
                    'GUID' => trim($row[5])
                ];
            }
            fclose($handle);
        } else {
            error_log("Failed to open branch maps CSV: " . $csv_path);
        }

        self::$branch_maps_cache = $maps;
        return self::$branch_maps_cache;
    }

    // 獲取所有分支名稱
    public static function get_branch_names() {
        return array_keys(self::load_branch_maps());
    }
    // 原有的掃描目錄函數
    public static function scan_build_directories($branch = '', $start_date = '', $end_date = '', $status = '') {
        $base_path = '/mnt/DB/';
        $branch_maps = self::load_branch_maps();

        $all_builds = [];

        // 決定要處理的分支
        if (empty($branch) || $branch === 'all') {
            $branch_to_scan = $branch_maps;
        } else {
            $branch_to_scan = [$branch => $branch_maps[$branch] ?? []];
        }
    
        // 統一掃描邏輯
        foreach ($branch_to_scan as $branch_name => $targets) {
            foreach ($targets as $target_id => $target_info) {
                $scan_path = $base_path . $target_info['path'];
                $target_builds = self::scan_single_build_directory(
                    $scan_path, 
                    $start_date, 
                    $end_date, 
                    $status
                );
                
                if (!empty($target_builds)) {
                    $all_builds[] = [
                        'branch_name' => $branch_name,
                        'target_id' => $target_id,
                        'target_name' => $target_info['name'],
                        'target_type' => $target_info['type'],
                        'target_GUID' => $target_info['GUID'],
                        'builds' => $target_builds
                    ];
                }
            }
        }   
        return $all_builds;
    }
    
    private static function scan_single_build_directory($scan_path, $start_date, $end_date, $status) {
        $builds = [];
        
        if (!is_dir($scan_path)) {
            error_log("Directory not found: " . $scan_path);
            return $builds;
        }
        
        $dirs = scandir($scan_path);
        natsort($dirs); //自然排序
        
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            
            preg_match('/(\d{8})/', $dir, $matches);
            $build_date = $matches[1] ?? '';
            
            if (empty($build_date)) continue; 
            if (!empty($start_date) && $build_date < $start_date) continue;
            if (!empty($end_date) && $build_date > $end_date) continue;
    
            $full_path = $scan_path . $dir;
            if (is_dir($full_path)) {
                $bin_file_path = glob($full_path . '/*.bin')[0] ?? null;
                $log_file_path = glob($full_path . '/*git*.txt')[0] ?? null;
                $build_file_path = glob($full_path . '/*build*.txt')[0] ?? null;
                
                $build_status = $bin_file_path ? 'PASS' : 'FAIL';
                
                if (!empty($status) && strtoupper($status) !== $build_status) {
                    continue;
                }
    
                if ($bin_file_path || $log_file_path) {
                    $builds[] = [
                        'build_date' => $build_date,
                        'bin_file_path' => $bin_file_path,
                        'log_file_path' => $log_file_path,
                        'build_file_path' => $build_file_path,
                        'build_status' => $build_status
                    ];
                }
            }
        }
        //日期 近 -> 遠 排列
        usort($builds, function($a, $b) {
            return strcmp($b['build_date'], $a['build_date']);
        });
        
        return $builds;
    }
}


class boards_repository {
    public static function query_boards_info($user_level = 'low') {
        $conn = database_connection::get_connection();
        
        // 根據使用者等級決定 SQL 查詢條件
        if ($user_level == 'high') {
            // high level 可以看到所有板子（包含 keep=1）
            $sql = "SELECT * FROM boards ORDER BY mp_num";
        } else {
            // 一般使用者只能看到 keep=0 的板子
            $sql = "SELECT * FROM boards WHERE keep = 0 ORDER BY mp_num";
        }
        
        $result = $conn->query($sql);
        
        $ip_list = [];
        $mp510_groups = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $ip_list[] = $row;
                $mp510_groups[$row['mp_num']][] = $row;
            }
        }
        
        return ['ip_list' => $ip_list, 'mp510_groups' => $mp510_groups];
    }

    public static function query_boards_info_by_id($id) {
        $conn   = database_connection::get_connection();
        $sql    = "SELECT * FROM boards WHERE id=?";
        $stmt   = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $board = $result->fetch_assoc();
        } else {
            echo "無記錄";
        }
        $stmt->close();
        return $board;
    }

    public static function delete_boards_info($id) {
        $conn = database_connection::get_connection();
        $sql  = "DELETE FROM boards WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function modify_boards_info($B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $current_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note, $id) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("UPDATE boards SET B_Name=?, IP=?, bmc_nc_mac=?, L1_MAC=?, unique_pw=?, current_pw=?, Locate=?, pw_ip=?, pw_num=?, pw_port=?, mp_ip=?, mp_num=?, mp_com=?, note=? WHERE id=?");
        $stmt->bind_param("ssssssssiisiisi", $B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $current_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function insert_boards_info($B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $current_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note){
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("INSERT INTO boards (B_Name, IP, bmc_nc_mac, L1_MAC, unique_pw, current_pw, Locate, pw_ip, pw_num, pw_port, mp_ip, mp_num, mp_com, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssisisi", $B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $current_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note);
        $result = $stmt->execute();
        if ($result) {
            $new_id = $conn->insert_id;  // Get 新ID原生語法
            $stmt->close();
            return $new_id;  // 返回新 ID
        } else {
            $stmt->close();
            return false;    // 插入失敗返回 false
        }
    }


    public static function query_boards_alive() {
        $conn = database_connection::get_connection();
        $sql = "SELECT 
                    COUNT(*) as total_count,
                    SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as alive_count 
                FROM boards";
        $result = $conn->query($sql);
        
        if ($result) {
            return $result->fetch_assoc();
        } else {
            return ["error" => $conn->error];
        }
    }

    public static function update_boards_status($status, $ip) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("UPDATE boards SET status = ? WHERE IP = ?");
        $stmt->bind_param("ss", $status, $ip);
        $stmt->execute();
        $stmt->close();
    }

    public static function update_current_pw($current_pw, $ip) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("UPDATE boards SET current_pw = ? WHERE IP = ?");
        $stmt->bind_param("ss", $current_pw, $ip);
        $stmt->execute();
        $stmt->close();
    }

    public static function update_board_id_version($board_id, $version, $ip) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("UPDATE boards SET B_id = ?, version = ? WHERE IP = ?");
        $stmt->bind_param("sss", $board_id, $version, $ip);
        $stmt->execute();
        $stmt->close();
    }

    public static function clear_board_id_version($ip) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("UPDATE boards SET B_id = NULL, version = NULL WHERE IP = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $stmt->close();
    }

    public static function query_boards_status($ip) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("SELECT status FROM boards WHERE IP = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['status'];
    }

    public static function query_boards_name($ip) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("SELECT B_Name FROM boards WHERE IP = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['B_Name'];
    }


    public static function get_GUID_by_B_id($B_id) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("SELECT GUID FROM boards_tmp WHERE B_id = ?");
        $stmt->bind_param("s", $B_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['GUID'];
        } else {
            return null;
        }
    }

    


}

// ==================== boards_tmp 管理 ====================
class boards_tmp_repository {
    public static function query_boards_tmp_info() {
        $conn   = database_connection::get_connection();
        $sql    = "SELECT * FROM boards_tmp ORDER BY b_name";
        $stmt   = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $boards = [];
        while ($row = $result->fetch_assoc()) {
            $boards[] = $row;
        }
        $stmt->close();
        return $boards;
    }

    public static function query_boards_tmp_by_id($b_id) {
        $conn   = database_connection::get_connection();
        $sql    = "SELECT * FROM boards_tmp WHERE b_id = ?";
        $stmt   = $conn->prepare($sql);
        $stmt->bind_param("s", $b_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $board  = $result->fetch_assoc();
        $stmt->close();
        return $board;
    }

    public static function insert_boards_tmp_info($b_id, $b_name, $guid, $pbid, $pbid_oem, $bmc_chip, $bmc_type, $rot_pfr, $redfish, $target, $fw_size, $owner, $gitlab_type, $gitlab_id, $notes) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("INSERT INTO boards_tmp (b_id, b_name, guid, pbid, pbid_oem, bmc_chip, bmc_type, rot_pfr, redfish, target, fw_size, owner, gitlab_type, gitlab_id, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiissssssssis", $b_id, $b_name, $guid, $pbid, $pbid_oem, $bmc_chip, $bmc_type, $rot_pfr, $redfish, $target, $fw_size, $owner, $gitlab_type, $gitlab_id, $notes);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function modify_boards_tmp_info($b_name, $guid, $pbid, $pbid_oem, $bmc_chip, $bmc_type, $rot_pfr, $redfish, $target, $fw_size, $owner, $gitlab_type, $gitlab_id, $notes, $b_id) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("UPDATE boards_tmp SET b_name=?, guid=?, pbid=?, pbid_oem=?, bmc_chip=?, bmc_type=?, rot_pfr=?, redfish=?, target=?, fw_size=?, owner=?, gitlab_type=?, gitlab_id=?, notes=? WHERE b_id=?");
        $stmt->bind_param("ssiissssssssiss", $b_name, $guid, $pbid, $pbid_oem, $bmc_chip, $bmc_type, $rot_pfr, $redfish, $target, $fw_size, $owner, $gitlab_type, $gitlab_id, $notes, $b_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function delete_boards_tmp_info($b_id) {
        $conn = database_connection::get_connection();
        $sql  = "DELETE FROM boards_tmp WHERE b_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $b_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

class firmware_repository {
    public static function fw_r_form_read_history($limit = 10) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("SELECT * FROM fw_r_form_history ORDER BY submit_time DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
        return $history;
    }

    public static function fw_r_form_record_history($u_acc, $branch, $platform, $ver, $option, $oemname, $status, $UUID) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("INSERT INTO fw_r_form_history (u_acc, branch, platform, ver, option, oemname, status, UUID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $u_acc, $branch, $platform, $ver, $option, $oemname, $status, $UUID);
        $result = $stmt->execute();
        $insert_id = $stmt->insert_id;
        $stmt->close();

        return [
            'result' => $result,
            'insert_id' => $insert_id
        ];
    }

    public static function get_schedule_builds($limit = 10) {
        $conn = database_connection::get_connection();
        $sql = "SELECT * FROM fw_r_form_history 
                WHERE status IN ('pending', 'in_progress') 
                ORDER BY 
                    CASE 
                        WHEN status = 'in_progress' THEN 0 -- in_progress 優先（排在前面）
                        WHEN status = 'pending' THEN 1 -- pending 次之（排在後面）
                    END, 
                    submit_time 
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $builds = [];
        while ($row = $result->fetch_assoc()) {
            $builds[] = $row;
        }
        return $builds;
    }

    public static function get_history_builds($limit) {
        $conn = database_connection::get_connection();
        $sql = "SELECT * FROM fw_r_form_history 
                WHERE status IN ('completed', 'failed') 
                ORDER BY id DESC
                LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $builds = [];
        while ($row = $result->fetch_assoc()) {
            $builds[] = $row;
        }

        return $builds;
    }
}
?>
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

    private static $branch_maps = [
        'master' => [
            'x13rot' => [
                'path' => 'dailybuild_master/',
                'type' => 'lbmc',
                'name' => 'sx13_rot2hw2_ast26_p',
                'GUID' => 'C301MS'
            ]
        ],
        'master_x12' => [
            'x12rot' => [
                'path' => 'dailybuild_master_x12/',
                'type' => 'lbmc',
                'name' => 'sx12_rot_ast26_p',
                'GUID' => '5201MS'
            ]
        ],
        'master_rel_1.05_20250818' => [
            'x13rot' => [
                'path' => 'dailybuild_lbmc_x13rot/',
                'type' => 'lbmc',
                'name' => 'sx13_rot2hw2_ast26_p',
                'GUID' => 'C301MS'
            ],
            'h13rot' => [
                'path' => 'dailybuild_lbmc_h13/',
                'type' => 'lbmc',
                'name' => 'sh13_rot2hw2_ast26_std_p',
                'GUID' => '6501MS'
            ]
        ],
        'master_x12_rel_1.07_20250818' => [
            'x13nonrot' => [
                'path' => 'dailybuild_lbmc_x13nonrot/',
                'type' => 'lbmc',
                'name' => 'sx13_ast26_ws_p',
                'GUID' => 'F201MS'
            ],
            'x12rot' => [
                'path' => 'dailybuild_lbmc_x12rot/',
                'type' => 'lbmc',
                'name' => 'sx12_rot_ast26_p',
                'GUID' => '5201MS'
            ]

        ],        
        'master_hw1_rel_1.05_20250818' => [
            'master_hw1' => [
                'path' => 'dailybuild_master_hw1/',
                'type' => 'lbmc',
                'name' => 'x13_ast26_pfr',
                'GUID' => '3401MS'
            ]
        ],        
        'aspeed-master' => [
            'x14rot' => [
                'path' => 'dailybuild_obmc/',
                'type' => 'obmc',
                'name' => 'x14-ast2600-rot',
                'GUID' => '5601MS'
            ]
        ],
        'master_rel_1.02_20250609' => [
            'x14rot' => [
                'path' => 'dailybuild_obmc_rel/',
                'type' => 'obmc',
                'name' => 'x14-ast2600-rot',
                'GUID' => '5601MS'
            ]
        ],
        'BR_BMC_X14AST2600-ROT-B601MS_01.00.16.00_OEM_CVE_FOR_OBON' => [
            'OBON' => [
                'path' => 'dailybuild_obmc_OBON/',
                'type' => 'obmc',
                'name' => 'x14-ast2600-deltanext',
                'GUID' => 'B601MS'
            ]
        ],
        'BR_BMC_X14H14_AST2600_20241128_redfish_1_11' => [
            'RF1.11' => [
                'path' => 'dailybuild_obmc_RF1.11/',
                'type' => 'obmc',
                'name' => 'x14-ast2600-rot',
                'GUID' => '5601MS'
            ]
        ],
    ];

    // 獲取所有分支名稱
    public static function get_branch_names() {
        return array_keys(self::$branch_maps); // array_keys() 會返回： ['master', 'aspeed-master']
    }
    // 原有的掃描目錄函數
    public static function scan_build_directories($branch = '', $start_date = '', $end_date = '', $status = '') {
        $base_path = '/mnt/DB/';
        
        $all_builds = [];
        
        // 決定要處理的分支
        if (empty($branch) || $branch === 'all') {
            $branch_to_scan = self::$branch_maps;  // 使用靜態屬性
        } else {
            $branch_to_scan = [$branch => self::$branch_maps[$branch]];
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
    public static function query_boards_info() {
        $conn    = database_connection::get_connection();
        $sql     = "SELECT * FROM boards ORDER BY mp_num";
        $result  = $conn->query($sql);
        
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

    public static function modify_boards_info($B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note, $id) {
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("UPDATE boards SET B_Name=?, IP=?, bmc_nc_mac=?, L1_MAC=?, unique_pw=?, Locate=?, pw_ip=?, pw_num=?, pw_port=?, mp_ip=?, mp_num=?, mp_com=?, note=? WHERE id=?");
        $stmt->bind_param("sssssssiisiisi", $B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function insert_boards_info($B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note){
        $conn = database_connection::get_connection();
        $stmt = $conn->prepare("INSERT INTO boards (B_Name, IP, bmc_nc_mac, L1_MAC, unique_pw, Locate, pw_ip, pw_num, pw_port, mp_ip, mp_num, mp_com, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssisisi", $B_Name, $IP, $BMC_MAC, $L1_MAC, $Unique_pw, $Locate, $pw_ip, $pw_num, $pw_port, $mp_ip, $mp_num, $mp_com, $note);
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

// 使用示例
// $user = user_repository::check_user_in_db('username');
// $boards_info = board_repository::query_boards_info();
// $firmware_history = firmware_repository::fw_r_form_read_history(5);
?>
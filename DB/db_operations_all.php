<?php
class database_connection {
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
                        WHEN status = 'in_progress' THEN 0 
                        WHEN status = 'pending' THEN 1 
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
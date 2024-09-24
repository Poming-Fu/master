<?php
function connect_to_db() {
    $db_server = "10.148.175.12";#main server
    $db_user = "one";
    $db_password = "1234";
    $database = "ipmi";

    // 建立数据库连接
    $conn = new mysqli($db_server, $db_user, $db_password, $database);

    // 检查连接
    if ($conn->connect_error) {
        die("連接失敗: " . $conn->connect_error);
    }

    return $conn;
}

function check_user_in_db($conn, $username) {
    // 檢查用戶是否存在db
    $sql = "SELECT * FROM users WHERE u_acc = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
    return $user;
}

function update_user_last_login($conn, $username) {
    // 更新用户最後登入時間
    $sql = "UPDATE users SET last_login = NOW() WHERE u_acc = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
}

function add_new_user($u_acc, $u_lev) {
	$conn = connect_to_db();
    // 插入新用戶的 SQL 語句
    $sql = "INSERT INTO users (u_acc, u_lev) VALUES (?, ?)";
    // 預處理 SQL 語句
    $stmt = $conn->prepare($sql);
    // 綁定參數
    $stmt->bind_param("ss", $u_acc, $u_lev);
    // 執行 SQL 語句
    $result = $stmt->execute();
    // 關閉語句和連接
    $stmt->close();
    $conn->close();
    return $result;
}

function update_new_user($conn, $u_acc, $u_lev, $id) {
    // 準備 SQL 語句
    $sql = "UPDATE users SET u_acc=?, u_lev=? WHERE id=?";
    // 預處理 SQL 語句
    $stmt = $conn->prepare($sql);
    // 綁定參數
    $stmt->bind_param("ssi", $u_acc, $u_lev, $id);
    // 執行語句
    $result = $stmt->execute();
    $stmt->close();
	return $result;
}

function delete_new_user($conn, $id) {
    $sql = "DELETE FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
	$stmt->close();
	return $result;
}

function log_user_actions($u_acc, $action, $element_id, $element_type, $page_url) {
    $conn = connect_to_db();
    $stmt = $conn->prepare("INSERT INTO users_action (u_acc, action, element_id, element_type, page_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $u_acc, $action, $element_id, $element_type, $page_url);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

//從boards 取得所有板子組合
function query_boards_info() {
    $conn = connect_to_db();
    $sql = "SELECT * FROM boards ORDER BY mp_num";
    $result = $conn->query($sql);
    
    $ip_list = [];
    $mp510_groups = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $ip_list[] = $row;
            $mp510_groups[$row['mp_num']][] = $row;
        }
    }
    
    $conn->close();
    
    return ['ip_list' => $ip_list, 'mp510_groups' => $mp510_groups];
}

//從boards 取得status
function query_boards_alive() {
    $conn = connect_to_db();
    
    //https://blog.csdn.net/qq_26106607/article/details/82906384
    //用as 的值當成變數，滿足就返回一次1，不符合就是0，用sum來統計
    $sql = "
        SELECT 
            COUNT(*) as total_count,
            SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as alive_count 
        FROM boards";
    $result = $conn->query($sql);
    
    $row_data = [];
    if ($result) {
        $row_data = $result->fetch_assoc();
    } else {
        echo json_encode(["error" => $conn->error]);
        exit;
    }
    
    $conn->close();
    return $row_data;
}

//從boards_tmp 拿 GUID
/* get_result() 是結果集合，fetch_assoc() 是單一個
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    echo $row['id'] . ": " . $row['name'] . "<br>";
}
*/
function get_GUID_by_B_id($conn, $B_id) {
    
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

function fw_r_form_read_history($limit = 10) {
    
    $conn = connect_to_db();
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

function fw_r_form_record_history($u_acc, $branch, $platform, $ver, $option, $oemname, $status = 'pending') {
    $conn = connect_to_db();

    $stmt = $conn->prepare("INSERT INTO fw_r_form_history (u_acc, branch, platform, ver, option, oemname, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $u_acc, $branch, $platform, $ver, $option, $oemname, $status);
    $result = $stmt->execute();

    //返回插入的id 可能有用
    $insert_id = $stmt->insert_id;
    $stmt->close();

    return [
        'result' => $result,
        'insert_id' => $insert_id
    ];
}




function get_schedule_builds($limit = 10) {
    $conn = connect_to_db();
    $sql = "SELECT * FROM fw_r_form_history 
    WHERE status IN ('pending', 'in_progress') 
    ORDER BY 
        CASE 
            WHEN status = 'in_progress' THEN 0 
            WHEN status = 'pending' THEN 1 
        END, 
        submit_time DESC 
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


function get_history_builds($limit = 10) {
    $conn = connect_to_db();
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


function update_build_status($status, $id) {
    $conn = connect_to_db();
    $stmt = $conn->prepare("UPDATE fw_r_form_history SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}


?>

<?php
session_start();
require_once __DIR__ . '/../DB/db_operations.php';
// 檢查是否為 POST 請求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 從表單獲取用戶名和密碼，用戶名要轉小寫，因LDAP沒有嚴格規定大小寫。
    $username = strtolower($_POST['username']);
    $password = $_POST['password'];

    // LDAP 伺服器設定
    $ldap_server = "ldap://10.148.165.16"; // LDAP 伺服器地址
    $ldap_dn = "ou=people,dc=ldap,dc=smcipmi,dc=com"; // 使用者 DN 的基礎路徑

    // 建立 LDAP 連接
    $ds = ldap_connect($ldap_server) or die("無法連接到 LDAP 伺服器。");
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);

    // 構造用戶的 DN
    $user_dn = "uid=$username," . $ldap_dn;

    // 嘗試使用用戶名和密碼進行 LDAP 綁定
    if (@ldap_bind($ds, $user_dn, $password)) {
	// LDAP 驗證成功，接下來檢查資料庫，function 都在db_operations.php
    $conn = connect_to_db();
	$user = check_user_in_db($conn, $username);
	if ($user) {
		update_user_last_login($conn, $username);
	}
    //綁定成功，設置屬性可用於其他頁面檢查是否已登入
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $username;
    //不建議把密碼存在session，測試方便
    $_SESSION['password'] = $password;
    header("Location: ../index.php"); // 重定向到首頁
    exit;
    } else {
        // 綁定失敗，顯示錯誤訊息
        $error = "用戶名或密碼不正確。";
    }

   
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>Login page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 500px;
            text-align: center;
        }
        h2 {
            margin-top: 0;
        }
        .image-container {
            margin-bottom: 20px;
        }
        .image-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            text-align: left;
        }
        input[type="text"], input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error-message {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="image-container">
            <img src="/web1/web_picture/SMC.png" alt="Login Image">
        </div>
        <h2>IPMI for testing</h2>
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <label for="username">用戶名:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">密碼:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" value="登入">
        </form>
    </div>
</body>
</html>



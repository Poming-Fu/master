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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-container {
            max-width: 500px;
            width: 90%;
            margin: auto;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .card-body {
            padding: 2.5rem;
        }

        .logo-container {
            margin-bottom: 2rem;
        }

        .logo-container img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .form-floating {
            margin-bottom: 1rem;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-login {
            padding: 0.8rem;
            font-size: 1.1rem;
            background-color: #0d6efd;
            border: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background-color: #0b5ed7;
            transform: translateY(-1px);
        }

        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-body">
                    <div class="logo-container text-center">
                        <img src="/web1/web_picture/SMC.png" alt="Login Image" class="img-fluid">
                    </div>
                    
                    <h2 class="text-center mb-4">IPMI for testing</h2>
                    
                    <?php if (!empty($error)): ?>
                        <div class="error-message">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Username" required>
                            <label for="username">
                                <i class="bi bi-person-fill me-2"></i>用戶名
                            </label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Password" required>
                            <label for="password">
                                <i class="bi bi-lock-fill me-2"></i>密碼
                            </label>
                        </div>

                        <button type="submit" class="btn btn-login btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>登入
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



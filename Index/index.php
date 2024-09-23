<?php

session_start(); // 會話

//  檢查用戶是否登入，$_SESSION['loggedin'] 須為 true
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // 未登入重定向到登入頁面
    header('Location: /web1/login_out/login.php');
    exit;
}


?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>Index</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

    </style>
</head>
<body>
<?php include '/var/www/html/web1/login_out/navbar.php'; ?>
<h1>Index intro</h1>
<h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>! ~</h2>
<p>This is the home page.</p>
</body>
</html>

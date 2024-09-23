<?php
session_start();
$_SESSION = array();
session_destroy(); // 釋放資源
header('Location: /web1/login_out/login.php'); //定向到首頁
exit;

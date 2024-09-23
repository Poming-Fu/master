<?php
function check_login() {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: /web1/login_out/login.php');
        exit;
    }
}







?>
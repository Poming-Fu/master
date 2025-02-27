<?php
class common {
    public static function check_login() {
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            header('Location: /web1/login_out/login.php');
            exit;
        }
    }
    public static function generate_uuid($num) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $UUID = '';
        for ($i = 0; $i < $num; $i++) {
        $UUID .= $characters[rand(0, strlen($characters) - 1)];// - 1 為索引 ex 英文有26位，但位元從0開始，所以要0~25 => 0~(26-1)
        }
        return $UUID;
        }
    
    public static function alert($msg) {
        echo "<script type='text/javascript'>alert('$msg');</script>";
    }


}



?>
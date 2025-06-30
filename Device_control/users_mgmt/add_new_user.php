<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>IPMI web service - add user</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 10px;
            color: #333;
        }
        input[type="text"], select {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        p {
            text-align: center;
            color: #333;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>新增用戶</h2>

    <?php
    session_start();
    //require_once '../../DB/db_operations.php';
    require_once '../../DB/db_operations_all.php';
    $conn       = database_connection::get_connection();
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // 獲取表單數據
        $u_acc = $_POST['u_acc'];
        $u_lev = $_POST['u_lev'];

        // 調用函數添加新用戶
        if (users_repository::add_new_user($u_acc, $u_lev)) {
            echo "<p>新增用戶成功！</p>";
        } else {
            echo "<p>新增用戶失敗。</p>";
        }
    }
    ?>

    <form action="" method="post">
        <label for="u_acc">帳號:</label>
        <input type="text" id="u_acc" name="u_acc" required>
        
        <label for="u_lev">用戶等級:</label>
        <select id="u_lev" name="u_lev">
            <option value="low">low</option>
            <option value="medium">medium</option>
            <option value="high">high</option>
        </select>
        
        <input type="submit" value="提交">
    </form>
</div>

</body>
</html>

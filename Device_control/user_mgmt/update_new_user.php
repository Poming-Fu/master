<?php
//這頁是整合 列出用戶 -> edit 後填入表單 -> submit -> 更改用戶資訊
session_start();
require_once '../../DB/db_operations.php';
$conn = connect_to_db();

// 檢查是否有更新或刪除請求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $u_acc = $_POST['u_acc'];
        $u_lev = $_POST['u_lev'];

        if (update_new_user($conn, $u_acc, $u_lev, $id)) {
            $message = "用戶訊息已更新。";
        } else {
            $message = "更新用戶信息時發生錯誤。";
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];

        if (delete_new_user($conn, $id)) {
            $message = "用戶已刪除。";
        } else {
            $message = "刪除用戶時發生錯誤。";
        }
    }
}

// 獲取用戶列表
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

$users = []; // 空的數組來存用戶信息
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row; // 直接將$row賦值給$users數組
    }
}

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>用戶管理</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .delete-button {
            background-color: #f44336;
        }
        .delete-button:hover {
            background-color: #e53935;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
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
        .message {
            text-align: center;
            color: red;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>用戶列表</h2>
    <?php if (isset($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
    <table>
        <tr>
            <th>ID</th>
            <th>帳號</th>
            <th>等級</th>
            <th>操作</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['u_acc']); ?></td>
                <td><?php echo htmlspecialchars($user['u_lev']); ?></td>
                <td>
                    <button onclick="update_user('<?php echo $user['id']; ?>', '<?php echo $user['u_acc']; ?>', '<?php echo $user['u_lev']; ?>')">編輯</button>
                    <form action="" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="delete" value="1">
                        <button type="submit" class="delete-button">刪除</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2 id="update_user_form">編輯用戶信息</h2>
    <form action="" method="post">
        <input type="hidden" id="id" name="id">
        <input type="hidden" name="update" value="1">
        <label for="u_acc">帳號:</label>
        <input type="text" id="u_acc" name="u_acc" required>
        <label for="u_lev">等級:</label>
        <select id="u_lev" name="u_lev">
            <option value="low">low</option>
            <option value="medium">medium</option>
            <option value="high">high</option>
        </select>
        <input type="submit" value="更新">
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function update_user(id, u_acc, u_lev) {
        //document.getElementById('id').value = id;
        //document.getElementById('u_acc').value = u_acc;
        //document.getElementById('u_lev').value = u_lev;
        $('#id').val(id);
        $('#u_acc').val(u_acc);
        $('#u_lev').val(u_lev);
        window.location = '#update_user_form';
    }
</script>

</body>
</html>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="users_mgmt.css" rel="stylesheet">
</head>
<body>
<?php
session_start();
require_once '../../DB/db_operations_all.php';
$conn = database_connection::get_connection();

$message = null;
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u_acc = $_POST['u_acc'];
    $u_lev = $_POST['u_lev'];

    if (users_repository::add_new_user($u_acc, $u_lev)) {
        $message = "User added successfully!";
        $success = true;
    } else {
        $message = "Failed to add user.";
        $success = false;
    }
}
?>

<div class="page-container">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-person-plus"></i>
            <h2>Add New User</h2>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                <i class="bi <?php echo $success ? 'bi-check-circle' : 'bi-exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="form-grid">
                    <div class="form-field">
                        <label for="u_acc">Account</label>
                        <input type="text" id="u_acc" name="u_acc" placeholder="Enter username" required>
                    </div>
                    <div class="form-field">
                        <label for="u_lev">Level</label>
                        <select id="u_lev" name="u_lev">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.close()">
                        <i class="bi bi-x-lg"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>

<?php
session_start();
require_once '../../DB/db_operations_all.php';
$conn  = database_connection::get_connection();
$users = users_repository::query_users_info();

$message = null;
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        $id    = $_POST['id'];
        $u_acc = $_POST['u_acc'];
        $u_lev = $_POST['u_lev'];

        if (users_repository::update_new_user($u_acc, $u_lev, $id)) {
            $message = "User updated successfully!";
            $success = true;
            // Refresh user list
            $users = users_repository::query_users_info();
        } else {
            $message = "Failed to update user.";
            $success = false;
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];

        if (users_repository::delete_new_user($id)) {
            $message = "User deleted successfully!";
            $success = true;
            // Refresh user list
            $users = users_repository::query_users_info();
        } else {
            $message = "Failed to delete user.";
            $success = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="users_mgmt.css" rel="stylesheet">
</head>
<body>

<div class="page-container">
    <!-- User List -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-people"></i>
            <h2>User List</h2>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                <i class="bi <?php echo $success ? 'bi-check-circle' : 'bi-exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Account</th>
                        <th>Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="user-id"><?php echo htmlspecialchars($user['id']); ?></td>
                        <td class="user-account"><?php echo htmlspecialchars($user['u_acc']); ?></td>
                        <td>
                            <span class="level-badge <?php echo htmlspecialchars($user['u_lev']); ?>">
                                <?php echo ucfirst(htmlspecialchars($user['u_lev'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button type="button" class="btn-icon edit"
                                        onclick="editUser('<?php echo $user['id']; ?>', '<?php echo htmlspecialchars($user['u_acc']); ?>', '<?php echo $user['u_lev']; ?>')"
                                        title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="delete" value="1">
                                    <button type="submit" class="btn-icon delete" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="card" id="edit-form">
        <div class="card-header">
            <i class="bi bi-pencil-square"></i>
            <h2>Edit User</h2>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <input type="hidden" id="edit_id" name="id">
                <input type="hidden" name="update" value="1">

                <div class="form-grid">
                    <div class="form-field">
                        <label for="edit_u_acc">Account</label>
                        <input type="text" id="edit_u_acc" name="u_acc" placeholder="Select a user to edit" required>
                    </div>
                    <div class="form-field">
                        <label for="edit_u_lev">Level</label>
                        <select id="edit_u_lev" name="u_lev">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.close()">
                        <i class="bi bi-x-lg"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(id, u_acc, u_lev) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_u_acc').value = u_acc;
    document.getElementById('edit_u_lev').value = u_lev;
    document.getElementById('edit-form').scrollIntoView({ behavior: 'smooth' });
}
</script>
</body>
</html>

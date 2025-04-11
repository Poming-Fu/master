<?php
require_once '../common/common.php';
require_once '../DB/db_operations_all.php';
$master_ip = mp510_repository::get_master_ip();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MP510 Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2>MP510 Node Management</h2>
        <div class="row mb-4">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        Current Master IP: <strong class="text-primary"><?php echo $master_ip ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>MP Number</th>
                    <th>IP Address</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $conn = database_connection::get_connection();
                $sql = "SELECT * FROM mp510 ORDER BY node_type ASC";
                $result = $conn->query($sql);
                
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['mp_num']}</td>";
                    echo "<td>{$row['mp_ip']}</td>";
                    echo "<td>" . ($row['node_type'] == 'master' ? 
                                "<strong class='text-primary'>Master
                                
                                </strong>" : 
                                $row['node_type']) . "</td>";
                    echo "<td>" . ($row['status'] == 'online' ? 
                                "<span class='text-success'>Online</span>" : 
                                "<span class='text-danger'>Offline</span>") . "</td>";
                    echo "<td>{$row['location']}</td>";
                    if($row['node_type'] == 'slave' && $row['status'] == 'online') {
                        echo "<td><button class='btn btn-warning btn-sm set-master' data-ip='{$row['mp_ip']}'>Set as Master</button></td>";
                    } else {
                        echo "<td>-</td>";
                    }
                    echo "</tr>";
                }
            ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
    $(document).ready(function() {
        $('.set-master').click(function() {
            if(confirm('Are you sure you want to change the master node?')) {
                let newMasterIP = $(this).data('ip');
                $.ajax({
                    url: 'mp510_ctrl_functions.php',
                    method: 'POST',
                    data: {
                        action: 'set_master',
                        new_master_ip: newMasterIP
                    },
                    success: function(response) {
                        try {
                            let result = JSON.parse(response);
                            if(result.success) {
                                location.reload();
                            } else {
                                alert('Failed to change master: ' + result.message);
                            }
                        } catch(e) {
                            alert('Error processing response');
                        }
                    },
                    error: function() {
                        alert('Failed to send request');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
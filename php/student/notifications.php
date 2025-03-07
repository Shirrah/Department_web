<?php

// Include the database connection class
include_once('php/db-conn.php');

// Instantiate the Database class to establish the connection
$db = new Database();

$notification_query = "SELECT * FROM notifications ORDER BY date_created DESC";
$notifications = $db->db->query($notification_query);
?>

<link rel="stylesheet" href=".//.//stylesheet/admin/notifications.css">
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Notifications</h5>
        </div>
        <div class="card-body">
            <ul class="list-group">
                <?php
                if ($notifications->num_rows > 0) {
                    while ($notification = $notifications->fetch_assoc()) {
                        // Convert the date to 12-hour format with AM/PM
                        $formatted_date = date('F j, Y, g:i A', strtotime($notification['date_created']));
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?php echo htmlspecialchars($notification['message']); ?></span>
                            <small class="text-muted">(<?php echo htmlspecialchars($formatted_date); ?>)</small>
                        </li>
                        <?php
                    }
                } else {
                    echo "<li class='list-group-item text-center text-muted'>No notifications</li>";
                }
                ?>
            </ul>
        </div>
    </div>
</div>

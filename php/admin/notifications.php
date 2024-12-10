<?php

// Include the database connection class
include_once('php/db-conn.php');

// Instantiate the Database class to establish the connection
$db = new Database();

$notification_query = "SELECT * FROM notifications ORDER BY date_created DESC";
$notifications = $db->db->query($notification_query);
?>

<link rel="stylesheet" href=".//.//stylesheet/admin/notifications.css">
<div class="notifications">
    <h2>Notifications</h2>
    <ul>
        <?php
        if ($notifications->num_rows > 0) {
            while ($notification = $notifications->fetch_assoc()) {
                // Convert the date to 12-hour format with AM/PM
                $formatted_date = date('F j, Y, g:i A', strtotime($notification['date_created']));
                
                echo "<li>" . htmlspecialchars($notification['message']) . " <small>(" . htmlspecialchars($formatted_date) . ")</small></li>";
            }
        } else {
            echo "<li>No notifications</li>";
        }
        ?>
    </ul>
</div>

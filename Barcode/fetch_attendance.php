<?php
// Include the Database class
require 'db-conn.php'; // Update the path accordingly

// Instantiate the Database class
$database = new Database();

// Check for connection errors
if ($database->error) {
    die($database->error);
}

if (isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // Use prepared statements to fetch attendance options for the selected event
    $attendance_query = "SELECT * FROM attendances WHERE id_event = ?";
    $stmt = $database->conn->prepare($attendance_query);
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<option value="">Select Attendance</option>';
        while ($attendance = $result->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($attendance['id_attendance']) . '">' . htmlspecialchars($attendance['type_attendance']) . '</option>';
        }
    } else {
        echo '<option value="">No attendance available for this event</option>';
    }
}
?>

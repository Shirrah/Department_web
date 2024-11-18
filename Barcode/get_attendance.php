<?php
// Include the Database class
require 'db-conn.php'; // Update with the correct path to your Database class

// Instantiate the Database class
$database = new Database();

// Check for connection errors
if ($database->error) {
    die($database->error);
}

// Check if the event ID is provided
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Use prepared statements to fetch attendance options based on the selected event
    $attendance_query = "SELECT * FROM attendances WHERE id_event = ?";
    $stmt = $database->conn->prepare($attendance_query);
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $attendance_options = [];
    while ($attendance = $result->fetch_assoc()) {
        $attendance_options[] = [
            'id_attendance' => htmlspecialchars($attendance['id_attendance']),
            'attendance_name' => htmlspecialchars($attendance['type_attendance'])
        ];
    }

    // Return attendance options as JSON
    echo json_encode($attendance_options);
}
?>

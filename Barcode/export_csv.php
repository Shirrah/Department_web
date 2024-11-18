<?php
// Include the Database class
require 'db-conn.php'; // Update this with the correct path to your Database class

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the event and attendance IDs from POST
    $event_id = $_POST['event'];
    $attendance_id = $_POST['attendance'];

    // Instantiate the Database class
    $database = new Database();

    // Check for connection errors
    if ($database->error) {
        die($database->error);
    }

    // Fetch the event name using the event_id
    $event_query = "SELECT name_event FROM events WHERE id_event = ?";
    $stmt = $database->conn->prepare($event_query);
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $event_result = $stmt->get_result();

    if ($event_result->num_rows > 0) {
        $event_row = $event_result->fetch_assoc();
        $event_name = $event_row['name_event'];
    } else {
        die('Event not found.');
    }

    // Fetch the attendance name using the attendance_id
    $attendance_query = "SELECT type_attendance FROM attendances WHERE id_attendance = ?";
    $stmt = $database->conn->prepare($attendance_query);
    $stmt->bind_param('i', $attendance_id);
    $stmt->execute();
    $attendance_result = $stmt->get_result();

    if ($attendance_result->num_rows > 0) {
        $attendance_row = $attendance_result->fetch_assoc();
        $attendance_name = $attendance_row['type_attendance'];
    } else {
        die('Attendance not found.');
    }

    // Query to get the scanned students
    $query = "SELECT s.id_student, s.lastname_student, s.firstname_student, s.year_student, sa.status_attendance 
              FROM student_attendance sa
              INNER JOIN student s ON sa.id_student = s.id_student
              WHERE sa.id_attendance = ?";
    $stmt = $database->conn->prepare($query);
    $stmt->bind_param('i', $attendance_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // File name based on event and attendance names
    $file_name = $event_name . ' - ' . $attendance_name . '.csv';

    // Headers for CSV file download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');

    // Open output stream to generate CSV content
    $output = fopen('php://output', 'w');

    // Write column headers to CSV
    fputcsv($output, ['Student ID', 'Last Name', 'First Name', 'Year', 'Status']);

    // Write data rows to CSV
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['id_student'], $row['lastname_student'], $row['firstname_student'], $row['year_student'], $row['status_attendance']]);
    }

    // Close output stream
    fclose($output);
    exit;
}
?>

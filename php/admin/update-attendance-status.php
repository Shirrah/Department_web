<?php
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Get the POST data
$student_id = $_POST['student_id'] ?? '';
$attendance_id = $_POST['attendance_id'] ?? '';
$status = $_POST['status'] ?? '';

// Validate the input
if (empty($student_id) || empty($attendance_id) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Validate status value
$allowed_statuses = ['Present', 'Cleared', 'Absent'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

try {
    // Update the status in the database
    $stmt = $db->prepare("UPDATE student_attendance SET status_attendance = ? WHERE id_student = ? AND id_attendance = ?");
    $stmt->bind_param("sss", $status, $student_id, $attendance_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 
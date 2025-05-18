<?php
session_start();
require_once "../../php/db-conn.php";

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->db;

    // Get the user ID from session
    $user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'] ?? null;
    if (!$user_id) {
        throw new Exception("User not authenticated.");
    }

    // Get the selected semester for this user
    $selected_semester = $_SESSION['selected_semester'][$user_id] ?? '';

    if (empty($selected_semester)) {
        throw new Exception("No semester selected.");
    }

    // Get the student ID from the request
    $student_id = $_GET['id'] ?? '';
    if (empty($student_id)) {
        throw new Exception("No student ID provided.");
    }

    // Delete the specific student record for the selected semester
    $deleteQuery = "DELETE FROM student WHERE id_student = ? AND semester_ID = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bind_param("ss", $student_id, $selected_semester);

    if ($deleteStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Student deleted successfully.']);
    } else {
        throw new Exception("Failed to delete student: " . $deleteStmt->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

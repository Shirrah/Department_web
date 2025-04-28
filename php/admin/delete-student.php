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

    // Check if there are students in this semester
    $checkQuery = "SELECT id_student FROM student WHERE semester_ID = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bind_param("s", $selected_semester);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if (!$result->num_rows) {
        throw new Exception("No students found for the selected semester.");
    }

    // Delete students belonging to the selected semester
    $deleteQuery = "DELETE FROM student WHERE semester_ID = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bind_param("s", $selected_semester);

    if ($deleteStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'All students from selected semester deleted successfully.']);
    } else {
        throw new Exception("Failed to delete students: " . $deleteStmt->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

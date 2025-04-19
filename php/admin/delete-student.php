<?php
session_start();
require_once "../../php/db-conn.php";

header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("Student ID is required");
    }

    $student_id = htmlspecialchars($_GET['id']);
    $db = Database::getInstance()->db;

    // First check if student exists
    $checkQuery = "SELECT 1 FROM student WHERE id_student = ? LIMIT 1";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bind_param("s", $student_id);
    $checkStmt->execute();
    
    if (!$checkStmt->get_result()->num_rows) {
        throw new Exception("Student not found");
    }

    // Delete the student
    $deleteQuery = "DELETE FROM student WHERE id_student = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bind_param("s", $student_id);

    if ($deleteStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
    } else {
        throw new Exception("Failed to delete student: " . $deleteStmt->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
<?php
session_start();
require_once "../../php/db-conn.php";

header('Content-Type: application/json');

$db = Database::getInstance()->db;

// Get POST data
$id_attendance = $_POST['id_attendance'] ?? '';
$id_student = $_POST['id_student'] ?? '';
$semester_ID = $_POST['semester_ID'] ?? '';
$action = $_POST['action'] ?? '';

if (empty($id_attendance) || empty($id_student) || empty($semester_ID) || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

try {
    if ($action === 'clear') {
        // Update to Cleared status with 0 penalty
        $query = "UPDATE student_attendance 
                 SET status_attendance = 'Cleared', Penalty_requirements = 0 
                 WHERE id_attendance = ? AND id_student = ? AND semester_ID = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("sss", $id_attendance, $id_student, $semester_ID);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } elseif ($action === 'revert') {
        // Get the original penalty requirements from attendances table
        $query = "SELECT Penalty_requirements FROM attendances WHERE id_attendance = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("s", $id_attendance);
        $stmt->execute();
        $result = $stmt->get_result();
        $originalPenalty = $result->fetch_assoc()['Penalty_requirements'] ?? 0;
        
        // Revert to Absent status with original penalty
        $query = "UPDATE student_attendance 
                 SET status_attendance = 'Absent', Penalty_requirements = ? 
                 WHERE id_attendance = ? AND id_student = ? AND semester_ID = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssss", $originalPenalty, $id_attendance, $id_student, $semester_ID);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'penalty_requirements' => $originalPenalty]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
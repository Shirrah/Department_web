<?php
require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['attendance_id'], $_POST['action'])) {
    $studentId = $_POST['student_id'];
    $attendanceId = $_POST['attendance_id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'clear') {
            // Mark as cleared using both IDs
            $stmt = $db->prepare("UPDATE student_attendance 
                                 SET penalty_requirements = '0', 
                                     status_attendance = 'Cleared' 
                                 WHERE id_student = ? AND id_attendance = ?");
            $stmt->bind_param("ii", $studentId, $attendanceId);
            $stmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Penalty successfully cleared!'
            ]);
        } elseif ($action === 'not_cleared') {
            // Get original penalty requirements from attendances table
            $getStmt = $db->prepare("SELECT penalty_requirements FROM attendances WHERE id_attendance = ?");
            $getStmt->bind_param("i", $attendanceId);
            $getStmt->execute();
            $result = $getStmt->get_result();
            $attendanceData = $result->fetch_assoc();
            
            if ($attendanceData) {
                $originalRequirements = $attendanceData['penalty_requirements'];
                
                // Revert to original using both IDs
                $updateStmt = $db->prepare("UPDATE student_attendance 
                                          SET penalty_requirements = ?, 
                                              status_attendance = 'Absent' 
                                          WHERE id_student = ? AND id_attendance = ?");
                $updateStmt->bind_param("sii", $originalRequirements, $studentId, $attendanceId);
                $updateStmt->execute();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Penalty successfully reverted!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Could not find original attendance record.'
                ]);
            }
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
}
?>
<?php
require_once './../php/db-conn.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$scans = $input['scans'] ?? [];
$attendanceId = $input['attendance_id'] ?? 0;
$eventId = $input['event_id'] ?? 0;
$semesterId = $input['semester_id'] ?? 0;

if (empty($scans) || !$attendanceId || !$eventId || !$semesterId) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$database = Database::getInstance();
$db = $database->db;

try {
    $db->begin_transaction();
    
    foreach ($scans as $scan) {
        $barcodeId = $scan['code'];
        $timestamp = $scan['timestamp'];
        
        // 1. Find student ID from barcode
        $query = "SELECT id_student FROM students WHERE barcode_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param('s', $barcodeId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            continue; // Skip if student not found
        }
        
        $student = $result->fetch_assoc();
        $studentId = $student['id_student'];
        
        // 2. Update or insert student attendance record
        $query = "INSERT INTO student_attendance 
                  (id_attendance, id_student, semester_ID, date_attendance, status_attendance, Penalty_requirements) 
                  VALUES (?, ?, ?, ?, 'Present', 0)
                  ON DUPLICATE KEY UPDATE 
                  status_attendance = 'Present', 
                  Penalty_requirements = 0, 
                  date_attendance = VALUES(date_attendance)";
        
        $stmt = $db->prepare($query);
        $stmt->bind_param('iiis', $attendanceId, $studentId, $semesterId, $timestamp);
        $stmt->execute();
    }
    
    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Attendance records updated successfully']);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
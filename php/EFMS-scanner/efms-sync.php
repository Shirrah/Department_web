<?php
// efms-sync.php
require_once '..//../php/db-conn.php';
$database = Database::getInstance();
$db = $database->db;

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_student'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id_student']);
    exit;
}

$id_student = $data['id_student'];

session_start();
if (!isset($_SESSION['current_id_attendance'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No current event']);
    exit;
}

$id_attendance = $_SESSION['current_id_attendance']; // Current attendance session ID

try {
    // Check if student exists for this attendance
    $stmt = $db->prepare("SELECT * FROM student_attendance WHERE id_attendance = ? AND id_student = ?");
    $stmt->execute([$id_attendance, $id_student]);
    $student = $stmt->fetch();

    if ($student) {
        // Update student attendance
        $update = $db->prepare("UPDATE student_attendance SET status_attendance = 'Present', Penalty_requirements = 0 WHERE id_attendance = ? AND id_student = ?");
        $update->execute([$id_attendance, $id_student]);
        
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found for this attendance']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

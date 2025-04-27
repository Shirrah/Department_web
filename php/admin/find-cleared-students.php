<?php
require_once "../../php/db-conn.php";
session_start();

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_data']['id_admin']) && !isset($_SESSION['user_data']['id_student'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get the user ID from the session (either admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

// Retrieve the selected semester from session
$current_semester = $_SESSION['selected_semester'][$user_id] ?? '';

// Handle the semester selection from POST request and store it in session for this user
if (isset($_POST['current_semester']) && !empty($_POST['current_semester'])) {
    // Store the selected semester for the user in session
    $_SESSION['selected_semester'][$user_id] = $_POST['current_semester'];
    $current_semester = $_POST['current_semester'];  // Update local variable
}

// Get target semester from POST
$target_semester = $_POST['target_semester'] ?? '';

// Validate semesters
if (empty($current_semester)) {
    http_response_code(400);
    echo json_encode(['error' => 'Current semester must be specified']);
    exit;
}

if (empty($target_semester)) {
    http_response_code(400);
    echo json_encode(['error' => 'Target semester must be specified']);
    exit;
}

try {
    $db = Database::getInstance()->db;

    // Find cleared students (either 'Cleared' OR 'Present' with 0 penalty)
    $query = "SELECT DISTINCT s.id_student, s.lastname_student, s.firstname_student 
              FROM student s
              INNER JOIN student_attendance sa ON 
                  s.id_student = sa.id_student AND 
                  s.semester_ID = sa.semester_ID AND
                  sa.status_attendance IN ('Cleared', 'Present') AND
                  sa.Penalty_requirements = 0
              INNER JOIN student_fees_record sfr ON
                  s.id_student = sfr.id_student AND
                  s.semester_ID = sfr.semester_ID AND
                  sfr.status_payment = 1 
              WHERE s.semester_ID = ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $current_semester);
    
    if (!$stmt->execute()) {
        throw new Exception("Database query failed");
    }

    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'current_semester' => $current_semester,
        'count' => count($students),
        'students' => $students
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

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

// Get target semester and student IDs from POST
$target_semester = $_POST['target_semester'] ?? '';
$student_ids = json_decode($_POST['student_ids'] ?? '[]', true);

// Validate input
if (empty($current_semester)) {
    http_response_code(400);
    echo json_encode(['error' => 'No semester selected in session']);
    exit;
}

if (empty($target_semester)) {
    http_response_code(400);
    echo json_encode(['error' => 'Target semester must be specified']);
    exit;
}

if (empty($student_ids)) {
    http_response_code(400);
    echo json_encode(['error' => 'No students selected for moving']);
    exit;
}

try {
    $db = Database::getInstance()->db;
    $db->begin_transaction();

    // 1. First verify all students meet the criteria in the current semester
    $verify_query = "SELECT s.id_student, s.lastname_student, s.firstname_student, s.semester_ID, s.pass_student, s.role_student, s.year_student
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
                     WHERE s.semester_ID = ? AND s.id_student IN (" . 
                         implode(',', array_fill(0, count($student_ids), '?')) . ")";

    $verify_stmt = $db->prepare($verify_query);
    $verify_params = array_merge([$current_semester], $student_ids);
    $verify_stmt->bind_param(str_repeat('s', count($verify_params)), ...$verify_params);
    $verify_stmt->execute();
    $valid_students = $verify_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $valid_student_ids = array_column($valid_students, 'id_student');

    // Check if all students are valid
    $invalid_students = array_diff($student_ids, $valid_student_ids);
    if (!empty($invalid_students)) {
        $db->rollback();
        http_response_code(400);
        echo json_encode([
            'error' => 'Some students no longer meet criteria',
            'invalid_students' => array_values($invalid_students)
        ]);
        exit;
    }

    // 2. Check which valid students are already in the target semester
    $check_existing_query = "SELECT id_student FROM student WHERE semester_ID = ? AND id_student IN (" . 
                            implode(',', array_fill(0, count($valid_student_ids), '?')) . ")";
    $check_existing_stmt = $db->prepare($check_existing_query);
    $check_existing_params = array_merge([$target_semester], $valid_student_ids);
    $check_existing_stmt->bind_param(str_repeat('s', count($check_existing_params)), ...$check_existing_params);
    $check_existing_stmt->execute();
    $existing_students = $check_existing_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $existing_student_ids = array_column($existing_students, 'id_student');

    // 3. Filter out students who are already in the target semester
    $students_to_insert = array_diff($valid_student_ids, $existing_student_ids);

    // 4. Insert only the students who are not already in the target semester
    $insert_query = "INSERT INTO student (id_student, pass_student, lastname_student, firstname_student, role_student, year_student, semester_ID)
                     SELECT id_student, pass_student, lastname_student, firstname_student, role_student, year_student, ? 
                     FROM student 
                     WHERE id_student = ? AND semester_ID = ?";
    $insert_stmt = $db->prepare($insert_query);
    
    $moved_count = 0;
    foreach ($students_to_insert as $student_id) {
        $insert_stmt->bind_param("sss", $target_semester, $student_id, $current_semester);
        $insert_stmt->execute();
        $moved_count += $insert_stmt->affected_rows;
    }

    // Commit transaction
    $db->commit();

    echo json_encode([
        'success' => true,
        'moved_count' => $moved_count,
        'current_semester' => $current_semester,
        'target_semester' => $target_semester,
        'message' => "Successfully created new records for $moved_count students in $target_semester"
    ]);

} catch (Exception $e) {
    $db->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

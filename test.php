<?php
require_once "././php/db-conn.php";
session_start();

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Retrieve the selected semester from session
$current_semester = $_SESSION['selected_semester'][$user_id] ?? '';

// Get target semester from POST
$target_semester = $_POST['target_semester'] ?? '';

// Validate semesters
if (empty($current_semester)) {
    echo 'No semester selected in session';
    exit;
}

if (empty($target_semester)) {
    echo 'Target semester must be specified';
    exit;
}

try {
    $db = Database::getInstance()->db;

    // Find students who meet criteria in the current semester
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

    // Display the students
    echo "<h3>Students in Current Semester</h3>";
    if (count($students) > 0) {
        echo "<ul>";
        foreach ($students as $student) {
            echo "<li>" . $student['firstname_student'] . " " . $student['lastname_student'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "No students found for the current semester.";
    }

} catch (Exception $e) {
    echo 'Database error: ' . $e->getMessage();
}
?>

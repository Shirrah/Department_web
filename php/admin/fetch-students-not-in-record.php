<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

// Validate and get attendance ID
if (!isset($_GET['id_attendance']) || !is_numeric($_GET['id_attendance'])) {
    die(json_encode(['error' => 'Invalid attendance ID']));
}
$id_attendance = (int)$_GET['id_attendance'];

// Get user and semester info
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'] ?? null;
if (!$user_id) die(json_encode(['error' => 'User not logged in']));

$selected_semester = $_SESSION['selected_semester'][$user_id] ?? null;
if (!$selected_semester) die(json_encode(['error' => 'No semester selected']));

// 1. First, get penalty requirements from attendances table
$penaltyQuery = "SELECT Penalty_requirements FROM attendances WHERE id_attendance = ?";
$penaltyStmt = $db->prepare($penaltyQuery);
$penaltyStmt->bind_param("i", $id_attendance);
$penaltyStmt->execute();
$penaltyResult = $penaltyStmt->get_result();

if ($penaltyResult->num_rows === 0) {
    die(json_encode(['error' => 'Attendance record not found']));
}

$penaltyData = $penaltyResult->fetch_assoc();
$penalty_requirements = $penaltyData['Penalty_requirements'];
$current_date = date('Y-m-d H:i:s'); // Current date and time
$status_attendance = 'Absent'; // Store status in a variable

// 2. Find all missing students
$missingStudents = [];
$findStmt = $db->prepare("
    SELECT s.id_student, s.lastname_student, s.firstname_student, s.year_student
    FROM student s
    LEFT JOIN student_attendance sa ON 
        s.id_student = sa.id_student AND 
        sa.id_attendance = ?
    WHERE 
        sa.id_attendance IS NULL AND 
        s.semester_ID = ?
");
$findStmt->bind_param("is", $id_attendance, $selected_semester);
$findStmt->execute();
$result = $findStmt->get_result();

while ($row = $result->fetch_assoc()) {
    $missingStudents[] = $row;
}

// 3. Insert all missing students with complete data
if (!empty($missingStudents)) {
    // Prepare the insert query with all required fields
    $insertQuery = "
        INSERT INTO student_attendance (
            id_attendance,
            id_student,
            semester_ID,
            date_attendance,
            status_attendance,
            Penalty_requirements
        ) VALUES (?, ?, ?, ?, ?, ?)
    ";
    
    $insertStmt = $db->prepare($insertQuery);
    $insertedCount = 0;
    $insertedStudents = [];
    
    foreach ($missingStudents as $student) {
        $insertStmt->bind_param(
            "iissss",
            $id_attendance,
            $student['id_student'],
            $selected_semester,
            $current_date,
            $status_attendance,
            $penalty_requirements
        );
        
        if ($insertStmt->execute()) {
            $insertedCount++;
            $insertedStudents[] = $student;
        }
    }
    
    if ($insertedCount > 0) {
        $year_levels = [1 => "1st Year", 2 => "2nd Year", 3 => "3rd Year", 4 => "4th Year"];
        $html = "<tr><td colspan='6' class='text-center text-success'>";
        $html .= "Successfully added $insertedCount students to attendance as Absent.";
        $html .= "</td></tr>";
        
        // Display the added students
        foreach ($insertedStudents as $student) {
            $html .= "<tr>";
            $html .= "<td>".htmlspecialchars($student['id_student'])."</td>";
            $html .= "<td>".htmlspecialchars($student['lastname_student'])."</td>";
            $html .= "<td>".htmlspecialchars($student['firstname_student'])."</td>";
            $html .= "<td>".($year_levels[$student['year_student']] ?? "Unknown")."</td>";
            $html .= "<td>".htmlspecialchars($current_date)."</td>";
            $html .= "<td><span class='badge bg-danger'>$status_attendance</span></td>";
            $html .= "</tr>";
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Successfully added $insertedCount students",
            'html' => $html
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Failed to add students to attendance: " . $insertStmt->error
        ]);
    }
} else {
    echo json_encode([
        'success' => true,
        'message' => "All students in this semester are already in the attendance record.",
        'html' => "<tr><td colspan='6' class='text-center'>All students in this semester are already in the attendance record.</td></tr>"
    ]);
}
?>
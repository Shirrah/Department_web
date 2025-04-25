<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
try {
    require_once "./php/db-conn.php";
    $db = Database::getInstance()->db;
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// Get attendance ID (with test fallback)
$id_attendance = $_GET['id_attendance'] ?? 174; // Default test ID
if (!is_numeric($id_attendance)) {
    die("Error: Invalid attendance ID format");
}
$id_attendance = (int)$id_attendance;

// Debug message if using test ID
if (!isset($_GET['id_attendance'])) {
    echo "<div class='alert alert-warning'>Using test attendance ID: $id_attendance</div>";
}

// Get user ID from session
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'] ?? null;
if (!$user_id) {
    die("Error: No user ID found in session");
}

// Get selected semester
$selected_semester = $_SESSION['selected_semester'][$user_id] ?? null;
if (!$selected_semester) {
    die("Error: No semester selected for this user");
}

// Prepare and execute query
$sql = "
    SELECT s.id_student, s.lastname_student, s.firstname_student, s.year_student
    FROM student s
    LEFT JOIN student_attendance sa ON s.id_student = sa.id_student AND sa.id_attendance = ?
    WHERE sa.id_attendance IS NULL AND s.semester_ID = ?
    ORDER BY s.lastname_student, s.firstname_student
";

$stmt = $db->prepare($sql);
if (!$stmt) {
    die("Error preparing query: " . $db->error);
}

if (!$stmt->bind_param("is", $id_attendance, $selected_semester)) {
    die("Error binding parameters: " . $stmt->error);
}

if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    die("Error getting result set: " . $stmt->error);
}

// Display results
$year_levels = [1 => "1st Year", 2 => "2nd Year", 3 => "3rd Year", 4 => "4th Year"];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_student = htmlspecialchars($row['id_student']);
        $lastname = htmlspecialchars($row['lastname_student']);
        $firstname = htmlspecialchars($row['firstname_student']);
        $year_student = $year_levels[$row['year_student']] ?? "Unknown";

        echo "
        <tr>
            <td>{$id_student}</td>
            <td>{$lastname}</td>
            <td>{$firstname}</td>
            <td>{$year_student}</td>
            <td colspan='2'>
                <button class='btn btn-sm btn-success add-attendance' 
                        data-student-id='{$id_student}' 
                        data-attendance-id='{$id_attendance}'>
                    Add to Attendance
                </button>
            </td>
        </tr>
        ";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>All students in this semester are already in the attendance record.</td></tr>";
}

$stmt->close();
?>
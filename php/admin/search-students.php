<?php
require_once "../db-conn.php";
$db = Database::getInstance()->db;

$search = isset($_GET['search']) ? '%' . htmlspecialchars($_GET['search']) . '%' : '';
$semester_ID = isset($_GET['semester_ID']) ? htmlspecialchars($_GET['semester_ID']) : '';
$show_all = isset($_GET['show_all']) && $_GET['show_all'] === 'true';
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Current page
$offset = ($page - 1) * $limit; // Calculate offset

if ($show_all) {
    // Fetch all records without limit
    $query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
              FROM student 
              WHERE semester_ID = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $semester_ID);
} else {
    // Fetch limited records with pagination
    $query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
              FROM student 
              WHERE semester_ID = ? AND 
                    (id_student LIKE ? OR lastname_student LIKE ? OR firstname_student LIKE ?)
              LIMIT ? OFFSET ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("ssssii", $semester_ID, $search, $search, $search, $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

header('Content-Type: application/json');
echo json_encode($students);
?>
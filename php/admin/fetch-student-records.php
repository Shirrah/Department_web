<?php
session_start();
require_once "../../php/db-conn.php";

try {
    $db = Database::getInstance()->db;
    
    // Get the selected semester from session (aligned with dashboard.php)
    $selected_semester = $_SESSION['selected_semester'][$_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student']] ?? null;

    // If no semester is stored, fallback to latest active semester (same as dashboard)
    if (!$selected_semester) {
        $query = "SELECT semester_ID FROM semester ORDER BY semester_ID DESC LIMIT 1";
        $stmt = $db->prepare($query);
        if (!$stmt->execute()) {
            throw new Exception("Failed to fetch latest semester");
        }
        
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $selected_semester = $row['semester_ID'];
        }
    }

    // Fetch students with optional search filter
    $search = $_GET['search'] ?? null;
    if ($search) {
        $query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
                  FROM student 
                  WHERE semester_ID = ? 
                  AND (id_student LIKE ? OR lastname_student LIKE ? OR firstname_student LIKE ?)";
        $searchTerm = "%$search%";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssss", $selected_semester, $searchTerm, $searchTerm, $searchTerm);
    } else {
        $query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
                  FROM student 
                  WHERE semester_ID = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("s", $selected_semester);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to fetch students");
    }
    
    $students = $stmt->get_result();
    $data = [];
    while ($row = $students->fetch_assoc()) {
        $data[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($data);
    
} catch (Exception $e) {
    error_log("Student Records Error: " . $e->getMessage());
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => $e->getMessage()]);
}
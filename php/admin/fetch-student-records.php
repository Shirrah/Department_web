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

    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $per_page;

    // Get total count for pagination
    $search = $_GET['search'] ?? null;
    if ($search) {
        $countQuery = "SELECT COUNT(*) as total FROM student 
                      WHERE semester_ID = ? 
                      AND (id_student LIKE ? OR lastname_student LIKE ? OR firstname_student LIKE ?)";
        $searchTerm = "%$search%";
        $countStmt = $db->prepare($countQuery);
        $countStmt->bind_param("ssss", $selected_semester, $searchTerm, $searchTerm, $searchTerm);
    } else {
        $countQuery = "SELECT COUNT(*) as total FROM student WHERE semester_ID = ?";
        $countStmt = $db->prepare($countQuery);
        $countStmt->bind_param("s", $selected_semester);
    }
    $countStmt->execute();
    $totalCount = $countStmt->get_result()->fetch_assoc()['total'];

    // Fetch students with optional search filter and pagination
    if ($search) {
        $query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
                  FROM student 
                  WHERE semester_ID = ? 
                  AND (id_student LIKE ? OR lastname_student LIKE ? OR firstname_student LIKE ?)
                  ORDER BY lastname_student, firstname_student
                  LIMIT ? OFFSET ?";
        $searchTerm = "%$search%";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssssii", $selected_semester, $searchTerm, $searchTerm, $searchTerm, $per_page, $offset);
    } else {
        $query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
                  FROM student 
                  WHERE semester_ID = ?
                  ORDER BY lastname_student, firstname_student
                  LIMIT ? OFFSET ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("sii", $selected_semester, $per_page, $offset);
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
    echo json_encode([
        'data' => $data,
        'pagination' => [
            'total' => $totalCount,
            'per_page' => $per_page,
            'current_page' => $page,
            'last_page' => ceil($totalCount / $per_page)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Student Records Error: " . $e->getMessage());
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => $e->getMessage()]);
}
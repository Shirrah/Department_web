<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if this is an edit operation
    if (isset($_POST['semester_ID']) && !empty($_POST['semester_ID'])) {
        // Edit existing term
        $semester_ID = htmlspecialchars($_POST['semester_ID']);
        $academic_year = htmlspecialchars($_POST['academic_year']);
        $semester_type = htmlspecialchars($_POST['semester_type']);

        $updateQuery = "UPDATE semester SET academic_year = ?, semester_type = ? WHERE semester_ID = ?";
        $stmt = $db->prepare($updateQuery);
        $stmt->bind_param("sss", $academic_year, $semester_type, $semester_ID);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Error updating term: " . $stmt->error;
        }
    } else {
        // Add new term
        $academic_year = htmlspecialchars($_POST['academic_year']);
        $semester_type = htmlspecialchars($_POST['semester_type']);

        // Generate semester ID in the format: AY2025-2026-1stsemester
        list($start_year, $end_year) = explode('-', $academic_year);
        $semester_type_formatted = strtolower(str_replace(' ', '', $semester_type));
        $semester_ID = "AY{$start_year}-{$end_year}-{$semester_type_formatted}";

        $insertQuery = "INSERT INTO semester (semester_ID, academic_year, semester_type, status) VALUES (?, ?, ?, 'inactive')";
        $stmt = $db->prepare($insertQuery);
        $stmt->bind_param("sss", $semester_ID, $academic_year, $semester_type);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Error adding term: " . $stmt->error;
        }
    }
} else {
    echo "Invalid request method";
}
?>

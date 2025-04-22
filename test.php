<?php
session_start();
require_once "php/db-conn.php";
$db = Database::getInstance()->db;

// Query filtered by semester_ID
$test_semester = "AY2025-2026-summer"; // Use a known existing semester ID
$stmt = $db->prepare("SELECT * FROM student_attendance WHERE semester_ID = ?");
$stmt->bind_param("s", $test_semester); // Changed "i" to "s" for string
$stmt->execute();
$result = $stmt->get_result();

// Output results
if ($result->num_rows > 0) {
    echo "<h3>Student Attendance for Semester ID: {$test_semester}</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>
            <th>ID Attendance</th>
            <th>ID Student</th>
            <th>Semester ID</th>
            <th>Date</th>
            <th>Status</th>
            <th>Penalty Requirements</th>
          </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id_attendance']}</td>
                <td>{$row['id_student']}</td>
                <td>{$row['semester_ID']}</td>  <!-- Check spelling: semester_ID vs semester_ID -->
                <td>{$row['date_attendance']}</td>
                <td>{$row['status_attendance']}</td>
                <td>{$row['Penalty_requirements']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No student attendance records found for semester ID: {$test_semester}.";
}
?>
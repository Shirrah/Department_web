<?php
// Include the database connection
require_once "../../php/db-conn.php";
$db = new Database();

// Get the id_attendance from the GET request
$id_attendance = isset($_GET['id_attendance']) ? $_GET['id_attendance'] : null;

if ($id_attendance) {
    // Query to fetch student attendance details based on the id_attendance, ordered by id_attendance DESC
    $stmt = $db->db->prepare("SELECT id_student, semester_ID, date_attendance, status_attendance FROM student_attendance WHERE id_attendance = ? ORDER BY id_attendance DESC");
    $stmt->bind_param("i", $id_attendance);  // Bind the id_attendance to the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if records are found
    if ($result->num_rows > 0) {
        // Output the student attendance records
        echo '<h3>Attendance Details for ID: ' . htmlspecialchars($id_attendance) . '</h3>';
        echo '<table class="attendance-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID Attendance</th>';
        echo '<th>Student ID</th>';
        echo '<th>Semester ID</th>';
        echo '<th>Date</th>';
        echo '<th>Status</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        while ($row = $result->fetch_assoc()) {
            // Fetch student details for each id_student
            $student_stmt = $db->db->prepare("SELECT lastname_student, firstname_student FROM student WHERE id_student = ?");
            $student_stmt->bind_param("i", $row['id_student']);
            $student_stmt->execute();
            $student_result = $student_stmt->get_result();
            
            // Check if student record is found
            if ($student_result->num_rows > 0) {
                $student = $student_result->fetch_assoc();
                $lastname = htmlspecialchars($student['lastname_student']);
                $firstname = htmlspecialchars($student['firstname_student']);
            } else {
                $lastname = 'Unknown';
                $firstname = 'Unknown';
            }

            // Output the attendance details
            echo '<tr>';
            echo '<td>' . htmlspecialchars($id_attendance) . '</td>';
            echo '<td>' . htmlspecialchars($row['id_student']) . '</td>';
            echo '<td>' . htmlspecialchars($row['semester_ID']) . '</td>';
            echo '<td>' . date("Y-m-d", strtotime($row['date_attendance'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['status_attendance']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo "<p>No student records found for this attendance ID.</p>";
    }
} else {
    echo "<p>Invalid or missing attendance ID.</p>";
}
?>

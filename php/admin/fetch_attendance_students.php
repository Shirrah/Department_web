<?php
// Start session and include database connection
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require "../../php/db-conn.php";
$db = new Database();

if (isset($_GET['id_attendance'])) {
    $id_attendance = $_GET['id_attendance'];

    // Query to fetch students from student_attendance and student tables
    $stmt = $db->db->prepare("
        SELECT sa.id_student, s.lastname_student, s.firstname_student, s.year_student, sa.date_attendance, sa.status_attendance, sa.fine_amount
        FROM student_attendance sa 
        INNER JOIN student s ON sa.id_student = s.id_student 
        WHERE sa.id_attendance = ?
    ");
    $stmt->bind_param("i", $id_attendance);
    $stmt->execute();
    $result = $stmt->get_result();

    // Start table output
    echo '<table class="attendance-table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Last Name</th>';
    echo '<th>First Name</th>';
    echo '<th>Year</th>';
    echo '<th>Date and Time</th>'; // Updated header
    echo '<th>Status</th>'; // New header for status
    echo '<th>Fine Amount</th>'; // New header for fine amount
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id_student']) . '</td>';
            echo '<td>' . htmlspecialchars($row['lastname_student']) . '</td>';
            echo '<td>' . htmlspecialchars($row['firstname_student']) . '</td>';
            echo '<td>' . htmlspecialchars($row['year_student']) . '</td>';
            // Format date and time to 12-hour format
            echo '<td>' . date("Y-m-d h:i A", strtotime($row['date_attendance'])) . '</td>';
            // Display status and fine amount
            echo '<td>' . htmlspecialchars($row['status_attendance']) . '</td>';
            echo '<td>' . htmlspecialchars($row['fine_amount']) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="7">No students found for this attendance.</td></tr>'; // Updated colspan
    }

    echo '</tbody>';
    echo '</table>';
}
?>

<style>
    .attendance-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    .attendance-table th, .attendance-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .attendance-table th {
        background-color: #f2f2f2;
        color: #333;
    }

    .attendance-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .attendance-table tr:hover {
        background-color: #f1f1f1;
    }

    .attendance-table td {
        vertical-align: middle;
    }
</style>

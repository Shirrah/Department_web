<?php
require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

if (isset($_GET['id_attendance'])) {
    $id_attendance = $_GET['id_attendance'];

    // Fetch attendance details
    $stmt = $db->prepare("
        SELECT s.id_student, s.lastname_student, s.firstname_student, s.year_student, 
               sa.date_attendance, sa.status_attendance 
        FROM student_attendance sa
        JOIN student s ON sa.id_student = s.id_student
        WHERE sa.id_attendance = ?
    ");
    $stmt->bind_param("i", $id_attendance);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id_student = htmlspecialchars($row['id_student']);
            $lastname = htmlspecialchars($row['lastname_student']);
            $firstname = htmlspecialchars($row['firstname_student']);
            $status = htmlspecialchars($row['status_attendance']);

            // Convert year_student to readable format
            $year_levels = [1 => "1st Year", 2 => "2nd Year", 3 => "3rd Year", 4 => "4th Year"];
            $year_student = $year_levels[$row['year_student']] ?? "Unknown";

            // Convert date_attendance to 12-hour format
            $date_attendance = date("F d, Y h:i A", strtotime($row['date_attendance']));

            // Badge for status
            $badgeClass = ($status == 'Present') ? 'bg-success' : 'bg-danger';

            echo "<tr>
                    <td>{$id_student}</td>
                    <td>{$lastname}</td>
                    <td>{$firstname}</td>
                    <td>{$year_student}</td>
                    <td>{$date_attendance}</td>
                    <td><span class='badge {$badgeClass}'>{$status}</span></td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='6' class='text-center'>No attendance records found.</td></tr>";
    }
}
?>
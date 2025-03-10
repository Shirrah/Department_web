<?php
// Include the database connection
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Initialize an array for student attendance records
$student_attendance_records = [];

// Check if a student is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === 'yes') {
    // Get the logged-in student's ID from the session
    $student_id = $_SESSION['user_data']['id_student'];

    // Check if event ID is provided in the URL
    $event_id = isset($_GET['id']) ? intval($_GET['id']) : null;

    // Fetch student attendance records joined with attendances for the specific event
    $attendance_sql = "
        SELECT 
            sa.date_attendance,
            sa.status_attendance,
            a.type_attendance,
            a.time_in,
            a.time_out
        FROM 
            student_attendance sa
        JOIN 
            attendances a ON sa.id_attendance = a.id_attendance
        WHERE 
            sa.id_student = ? AND a.id_event = ?
    ";

    $attendance_stmt = $db->prepare($attendance_sql);
    $attendance_stmt->bind_param("ii", $student_id, $event_id); // Bind both student ID and event ID
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();

    // Store attendances in an array
    while ($attendance = $attendance_result->fetch_assoc()) {
        // Convert date and time to user-friendly format
        $attendance['date_attendance'] = date("l, F j, Y g:i A", strtotime($attendance['date_attendance'])); // Full date and time
        $attendance['time_in'] = date("g:i A", strtotime($attendance['time_in'])); // 12-hour format with AM/PM
        $attendance['time_out'] = date("g:i A", strtotime($attendance['time_out'])); // 12-hour format with AM/PM
        $student_attendance_records[] = $attendance;
    }
}
?>

<button class="backbtn"><a href="?content=student-index&student=student-events-payments">Back</a></button>


<link rel="stylesheet" href=".//.//stylesheet/student/student-attendance-records.css">
<!-- Attendance Records Display -->
<div class="attendance-records">
    <h4>Records</h4>
    <?php if (!empty($student_attendance_records)): ?>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($student_attendance_records as $attendance): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($attendance['type_attendance']); ?></td>
                        <td><?php echo htmlspecialchars($attendance['time_in']); ?></td>
                        <td><?php echo htmlspecialchars($attendance['time_out']); ?></td>
                        <td><?php echo htmlspecialchars($attendance['date_attendance']); ?></td>
                        <td><?php echo htmlspecialchars($attendance['status_attendance']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No attendance records found for this student for the selected event.</p>
    <?php endif; ?>
</div>

<style>
    /* General Styles for Attendance Records Section */
    .attendance-records {
        margin: 20px;
        padding: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #f9f9f9;
    }

    .attendance-records h4 {
        font-size: 1.5em;
        margin-bottom: 15px;
        color: #333;
    }

    .attendance-records table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .attendance-records th, .attendance-records td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }

    .attendance-records th {
        background-color: #f94316;
        color: white;
    }

    .attendance-records tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .attendance-records tr:hover {
        background-color: #ddd;
    }

    .attendance-records p {
        color: #666;
        font-size: 1em;
    }
</style>

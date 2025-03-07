<?php
require_once "../../php/db-conn.php"; // Adjust the path as needed
$db = new Database();

$id_student = $_GET['id_student'] ?? '';

if (!$id_student) {
    echo "<p class='text-danger'>Invalid Student ID.</p>";
    exit();
}

// Fetch student details
$queryStudent = "SELECT firstname_student, lastname_student, year_student FROM student WHERE id_student = ?";
$stmtStudent = $db->db->prepare($queryStudent);
$stmtStudent->bind_param("s", $id_student);
$stmtStudent->execute();
$resultStudent = $stmtStudent->get_result();
$student = $resultStudent->fetch_assoc();

if (!$student) {
    echo "<p class='text-danger'>Student not found.</p>";
    exit();
}

// Fetch attendance records
$queryAttendance = "
    SELECT 
        e.name_event, 
        a.type_attendance, 
        sa.date_attendance, 
        sa.status_attendance, 
        sa.Penalty_requirements
    FROM student_attendance sa
    JOIN attendances a ON sa.id_attendance = a.id_attendance
    JOIN events e ON a.id_event = e.id_event
    WHERE sa.id_student = ?
    ORDER BY sa.date_attendance DESC";
$stmtAttendance = $db->db->prepare($queryAttendance);
$stmtAttendance->bind_param("s", $id_student);
$stmtAttendance->execute();
$resultAttendance = $stmtAttendance->get_result();
?>

<!-- Student Info -->
<h5 class="mb-3">
    Fullname: <strong><?= htmlspecialchars($student['firstname_student'] . " " . $student['lastname_student']) ?></strong><br>
    Year Level: <strong><?= htmlspecialchars($student['year_student']) ?></strong>
</h5>

<!-- Attendance Records -->
<div class="table-responsive">
    <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
            <tr>
                <th>Event Name</th>
                <th>Attendance Type</th>
                <th>Date</th>
                <th>Status</th>
                <th>Penalty Requirements</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultAttendance->num_rows > 0): ?>
                <?php while ($row = $resultAttendance->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name_event']) ?></td>
                        <td><?= htmlspecialchars($row['type_attendance']) ?></td>
                        <td><?= htmlspecialchars($row['date_attendance']) ?></td>
                        <td>
                            <?php if ($row['status_attendance'] === 'Present'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Present</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Absent</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['Penalty_requirements'] == 0): ?>
                                <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                            <?php else: ?>
                                <?= htmlspecialchars($row['Penalty_requirements']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No attendance records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

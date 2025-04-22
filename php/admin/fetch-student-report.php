<?php
session_start(); // Start the session

require_once "../../php/db-conn.php"; // Adjust the path as needed
$db = Database::getInstance()->db;
$id_student = $_GET['id_student'] ?? '';

if (!$id_student) {
    echo "<p class='text-danger'>Invalid Student ID.</p>";
    exit();
}

// Get the user ID from the session (either admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

// Handle the semester selection from GET request and store it in session for this user
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    // Store the selected semester for the user in session
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
}

// Use the selected semester from the session or default to the latest semester
if (isset($_SESSION['selected_semester'][$user_id]) && !empty($_SESSION['selected_semester'][$user_id])) {
    $selected_semester = $_SESSION['selected_semester'][$user_id];
} else {
    // Get the latest semester from the database
    $query = "SELECT semester_ID, academic_year, semester_type FROM semester ORDER BY semester_ID DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $selected_semester = $row['semester_ID'];
    } else {
        $selected_semester = null;
    }
}

// Fetch student details
$queryStudent = "SELECT firstname_student, lastname_student, year_student FROM student WHERE id_student = ?";
$stmtStudent = $db->prepare($queryStudent);
$stmtStudent->bind_param("s", $id_student);
$stmtStudent->execute();
$resultStudent = $stmtStudent->get_result();
$student = $resultStudent->fetch_assoc();

if (!$student) {
    echo "<p class='text-danger'>Student not found.</p>";
    exit();
}

// Fetch payment records for the selected semester
$queryPayment = "
    SELECT 
        p.payment_name, 
        p.payment_amount, 
        p.date_payment, 
        sfr.status_payment
    FROM student_fees_record sfr
    JOIN payments p ON sfr.id_payment = p.id_payment
    WHERE sfr.id_student = ? AND sfr.semester_ID = ?
    ORDER BY p.date_payment DESC";
$stmtPayment = $db->prepare($queryPayment);
$stmtPayment->bind_param("ss", $id_student, $selected_semester);
$stmtPayment->execute();
$resultPayment = $stmtPayment->get_result();

// Fetch attendance records for the student in the selected semester, including penalty type
$queryAttendance = "
    SELECT 
        e.name_event, 
        a.type_attendance, 
        sa.date_attendance, 
        sa.status_attendance, 
        sa.Penalty_requirements,
        a.penalty_type
    FROM student_attendance sa
    JOIN attendances a ON sa.id_attendance = a.id_attendance
    JOIN events e ON a.id_event = e.id_event
    WHERE sa.id_student = ? AND sa.semester_ID = ?
    ORDER BY sa.date_attendance DESC";
$stmtAttendance = $db->prepare($queryAttendance);
$stmtAttendance->bind_param("ss", $id_student, $selected_semester);
$stmtAttendance->execute();
$resultAttendance = $stmtAttendance->get_result();
?>

<!-- Student Info -->
<h5 class="mb-3">
    Fullname: <strong><?= htmlspecialchars($student['firstname_student'] . " " . $student['lastname_student']) ?></strong><br>
    Year Level: <strong><?= htmlspecialchars($student['year_student']) ?></strong>
</h5>

<!-- Payment Records -->
<div class="table-responsive">
    <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
            <tr>
                <th>Payment Name</th>
                <th>Amount</th>
                <th>Date Paid</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultPayment->num_rows > 0): ?>
                <?php while ($row = $resultPayment->fetch_assoc()): ?>
                    <?php
                    // Format the date into a simpler format (e.g., Y-m-d)
                    $formatted_date_payment = (new DateTime($row['date_payment']))->format('M d, Y');
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['payment_name']) ?></td>
                        <td><?= htmlspecialchars(number_format($row['payment_amount'], 2)) ?></td>
                        <td><?= htmlspecialchars($formatted_date_payment) ?></td>
                        <td>
                            <?php if ($row['status_payment'] == 1): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Paid</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Not Paid</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">No payment records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Attendance Records -->
<div class="table-responsive">
    <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
            <tr>
                <th>Event Name</th>
                <th>Attendance Type</th>
                <th>Date</th>
                <th>Penalty Type</th>
                <th>Penalty Requirements</th>
                <th>Status</th>
                
            </tr>
        </thead>
        <tbody>
            <?php if ($resultAttendance->num_rows > 0): ?>
                <?php while ($row = $resultAttendance->fetch_assoc()): ?>
                    <?php
                    // Format the attendance date into a simpler format (e.g., Y-m-d)
                    $formatted_date_attendance = (new DateTime($row['date_attendance']))->format('M d, Y');
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name_event']) ?></td>
                        <td><?= htmlspecialchars($row['type_attendance']) ?></td>
                        <td><?= htmlspecialchars($formatted_date_attendance) ?></td>
                        <td><?= htmlspecialchars($row['penalty_type']) ?></td>
                        <td>
                            <?php if ($row['Penalty_requirements'] == 0): ?>
                                <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                            <?php else: ?>
                                <?= htmlspecialchars($row['Penalty_requirements']) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['status_attendance'] === 'Present'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Present</span>
                            <?php elseif ($row['status_attendance'] === 'Cleared'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Cleared</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Absent</span>
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No attendance records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

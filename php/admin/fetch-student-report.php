<?php
session_start();
require_once "../../php/db-conn.php";

$db = Database::getInstance()->db;
$id_student = $_GET['id_student'] ?? '';

if (!$id_student) {
    echo "<p class='text-danger'>Invalid Student ID.</p>";
    exit();
}

// Get user ID (admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'] ?? null;

// Handle semester selection
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
}

// Determine selected semester
if (isset($_SESSION['selected_semester'][$user_id]) && !empty($_SESSION['selected_semester'][$user_id])) {
    $selected_semester = $_SESSION['selected_semester'][$user_id];
} else {
    $query = "SELECT semester_ID FROM semester ORDER BY semester_ID DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $selected_semester = ($row = $result->fetch_assoc()) ? $row['semester_ID'] : null;
}

// Fetch student info
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

// Fetch payment records
$queryPayment = "
    SELECT 
        p.id_payment, -- <-- Added this to fix your 'undefined array key' issue
        p.payment_name, 
        p.payment_amount, 
        sfr.date_payment, 
        sfr.status_payment
    FROM student_fees_record sfr
    JOIN payments p ON sfr.id_payment = p.id_payment
    WHERE sfr.id_student = ? AND sfr.semester_ID = ?
    ORDER BY sfr.date_payment DESC";
$stmtPayment = $db->prepare($queryPayment);
$stmtPayment->bind_param("ss", $id_student, $selected_semester);
$stmtPayment->execute();
$resultPayment = $stmtPayment->get_result();

// Fetch attendance records
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
<div class="table-responsive mb-4">
    <h6>Payment Records</h6>
    <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
            <tr>
                <th>Payment Name</th>
                <th>Amount</th>
                <th>Date Paid</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_amount = 0;
            $total_paid = 0;
            if ($resultPayment->num_rows > 0): 
                while ($row = $resultPayment->fetch_assoc()): 
                    $formatted_date_payment = $row['date_payment'] ? (new DateTime($row['date_payment']))->format('M d, Y') : 'N/A';
                    $paymentId = $row['id_payment'];
                    $total_amount += $row['payment_amount'];
                    if ($row['status_payment'] == 1) {
                        $total_paid += $row['payment_amount'];
                    }
            ?>
                    <tr id="payment-row-<?= $paymentId ?>">
                        <td><?= htmlspecialchars($row['payment_name']) ?></td>
                        <td><?= htmlspecialchars(number_format($row['payment_amount'], 2)) ?></td>
                        <td><?= htmlspecialchars($formatted_date_payment) ?></td>
                        <td id="payment-status-<?= $paymentId ?>">
                            <?php if ($row['status_payment'] == 1): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Paid</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Not Paid</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm <?= $row['status_payment'] == 1 ? 'btn-warning' : 'btn-success' ?> update-payment-btn"
                                    data-id="<?= $paymentId ?>"
                                    data-student="<?= $id_student ?>"
                                    data-semester="<?= $selected_semester ?>"
                                    data-status="<?= $row['status_payment'] == 1 ? '0' : '1' ?>">
                                <?= $row['status_payment'] == 1 ? 'Mark as Unpaid' : 'Mark as Paid' ?>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <tr class="table-light">
                    <td colspan="1" class="text-end"><strong>Total Amount:</strong></td>
                    <td colspan="4"><strong>₱ <?= number_format($total_amount, 2) ?></strong></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No payment records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Attendance Records -->
<div class="table-responsive">
    <h6>Attendance Records</h6>
    <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
            <tr>
                <th>Event Name</th>
                <th>Type</th>
                <th>Date</th>
                <th>Penalty Type</th>
                <th>Penalty Requirements</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultAttendance->num_rows > 0): ?>
                <?php while ($row = $resultAttendance->fetch_assoc()): ?>
                    <?php
                    $formatted_date_attendance = (new DateTime($row['date_attendance']))->format('M d, Y');
                    // Get the attendance ID for each record
                    $queryId = "SELECT id_attendance FROM attendances WHERE id_event = (SELECT id_event FROM events WHERE name_event = ?) AND type_attendance = ?";
                    $stmtId = $db->prepare($queryId);
                    $stmtId->bind_param("ss", $row['name_event'], $row['type_attendance']);
                    $stmtId->execute();
                    $resultId = $stmtId->get_result();
                    $attendanceId = $resultId->fetch_assoc()['id_attendance'];
                    ?>
                    <tr id="attendance-row-<?= $attendanceId ?>">
                        <td><?= htmlspecialchars($row['name_event']) ?></td>
                        <td><?= htmlspecialchars($row['type_attendance']) ?></td>
                        <td><?= htmlspecialchars($formatted_date_attendance) ?></td>
                        <td><?= htmlspecialchars($row['penalty_type']) ?></td>
                        <td id="penalty-requirements-<?= $attendanceId ?>">
                            <?php if ($row['Penalty_requirements'] == 0): ?>
                                <span class="text-success"><i class="bi bi-check-circle-fill"></i> Cleared</span>
                            <?php else: ?>
                                <?= htmlspecialchars($row['Penalty_requirements']) ?>
                            <?php endif; ?>
                        </td>
                        <td id="attendance-status-<?= $attendanceId ?>">
                            <?php if ($row['status_attendance'] === 'Present' || $row['status_attendance'] === 'Cleared'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($row['status_attendance']) ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Absent</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['status_attendance'] !== 'Cleared'): ?>
                                <button class="btn btn-sm btn-success clear-attendance-btn"
                                        data-id="<?= $attendanceId ?>"
                                        data-student="<?= $id_student ?>"
                                        data-semester="<?= $selected_semester ?>">
                                    Mark as Cleared
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-warning revert-attendance-btn"
                                        data-id="<?= $attendanceId ?>"
                                        data-student="<?= $id_student ?>"
                                        data-semester="<?= $selected_semester ?>">
                                    Revert to Absent
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No attendance records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

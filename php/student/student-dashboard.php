<?php
// Include the database connection
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != 'yes') {
    header("location: ../index.php?content=log-in");
    exit();
}

// Get user ID (admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

// Handle semester selection
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
}

// Determine the selected semester
if (!empty($_SESSION['selected_semester'][$user_id])) {
    $selected_semester = $_SESSION['selected_semester'][$user_id];
} else {
    $query = "SELECT semester_ID FROM semester ORDER BY semester_ID DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $selected_semester = ($result && $row = $result->fetch_assoc()) ? $row['semester_ID'] : null;
}

// Fetch semesters for dropdown
$semesters = $db->query("SELECT semester_ID, academic_year, semester_type FROM semester");

// Count total events
$stmt = $db->prepare("SELECT COUNT(*) AS events_count FROM events WHERE semester_ID = ?");
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$events_count = $stmt->get_result()->fetch_assoc()['events_count'] ?? 0;

// Count total fees
$stmt = $db->prepare("SELECT COUNT(*) AS fees_count FROM payments WHERE semester_ID = ?");
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$fees_count = $stmt->get_result()->fetch_assoc()['fees_count'] ?? 0;
?>

<div class="container mt-4">
    <div class="card shadow-sm p-4">
        <h4 class="mb-3"><strong>Report Summary</strong></h4>
        
        <!-- Semester Selection -->
        <form method="GET" action="index.php" id="semesterForm">
            <input type="hidden" name="content" value="student-index">
            <input type="hidden" name="admin" value="dashboard">
            <select class="form-select w-auto mb-3" name="semester" id="semester" onchange="this.form.submit()">
                <?php while ($row = $semesters->fetch_assoc()): ?>
                    <option value="<?php echo $row['semester_ID']; ?>" <?php echo ($row['semester_ID'] == $selected_semester) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['semester_type'] . ' - ' . $row['academic_year']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <!-- Dashboard Cards -->
        <div class="row">
            <div class="col-md-6">
                <div class="card border-primary shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Events</h5>
                        <p class="display-5 text-primary"> <?php echo htmlspecialchars($events_count); ?> </p>
                        <a href="?content=student-index&student=student-events" class="btn btn-primary btn-sm">View Events</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-success shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Fees</h5>
                        <p class="display-5 text-success"> <?php echo htmlspecialchars($fees_count); ?> </p>
                        <a href="?content=student-index&student=student-fees" class="btn btn-success btn-sm">View Fees</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Show Report Button -->
        <div class="mt-3">
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#reportModal">
                Show Report
            </button>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Attendance & Fee Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reportContent">

                <?php
                // Fetch student details
                $id_student = $_SESSION['user_data']['id_student'];
                $queryStudent = "SELECT firstname_student, lastname_student, year_student FROM student WHERE id_student = ?";
                $stmtStudent = $db->prepare($queryStudent);
                $stmtStudent->bind_param("s", $id_student);
                $stmtStudent->execute();
                $resultStudent = $stmtStudent->get_result();
                $student = $resultStudent->fetch_assoc();
                ?>

                <!-- Student Info -->
                <h5 class="mb-3">
                    Fullname: <strong><?php echo htmlspecialchars($student['firstname_student'] . " " . $student['lastname_student']); ?></strong>
                    <br>
                    Year Level: <strong><?php echo htmlspecialchars($student['year_student']); ?></strong>
                </h5>

                <!-- Event Attendance Table -->
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
                            <?php
                            // Fetch all attendance records for the logged-in student
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
                            $stmtAttendance = $db->prepare($queryAttendance);
                            $stmtAttendance->bind_param("s", $id_student);
                            $stmtAttendance->execute();
                            $resultAttendance = $stmtAttendance->get_result();

                            // Display records
                            while ($row = $resultAttendance->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name_event']); ?></td>
                                    <td><?php echo htmlspecialchars($row['type_attendance']); ?></td>
                                    <td><?php echo htmlspecialchars($row['date_attendance']); ?></td>
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
                                            <?php echo htmlspecialchars($row['Penalty_requirements']); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Fee Payment Table -->
                <h5 class="mt-4"><strong>Fee Payment Report</strong></h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Fee Name</th>
                                <th>Amount</th>
                                <th>Payment Status</th>
                                <th>Payment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch student fee payment records
                            $queryFees = "
                                SELECT 
                                    p.payment_name, 
                                    p.payment_amount, 
                                    sfr.status_payment, 
                                    sfr.date_payment
                                FROM student_fees_record sfr
                                JOIN payments p ON sfr.id_payment = p.id_payment
                                WHERE sfr.id_student = ? 
                                AND sfr.semester_ID = ? 
                                ORDER BY sfr.date_payment DESC";

                            $stmtFees = $db->prepare($queryFees);
                            $stmtFees->bind_param("ss", $id_student, $selected_semester);
                            $stmtFees->execute();
                            $resultFees = $stmtFees->get_result();

                            // Display fee records
                            while ($row = $resultFees->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['payment_name']); ?></td>
                                    <td>₱<?php echo number_format($row['payment_amount'], 2); ?></td>
                                    <td>
                                        <?php if ($row['status_payment'] == 1): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Not Paid</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo ($row['status_payment'] == 1) ? htmlspecialchars($row['date_payment']) : 'N/A'; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

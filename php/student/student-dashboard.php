<?php
// Include the database connection
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Check if the user is logged in
if (!isset($_SESSION['logged_in'])) {
    session_start();
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != 'yes') {
    header("location: ../index.php?content=log-in");
    exit();
}

// Get user ID (admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'] ?? null;
if (!$user_id) {
    header("location: ../index.php?content=log-in");
    exit();
}

// Fetch all semesters the student has been enrolled in
$student_semesters = [];
if (isset($_SESSION['user_data']['id_student'])) {
    $student_id = $_SESSION['user_data']['id_student'];
    
    // Get all semester IDs from student records for this student
    $queryStudentSemesters = "SELECT DISTINCT semester_ID FROM student WHERE id_student = ?";
    $stmtStudentSemesters = $db->prepare($queryStudentSemesters);
    $stmtStudentSemesters->bind_param("s", $student_id);
    $stmtStudentSemesters->execute();
    $resultStudentSemesters = $stmtStudentSemesters->get_result();
    
    while ($row = $resultStudentSemesters->fetch_assoc()) {
        $student_semesters[] = $row['semester_ID'];
    }
}

// Handle semester selection from URL
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    // Validate the semester belongs to the student
    if (in_array($_GET['semester'], $student_semesters)) {
        $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
        setcookie('selected_semester', $_GET['semester'], time() + (86400 * 30), "/"); // 30 days
    }
}

// Determine the selected semester
if (!empty($_SESSION['selected_semester'][$user_id])) {
    $selected_semester = $_SESSION['selected_semester'][$user_id];
} elseif (!empty($_COOKIE['selected_semester'])) {
    // Validate cookie value against student's semesters
    if (in_array($_COOKIE['selected_semester'], $student_semesters)) {
        $selected_semester = $_COOKIE['selected_semester'];
        $_SESSION['selected_semester'][$user_id] = $selected_semester;
    } else {
        $selected_semester = !empty($student_semesters) ? $student_semesters[0] : null;
    }
} else {
    // Default to most recent semester
    $selected_semester = !empty($student_semesters) ? $student_semesters[0] : null;
    if ($selected_semester) {
        $_SESSION['selected_semester'][$user_id] = $selected_semester;
        setcookie('selected_semester', $selected_semester, time() + (86400 * 30), "/");
    }
}

// Fetch all active semesters the student has been enrolled in
$semesters = [];
if (!empty($student_semesters)) {
    $placeholders = implode(',', array_fill(0, count($student_semesters), '?'));
    $query = "SELECT semester_ID, academic_year, semester_type FROM semester 
              WHERE semester_ID IN ($placeholders) AND status = 'Active' 
              ORDER BY semester_ID DESC";
    $stmt = $db->prepare($query);
    
    // Bind parameters dynamically
    $types = str_repeat('s', count($student_semesters));
    $stmt->bind_param($types, ...$student_semesters);
    $stmt->execute();
    $result = $stmt->get_result();
    $semesters = $result->fetch_all(MYSQLI_ASSOC);
}

// Count total events for the selected semester
$events_count = 0;
if ($selected_semester) {
    $stmt = $db->prepare("SELECT COUNT(*) AS events_count FROM events WHERE semester_ID = ?");
    $stmt->bind_param("s", $selected_semester);
    $stmt->execute();
    $result = $stmt->get_result();
    $events_count = $result->fetch_assoc()['events_count'] ?? 0;
}

// Count total fees for the selected semester
$fees_count = 0;
if ($selected_semester) {
    $stmt = $db->prepare("SELECT COUNT(*) AS fees_count FROM payments WHERE semester_ID = ?");
    $stmt->bind_param("s", $selected_semester);
    $stmt->execute();
    $result = $stmt->get_result();
    $fees_count = $result->fetch_assoc()['fees_count'] ?? 0;
}
?>

<div class="container mt-4">
    <div class="card shadow-sm p-4">
        <h4 class="mb-3"><strong>Report Summary</strong></h4>
        
        <!-- Semester Selection -->
        <form method="GET" action="index.php" id="semesterForm">
            <input type="hidden" name="content" value="student-index">
            <input type="hidden" name="admin" value="dashboard">
            <!-- Semester Selection Dropdown -->
            <select class="form-select w-auto mb-3" name="semester" id="semester" onchange="this.form.submit()" <?php echo empty($semesters) ? 'disabled' : ''; ?>>
                <?php if (!empty($semesters)): ?>
                    <?php foreach ($semesters as $semester): ?>
                        <option value="<?php echo htmlspecialchars($semester['semester_ID']); ?>" 
                            <?php echo ($semester['semester_ID'] == $selected_semester) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars('AY: ' . $semester['academic_year'] . ' - ' . $semester['semester_type']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="">No active semesters found</option>
                <?php endif; ?>
            </select>
        </form>

        <!-- Dashboard Cards -->
        <div class="row">
            <div class="col-md-6">
                <div class="card border-primary shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Events</h5>
                        <p class="display-5 text-primary"><?php echo htmlspecialchars($events_count); ?></p>
                        <a href="?content=student-index&student=student-events&semester=<?php echo urlencode($selected_semester ?? ''); ?>" class="btn btn-primary btn-sm">View Events</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-success shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Fees</h5>
                        <p class="display-5 text-success"><?php echo htmlspecialchars($fees_count); ?></p>
                        <a href="?content=student-index&student=student-fees&semester=<?php echo urlencode($selected_semester ?? ''); ?>" class="btn btn-success btn-sm">View Fees</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Show Report Button -->
        <div class="mt-3">
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#reportModal" <?php echo !$selected_semester ? 'disabled' : ''; ?>>
                Show Report
            </button>
        </div>
    </div>
</div>

<!-- Report Modal -->
<?php if ($selected_semester): ?>
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Attendance & Fee Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reportContent">
                <?php
                $id_student = $_SESSION['user_data']['id_student'] ?? '';
                if (!$id_student) {
                    echo "<p class='text-danger'>Invalid Student ID.</p>";
                    exit();
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
                ?>

                <!-- Student Info -->
                <h5 class="mb-3">
                    Fullname: <strong><?php echo htmlspecialchars($student['firstname_student'] . " " . $student['lastname_student']); ?></strong>
                    <br>
                    Year Level: <strong><?php echo htmlspecialchars($student['year_student']); ?></strong>
                </h5>

                <!-- Fee Payment Table -->
                <h5 class="mt-4"><strong>Fee Payment Report</strong></h5>
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
                            <?php
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

                            if ($resultPayment->num_rows > 0): 
                                while ($row = $resultPayment->fetch_assoc()): 
                                    $formatted_date_payment = (new DateTime($row['date_payment']))->format('M d, Y');
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['payment_name']); ?></td>
                                        <td>₱<?php echo number_format($row['payment_amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($formatted_date_payment); ?></td>
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

                <!-- Event Attendance Table -->
                <h5 class="mt-4"><strong>Attendance Report</strong></h5>
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
                            <?php
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

                            if ($resultAttendance->num_rows > 0): 
                                while ($row = $resultAttendance->fetch_assoc()): 
                                    $formatted_date_attendance = (new DateTime($row['date_attendance']))->format('M d, Y');
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name_event']); ?></td>
                                        <td><?php echo htmlspecialchars($row['type_attendance']); ?></td>
                                        <td><?php echo htmlspecialchars($formatted_date_attendance); ?></td>
                                        <td><?php echo htmlspecialchars($row['penalty_type']); ?></td>
                                        <td>
                                            <?php if ($row['Penalty_requirements'] == 0): ?>
                                                <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($row['Penalty_requirements']); ?>
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
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    // Check if semester cookie exists
    window.onload = function() {
        const cookie = document.cookie.split('; ').find(row => row.startsWith('selected_semester='));
        if (!cookie && document.querySelector('#semester').value) {
            // No cookie found, set the default semester if not selected
            const defaultSemester = document.querySelector('#semester').value;
            document.cookie = `selected_semester=${defaultSemester}; path=/;`;
        }
    }

    // On changing the semester selection, store it in a cookie
    document.querySelector('#semester')?.addEventListener('change', function() {
        const selectedSemester = this.value;
        document.cookie = `selected_semester=${selectedSemester}; path=/;`;
    });
</script>
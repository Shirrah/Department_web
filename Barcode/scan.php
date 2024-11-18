<?php
session_start();

// Include the Database class
require 'db-conn.php';

// Instantiate the Database class
$database = new Database();

// Check for connection errors
if ($database->error) {
    die($database->error);
}

// Initialize variables
$event_id = $attendance_id = null;
$event_name = $attendance_name = '';
$student = null;
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';

// Clear the session message
unset($_SESSION['message']);

// Generate a CSRF token if it doesn't already exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if event and attendance are set via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['event']) && isset($_POST['attendance'])) {
        $event_id = $_POST['event'];
        $attendance_id = $_POST['attendance'];
    } else {
        die('Event and attendance selection missing.');
    }
} elseif (isset($_GET['event']) && isset($_GET['attendance'])) {
    // Handle CSV export through GET
    $event_id = $_GET['event'];
    $attendance_id = $_GET['attendance'];
} else {
    die('Event and attendance not specified.');
}

// Fetch event and attendance names
$event_query = "SELECT name_event FROM events WHERE id_event = '$event_id'";
$event_result = $database->conn->query($event_query);
if ($event_result && $event_result->num_rows > 0) {
    $event_name = $event_result->fetch_assoc()['name_event'];
}

$attendance_query = "SELECT type_attendance FROM attendances WHERE id_attendance = '$attendance_id'";
$attendance_result = $database->conn->query($attendance_query);
if ($attendance_result && $attendance_result->num_rows > 0) {
    $attendance_name = $attendance_result->fetch_assoc()['type_attendance'];
}

// Handle scanning student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['csrf_token'])) {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "<div class='error'>Invalid CSRF token!</div>";
        header("Location: " . $_SERVER['PHP_SELF'] . "?event=$event_id&attendance=$attendance_id");
        exit();
    }

    $student_id = $_POST['student_id'];
    $student_query = "SELECT * FROM student WHERE id_student = '$student_id'";
    $result = $database->conn->query($student_query);
    
    if ($result && $result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        $attendance_check_query = "SELECT * FROM student_attendance WHERE id_student = '$student_id' AND id_attendance = '$attendance_id'";
        $attendance_check_result = $database->conn->query($attendance_check_query);

        if ($attendance_check_result->num_rows > 0) {
            $attendance_record = $attendance_check_result->fetch_assoc();
            if ($attendance_record['status_attendance'] === 'Present') {
                $_SESSION['message'] = "<div class='error'>Student is already present!</div>";
            } else {
                $update_query = "UPDATE student_attendance SET status_attendance = 'Present', fine_amount = 0, date_attendance = NOW() WHERE id_student = '$student_id' AND id_attendance = '$attendance_id'";
                $_SESSION['message'] = $database->conn->query($update_query) === TRUE ? "<div class='success'>Student Recorded!</div>" : "<div class='error'>Error updating attendance: " . $database->conn->error . "</div>";
            }
        } else {
            $insert_query = "INSERT INTO student_attendance (id_student, id_attendance, date_attendance, status_attendance, fine_amount) VALUES ('$student_id', '$attendance_id', NOW(), 'Present', 0)";
            $_SESSION['message'] = $database->conn->query($insert_query) === TRUE ? "<div class='success'>Student attendance recorded as Present!</div>" : "<div class='error'>Error recording attendance: " . $database->conn->error . "</div>";
        }
    } else {
        $_SESSION['message'] = "<div class='error'>Student ID not found!</div>";
    }

    // Invalidate the CSRF token after processing
    unset($_SESSION['csrf_token']);

    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?event=$event_id&attendance=$attendance_id");
    exit();
}

// Regenerate CSRF token after invalidation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Export scanned student data as CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $export_query = "SELECT student.id_student, student.firstname_student, student.lastname_student, student.year_student, student_attendance.date_attendance FROM student JOIN student_attendance ON student.id_student = student_attendance.id_student WHERE student_attendance.id_attendance = '$attendance_id'";
    $result = $database->conn->query($export_query);

    if ($result && $result->num_rows > 0) {
        $sanitized_event_name = preg_replace('/[^a-zA-Z0-9-_]/', '_', $event_name);
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"scanned_students_{$sanitized_event_name}.csv\"");
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Student ID', 'First Name', 'Last Name', 'Year', 'Date Attended']);
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [$row['id_student'], $row['firstname_student'], $row['lastname_student'], $row['year_student'], $row['date_attendance']]);
        }
        
        fclose($output);
        exit();
    } else {
        echo "<div class='error'>No records found to export.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/scans.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="left-column">
            <h1><i class="fas fa-calendar-alt"></i> Event: <?php echo htmlspecialchars($event_name); ?></h1>
            <h2><i class="fas fa-clipboard-check"></i> Attendance: <?php echo htmlspecialchars($attendance_name); ?></h2>

            <?php if ($message): echo $message; endif; ?>
            
            <form method="POST" action="scan.php" id="scanForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="event" value="<?php echo htmlspecialchars($event_id); ?>">
                <input type="hidden" name="attendance" value="<?php echo htmlspecialchars($attendance_id); ?>">
                <label for="student_id"><i class="fas fa-id-card"></i> Scan Student ID:</label>
                <input type="text" name="student_id" id="student_id" required>
            </form>

            <?php if ($student): ?>
                <div class="student-details">
                    <h3><i class="fas fa-user-graduate"></i> Student Details:</h3>
                    <div class="student-box">
                        <p><strong><i class="fas fa-id-badge"></i> Student ID:</strong> <?php echo htmlspecialchars($student['id_student']); ?></p>
                        <p><strong><i class="fas fa-user"></i> Name:</strong> <?php echo htmlspecialchars($student['firstname_student'] . " " . $student['lastname_student']); ?></p>
                        <p><strong><i class="fas fa-graduation-cap"></i> Year:</strong> <?php echo htmlspecialchars($student['year_student']); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="right-column" id="scannedRecords">
            <h3>Scanned Students</h3>
            <?php
            $scanned_students_query = "SELECT student.id_student, student.firstname_student, student.lastname_student, student.year_student, student_attendance.date_attendance FROM student JOIN student_attendance ON student.id_student = student_attendance.id_student WHERE student_attendance.id_attendance = '$attendance_id'";
            $scanned_students_result = $database->conn->query($scanned_students_query);

            if ($scanned_students_result && $scanned_students_result->num_rows > 0) {
                echo "<table><tr><th>Student ID</th><th>Name</th><th>Year</th><th>Date Attended</th></tr>";
                while ($row = $scanned_students_result->fetch_assoc()) {
                    echo "<tr><td>{$row['id_student']}</td><td>{$row['firstname_student']} {$row['lastname_student']}</td><td>{$row['year_student']}</td><td>{$row['date_attendance']}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No scanned students found for this event and attendance.</p>";
            }
            ?>

            <form method="GET" action="scan.php">
                <input type="hidden" name="event" value="<?php echo htmlspecialchars($event_id); ?>">
                <input type="hidden" name="attendance" value="<?php echo htmlspecialchars($attendance_id); ?>">
                <button type="submit" name="export" value="csv"><i class="fas fa-file-csv"></i> Export to CSV</button>
            </form>
        </div>
    </div>
</body>
</html>

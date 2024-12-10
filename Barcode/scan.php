<?php
// Include the Database class
require 'db-conn.php';

// Start the session
session_start();

// Instantiate the Database class
$database = new Database();

// Check for connection errors
if ($database->error) {
    die($database->error);
}

// Initialize variables
$event_id = $attendance_id = null;
$event_name = $attendance_name = $message = '';
$student = null;

// Check if event and attendance are set via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['event']) && isset($_POST['attendance'])) {
        $event_id = $_POST['event'];
        $attendance_id = $_POST['attendance'];
    } else {
        die('Event and attendance selection missing.');
    }
} elseif (isset($_GET['event']) && isset($_GET['attendance'])) {
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

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Sanitize event name for the filename
    $sanitized_event_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $event_name);
    $filename = "{$sanitized_event_name}_attendance_records.csv";

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $output = fopen("php://output", "w");
    fputcsv($output, ['Student ID', 'Name', 'Year', 'Date Attended']);

    $scanned_students_query = "SELECT student.id_student, student.firstname_student, student.lastname_student, student.year_student, student_attendance.date_attendance 
        FROM student 
        JOIN student_attendance ON student.id_student = student_attendance.id_student 
        WHERE student_attendance.id_attendance = '$attendance_id' 
        AND student_attendance.status_attendance = 'Present'";
    $scanned_students_result = $database->conn->query($scanned_students_query);

    if ($scanned_students_result && $scanned_students_result->num_rows > 0) {
        while ($row = $scanned_students_result->fetch_assoc()) {
            fputcsv($output, [
                $row['id_student'],
                $row['firstname_student'] . ' ' . $row['lastname_student'],
                $row['year_student'],
                $row['date_attendance']
            ]);
        }
    }

    fclose($output);
    exit;
}

// Handle student scan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];

    // Check if the student has already been scanned for this attendance
    $check_query = "SELECT * FROM student_attendance 
                    WHERE id_student = '$student_id' 
                    AND id_attendance = '$attendance_id' 
                    AND status_attendance = 'Present'";
    $check_result = $database->conn->query($check_query);

    if ($check_result && $check_result->num_rows > 0) {
        $message = "<p class='error'>You are already IN</p>";
    } else {
        // Fetch student details
        $student_query = "SELECT id_student, firstname_student, lastname_student, year_student 
                          FROM student WHERE id_student = '$student_id'";
        $student_result = $database->conn->query($student_query);

        if ($student_result && $student_result->num_rows > 0) {
            $student = $student_result->fetch_assoc();

            // Insert attendance record
            $insert_query = "INSERT INTO student_attendance (id_student, id_attendance, status_attendance, date_attendance) 
                             VALUES ('$student_id', '$attendance_id', 'Present', NOW())";
            if ($database->conn->query($insert_query) === TRUE) {
                $message = "<p class='success'>Attendance recorded successfully</p>";
            } else {
                $message = "<p class='error'>Error recording attendance: " . $database->conn->error . "</p>";
            }
        } else {
            $message = "<p class='error'>Student not found</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scan Attendance</title>
    <link rel="stylesheet" type="text/css" href="css/Scan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="particles"></div>
    <div class="container">
        <div class="left-column">
            <h1><i class="fas fa-calendar-alt"></i> Event: <?php echo htmlspecialchars($event_name); ?></h1>
            <h2><i class="fas fa-clipboard-check"></i> Attendance: <?php echo htmlspecialchars($attendance_name); ?></h2>

            <?php if ($message): echo $message; endif; ?>

            <!-- Barcode Scan Form -->
            <form method="POST" action="scan.php" id="scanForm">
                <input type="hidden" name="event" value="<?php echo htmlspecialchars($event_id); ?>">
                <input type="hidden" name="attendance" value="<?php echo htmlspecialchars($attendance_id); ?>">

                <div class="input-group">
                    <label for="student_id"><i class="fas fa-id-card"></i> Scan Student ID:</label>
                    <input type="text" name="student_id" id="student_id" autofocus required>
                </div>
            </form>

            <!-- QR Code Scanner Toggle Switch -->
            <div class="switch-container">
                <label class="switch">
                    <input type="checkbox" id="toggleQR">
                    <span class="slider round"></span>
                </label>
                <span>Toggle QR Scanner</span>
            </div>

            <!-- QR Code Scanner Section -->
            <div class="qr-scanner">
                <h3><i class="fas fa-qrcode"></i> QR Code Scanner</h3>
                <div id="qr-reader"></div>
                <p id="qr-result"></p>
            </div>

            <!-- Student Status Section -->
            <?php if ($student): ?>
                <div class="student-status">
                    <h3>Student Status</h3>
                    <p><strong>ID:</strong> <?php echo htmlspecialchars($student['id_student']); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($student['firstname_student'] . ' ' . $student['lastname_student']); ?></p>
                    <p><strong>Year:</strong> <?php echo htmlspecialchars($student['year_student']); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="right-column">
            <h3>Scanned Students</h3>
            <form method="GET" action="scan.php" class="export-form">
                <input type="hidden" name="event" value="<?php echo htmlspecialchars($event_id); ?>">
                <input type="hidden" name="attendance" value="<?php echo htmlspecialchars($attendance_id); ?>">
                <button type="submit" name="export" value="csv" class="export-btn">
                    <i class="fas fa-file-export"></i> Export to CSV
                </button>
            </form>
            <div id="scannedRecords">
                <?php
                $scanned_students_query = "SELECT student.id_student, student.firstname_student, student.lastname_student, student.year_student, student_attendance.date_attendance 
                    FROM student 
                    JOIN student_attendance ON student.id_student = student_attendance.id_student 
                    WHERE student_attendance.id_attendance = '$attendance_id' 
                    AND student_attendance.status_attendance = 'Present'";
                $scanned_students_result = $database->conn->query($scanned_students_query);

                if ($scanned_students_result && $scanned_students_result->num_rows > 0) {
                    echo "<table><tr><th>Student ID</th><th>Name</th><th>Year</th><th>Date Attended</th></tr>";
                    while ($row = $scanned_students_result->fetch_assoc()) {
                        echo "<tr><td>{$row['id_student']}</td><td>{$row['firstname_student']} {$row['lastname_student']}</td><td>{$row['year_student']}</td><td>{$row['date_attendance']}</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No students scanned yet.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/minified/html5-qrcode.min.js"></script>
    <script>
        // Automatically submit the form when barcode is detected
        function autoSubmitForm() {
            const studentInput = document.getElementById('student_id');
            studentInput.addEventListener('input', function () {
                if (studentInput.value.trim() !== '') {
                    document.getElementById('scanForm').submit();
                }
            });
        }

        window.onload = autoSubmitForm;

        let qrScannerEnabled = false;
        const qrCodeScanner = new Html5Qrcode("qr-reader");

        document.getElementById('toggleQR').addEventListener('change', function() {
            if (this.checked) {
                qrScannerEnabled = true;
                qrCodeScanner.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,
                        qrbox: 250,
                    },
                    onScanSuccess
                );
            } else {
                qrScannerEnabled = false;
                qrCodeScanner.stop();
                document.getElementById('qr-result').innerText = '';
            }
        });

        function onScanSuccess(decodedText) {
            if (qrScannerEnabled) {
                document.getElementById('qr-result').innerText = `QR Code Result: ${decodedText}`;
                document.getElementById('student_id').value = decodedText;
                document.getElementById('scanForm').submit();
            }
        }

        // Add particle effect
        function createParticles() {
            const particlesContainer = document.querySelector('.particles');
            const particleCount = 30; // Reduced for a more minimal effect

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                particle.style.width = `${Math.random() * 3 + 1}px`; // Smaller particles
                particle.style.height = particle.style.width;
                particle.style.left = `${Math.random() * 100}vw`;
                particle.style.top = `${Math.random() * 100}vh`;
                particle.style.animationDuration = `${Math.random() * 20 + 10}s`; // Slower animation
                particlesContainer.appendChild(particle);
            }
        }

        createParticles();
    </script>
</body>
</html>

<?php
require_once '././php/db-conn.php';

include "././php/auth-check.php";

$database = Database::getInstance();
$db = $database->db;

$semesterQuery = "SELECT semester_ID, academic_year, semester_type, status FROM semester WHERE status = 'active' ORDER BY academic_year DESC";
$semesters = $db->query($semesterQuery);

// Handle AJAX for events
if (isset($_POST['semester_id'])) {
    $semesterId = $_POST['semester_id'];
    $stmt = $db->prepare("SELECT id_event, name_event FROM events WHERE semester_ID = ? ORDER BY date_event, event_start_time");
    $stmt->bind_param("s", $semesterId);
    $stmt->execute();
    $result = $stmt->get_result();

    $eventOptions = ($result->num_rows > 0)
        ? array_reduce(iterator_to_array($result), fn($carry, $event) => $carry . '<option value="' . $event['id_event'] . '">' . htmlspecialchars($event['name_event']) . '</option>', '')
        : '<option value="">No events found for this semester.</option>';

    echo $eventOptions;
    exit;
}

// Handle AJAX for attendances
if (isset($_POST['event_id'])) {
    $eventId = $_POST['event_id'];
    $stmt = $db->prepare("SELECT id_attendance, type_attendance, attendance_status FROM attendances WHERE id_event = ? ORDER BY start_time");
    $stmt->bind_param("s", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();

    $attendanceOptions = ($result->num_rows > 0)
        ? array_reduce(iterator_to_array($result), fn($carry, $att) => $carry . '<option value="' . $att['id_attendance'] . '">' . htmlspecialchars($att['type_attendance']) . ' - ' . htmlspecialchars($att['attendance_status']) . '</option>', '')
        : '<option value="">No attendance records found for this event.</option>';

    echo $attendanceOptions;
    exit;
}

// Handle AJAX for attendance summary (example)
if (isset($_POST['get_attendance_details'])) {
    echo json_encode([
        'present' => 24,
        'absent' => 3,
        'late' => 2,
        'rate' => 85
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Events and Attendance</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./php/EFMS-scanner/style.css">
</head>

<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-calendar-alt me-2"></i>View Events & Attendance</h2>
                        <span class="badge bg-light text-dark"><i class="fas fa-database me-1"></i> Live</span>
                    </div>
                    <div class="card-body">
                        <form id="attendanceForm">
                            <!-- Semester Select -->
                            <div class="mb-4">
                                <label for="semester" class="form-label">Select Semester</label>
                                <select class="form-select" id="semester" required>
                                    <option selected disabled>-- Select Semester --</option>
                                    <?php while($sem = $semesters->fetch_assoc()): ?>
                                        <option value="<?= $sem['semester_ID']; ?>">
                                            <?= htmlspecialchars($sem['academic_year']) . ' - ' . htmlspecialchars($sem['semester_type']); ?>
                                            <?= ($sem['status'] === 'active') ? ' (Active)' : ''; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Event Select -->
                            <div class="mb-4" id="eventSection" style="display: none;">
                                <label for="event" class="form-label">Select Event</label>
                                <select class="form-select" id="event" disabled></select>
                                <div id="eventLoading" class="loading-spinner spinner-border mt-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <!-- Attendance Select -->
                            <div class="mb-4" id="attendanceSection" style="display: none;">
                                <label for="attendance" class="form-label">Select Attendance</label>
                                <select class="form-select" id="attendance" disabled></select>
                                <div id="attendanceLoading" class="loading-spinner spinner-border mt-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </form>

                        <!-- Scanner Button -->
                        <div id="scannerButtonContainer" class="text-center" style="display: none;">
    <a id="proceedToScanner" href="#" class="btn btn-success btn-lg">
        <i class="fas fa-qrcode me-2"></i> Proceed to Scanner
    </a>
</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(function() {
            $('#semester').change(function() {
                let semesterId = $(this).val();
                if (semesterId) {
                    $('#event').prop('disabled', true).html('<option>Loading...</option>');
                    $('#eventLoading').show();
                    $('#attendanceSection, #scannerButtonContainer').hide();

                    $.post('', { semester_id: semesterId }, function(data) {
                        $('#event').html('<option selected disabled>-- Select Event --</option>' + data).prop('disabled', false);
                        $('#eventSection').fadeIn();
                        $('#eventLoading').hide();
                    });
                }
            });

            $('#event').change(function() {
                let eventId = $(this).val();
                if (eventId) {
                    $('#attendance').prop('disabled', true).html('<option>Loading...</option>');
                    $('#attendanceLoading').show();

                    $.post('', { event_id: eventId }, function(data) {
                        $('#attendance').html('<option selected disabled>-- Select Attendance --</option>' + data).prop('disabled', false);
                        $('#attendanceSection').fadeIn();
                        $('#attendanceLoading').hide();
                    });
                }
            });

            $('#attendance').change(function() {
                if ($(this).val()) {
                    $('#scannerButtonContainer').fadeIn();
                } else {
                    $('#scannerButtonContainer').hide();
                }
            });

// To this:
$('#proceedToScanner').click(function(e) {
    e.preventDefault();
    let attendanceId = $('#attendance').val();
    if (attendanceId) {
        window.location.href = '?content=efms-scanner-app&attendance_id=' + attendanceId;
    }
});
        });
    </script>
</body>
</html>

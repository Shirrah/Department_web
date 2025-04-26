<?php
require_once '././php/db-conn.php';

$database = Database::getInstance();
$db = $database->db;

// Get all semesters
$semesterQuery = "SELECT semester_ID, academic_year, semester_type, status FROM semester ORDER BY academic_year DESC";
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

    <style>
        :root {
            --primary-color: tomato;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
        }
        body { background: var(--light-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background: var(--primary-color); color: white; font-weight: bold; }
        .btn-primary { background: var(--primary-color); border-color: var(--primary-color); }
        .btn-primary:hover { background: #d84332; border-color: #d84332; }
        .loading-spinner { display: none; color: var(--primary-color); }
        @media (max-width: 768px) {
            .card-header h2 { font-size: 1.5rem; }
        }
    </style>
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
                            <button id="proceedToScanner" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#scannerModal">
                                <i class="fas fa-qrcode me-2"></i> Proceed to Scanner
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Modal -->
    <div class="modal fade" id="scannerModal" tabindex="-1" aria-labelledby="scannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Attendance Scanner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <iframe id="scannerFrame" src="" style="width: 100%; height: 500px;"></iframe>
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

            $('#proceedToScanner').click(function() {
                let attendanceId = $('#attendance').val();
                if (attendanceId) {
                    $('#scannerFrame').attr('src', 'php/EFMS-scanner/efms-scanner.php?attendance_id=' + attendanceId);
                }
            });
        });
    </script>
</body>
</html>

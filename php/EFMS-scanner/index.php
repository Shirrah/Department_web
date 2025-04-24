<?php
require_once '././php/db-conn.php';

$database = Database::getInstance();
$db = $database->db;

// Get all semesters
$semesterQuery = "SELECT semester_ID, academic_year, semester_type, status FROM semester ORDER BY academic_year DESC";
$semesters = $db->query($semesterQuery);

// Get events by semester
if (isset($_POST['semester_id'])) {
    $semesterId = $_POST['semester_id'];
    $eventQuery = "SELECT * FROM events WHERE semester_ID = ? ORDER BY date_event, event_start_time";
    $stmt = $db->prepare($eventQuery);
    $stmt->bind_param("s", $semesterId);
    $stmt->execute();
    $events = $stmt->get_result();
    $eventOptions = '';

    if ($events->num_rows > 0) {
        while ($event = $events->fetch_assoc()) {
            $eventOptions .= '<option value="' . $event['id_event'] . '">' . htmlspecialchars($event['name_event']) . '</option>';
        }
    } else {
        $eventOptions = '<option value="">No events found for this semester.</option>';
    }

    echo $eventOptions;
    exit;
}

// Get attendances by event
if (isset($_POST['event_id'])) {
    $eventId = $_POST['event_id'];
    $attendanceQuery = "SELECT * FROM attendances WHERE id_event = ? ORDER BY start_time";
    $stmt = $db->prepare($attendanceQuery);
    $stmt->bind_param("s", $eventId);
    $stmt->execute();
    $attendances = $stmt->get_result();
    $attendanceOptions = '';

    if ($attendances->num_rows > 0) {
        while ($attendance = $attendances->fetch_assoc()) {
            $attendanceOptions .= '<option value="' . $attendance['id_attendance'] . '">' . htmlspecialchars($attendance['type_attendance']) . ' - ' . htmlspecialchars($attendance['attendance_status']) . '</option>';
        }
    } else {
        $attendanceOptions = '<option value="">No attendance records found for this event.</option>';
    }

    echo $attendanceOptions;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Events and Attendance</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .select2-container--bootstrap-5 .select2-selection {
            height: 45px;
            padding: 8px 12px;
            border-radius: 8px;
        }
        
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
        }
        
        .loading-spinner {
            display: none;
            color: var(--primary-color);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
        }
        
        .result-container {
            min-height: 200px;
            border-left: 4px solid var(--primary-color);
        }
        
        #scannerButtonContainer {
            display: none;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
<a href="?content=logout">logout</a>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>View Events and Attendance</h2>
                        <span class="badge bg-light text-dark"><i class="fas fa-database me-1"></i> Live Data</span>
                    </div>
                    <div class="card-body">
                        <form id="attendanceForm">
                            <!-- Semester Selection -->
                            <div class="mb-4">
                                <label for="semester" class="form-label">
                                    <i class="fas fa-graduation-cap me-2"></i>Select Semester
                                </label>
                                <select class="form-select form-select-lg" name="semester" id="semester" required>
                                    <option value="" selected disabled>-- Select a Semester --</option>
                                    <?php while ($semester = $semesters->fetch_assoc()) : ?>
                                        <option value="<?php echo $semester['semester_ID']; ?>">
                                            <?php echo htmlspecialchars($semester['academic_year']) . ' - ' . htmlspecialchars($semester['semester_type']); ?>
                                            <?php if ($semester['status'] == 'active') : ?>
                                                <span class="badge bg-success ms-2">Active</span>
                                            <?php endif; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="form-text">Select the academic semester to view associated events</div>
                            </div>
                            
                            <!-- Event Selection (initially hidden) -->
                            <div class="mb-4" id="eventSection" style="display: none;">
                                <label for="event" class="form-label">
                                    <i class="fas fa-calendar-check me-2"></i>Select Event
                                </label>
                                <select class="form-select form-select-lg" name="event" id="event" disabled>
                                    <option value="" selected disabled>-- Select an Event --</option>
                                </select>
                                <div class="d-flex align-items-center mt-2">
                                    <div class="loading-spinner spinner-border text-primary me-2" role="status" id="eventLoading">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <small class="form-text">Events will load when you select a semester</small>
                                </div>
                            </div>
                            
                            <!-- Attendance Selection (initially hidden) -->
                            <div class="mb-4" id="attendanceSection" style="display: none;">
                                <label for="attendance" class="form-label">
                                    <i class="fas fa-clipboard-list me-2"></i>Select Attendance Record
                                </label>
                                <select class="form-select form-select-lg" name="attendance" id="attendance" disabled>
                                    <option value="" selected disabled>-- Select Attendance --</option>
                                </select>
                                <div class="d-flex align-items-center mt-2">
                                    <div class="loading-spinner spinner-border text-primary me-2" role="status" id="attendanceLoading">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <small class="form-text">Attendance records will load when you select an event</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Results Card (initially hidden) -->
                <div class="card" id="resultsCard" style="display: none;">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Attendance Details</h3>
                    </div>
                    <div class="card-body result-container">
                        <div class="text-center py-5" id="emptyState">
                            <i class="fas fa-magnifying-glass fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Attendance Selected</h4>
                            <p class="text-muted">Select an attendance record to view details</p>
                        </div>
                        <div id="attendanceDetails"></div>
                        <div id="scannerButtonContainer">
                            <a href="#" id="proceedToScanner" class="btn btn-success btn-lg">
                                <i class="fas fa-qrcode me-2"></i> Proceed to Scanner
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // When semester is selected
            $('#semester').change(function() {
                var semesterId = $(this).val();
                
                if (semesterId) {
                    // Show event section
                    $('#eventSection').fadeIn();
                    $('#event').prop('disabled', false);
                    
                    // Show loading spinner
                    $('#eventLoading').show();
                    
                    // Clear previous selections
                    $('#event').html('<option value="" selected disabled>Loading events...</option>');
                    $('#attendance').html('<option value="" selected disabled>-- Select Attendance --</option>');
                    $('#attendanceSection').hide();
                    $('#attendance').prop('disabled', true);
                    $('#resultsCard').hide();
                    $('#scannerButtonContainer').hide();
                    
                    // Fetch events via AJAX
                    $.ajax({
                        url: '',
                        type: 'POST',
                        data: { semester_id: semesterId },
                        success: function(response) {
                            $('#event').html('<option value="" selected disabled>-- Select an Event --</option>' + response);
                            $('#eventLoading').hide();
                        },
                        error: function() {
                            $('#event').html('<option value="" selected disabled>Error loading events</option>');
                            $('#eventLoading').hide();
                        }
                    });
                } else {
                    // Reset form if no semester selected
                    $('#eventSection').hide();
                    $('#attendanceSection').hide();
                    $('#resultsCard').hide();
                    $('#scannerButtonContainer').hide();
                    $('#event').html('<option value="" selected disabled>-- Select an Event --</option>');
                    $('#attendance').html('<option value="" selected disabled>-- Select Attendance --</option>');
                    $('#event').prop('disabled', true);
                    $('#attendance').prop('disabled', true);
                }
            });
            
            // When event is selected
            $('#event').change(function() {
                var eventId = $(this).val();
                
                if (eventId) {
                    // Show attendance section
                    $('#attendanceSection').fadeIn();
                    $('#attendance').prop('disabled', false);
                    
                    // Show loading spinner
                    $('#attendanceLoading').show();
                    
                    // Clear previous selection
                    $('#attendance').html('<option value="" selected disabled>Loading attendance records...</option>');
                    $('#resultsCard').hide();
                    $('#scannerButtonContainer').hide();
                    
                    // Fetch attendances via AJAX
                    $.ajax({
                        url: '',
                        type: 'POST',
                        data: { event_id: eventId },
                        success: function(response) {
                            $('#attendance').html('<option value="" selected disabled>-- Select Attendance --</option>' + response);
                            $('#attendanceLoading').hide();
                        },
                        error: function() {
                            $('#attendance').html('<option value="" selected disabled>Error loading attendance records</option>');
                            $('#attendanceLoading').hide();
                        }
                    });
                } else {
                    $('#attendanceSection').hide();
                    $('#resultsCard').hide();
                    $('#scannerButtonContainer').hide();
                    $('#attendance').html('<option value="" selected disabled>-- Select Attendance --</option>');
                    $('#attendance').prop('disabled', true);
                }
            });
            
            // When attendance is selected
            $('#attendance').change(function() {
                var attendanceId = $(this).val();
                
                if (attendanceId) {
                    // Show results card
                    $('#resultsCard').fadeIn();
                    $('#emptyState').hide();
                    
                    // Show the scanner button
                    $('#scannerButtonContainer').fadeIn();
                    
                    // Update the scanner button link
                    $('#proceedToScanner').attr('href', 'index.php?content=efms-scanner-app?attendance_id=' + attendanceId);
                    
                    // Here you would typically fetch the attendance details
                    // For now we'll just show a placeholder
                    $('#attendanceDetails').html(`
                        <div class="alert alert-info">
                            <h4><i class="fas fa-spinner fa-spin me-2"></i>Loading attendance details...</h4>
                            <p>In a real implementation, this would show detailed attendance records for the selected event.</p>
                        </div>
                    `);
                    
                    // Simulate loading data (replace with actual AJAX call)
                    setTimeout(function() {
                        $('#attendanceDetails').html(`
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Attendance Summary</h5>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Total Present
                                                    <span class="badge bg-success rounded-pill">24</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Total Absent
                                                    <span class="badge bg-danger rounded-pill">3</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Total Late
                                                    <span class="badge bg-warning rounded-pill">2</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-users me-2"></i>Attendance Rate</h5>
                                            <div class="progress mb-3" style="height: 30px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">85%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                    }, 1500);
                } else {
                    $('#emptyState').show();
                    $('#attendanceDetails').empty();
                    $('#scannerButtonContainer').hide();
                }
            });
        });
    </script>
</body>
</html>
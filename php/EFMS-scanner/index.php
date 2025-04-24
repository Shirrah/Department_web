<?php
require_once './../php/db-conn.php';

class SemesterDropdown {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->db;
    }
    
    public function renderDropdown($name = 'semester', $selectedId = null) {
        // Fetch semesters from database
        $query = "SELECT semester_ID, CONCAT(academic_year, ' - ', semester_type) AS semester_name 
                  FROM semester 
                  WHERE status = 'active' 
                  ORDER BY academic_year DESC, semester_type DESC";
        $result = $this->db->query($query);
        
        if (!$result) {
            echo "<select name='{$name}' class='form-control' disabled>";
            echo "<option value=''>Database Error</option>";
            echo "</select>";
            return;
        }
        
        // Start building the dropdown HTML
        echo "<select name='{$name}' id='{$name}' class='form-control'>";
        echo "<option value=''>-- Select Semester --</option>";
        
        while ($row = $result->fetch_assoc()) {
            $selected = ($selectedId == $row['semester_ID']) ? 'selected' : '';
            echo "<option value='{$row['semester_ID']}' {$selected}>{$row['semester_name']}</option>";
        }
        
        echo "</select>";
    }
}

class EventDropdown {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->db;
    }
    
    public function renderDropdown($semesterId = null) {
        if (!$semesterId) {
            echo "<select name='event' id='event' class='form-control' disabled>";
            echo "<option value=''>-- Select Event --</option>";
            echo "</select>";
            return;
        }
        
        // Fetch events for the selected semester
        $query = "SELECT id_event, name_event 
                  FROM events 
                  WHERE semester_ID = ? 
                  ORDER BY date_event DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $semesterId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            echo "<select name='event' id='event' class='form-control' disabled>";
            echo "<option value=''>No Events Found</option>";
            echo "</select>";
            return;
        }
        
        // Start building the events dropdown
        echo "<select name='event' id='event' class='form-control'>";
        echo "<option value=''>-- Select Event --</option>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['id_event']}'>{$row['name_event']}</option>";
        }
        
        echo "</select>";
    }
}

class AttendanceDropdown {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->db;
    }
    
    public function renderDropdown($eventId = null) {
        if (!$eventId) {
            echo "<select name='attendance' id='attendance' class='form-control mt-3' disabled>";
            echo "<option value=''>-- Select Attendance --</option>";
            echo "</select>";
            return;
        }
        
        // Fetch attendance records for the selected event
        $query = "SELECT id_attendance, CONCAT(type_attendance, ' (', TIME_FORMAT(start_time, '%h:%i %p'), ' - ', TIME_FORMAT(end_time, '%h:%i %p'), ')') AS attendance_label 
                  FROM attendances 
                  WHERE id_event = ? 
                  ORDER BY start_time";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result || $result->num_rows === 0) {
            echo "<select name='attendance' id='attendance' class='form-control mt-3' disabled>";
            echo "<option value=''>No Attendance Records Found</option>";
            echo "</select>";
            return;
        }
        
        // Start building the attendance dropdown
        echo "<select name='attendance' id='attendance' class='form-control mt-3'>";
        echo "<option value=''>-- Select Attendance --</option>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['id_attendance']}'>{$row['attendance_label']}</option>";
        }
        
        echo "</select>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Selection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
            <form id="attendanceForm" action="efms-scanner.php" method="get" onsubmit="return validateForm()">
    <div class="mb-3">
        <label for="semester" class="form-label">Select Semester:</label>
        <?php
        $dropdown = new SemesterDropdown();
        $dropdown->renderDropdown('semester');
        ?>
    </div>

    <div class="mb-3">
        <label for="event" class="form-label">Select Event:</label>
        <select name="event" id="event" class="form-control" disabled>
            <option value="">-- Select Event --</option>
        </select>
    </div>

    <div id="attendanceDropdownContainer">
        <?php
        $attendanceDropdown = new AttendanceDropdown();
        $attendanceDropdown->renderDropdown();
        ?>
    </div>

    <div class="mt-3">
        <button type="submit" id="submitBtn" class="btn btn-primary" disabled>
            <i class="fas fa-qrcode"></i> Proceed to Scanner
        </button>
    </div>
</form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('semester').addEventListener('change', function () {
            const semesterId = this.value;
            const eventSelect = document.getElementById('event');
            const submitBtn = document.getElementById('submitBtn');
            
            eventSelect.innerHTML = '<option value="">Loading...</option>';
            eventSelect.disabled = true;
            submitBtn.disabled = true;

            // Clear attendance dropdown when semester changes
            document.getElementById('attendanceDropdownContainer').innerHTML = `
                <select name="attendance" id="attendance" class="form-control mt-3" disabled>
                    <option value="">-- Select Attendance --</option>
                </select>
            `;

            if (semesterId) {
                fetch(`get_events.php?semester_ID=${semesterId}`)
                    .then(response => response.json())
                    .then(data => {
                        eventSelect.innerHTML = '<option value="">-- Select Event --</option>';
                        data.forEach(event => {
                            const option = document.createElement('option');
                            option.value = event.id_event;
                            option.textContent = event.name_event;
                            eventSelect.appendChild(option);
                        });
                        eventSelect.disabled = false;
                    })
                    .catch(error => {
                        eventSelect.innerHTML = '<option value="">Failed to load</option>';
                        console.error('Error:', error);
                    });
            } else {
                eventSelect.innerHTML = '<option value="">-- Select Event --</option>';
                eventSelect.disabled = true;
            }
        });

        document.getElementById('attendanceForm').addEventListener('submit', function(e) {
    const semester = document.getElementById('semester').value;
    const event = document.getElementById('event').value;
    const attendance = document.getElementById('attendance').value;
    
    if (!semester || !event || !attendance) {
        e.preventDefault();
        alert('Please select semester, event, and attendance before proceeding');
        return false;
    }
    return true;
});

        document.getElementById('event').addEventListener('change', function() {
            const eventId = this.value;
            const attendanceContainer = document.getElementById('attendanceDropdownContainer');
            const submitBtn = document.getElementById('submitBtn');
            
            submitBtn.disabled = true;
            
            if (!eventId) {
                attendanceContainer.innerHTML = `
                    <select name="attendance" id="attendance" class="form-control mt-3" disabled>
                        <option value="">-- Select Attendance --</option>
                    </select>
                `;
                return;
            }
            
            // Show loading state
            attendanceContainer.innerHTML = `
                <select name="attendance" id="attendance" class="form-control mt-3" disabled>
                    <option value="">Loading attendance records...</option>
                </select>
            `;
            
            // Fetch attendance records
            fetch(`get_attendances.php?event_ID=${eventId}`)
                .then(response => response.json())
                .then(data => {
                    let html = `
                        <select name="attendance" id="attendance" class="form-control mt-3">
                            <option value="">-- Select Attendance --</option>
                    `;
                    
                    data.forEach(attendance => {
                        html += `<option value="${attendance.id_attendance}">${attendance.attendance_label}</option>`;
                    });
                    
                    html += `</select>`;
                    attendanceContainer.innerHTML = html;
                    
                    // Enable submit button when attendance is selected
                    document.getElementById('attendance').addEventListener('change', function() {
                        submitBtn.disabled = !this.value;
                    });
                })
                .catch(error => {
                    attendanceContainer.innerHTML = `
                        <select name="attendance" id="attendance" class="form-control mt-3" disabled>
                            <option value="">Failed to load attendance records</option>
                        </select>
                    `;
                    console.error('Error:', error);
                });
        });
        function validateForm() {
    const semester = document.getElementById('semester').value;
    const event = document.getElementById('event').value;
    const attendance = document.getElementById('attendance').value;
    
    if (!semester || !event || !attendance) {
        alert('Please select all options (semester, event, and attendance) before proceeding');
        return false;
    }
    return true;
}
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!--  -->
<?php
require_once 'db-conn.php';

class EventSystem {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->db;
    }
    
    // [Previous methods for semesters and events...]
    
    public function getAttendances($eventId) {
        $query = "SELECT id_attendance, type_attendance, 
                 TIME_FORMAT(start_time, '%h:%i %p') AS formatted_start,
                 TIME_FORMAT(end_time, '%h:%i %p') AS formatted_end
                 FROM attendances 
                 WHERE id_event = ?
                 ORDER BY start_time ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $attendances = [];
        while ($row = $result->fetch_assoc()) {
            $attendances[] = $row;
        }
        
        return $attendances;
    }
}

$urlSemester = $_GET['semester'] ?? '';
$selectedEventId = $_GET['event'] ?? '';
$system = new EventSystem();
$selectedSemesterId = $system->getSemesterIdFromUrl($urlSemester);

// Get data
$events = [];
if ($selectedSemesterId) {
    $events = $system->getEvents($selectedSemesterId);
}

$attendances = [];
if ($selectedEventId) {
    $attendances = $system->getAttendances($selectedEventId);
}
?>

<!-- [Previous HTML head and semester/event dropdowns...] -->

<div class="mb-3">
    <label for="event" class="form-label">Select Event:</label>
    <select name="event" id="event" class="form-control" onchange="loadAttendances(this.value)" <?= empty($selectedSemesterId) ? 'disabled' : '' ?>>
        <?php if(empty($selectedSemesterId)): ?>
            <option value="">-- Select a semester first --</option>
        <?php elseif(empty($events)): ?>
            <option value="">No events available</option>
        <?php else: ?>
            <option value="">-- Select Event --</option>
            <?php foreach($events as $event): ?>
                <option value="<?= $event['id_event'] ?>" <?= $selectedEventId == $event['id_event'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($event['event_display']) ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>

<!-- Attendance Records Section -->
<div class="card mt-4">
    <div class="card-header">
        <h4>Attendance Records</h4>
    </div>
    <div class="card-body">
        <div id="attendanceResults">
            <?php if(!empty($attendances)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Attendance Type</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($attendances as $attendance): ?>
                            <tr>
                                <td><?= htmlspecialchars($attendance['type_attendance']) ?></td>
                                <td><?= $attendance['formatted_start'] ?></td>
                                <td><?= $attendance['formatted_end'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif($selectedEventId): ?>
                <div class="alert alert-info">No attendance records found for this event</div>
            <?php else: ?>
                <div class="alert alert-secondary">Select an event to view attendance records</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    async function loadAttendances(eventId) {
        const resultsDiv = document.getElementById('attendanceResults');
        const semesterDropdown = document.getElementById('semester');
        const semesterOption = semesterDropdown.options[semesterDropdown.selectedIndex];
        const semesterParam = semesterOption.getAttribute('data-url');
        
        // Update URL
        const newUrl = `?semester=${semesterParam}&event=${eventId}`;
        window.history.pushState({}, '', newUrl);
        
        // Show loading state
        resultsDiv.innerHTML = `<div class="text-center my-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Loading attendance records...</p>
        </div>`;
        
        try {
            const response = await fetch(`get_attendances.php?event_id=${eventId}`);
            const data = await response.json();
            
            if (data.success && data.data.length > 0) {
                let html = `<table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Attendance Type</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                        </tr>
                    </thead>
                    <tbody>`;
                
                data.data.forEach(attendance => {
                    html += `<tr>
                        <td>${escapeHtml(attendance.type_attendance)}</td>
                        <td>${attendance.formatted_start}</td>
                        <td>${attendance.formatted_end}</td>
                    </tr>`;
                });
                
                html += `</tbody></table>`;
                resultsDiv.innerHTML = html;
            } else {
                resultsDiv.innerHTML = `<div class="alert alert-info">No attendance records found</div>`;
            }
        } catch (error) {
            console.error('Error:', error);
            resultsDiv.innerHTML = `<div class="alert alert-danger">Error loading attendance records</div>`;
        }
    }
    
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    // Initialize if event is preselected
    document.addEventListener('DOMContentLoaded', function() {
        const eventId = "<?= $selectedEventId ?>";
        if (eventId) {
            loadAttendances(eventId);
        }
    });
</script>
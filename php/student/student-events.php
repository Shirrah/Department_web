<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
require_once "././php/db-conn.php";
$db = new Database();

// Fetch events from the database
$sql = "SELECT id_event, name_event, date_event, event_desc, date_created FROM events";
$events = $db->db->query($sql);

// Handle showing attendances
$attendances = [];
if (isset($_GET['show_attendances'])) {
    $event_id = $_GET['id'];
    $attendance_sql = "SELECT type_attendance, time_in, time_out FROM attendances WHERE id_event = ?";
    $attendance_stmt = $db->db->prepare($attendance_sql);
    $attendance_stmt->bind_param("i", $event_id);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();
    
    // Store attendances in an array
    while ($attendance = $attendance_result->fetch_assoc()) {
        $attendances[] = $attendance;
    }
}
?>

<link rel="stylesheet" href=".//.//stylesheet/student/student-events.css">

<div class="event-management-con">
    <div class="title">
        <h3>Events</h3>
        <span>View department events and your attendance records</span>
    </div>

    <div class="list-events-con">
        <?php if ($events->num_rows > 0): ?>
            <?php while($event = $events->fetch_assoc()): ?>
                <div class="event">
                    <div class="name-date">
                        <h4><?php echo $event['name_event']; ?></h4>
                        <p><?php echo date("F j, Y", strtotime($event['date_event'])); ?></p>
                    </div>
                    <div class="date-created">
                        <p>Date created: <?php echo date("F j, Y g:i a", strtotime($event['date_created'])); ?></p>
                    </div>
                    <div class="action-btn">
                        <div class="dropdown">
                            <span class="dots">Options</span>
                            <div class="dropdown-content">
                            <a href="?content=student-index&student=student-attendance-records&show_attendances=true&id=<?php echo $event['id_event']; ?>">
    <img src=".//.//assets/images/attendance.png" alt="">Show Attendance
</a>

                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No events found.</p>
        <?php endif; ?>
    </div>
</div>

<script>
window.onload = function() {
    // Dropdown functionality
    document.querySelectorAll('.dropdown').forEach(function(dropdown) {
        dropdown.querySelector('.dots').addEventListener('click', function() {
            dropdown.classList.toggle('show');
        });
    });

    // Close dropdown if clicked outside of dots
    window.onclick = function(event) {
        if (!event.target.matches('.dots')) {
            document.querySelectorAll('.dropdown.show').forEach(function(dropdown) {
                dropdown.classList.remove('show');
            });
        }
    }
};
</script>

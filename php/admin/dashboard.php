<?php
// Start the session
	$error ='';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "././php/db-conn.php";
$db = new Database();

// Get the selected semester from the URL
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : '';

// Query to count students in the selected semester
$query = "SELECT COUNT(*) AS student_count FROM student WHERE semester_ID = ?";
$stmt = $db->db->prepare($query);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$result = $stmt->get_result();

// Check if the query was successful
if ($result) {
    $row = $result->fetch_assoc();
    $student_count = $row['student_count'];
} else {
    echo "<p>Error retrieving student count.</p>";
}

// Query to count events in the selected semester
$query = "SELECT COUNT(*) AS events_count FROM events WHERE semester_ID = ?";
$stmt = $db->db->prepare($query);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$result = $stmt->get_result();

// Check if the query was successful
if ($result) {
    $row = $result->fetch_assoc();
    $events_count = $row['events_count'];
} else {
    echo "<p>Error retrieving event count.</p>";
}

// Query to count fees in the selected semester
$query = "SELECT COUNT(*) AS fees_count FROM payments WHERE semester_ID = ?";
$stmt = $db->db->prepare($query);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$result = $stmt->get_result();

// Check if the query was successful
if ($result) {
    $row = $result->fetch_assoc();
    $fees_count = $row['fees_count'];
} else {
    echo "<p>Error retrieving fee count.</p>";
}

?>

<link rel="stylesheet" href=".//.//stylesheet/admin/dashboard.css">


<div class="admin-dashboard-body">
    <div class="admin-dashboard-con">
    <div class="report-summary-header">
        <span>Report Summary</span>
        </div>
    <div class="dashcard-item">
        <div class="dashboard-card">
            <div class="card-details">
            <h2>Total students</h2>
            <a class="dash-view-count" href=""><?php echo htmlspecialchars($student_count); ?></a>
            <a class="dash-view-loc" href="?content=admin-index&admin=student-management">View Students</a>
            </div>
            <img src=".//.//assets/images/team.png" alt="">
        </div>
        <div class="dashboard-card">
        <div class="card-details">
            <h2>Total events</h2>
            <a class="dash-view-count" href=""><?php echo htmlspecialchars($events_count); ?></a>
            <a class="dash-view-loc" href="?content=admin-index&admin=event-management&admin_events=admin-events">View Events</a>
            </div>
            <img src=".//.//assets/images/event.png" alt="">
        </div>
        <div class="dashboard-card">
        <div class="card-details">
            <h2>Total fees</h2>
            <a class="dash-view-count" href=""><?php echo htmlspecialchars($fees_count); ?></a>
            <a class="dash-view-loc" href="?content=admin-index&admin=event-management&admin_events=admin-fees">View Fees</a>
            </div>
            <img src=".//.//assets/images/money.png" alt="">
        </div>
        </div>
        </div>
</div>

<script>
  window.addEventListener('unload', function () {
    navigator.sendBeacon('http://localhost/Department_web//php/logout.php'); // Sends a logout request when the tab is closed
  });
</script>
<?php
// Start the session
	$error ='';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "././php/db-conn.php";
$db = new Database();

// This is query that counts how much students in the db
$query = "SELECT COUNT(*) AS student_count FROM student";
$result = $db->db->query($query);

// Check if the query was successful
if ($result) {
    // Fetch the result as an associative array
    $row = $result->fetch_assoc();
    
    // Retrieve the count from the result
    $student_count = $row['student_count'];
} else {
    // Display an error message if the query fails
    echo "<p>Error retrieving student count.</p>";
}


// This is query that counts how much students in the db
$query = "SELECT COUNT(*) AS events_count FROM events";
$result = $db->db->query($query);

// Check if the query was successful
if ($result) {
    // Fetch the result as an associative array
    $row = $result->fetch_assoc();
    
    // Retrieve the count from the result
    $events_count = $row['events_count'];
} else {
    // Display an error message if the query fails
    echo "<p>Error retrieving event count.</p>";
}

// This is query that counts how much students in the db
$query = "SELECT COUNT(*) AS fees_count FROM payments";
$result = $db->db->query($query);

// Check if the query was successful
if ($result) {
    // Fetch the result as an associative array
    $row = $result->fetch_assoc();
    
    // Retrieve the count from the result
    $fees_count = $row['fees_count'];
} else {
    // Display an error message if the query fails
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
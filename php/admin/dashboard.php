<?php
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != 'yes') {
    header("location: ../index.php?content=log-in");
    exit();
}


// Count students in selected semester
$query = "SELECT COUNT(*) AS student_count FROM student WHERE semester_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$result = $stmt->get_result();
$student_count = ($result) ? $result->fetch_assoc()['student_count'] : 0;

// Count events in selected semester
$query = "SELECT COUNT(*) AS events_count FROM events WHERE semester_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$result = $stmt->get_result();
$events_count = ($result) ? $result->fetch_assoc()['events_count'] : 0;

// Count fees/payments in selected semester
$query = "SELECT COUNT(*) AS fees_count FROM payments WHERE semester_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$result = $stmt->get_result();
$fees_count = ($result) ? $result->fetch_assoc()['fees_count'] : 0;
?>

<link rel="stylesheet" href=".//.//stylesheet/admin/dashboard.css">

<div class="admin-dashboard-body">
    <div class="admin-dashboard-con">
        <div class="report-summary-header">
            <span>Dashboard</span>
        </div>
        
        <!-- Semester Selector (now included from external component) -->
        <div class="semester-select">
            <?php 
            // The semester form is now handled by the included component
            // It will automatically maintain the current page's context
            ?>
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
  window.addEventListener('load', function () {
    // Automatically submit the form once when the page loads
    if (!sessionStorage.getItem('formSubmitted')) {
      document.getElementById('semesterForm').submit(); // Submit the form
      sessionStorage.setItem('formSubmitted', 'true'); // Mark that the form has been submitted
    }
  });

  window.addEventListener('unload', function () {
    navigator.sendBeacon('http://localhost/Department_web//php/logout.php'); // Sends a logout request when the tab is closed
  });
</script>
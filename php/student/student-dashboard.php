<?php
// Include the database connection
require_once "././php/db-conn.php";
$db = new Database();


// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != 'yes') {
    // Redirect to login if not logged in
    header("location: ../index.php?content=log-in");
    exit();
}

// Get the user ID from the session (either admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

// Handle the semester selection from GET request and store it in session for this user
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    // Store the selected semester for the user in session
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
}

// Use the selected semester from the session or default to the latest semester
if (isset($_SESSION['selected_semester'][$user_id]) && !empty($_SESSION['selected_semester'][$user_id])) {
    $selected_semester = $_SESSION['selected_semester'][$user_id];
} else {
    // Get the latest semester from the database
    $query = "SELECT semester_ID, academic_year, semester_type FROM semester ORDER BY semester_ID DESC LIMIT 1";
    $stmt = $db->db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $selected_semester = $row['semester_ID'];
    } else {
        $selected_semester = null;
    }
}

// Fetch all semesters for dropdown
$sql = "SELECT semester_ID, academic_year, semester_type FROM semester";
$stmt = $db->db->prepare($sql);
$stmt->execute();
$allSemesters = $stmt->get_result();

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

// Fetch all semester data to populate the dropdown
$sql = "SELECT semester_ID, academic_year, semester_type FROM semester";
$result = $db->db->query($sql);

?>


<link rel="stylesheet" href=".//.//stylesheet/admin/dashboard.css">

<div class="admin-dashboard-body">
    <div class="admin-dashboard-con">
        <div class="report-summary-header">
            <span>Report Summary</span>
        </div>
        <div class="semester-select">
        <form method="GET" action="index.php" id="semesterForm">
    <input type="hidden" name="content" value="admin-index">
    <input type="hidden" name="admin" value="dashboard">
    <select class="form-select" style="width: min-content;" name="semester" id="semester" onchange="this.form.submit()">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $semester_id = $row['semester_ID'];
                $academic_year = $row['academic_year'];
                $semester_type = $row['semester_type'];

                // Check if this semester is the selected one
                $selected = ($semester_id == $selected_semester) ? 'selected' : '';
                echo "<option value='$semester_id' $selected>$semester_type - $academic_year</option>";
            }
        } else {
            echo "<option value=''>No semesters available</option>";
        }
        ?>
    </select>
</form>

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
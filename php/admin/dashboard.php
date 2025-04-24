<?php
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != 'yes') {
    header("location: ../index.php?content=log-in");
    exit();
}

$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

// Check if semester is set via GET and store it in session and cookies
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
    setcookie('selected_semester', $_GET['semester'], time() + (86400 * 30), "/"); // Store in cookie for 30 days
}

// Use selected semester from session or cookie, fallback to latest active semester
if (isset($_SESSION['selected_semester'][$user_id]) && !empty($_SESSION['selected_semester'][$user_id])) {
    $selected_semester = $_SESSION['selected_semester'][$user_id];
} elseif (isset($_COOKIE['selected_semester']) && !empty($_COOKIE['selected_semester'])) {
    $selected_semester = $_COOKIE['selected_semester'];
} else {
    // If no semester selected, fetch the latest active semester
    $query = "SELECT semester_ID FROM semester WHERE status = 'active' ORDER BY semester_ID DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $selected_semester = ($result && $row = $result->fetch_assoc()) ? $row['semester_ID'] : null;
}

// Dropdown: only active semesters
$sql = "SELECT semester_ID, academic_year, semester_type FROM semester WHERE status = 'active'";
$stmt = $db->prepare($sql);
$stmt->execute();
$allSemesters = $stmt->get_result();

// Student count
$query = "SELECT COUNT(*) AS student_count FROM student WHERE semester_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$result = $stmt->get_result();
$student_count = ($result) ? $result->fetch_assoc()['student_count'] : 0;

// Events count
$query = "SELECT COUNT(*) AS events_count FROM events WHERE semester_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$result = $stmt->get_result();
$events_count = ($result) ? $result->fetch_assoc()['events_count'] : 0;

// Fees count
$query = "SELECT COUNT(*) AS fees_count FROM payments WHERE semester_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$result = $stmt->get_result();
$fees_count = ($result) ? $result->fetch_assoc()['fees_count'] : 0;

// Updated fetch for dropdown
$sql = "SELECT semester_ID, academic_year, semester_type, status FROM semester ORDER BY semester_ID DESC";
$result = $db->query($sql);
?>

<link rel="stylesheet" href=".//.//stylesheet/admin/dashboard.css">

<div class="admin-dashboard-body">
    <div class="admin-dashboard-con">
        <div class="report-summary-header">
            <span>Dashboard</span>
        </div>
        <div class="semester-select">
        <form method="GET" action="index.php" id="semesterForm">
    <input type="hidden" name="content" value="admin-index">
    <input type="hidden" name="admin" value="dashboard">
    <select class="form-select" style="width: min-content;" name="semester" id="semester" onchange="handleSemesterChange(this)">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $semester_id = $row['semester_ID'];
            $academic_year = $row['academic_year'];
            $semester_type = $row['semester_type'];
            $status = $row['status'];

            $selected = ($semester_id == $selected_semester) ? 'selected' : '';
$statusAttr = "data-status='" . strtolower($status) . "'";

echo "<option value='$semester_id' $selected $statusAttr>AY: $academic_year - $semester_type ($status)</option>";

        }
    } else {
        echo "<option value=''>No semesters available</option>";
    }
    ?>
</select>

</form>

<script>
function handleSemesterChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const isInactive = selectedOption.getAttribute('data-status') === 'inactive';

    if (isInactive) {
        // Reset to previous selection
        select.selectedIndex = [...select.options].findIndex(opt => opt.defaultSelected);
        // Show modal
        var myModal = new bootstrap.Modal(document.getElementById('inactiveSemesterModal'));
        myModal.show();
    } else {
        // Submit the form if active
        document.getElementById('semesterForm').submit();
    }
}
</script>

<!-- Inactive Semester Modal -->
<div class="modal fade" id="inactiveSemesterModal" tabindex="-1" aria-labelledby="inactiveSemesterLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-white text-dark">
        <h5 class="modal-title" id="inactiveSemesterLabel">Semester Not Active</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-dark">
        The semester you selected is currently <strong>not active</strong>. Please select an active term to proceed.
      </div>
      <div class="modal-footer bg-white">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Okay</button>
      </div>
    </div>
  </div>
</div>


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


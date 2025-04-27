<?php
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure $_SESSION['selected_semester'] is initialized as an array
if (!isset($_SESSION['selected_semester']) || !is_array($_SESSION['selected_semester'])) {
    $_SESSION['selected_semester'] = array();
}

// Identify user ID (admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

// Check if user_id is set properly
if (!$user_id) {
    die("User ID is not set correctly in the session.");
}

// Set semester from GET and store in session and cookie
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
    setcookie('selected_semester', $_GET['semester'], time() + (86400 * 30), "/");
}

// Determine selected semester from session or cookie, or fallback
if (isset($_SESSION['selected_semester'][$user_id]) && !empty($_SESSION['selected_semester'][$user_id])) {
    $selected_semester = $_SESSION['selected_semester'][$user_id];
} elseif (isset($_COOKIE['selected_semester']) && !empty($_COOKIE['selected_semester'])) {
    $selected_semester = $_COOKIE['selected_semester'];
    $_SESSION['selected_semester'][$user_id] = $selected_semester;
} else {
    // Fetch latest active semester
    $query = "SELECT semester_ID FROM semester WHERE status = 'active' ORDER BY semester_ID DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $selected_semester = ($result && $row = $result->fetch_assoc()) ? $row['semester_ID'] : null;
    if ($selected_semester) {
        $_SESSION['selected_semester'][$user_id] = $selected_semester;
        setcookie('selected_semester', $selected_semester, time() + (86400 * 30), "/");
    }
}

// Fetch all semesters for dropdown
$sql = "SELECT semester_ID, academic_year, semester_type, status FROM semester ORDER BY semester_ID DESC";
$allSemesters = $db->query($sql);
?>

<div class="semester-select">
    <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="semesterForm">
        <input type="hidden" name="content" value="<?php echo htmlspecialchars($_GET['content'] ?? ''); ?>">
        <input type="hidden" name="admin" value="<?php echo htmlspecialchars($_GET['admin'] ?? ''); ?>">
        <select class="form-select" style="width: min-content;" name="semester" id="semester" onchange="handleSemesterChange(this)">
    <option value="" <?php echo empty($selected_semester) ? 'selected' : ''; ?>>-- Select a semester --</option>
    <?php
    if ($allSemesters->num_rows > 0) {
        while ($row = $allSemesters->fetch_assoc()) {
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
</div>

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

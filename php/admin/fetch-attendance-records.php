<?php
session_start();
require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

if (isset($_GET['id_attendance'])) {
    $id_attendance = $_GET['id_attendance'];

    // First, get the penalty type from the attendances table
    $attendanceStmt = $db->prepare("
        SELECT penalty_type 
        FROM attendances 
        WHERE id_attendance = ?
    ");
    $attendanceStmt->bind_param("i", $id_attendance);
    $attendanceStmt->execute();
    $attendanceResult = $attendanceStmt->get_result();
    $attendanceData = $attendanceResult->fetch_assoc();
    $penalty_type = htmlspecialchars($attendanceData['penalty_type'] ?? 'None');

    // Get selected semester from session (make sure this is set correctly)
    $selected_semester = $_SESSION['selected_semester'][$_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student']] ?? null;
    
    if (!$selected_semester) {
        die("No semester selected in session");
    }

    // Modified query to filter by semester_ID (using "s" for string)
    $stmt = $db->prepare("
        SELECT s.id_student, s.lastname_student, s.firstname_student, s.year_student, 
               sa.date_attendance, sa.status_attendance, sa.Penalty_requirements, sa.status_attendance
        FROM student_attendance sa
        JOIN student s ON sa.id_student = s.id_student
        WHERE sa.id_attendance = ? AND s.semester_ID = ?
    ");
    $stmt->bind_param("is", $id_attendance, $selected_semester); // Changed to "is" (integer, string)
    
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id_student = htmlspecialchars($row['id_student']);
            $lastname = htmlspecialchars($row['lastname_student']);
            $firstname = htmlspecialchars($row['firstname_student']);
            $status = htmlspecialchars($row['status_attendance']);
            $penalty_requirements = htmlspecialchars($row['Penalty_requirements']);

            // Convert year_student to readable format
            $year_levels = [1 => "1st Year", 2 => "2nd Year", 3 => "3rd Year", 4 => "4th Year"];
            $year_student = $year_levels[$row['year_student']] ?? "Unknown";

            // Convert date_attendance to 12-hour format
            $date_attendance = date("F d, Y h:i A", strtotime($row['date_attendance']));

            // Badge for status
            $badgeClass = in_array($status, ['Cleared', 'Present']) ? 'bg-success' : 'bg-danger';
            
            // Determine what to display for penalty requirements
            $penalty_display = ($status == 'Present' || $penalty_requirements == 0) ? 'Cleared' : $penalty_requirements;

            
            // Action buttons
// Action buttons
$action_buttons = '';
if ($status == 'Absent' || $status == 'Cleared') {
    if ($status == 'Absent') {
        $action_buttons .= "
            <button class='btn btn-sm btn-success clear-penalty' 
                    data-student-id='{$id_student}'
                    data-attendance-id='{$id_attendance}'>
                Mark as Cleared
            </button>
        ";
    }
    $action_buttons .= "
        <button class='btn btn-sm btn-danger not-cleared-penalty' 
                data-student-id='{$id_student}'
                data-attendance-id='{$id_attendance}'>
            Not Cleared
        </button>
    ";
} else {
    $action_buttons = "No action needed";
}

echo "
<tr class='accordion-item'>
  <td>{$id_student}</td>
  <td>{$lastname}</td>
  <td>{$firstname}</td>
  <td>{$year_student}</td>
  <td>{$date_attendance}</td>
  <td><span class='badge {$badgeClass}'>{$status}</span></td>
  <td>
    <button class='btn btn-sm btn-outline-primary' type='button' data-bs-toggle='collapse' data-bs-target='#accordionRow{$id_student}' aria-expanded='false' aria-controls='accordionRow{$id_student}'>
      +
    </button>
  </td>
</tr>
<tr class='collapse accordion-collapse accordion-item' id='accordionRow{$id_student}' data-bs-parent='#accordionTable'>
  <td colspan='7'>
    <div class='card card-body bg-light'>
      <div class='row'>
        <div class='col-md-4'><strong>Penalty Type:</strong> {$penalty_type}</div>
        <div class='col-md-4'><strong>Penalty Requirement:</strong> {$penalty_display}</div>
        <div class='col-md-4'><strong>Action:</strong> {$action_buttons}</div>
      </div>
    </div>
  </td>
</tr>
";
        }
    } else {
        echo "<tr><td colspan='7' class='text-center'>No attendance records found.</td></tr>";
    }
}
?>

<script>
$(document).on('click', '.clear-penalty', function() {
    var studentId = $(this).data('student-id');
    var attendanceId = $(this).data('attendance-id');
    
    if (confirm("Are you sure you want to mark this penalty as cleared?")) {
        $.ajax({
            url: './php/admin/update-penalty.php',
            type: 'POST',
            data: {
                student_id: studentId,
                attendance_id: attendanceId,
                action: 'clear'
            },
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                alert("Error updating penalty status");
            }
        });
    }
});

$(document).on('click', '.not-cleared-penalty', function() {
    var studentId = $(this).data('student-id');
    var attendanceId = $(this).data('attendance-id');
    
    if (confirm("Are you sure you want to revert this penalty?")) {
        $.ajax({
            url: './php/admin/update-penalty.php',
            type: 'POST',
            data: {
                student_id: studentId,
                attendance_id: attendanceId,
                action: 'not_cleared'
            },
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                alert("Error updating penalty status");
            }
        });
    }
});
</script>

<script>
    // Handle accordion state changes
document.getElementById('attendanceBody').addEventListener('show.bs.collapse', function(e) {
    const targetRow = e.target.closest('tr');
    if (targetRow) {
        targetRow.style.display = "";
    }
});

document.getElementById('attendanceBody').addEventListener('hide.bs.collapse', function(e) {
    const targetRow = e.target.closest('tr');
    const searchInput = document.getElementById('searchInput');
    if (targetRow && searchInput.value) {
        // Only hide if there's an active search
        targetRow.style.display = "none";
    }
});
</script>
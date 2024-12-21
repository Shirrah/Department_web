<?php
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once "./php/db-conn.php";
$db = new Database();

// Get the user ID from the session (either admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

// Handle the semester selection from GET request and store it in session for this user
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    // Store the selected semester for the user in session
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
}

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Fetch attendance records for the event
    $attendance_stmt = $db->db->prepare("SELECT id_attendance, type_attendance, time_in, time_out FROM attendances WHERE id_event = ?");
    $attendance_stmt->bind_param("i", $event_id);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();

    // Store attendance records in an array
    $attendances = [];
    while ($attendance = $attendance_result->fetch_assoc()) {
        $attendances[] = $attendance;
    }

    // Fetch event name based on the event_id
    $event_stmt = $db->db->prepare("SELECT name_event FROM events WHERE id_event = ?");
    $event_stmt->bind_param("i", $event_id);
    $event_stmt->execute();
    $event_result = $event_stmt->get_result();
    $event = $event_result->fetch_assoc();
}

// Handle the semester selection from GET request and store it in session for this user
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    // Store the selected semester for the user in session
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
}

// Get the selected semester ID from the session
$semester_ID = isset($_SESSION['selected_semester'][$user_id]) ? $_SESSION['selected_semester'][$user_id] : null;

// Add attendance logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_attendance'])) {
    // Add attendance logic
    $type_attendance = $_POST['type_attendance'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'];

    // Insert into the attendances table
    $add_stmt = $db->db->prepare("INSERT INTO attendances (id_event, type_attendance, time_in, time_out) VALUES (?, ?, ?, ?)");
    $add_stmt->bind_param("isss", $event_id, $type_attendance, $time_in, $time_out);
    $add_stmt->execute();

    // Fetch the last inserted attendance ID
    $id_attendance = $db->db->insert_id;

    // Check if semester is selected
    if ($semester_ID) {
        // Fetch all student IDs filtered by the selected semester
        $student_stmt = $db->db->prepare("SELECT id_student FROM student WHERE semester_ID = ?");
        $student_stmt->bind_param("i", $semester_ID);
        $student_stmt->execute();
        $student_result = $student_stmt->get_result();

        // Insert attendance records for each student
        while ($student = $student_result->fetch_assoc()) {
            $insert_student_attendance = $db->db->prepare("INSERT INTO student_attendance (id_attendance, id_student, semester_ID, date_attendance, status_attendance) VALUES (?, ?, ?, NOW(), 'Absent')");
$insert_student_attendance->bind_param("iis", $id_attendance, $student['id_student'], $semester_ID);
$insert_student_attendance->execute();

        }
    } else {
        // Handle the case where no semester is selected
        echo "Error: No semester selected. Please select a semester and try again.";
    }

    // Redirect to refresh the page
    header("Location: ?content=admin-index&admin=attendance-records&id=$event_id");
    exit();
}

// Handle editing attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_attendance'])) {
    $id_attendance = $_POST['id_attendance'];
    $type_attendance = $_POST['type_attendance'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'];

    $edit_stmt = $db->db->prepare("UPDATE attendances SET type_attendance = ?, time_in = ?, time_out = ? WHERE id_attendance = ?");
    $edit_stmt->bind_param("sssi", $type_attendance, $time_in, $time_out, $id_attendance);
    $edit_stmt->execute();

    // Refresh the page to see changes
    header("Location: ?content=admin-index&admin=attendance-records&id=$event_id");
    exit();
}

// Handle deleting attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_attendance'])) {
    $id_attendance = $_POST['id_attendance'];

    // Delete from the attendances table
    $delete_stmt = $db->db->prepare("DELETE FROM attendances WHERE id_attendance = ?");
    $delete_stmt->bind_param("i", $id_attendance);
    $delete_stmt->execute();

    // Delete from the student_attendance table
    $delete_student_attendance_stmt = $db->db->prepare("DELETE FROM student_attendance WHERE id_attendance = ?");
    $delete_student_attendance_stmt->bind_param("i", $id_attendance);
    $delete_student_attendance_stmt->execute();

    // Refresh the page to see changes
    header("Location: ?content=admin-index&admin=attendance-records&id=$event_id");
    exit();
}
?>


<link rel="stylesheet" href=".//.//stylesheet/admin/admin-attendance.css">

<div class="attendance-management-con">
    <div class="title">
        <h3>Attendances for <?php echo htmlspecialchars($event['name_event']); ?></h3>
        <button class="back-button" onclick="window.location.href='?content=admin-index&admin=event-management&admin_events=admin-events'">Back</button>
        <button class="add-button" onclick="document.getElementById('addModal').style.display='block'">Add Attendance</button>
    </div>

    <div class="attendance-list-con">
    <?php if (!empty($attendances)): ?>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Attendance Status</th>
                    <th>Attendance Timer</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendances as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['type_attendance']); ?></td>
                        <td><?php echo date("g:i a", strtotime($record['time_in'])); ?></td>
                        <td><?php echo date("g:i a", strtotime($record['time_out'])); ?></td>
                        <td></td>
                        <td></td>
                        <td>
                        <button onclick="editAttendance(<?php echo $record['id_attendance']; ?>, '<?php echo htmlspecialchars($record['type_attendance']); ?>', '<?php echo htmlspecialchars($record['time_in']); ?>', '<?php echo htmlspecialchars($record['time_out']); ?>')">Edit</button>

                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="id_attendance" value="<?php echo $record['id_attendance']; ?>">
                                <button type="submit" name="delete_attendance" onclick="return confirm('Are you sure you want to delete this attendance?');">Delete</button>
                            </form>
                            <button type="button" onclick="showAttendance(<?php echo $record['id_attendance']; ?>, '<?php echo htmlspecialchars($event['name_event']); ?>', '<?php echo htmlspecialchars($record['type_attendance']); ?>')">Show records</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No attendance records found for this event.</p>
    <?php endif; ?>
</div>

</div>

<!-- Show Attendance Modal -->
<div id="showModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('showModal').style.display='none'">&times;</span>
        <h2 id="attendance-header">Students Present</h2>
        <div id="attendance-details">
            <!-- Student details will be populated here dynamically -->
        </div>
    </div>
</div>

<script>
function showAttendance(id_attendance, event_name, event_type) {
    // Update the modal header with the event name and type
    const attendanceHeader = document.getElementById('attendance-header');
    attendanceHeader.innerHTML = `Students Present for ${event_name} (${event_type})`;

    // Use AJAX to fetch the students for this attendance
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "php/admin/fetch_attendance_students.php?id_attendance=" + id_attendance, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            const response = xhr.responseText;
            const attendanceDetails = document.getElementById('attendance-details');
            attendanceDetails.innerHTML = response;

            // Display the modal
            document.getElementById('showModal').style.display = 'block';
        }
    };
    xhr.send();
}
</script>



<!-- Add Attendance Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2>Add Attendance</h2>
        <form action="" method="post">
            <input type="hidden" name="id_event" value="<?php echo $event_id; ?>">
            <label for="type_attendance">Attendance Type:</label>
            <input type="text" name="type_attendance" required>
            <label for="time_in">Time In:</label>
            <input type="time" name="time_in" required>
            <label for="time_out">Time Out:</label>
            <input type="time" name="time_out" required>
            <button type="submit" name="add_attendance">Add Attendance</button>
        </form>
    </div>
</div>

<!-- Edit Attendance Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h2>Edit Attendance</h2>
        <form action="" method="post">
            <input type="hidden" name="id_attendance" id="edit_id_attendance">
            <label for="edit_type_attendance">Attendance Type</label>
                <select name="type_attendance" id="edit_type_attendance" required>
                    <option value="" disabled selected>Select Attendance Type</option>
                    <option value="IN">IN</option>
                    <option value="OUT">OUT</option>
                    <option value="SA">SA(Surprise Attendance)</option>
                </select>
            <label for="edit_time_in">Time In:</label>
            <input type="time" name="time_in" id="edit_time_in" required>
            <label for="edit_time_out">Time Out:</label>
            <input type="time" name="time_out" id="edit_time_out" required>
            <button type="submit" name="edit_attendance">Update Attendance</button>
        </form>
    </div>
</div>

<script>
function editAttendance(id_attendance, type_attendance, time_in, time_out) {
    // Populate the edit modal fields
    document.getElementById('edit_id_attendance').value = id_attendance;
    document.getElementById('edit_type_attendance').value = type_attendance;
    document.getElementById('edit_time_in').value = time_in;
    document.getElementById('edit_time_out').value = time_out;

    // Display the edit modal
    document.getElementById('editModal').style.display = 'block';
}
</script>

<?php
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once "./php/db-conn.php";
$db = new Database();

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Fetch attendance records for the event
    $attendance_stmt = $db->db->prepare("SELECT id_attendance, type_attendance, time_in, time_out, fine_amount FROM attendances WHERE id_event = ?");
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

// Handle adding, editing, and deleting attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_attendance'])) {
        // Add attendance logic
        $type_attendance = $_POST['type_attendance'];
        $time_in = $_POST['time_in'];
        $time_out = $_POST['time_out'];
        $fine_amount = $_POST['fine_amount']; // Capture fine_amount

        // Insert into attendances table
        $add_stmt = $db->db->prepare("INSERT INTO attendances (id_event, type_attendance, time_in, time_out, fine_amount) VALUES (?, ?, ?, ?, ?)");
        $add_stmt->bind_param("issss", $event_id, $type_attendance, $time_in, $time_out, $fine_amount);
        $add_stmt->execute();

        // Fetch the last inserted attendance ID
        $id_attendance = $db->db->insert_id;

        // Fetch all student IDs and insert them into student_attendances
        $student_stmt = $db->db->prepare("SELECT id_student FROM student");
        $student_stmt->execute();
        $student_result = $student_stmt->get_result();

        while ($student = $student_result->fetch_assoc()) {
            $insert_student_attendance = $db->db->prepare("INSERT INTO student_attendance (id_attendance, id_student, date_attendance, status_attendance, fine_amount) VALUES (?, ?, NOW(), 'Absent', ?)");
            $insert_student_attendance->bind_param("iis", $id_attendance, $student['id_student'], $fine_amount);
            $insert_student_attendance->execute();
        }
    } elseif (isset($_POST['edit_attendance'])) {
        // Edit attendance logic
        $id_attendance = $_POST['id_attendance'];
        $type_attendance = $_POST['type_attendance'];
        $time_in = $_POST['time_in'];
        $time_out = $_POST['time_out'];
        $fine_amount = $_POST['fine_amount']; // Capture fine_amount

        $edit_stmt = $db->db->prepare("UPDATE attendances SET type_attendance = ?, time_in = ?, time_out = ?, fine_amount = ? WHERE id_attendance = ?");
        $edit_stmt->bind_param("ssssi", $type_attendance, $time_in, $time_out, $fine_amount, $id_attendance);
        $edit_stmt->execute();
    } elseif (isset($_POST['delete_attendance'])) {
        // Delete attendance logic
        $id_attendance = $_POST['id_attendance'];
    // Delete from the attendances table
    $delete_stmt = $db->db->prepare("DELETE FROM attendances WHERE id_attendance = ?");
    $delete_stmt->bind_param("i", $id_attendance);
    $delete_stmt->execute();

    // Delete from the student_attendance table
    $delete_student_attendance_stmt = $db->db->prepare("DELETE FROM student_attendance WHERE id_attendance = ?");
    $delete_student_attendance_stmt->bind_param("i", $id_attendance);
    $delete_student_attendance_stmt->execute();
    }

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
            <div class="attendance-table">
                <div class="attendance-body">
                    <?php foreach ($attendances as $record): ?>
                        <div class="attendance-row">
                            <span><?php echo htmlspecialchars($record['type_attendance']); ?></span>
                            <span>Time In: <?php echo date("g:i a", strtotime($record['time_in'])); ?></span>
                            <span>Time Out: <?php echo date("g:i a", strtotime($record['time_out'])); ?></span>
                            <span>Fine Amount: ₱<?php echo htmlspecialchars($record['fine_amount']); ?></span>
                            <span>
                                <button onclick="editAttendance(<?php echo $record['id_attendance']; ?>, '<?php echo htmlspecialchars($record['type_attendance']); ?>', '<?php echo htmlspecialchars($record['time_in']); ?>', '<?php echo htmlspecialchars($record['time_out']); ?>', '<?php echo htmlspecialchars($record['fine_amount']); ?>')">Edit</button>
                                <form action="" method="post" style="display:inline;">
                                    <input type="hidden" name="id_attendance" value="<?php echo $record['id_attendance']; ?>">
                                    <button type="submit" name="delete_attendance" onclick="return confirm('Are you sure you want to delete this attendance?');">Delete</button>
                                </form>
                                <button type="button" onclick="showAttendance(<?php echo $record['id_attendance']; ?>, '<?php echo htmlspecialchars($event['name_event']); ?>', '<?php echo htmlspecialchars($record['type_attendance']); ?>')">Show records</button>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
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
    xhr.open("GET", "http://localhost/Department_web/php/admin/fetch_attendance_students.php?id_attendance=" + id_attendance, true);
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
            <label for="fine_amount">Fine Amount:</label>
            <input type="text" name="fine_amount" required>
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
            <label for="edit_type_attendance">Attendance Type:</label>
            <input type="text" name="type_attendance" id="edit_type_attendance" required>
            <label for="edit_time_in">Time In:</label>
            <input type="time" name="time_in" id="edit_time_in" required>
            <label for="edit_time_out">Time Out:</label>
            <input type="time" name="time_out" id="edit_time_out" required>
            <label for="edit_fine_amount">Fine Amount:</label>
            <input type="text" name="fine_amount" id="edit_fine_amount" required>
            <button type="submit" name="edit_attendance">Edit Attendance</button>
        </form>
    </div>
</div>

<script>
function editAttendance(id_attendance, type_attendance, time_in, time_out, fine_amount) {
    // Populate the edit modal fields
    document.getElementById('edit_id_attendance').value = id_attendance;
    document.getElementById('edit_type_attendance').value = type_attendance;
    document.getElementById('edit_time_in').value = time_in;
    document.getElementById('edit_time_out').value = time_out;
    document.getElementById('edit_fine_amount').value = fine_amount;

    // Display the edit modal
    document.getElementById('editModal').style.display = 'block';
}
</script>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>

<style>
/* Existing CSS code... */
.attendance-management-con {
    width: 100%;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.attendance-management-con h3 {
    font-family: Arial, sans-serif;
    color: #333;
    margin-bottom: 15px;
}

.back-button,
.add-button {
    background-color: #f94316;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    margin-bottom: 15px;
    font-family: Arial, sans-serif;
}

.back-button:hover,
.add-button:hover {
    background-color: rgb(255, 164, 164);
}

.attendance-list-con {
    margin-top: 10px;
}

.attendance-table {
    width: 100%;
    border-collapse: collapse;
}

.attendance-header,
.attendance-row {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    border: 1px solid #ddd;
}

.attendance-body {
    margin-top: 10px;
}

.attendance-row {
    background-color: #fff;
}

.attendance-row:hover {
    background-color: #f1f1f1;
}

.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0, 0, 0); /* Fallback color */
    background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 80%; /* Could be more or less, depending on screen size */
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>
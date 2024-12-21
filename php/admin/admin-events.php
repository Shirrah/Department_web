<?php
// Start the session
$error = '';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
require_once "././php/db-conn.php";
$db = new Database();
    

// Get the user ID from the session (either admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

// Handle the semester selection from GET request and store it in session for this user
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    // Store the selected semester for the user in session
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
}

// Handle event creation
if (isset($_POST['create_event'])) {
    $name_event = $_POST['name_event'];
    $date_event = $_POST['date_event'];
    $event_start_time = $_POST['event_start_time'];
    $event_end_time = $_POST['event_end_time'];
    $event_desc = $_POST['event_desc'];

    // Get the selected semester ID from the session
    $semester_ID = isset($_SESSION['selected_semester'][$user_id]) ? $_SESSION['selected_semester'][$user_id] : null;

    // Validate that the semester ID exists
    if (empty($semester_ID)) {
        $error = "No semester selected. Please select a semester to create an event.";
    } else {
        // Insert into the events table
        $stmt = $db->db->prepare("INSERT INTO events (name_event, date_event, event_start_time,event_end_time, event_desc, semester_ID) VALUES (?,?,?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name_event, $date_event, $event_start_time, $event_end_time, $event_desc, $semester_ID);

        if ($stmt->execute()) {
            // Get the last inserted event ID
            $event_id = $db->db->insert_id;

            // Insert attendances if they were submitted
            if (!empty($_POST['type_attendance'])) {
                $attendance_stmt = $db->db->prepare("INSERT INTO attendances (id_event, type_attendance, time_in, time_out) VALUES (?, ?, ?, ?)");
                foreach ($_POST['type_attendance'] as $key => $type) {
                    $time_in = $_POST['time_in'][$key];
                    $time_out = $_POST['time_out'][$key];

                    // Bind the parameters
                    $attendance_stmt->bind_param("isss", $event_id, $type, $time_in, $time_out);
                    $attendance_stmt->execute();

                    // Fetch the last inserted attendance ID
                    $id_attendance = $db->db->insert_id;

                    // Fetch all student IDs and insert them into student_attendance
                    $student_stmt = $db->db->prepare("SELECT id_student FROM student");
                    $student_stmt->execute();
                    $student_result = $student_stmt->get_result();

                    while ($student = $student_result->fetch_assoc()) {
                        // Insert student attendance with semester_ID
                        $insert_student_attendance = $db->db->prepare("INSERT INTO student_attendance (id_attendance, id_student, semester_ID, date_attendance, status_attendance) VALUES (?, ?, ?, NOW(), 'Absent')");
                        $insert_student_attendance->bind_param("iis", $id_attendance, $student['id_student'], $semester_ID); // Include semester_ID
                        $insert_student_attendance->execute();                    
                    }

                }
            }

            // Redirect or display success message
            echo "<script>window.location.href='';</script>";
        } else {
            $error = "Error creating event: " . $stmt->error;
        }
    }
}



// Handle showing attendances
if (isset($_GET['show_attendances'])) {
    $event_id = $_GET['id'];
    $attendance_sql = "SELECT type_attendance, time_in, time_out FROM attendances WHERE id_event = ?";
    $attendance_stmt = $db->db->prepare($attendance_sql);
    $attendance_stmt->bind_param("i", $event_id);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();
    
    // Store attendances in an array
    $attendances = [];
    while ($attendance = $attendance_result->fetch_assoc()) {
        $attendances[] = $attendance;
    }
}

// Handle event deletion
if (isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'];

    // Step 1: Get the ids of attendances associated with the event
    $attendance_stmt = $db->db->prepare("SELECT id_attendance FROM attendances WHERE id_event = ?");
    $attendance_stmt->bind_param("i", $event_id);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();

    // Collecting all id_attendance into an array
    $attendance_ids = [];
    while ($row = $attendance_result->fetch_assoc()) {
        $attendance_ids[] = $row['id_attendance'];
    }

    // Step 2: Delete associated attendances
    $delete_attendance_stmt = $db->db->prepare("DELETE FROM attendances WHERE id_event = ?");
    $delete_attendance_stmt->bind_param("i", $event_id);
    $delete_attendance_stmt->execute();

    // Step 3: Delete from student_attendance for each id_attendance
    if (!empty($attendance_ids)) {
        // Prepare the statement for deleting from student_attendance
        $delete_student_attendance_stmt = $db->db->prepare("DELETE FROM student_attendance WHERE id_attendance = ?");
        
        // Loop through each id_attendance and execute the delete
        foreach ($attendance_ids as $id_attendance) {
            $delete_student_attendance_stmt->bind_param("i", $id_attendance);
            $delete_student_attendance_stmt->execute();
        }
    }

    // Step 4: Delete the event after attendances are deleted
    $delete_event_stmt = $db->db->prepare("DELETE FROM events WHERE id_event = ?");
    $delete_event_stmt->bind_param("i", $event_id);

    if ($delete_event_stmt->execute()) {
        // Redirect or display success message
        echo "<script>window.location.href='';</script>";
    } else {
        $error = "Error deleting event: " . $delete_event_stmt->error;
    }
}

// Handle event editing
if (isset($_POST['edit_event'])) {
    $event_id = $_POST['event_id'];
    $name_event = $_POST['name_event'];
    $date_event = $_POST['date_event'];
    $event_desc = $_POST['event_desc'];

    // Update the event in the database
    $stmt = $db->db->prepare("UPDATE events SET name_event = ?, date_event = ?, event_desc = ? WHERE id_event = ?");
    $stmt->bind_param("sssi", $name_event, $date_event, $event_desc, $event_id);

    if ($stmt->execute()) {
        // Success, reload the page or redirect
        echo "<script>window.location.href='';</script>";
    } else {
        $error = "Error updating event: " . $stmt->error;
    }
}

// Fetch attendance records when editing an event
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Fetch attendance records associated with the event
    $attendance_sql = "SELECT id_attendance, type_attendance, time_in, time_out FROM attendances WHERE id_event = ?";
    $attendance_stmt = $db->db->prepare($attendance_sql);
    $attendance_stmt->bind_param("i", $event_id);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();

    // Store attendance records in an array
    $attendances = [];
    while ($attendance = $attendance_result->fetch_assoc()) {
        $attendances[] = $attendance;
    }
}


// Initialize the selected semester variable
$selected_semester = isset($_SESSION['selected_semester'][$user_id]) ? $_SESSION['selected_semester'][$user_id] : '';

// Initialize pagination variables
$limit = 7; // Number of records per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : (isset($_SESSION['page']) ? $_SESSION['page'] : 1); // Default to page 1 if not set
$_SESSION['page'] = $page; // Store page number in session
$offset = ($page - 1) * $limit;

// Fetch total records and calculate total pages for the selected semester
$countQuery = "SELECT COUNT(*) as total FROM events WHERE semester_ID = ?";
$stmt = $db->db->prepare($countQuery);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$totalResult = $stmt->get_result();
$row = $totalResult->fetch_assoc();
$totalRecords = $row ? (int)$row['total'] : 0; // Ensure totalRecords is assigned
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;

// Fetch events for the current page for the selected semester
if (isset($_GET['show_all']) && $_GET['show_all'] == 'true') {
    // Query to fetch all events for the selected semester (no pagination)
    $query = "SELECT id_event, name_event, date_event, event_start_time, event_end_time, event_desc FROM events WHERE semester_ID = ?";
    $stmt = $db->db->prepare($query);
    $stmt->bind_param("s", $selected_semester);
    $stmt->execute();
    $events = $stmt->get_result();
    $totalPages = 1; // Only one page when showing all events
    $page = 1; // Reset to the first page
} else {
    // Query to fetch paginated events for the selected semester
    $query = "SELECT id_event, name_event, date_event,event_start_time, event_end_time, event_desc
              FROM events WHERE semester_ID = ? LIMIT $limit OFFSET $offset";
    $stmt = $db->db->prepare($query);
    $stmt->bind_param("s", $selected_semester);
    $stmt->execute();
    $events = $stmt->get_result();
}
?>

<link rel="stylesheet" href=".//.//stylesheet/admin/admin-events.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="event-management-con">
    <div class="event-management-header">
        <span>Manage Events</span>
        <div class="location">
            <a href="?content=admin-index&admin=dashboard">Dashboard</a>
            /
            <span>Events & Fees</span>
            /
            <span>Manage Events</span>
        </div>
    </div>

    <div class="list-events-con">
        <?php if ($events->num_rows > 0): ?>
            <table class="events-table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Event Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($event = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $event['name_event']; ?></td>
                            <td><?php echo date("F j, Y", strtotime($event['date_event'])); ?></td>
                            <td><?php echo date("h:i A", strtotime($event['event_start_time'])); ?></td>
                            <td><?php echo date("h:i A", strtotime($event['event_end_time'])); ?></td>
                            <td>
                                <div class="dropdown-content">
                                    <a href="#" onclick="openEditModal(
                                        '<?php echo $event['id_event']; ?>',
                                        '<?php echo addslashes($event['name_event']); ?>',
                                        '<?php echo date('Y-m-d', strtotime($event['date_event'])); ?>',
                                    )"><i class='fas fa-edit'></i></a>
                                    <a href="#" onclick="confirmDelete(<?php echo $event['id_event']; ?>)"><i class='fas fa-trash'></i></a>
                                    <a href="?content=admin-index&admin=attendance-records&id=<?php echo $event['id_event']; ?>"><i class="fas fa-database"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No events found.</p>
        <?php endif; ?>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination">
        <button <?php if($page <= 1) echo 'disabled'; ?> onclick="navigateToPage(1)">First</button>
        <button <?php if($page <= 1) echo 'disabled'; ?> onclick="navigateToPage(<?php echo $page - 1; ?>)">Previous</button>
        <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
        <button <?php if($page >= $totalPages) echo 'disabled'; ?> onclick="navigateToPage(<?php echo $page + 1; ?>)">Next</button>
        <button <?php if($page >= $totalPages) echo 'disabled'; ?> onclick="navigateToPage(<?php echo $totalPages; ?>)">Last</button>
    </div>

    <script>
        function navigateToPage(page) {
            window.location.href = '?content=admin-index&admin=event-management&page=' + page;
        }
    </script>

    <button id="create-event-btn">Create event</button>
    <button class="start-attendance-btn"><a href="./Barcode" target="_blank">Start Attendance</a></button>
</div>

<!-- Edit Event Modal -->
<div id="edit-event-modal" class="modal">
    <div class="modal-content">
        <span class="close-edit">&times;</span>
        <h3>Edit Event</h3>
        <form method="POST" action="">
            <input type="hidden" name="event_id" id="edit-event-id">
            
            <label for="name_event">Event Name</label>
            <input type="text" id="edit-name_event" name="name_event" required>

            <label for="date_event">Event Date</label>
            <input type="date" id="edit-date_event" name="date_event" required>

            <button type="submit" name="edit_event">Update Event</button>
        </form>
    </div>
</div>

<script>
    // Open edit modal and populate it with event data
    function openEditModal(eventId, name, date, desc) {
        // Set values in the modal form
        document.getElementById('edit-event-id').value = eventId;
        document.getElementById('edit-name_event').value = name;
        document.getElementById('edit-date_event').value = date;
        
        // Show the modal
        document.getElementById('edit-event-modal').style.display = 'block';
    }

    // Close edit modal
    document.getElementsByClassName("close-edit")[0].onclick = function() {
        document.getElementById('edit-event-modal').style.display = "none";
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        const modal = document.getElementById('edit-event-modal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>


<!-- Deleting event and attendances -->
<form id="delete-event-form" method="POST" action="">
    <input type="hidden" name="event_id" id="delete-event-id">
    <button type="submit" name="delete_event" style="display: none;"></button>
</form>

<script>
// Confirm deletion
function confirmDelete(eventId) {
    if (confirm('Are you sure you want to delete this event? \nNote that this will delete all the data assiocated with it!')) {
        // Set the event ID in the hidden form and submit
        document.getElementById('delete-event-id').value = eventId;
        document.querySelector('#delete-event-form button').click();
    }
}
</script>


<!-- Modal Form -->
<div id="create-event-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Create New Event</h3>
        <form method="POST" action="">
            <label for="name_event">Event Name</label>
            <input type="text" id="name_event" name="name_event" required>

            <label for="date_event">Event Date</label>
            <input type="date" id="date_event" name="date_event" required>

            <label for="event_start_time">Start Time: </label>
            <input type="time" id="event_start_timet" name="event_start_time" required>

            <label for="event_end_time">End Time: </label>
            <input type="time" id="event_end_timet" name="event_end_time" required>

            <label for="event_desc">Event Description</label>
            <textarea id="event_desc" name="event_desc" required></textarea>

            <button type="button" id="add-attendance-btn">Add Attendance</button>
            
            <div id="attendance-container" style="display: none;">
            <div class="attendance-entry">

                <label for="type_attendance">Attendance Type</label>
                <select name="type_attendance[]" required>
                    <option value="" disabled selected>Select Attendance Type</option>
                    <option value="IN">IN</option>
                    <option value="OUT">OUT</option>
                    <option value="SA">SA(Surprise Attendance)</option>
                </select>
                
                <label for="time_in">Time In</label>
                <input type="time" name="time_in[]" required>

                <label for="time_out">Time Out</label>
                <input type="time" name="time_out[]" required>
            </div>
        </div>

            <button type="button" id="add-more-attendance-btn" style="display: none;">Add More Attendance</button>
            <button type="submit" name="create_event">Create Event</button>
        </form>
    </div>
</div>

<!-- JavaScript for modal functionality and dynamic attendance fields -->
<script>
    // Modal functionality
    const modal = document.getElementById("create-event-modal");
    const btn = document.getElementById("create-event-btn");
    const span = document.getElementsByClassName("close")[0];

    btn.onclick = function() {
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Show attendance container
    document.getElementById('add-attendance-btn').onclick = function() {
        document.getElementById('attendance-container').style.display = "block"; // Show the attendance container
        document.getElementById('add-more-attendance-btn').style.display = "inline"; // Show the add more button
    }

// Dynamic attendance fields
document.getElementById('add-more-attendance-btn').onclick = function() {
    const container = document.getElementById('attendance-container');
    const entry = document.createElement('div');
    entry.className = 'attendance-entry';

    entry.innerHTML = `
        <label for="type_attendance">Attendance Type</label>
        <select name="type_attendance[]" required>
        <option value="" disabled selected>Select Attendance Type</option>
        <option value="IN">IN</option>
        <option value="OUT">OUT</option>
        <option value="SA">SA(Surprise Attendance)</option>
        </select>
        
        <label for="time_in">Time In</label>
        <input type="time" name="time_in[]" required>

        <label for="time_out">Time Out</label>
        <input type="time" name="time_out[]" required>
    `;
    
    container.appendChild(entry);
}


    // Dropdown functionality
    document.querySelectorAll('.dropdown').forEach(function(dropdown) {
        dropdown.querySelector('.dots').addEventListener('click', function() {
            dropdown.classList.toggle('show');
        });
    });

    window.onclick = function(event) {
        if (!event.target.matches('.dots')) {
            document.querySelectorAll('.dropdown.show').forEach(function(dropdown) {
                dropdown.classList.remove('show');
            });
        }
    };
</script>

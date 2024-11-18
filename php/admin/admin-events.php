<?php

// Include the database connection
require_once "././php/db-conn.php";
$db = new Database();

// Start the session
$error = '';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
require_once "././php/db-conn.php";
$db = new Database();

// Handle event creation
if (isset($_POST['create_event'])) {
    $name_event = $_POST['name_event'];
    $date_event = $_POST['date_event'];
    $event_desc = $_POST['event_desc'];

    // Insert into the events table
    $stmt = $db->db->prepare("INSERT INTO events (name_event, date_event, event_desc) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name_event, $date_event, $event_desc);

    if ($stmt->execute()) {
        // Get the last inserted event ID
        $event_id = $db->db->insert_id;

        // Insert attendances if they were submitted
        if (!empty($_POST['type_attendance'])) {
            $attendance_stmt = $db->db->prepare("INSERT INTO attendances (id_event, type_attendance, time_in, time_out, fine_amount) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['type_attendance'] as $key => $type) {
                $time_in = $_POST['time_in'][$key];
                $time_out = $_POST['time_out'][$key];
                $fine_amount = $_POST['fine_amount'][$key]; // Get fine amount from form input

                // Bind the parameters
                $attendance_stmt->bind_param("issss", $event_id, $type, $time_in, $time_out, $fine_amount);
                $attendance_stmt->execute();

                // Fetch the last inserted attendance ID
                $id_attendance = $db->db->insert_id;

                // Fetch all student IDs and insert them into student_attendance
                $student_stmt = $db->db->prepare("SELECT id_student FROM student");
                $student_stmt->execute();
                $student_result = $student_stmt->get_result();

                while ($student = $student_result->fetch_assoc()) {
                    $insert_student_attendance = $db->db->prepare("INSERT INTO student_attendance (id_attendance, id_student, date_attendance, status_attendance, fine_amount) VALUES (?, ?, NOW(), 'Absent', ?)");
                    $insert_student_attendance->bind_param("iis", $id_attendance, $student['id_student'], $fine_amount);
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


// Fetch events from the database
$sql = "SELECT id_event, name_event, date_event, event_desc, date_created FROM events";
$events = $db->db->query($sql);
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
            <?php while($event = $events->fetch_assoc()): ?>
    <div class="event">
        <div class="name-date">
            <h4><?php echo $event['name_event']; ?></h4>
            <p><?php echo date("F j, Y", strtotime($event['date_event'])); ?></p>
        </div>
        <div class="date-created">
            <p>Date created: <?php echo date("F j, Y g:i a", strtotime($event['date_created'])); ?></p>
        </div>
        <div class="action-btn">
                <div class="dropdown-content">
                    <!-- Edit Button: passes event data to the modal -->
                    <a href="#" onclick="openEditModal(
                        '<?php echo $event['id_event']; ?>',
                        '<?php echo addslashes($event['name_event']); ?>',
                        '<?php echo date('Y-m-d', strtotime($event['date_event'])); ?>',
                        '<?php echo addslashes($event['event_desc']); ?>'
                    )"><i class='fas fa-edit'></i></a>
                    <a href="#" onclick="confirmDelete(<?php echo $event['id_event']; ?>)"><i class='fas fa-trash'></i></a>
                    <a href="?content=admin-index&admin=attendance-records&id=<?php echo $event['id_event']; ?>"><i class="fas fa-database"></i>
                    </a>
                </div>
        </div>
    </div>
<?php endwhile; ?>

        <?php else: ?>
            <p>No events found.</p>
        <?php endif; ?>
        <button id="create-event-btn">Create event</button>
        <button class="start-attendance-btn"><a href="./Barcode" target="_blank">Start Attendance</a></button>
    </div>

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

            <label for="event_desc">Event Description</label>
            <textarea id="edit-event_desc" name="event_desc" required></textarea>

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
        document.getElementById('edit-event_desc').value = desc;
        
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

            <label for="event_desc">Event Description</label>
            <textarea id="event_desc" name="event_desc" required></textarea>

            <button type="button" id="add-attendance-btn">Add Attendance</button>
            
            <div id="attendance-container" style="display: none;">
    <div class="attendance-entry">
        <label for="type_attendance">Attendance Type</label>
        <input type="text" name="type_attendance[]" required>
        
        <label for="time_in">Time In</label>
        <input type="time" name="time_in[]" required>

        <label for="time_out">Time Out</label>
        <input type="time" name="time_out[]" required>

        <label for="fine_amount">Fine Amount</label>
        <input type="number" step="0.01" name="fine_amount[]" placeholder="0.00" required>
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
        <input type="text" name="type_attendance[]" required>
        
        <label for="time_in">Time In</label>
        <input type="time" name="time_in[]" required>

        <label for="time_out">Time Out</label>
        <input type="time" name="time_out[]" required>

        <label for="fine_amount">Fine Amount</label>
        <input type="number" step="0.01" name="fine_amount[]" placeholder="0.00" required>
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

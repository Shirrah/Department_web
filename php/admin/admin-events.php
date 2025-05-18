<?php
// Start the session
$error = '';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;
    

// Get the user ID from the session (either admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

// Handle the semester selection from GET request and store it in session for this user
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    // Store the selected semester for the user in session
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
}


// Handle event creation
if (isset($_POST['create_event'])) {
    $name_event = htmlspecialchars($_POST['name_event'], ENT_QUOTES, 'UTF-8');
    $date_event = htmlspecialchars($_POST['date_event'], ENT_QUOTES, 'UTF-8');
    $event_start_time = htmlspecialchars($_POST['event_start_time'], ENT_QUOTES, 'UTF-8');
    $event_end_time = htmlspecialchars($_POST['event_end_time'], ENT_QUOTES, 'UTF-8');
    $event_desc = htmlspecialchars($_POST['event_desc'], ENT_QUOTES, 'UTF-8');

    // Get user ID from session (admin or student)
    $user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

    // Get the selected semester ID from the session
    $semester_ID = $_SESSION['selected_semester'][$user_id] ?? null;

    // Validate semester ID
    if (empty($semester_ID)) {
        $error = "No semester selected. Please select a semester to create an event.";
    } else {
        // Insert into the events table including created_by
        $stmt = $db->prepare("INSERT INTO events (name_event, date_event, event_start_time, event_end_time, event_desc, semester_ID, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name_event, $date_event, $event_start_time, $event_end_time, $event_desc, $semester_ID, $user_id);

        if ($stmt->execute()) {
            // Redirect or display success message
            echo "<script>window.location.href='';</script>";
        } else {
            $error = "Error creating event: " . $stmt->error;
        }
    }
}

// Handle event update
if (isset($_POST['update_event'])) {
  $event_id = $_POST['edit_event_id'];
  $name_event = htmlspecialchars($_POST['edit_name_event'], ENT_QUOTES, 'UTF-8');
  $date_event = htmlspecialchars($_POST['edit_date_event'], ENT_QUOTES, 'UTF-8');
  $event_start_time = htmlspecialchars($_POST['edit_event_start_time'], ENT_QUOTES, 'UTF-8');
  $event_end_time = htmlspecialchars($_POST['edit_event_end_time'], ENT_QUOTES, 'UTF-8');
  $event_desc = htmlspecialchars($_POST['edit_event_desc'], ENT_QUOTES, 'UTF-8');

  // Get user ID from session (admin or student)
  $user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

  // Prepare update query
  $stmt = $db->prepare("UPDATE events SET name_event = ?, date_event = ?, event_start_time = ?, event_end_time = ?, event_desc = ? WHERE id_event = ? AND created_by = ?");
  $stmt->bind_param("ssssssi", $name_event, $date_event, $event_start_time, $event_end_time, $event_desc, $event_id, $user_id);

  if ($stmt->execute()) {
      // Redirect to refresh the page and show updated event
      echo "<script>window.location.href='';</script>";
  } else {
      $error = "Error updating event: " . $stmt->error;
  }
}


// Handle adding attendance records
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_event'], $_POST['type_attendance'], $_POST['penalty_type'])) {
    $id_event = $_POST['id_event'];
    $type_attendance = $_POST['type_attendance'];
    $penalty_type = $_POST['penalty_type'];
    $penalty_requirements = $_POST['penalty_requirements'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $attendance_status = "Pending"; // Default status

    // Get the user ID from session (either admin or student)
    $user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];
    $semester_ID = $_SESSION['selected_semester'][$user_id] ?? ''; // Get selected semester

    // Validate input fields
    if (empty($id_event) || empty($type_attendance) || empty($penalty_type)) {
        $error = "All fields are required.";
    } else {
        try {
            // Start transaction
            $db->begin_transaction();

            // Insert the attendance record into the database
            $stmt = $db->prepare("INSERT INTO attendances (id_event, type_attendance, attendance_status, penalty_type, penalty_requirements, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $id_event, $type_attendance, $attendance_status, $penalty_type, $penalty_requirements, $start_time, $end_time);

            if ($stmt->execute()) {
                // Get the last inserted attendance ID
                $id_attendance = $stmt->insert_id;

                // Fetch all students from the selected semester
                $stmt_students = $db->prepare("SELECT id_student FROM student WHERE semester_ID = ?");
                $stmt_students->bind_param("s", $semester_ID);
                $stmt_students->execute();
                $students_result = $stmt_students->get_result();

                // Insert each student into student_attendance with status "Absent"
                $insert_stmt = $db->prepare("INSERT INTO student_attendance (id_attendance, id_student, semester_ID, date_attendance, status_attendance, penalty_requirements) VALUES (?, ?, ?, NOW(), 'Absent', ?)");

                while ($row = $students_result->fetch_assoc()) {
                    $id_student = $row['id_student'];
                    $insert_stmt->bind_param("iiss", $id_attendance, $id_student, $semester_ID, $penalty_requirements);
                    $insert_stmt->execute();
                }

                // Commit transaction
                $db->commit();

                // Redirect with success message
                header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Attendance+record+added+successfully");
                exit();
            } else {
                throw new Exception("Error adding attendance record: " . $stmt->error);
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->rollback();
            $error = $e->getMessage();
            header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=danger&message=" . urlencode($error));
            exit();
        }
    }
}


// Handle edit attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_attendance'], $_POST['type_attendance'], $_POST['penalty_type'], $_POST['penalty_requirements'], $_POST['start_time'], $_POST['end_time'])) {
    $id_attendance = $_POST['id_attendance'];
    $type_attendance = $_POST['type_attendance'];
    $penalty_type = $_POST['penalty_type'];
    $penalty_requirements = $_POST['penalty_requirements'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Validate input fields
    if (empty($id_attendance) || empty($type_attendance) || empty($penalty_type)) {
        $error = "All fields are required.";
    } else {
        // Update the attendance record in the database
        $stmt = $db->prepare("UPDATE attendances SET type_attendance = ?, penalty_type = ?, penalty_requirements = ?, start_time = ?, end_time = ? WHERE id_attendance = ?");
        $stmt->bind_param("sssssi", $type_attendance, $penalty_type, $penalty_requirements, $start_time, $end_time, $id_attendance);

        if ($stmt->execute()) {
            // Redirect or display success message
            echo "<script>window.location.href='?content=admin-index&admin=event-management&admin_events=admin-events';</script>";
        } else {
            $error = "Error updating attendance record: " . $stmt->error;
        }
    }
}


// Handle event deletion
if (isset($_POST['delete_event'])) {
    $id_event = $_POST['id_event'];

    try {
        // Start transaction
        $db->begin_transaction();

        // First, get all attendance IDs for this event
        $get_attendance_ids = $db->prepare("SELECT id_attendance FROM attendances WHERE id_event = ?");
        $get_attendance_ids->bind_param("i", $id_event);
        $get_attendance_ids->execute();
        $attendance_ids = $get_attendance_ids->get_result();

        // Delete student_attendance records for each attendance
        $delete_student_attendance = $db->prepare("DELETE FROM student_attendance WHERE id_attendance = ?");
        while ($row = $attendance_ids->fetch_assoc()) {
            $delete_student_attendance->bind_param("i", $row['id_attendance']);
            $delete_student_attendance->execute();
        }

        // Then, delete all attendance records for this event
        $delete_attendance_stmt = $db->prepare("DELETE FROM attendances WHERE id_event = ?");
        $delete_attendance_stmt->bind_param("i", $id_event);
        $delete_attendance_stmt->execute();

        // Finally, delete the event
        $delete_event_stmt = $db->prepare("DELETE FROM events WHERE id_event = ?");
        $delete_event_stmt->bind_param("i", $id_event);
        $delete_event_stmt->execute();

        // Commit transaction
        $db->commit();

        // Redirect with success message
        header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Event+and+all+related+records+deleted+successfully");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollback();
        $error = "Error deleting event: " . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=danger&message=" . urlencode($error));
        exit();
    }
}


// Handle attendance deletion
if (isset($_POST['delete_attendance'])) {
  $id_attendance = $_POST['id_attendance'];

  try {
    // Start transaction
    $db->begin_transaction();

    // First, delete from student_attendance table
    $delete_student_attendance_stmt = $db->prepare("DELETE FROM student_attendance WHERE id_attendance = ?");
    $delete_student_attendance_stmt->bind_param("i", $id_attendance);
    $delete_student_attendance_stmt->execute();

    // Then, delete from attendances table
    $delete_attendance_stmt = $db->prepare("DELETE FROM attendances WHERE id_attendance = ?");
    $delete_attendance_stmt->bind_param("i", $id_attendance);
    $delete_attendance_stmt->execute();

    // Commit transaction
    $db->commit();

    header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Attendance+and+all+related+records+deleted+successfully");
    exit();
  } catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    $error = "Error deleting attendance: " . $e->getMessage();
    header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=danger&message=" . urlencode($error));
    exit();
  }
}

// Check for status messages
$status = isset($_GET['status']) ? $_GET['status'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';


// Get the user ID from the session (either admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

// Handle the semester selection from GET request and store it in session for this user
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
}

// Initialize the selected semester variable
$selected_semester = $_SESSION['selected_semester'][$user_id] ?? '';

// Fetch all events for the selected semester (No pagination)
$query = "SELECT id_event, name_event, date_event, event_start_time, event_end_time, event_desc 
          FROM events WHERE semester_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$events = $stmt->get_result();

?>

<link rel="stylesheet" href=".//.//stylesheet/admin/admin-events.css">



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
        <div class="accordion" id="accordionExample">
            <?php 
            // Check if there are any events
            if ($events->num_rows === 0): ?>
                <div class="alert alert-light alert-info">
                    No events found in this semester.
                </div>
            <?php else: 
                // Updated query to fetch creator details
                $eventQuery = "
                    SELECT 
                        events.*, 
                        COALESCE(admins.firstname_admin, student.firstname_student) AS creator_firstname,
                        COALESCE(admins.lastname_admin, student.lastname_student) AS creator_lastname
                    FROM events
                    LEFT JOIN admins ON events.created_by = admins.id_admin
                    LEFT JOIN student ON events.created_by = student.id_student
                    WHERE events.semester_ID = ?
                    ORDER BY events.date_event DESC
                ";

                $stmt = $db->prepare($eventQuery);
                $stmt->bind_param("s", $selected_semester);
                $stmt->execute();
                $events = $stmt->get_result();

                while ($event = $events->fetch_assoc()): 
            ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $event['id_event']; ?>">
                        <button class="accordion-button bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $event['id_event']; ?>" aria-expanded="true" aria-controls="collapse<?php echo $event['id_event']; ?>">
                           <strong><?php echo $event['name_event']; ?> - <?php echo date("F j, Y", strtotime($event['date_event'])); ?></strong> 
                        </button>
                    </h2>
                    <div id="collapse<?php echo $event['id_event']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <p><strong>Event Name:</strong> <?php echo $event['name_event']; ?></p>
                            <p><strong>Event Date:</strong> <?php echo date("F j, Y", strtotime($event['date_event'])); ?></p>
                            <p><strong>Start Time:</strong> <?php echo date("h:i A", strtotime($event['event_start_time'])); ?></p>
                            <p><strong>End Time:</strong> <?php echo date("h:i A", strtotime($event['event_end_time'])); ?></p>
                            <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($event['event_desc'])); ?></p>
                            <p><strong>Created By:</strong> <?php echo $event['creator_firstname'] . ' ' . $event['creator_lastname']; ?></p>
                            <p><strong>Actions:</strong></p>
                            <script>
function handleEditClick(button) {
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    const date = button.getAttribute('data-date');
    const start = button.getAttribute('data-start');
    const end = button.getAttribute('data-end');
    const desc = button.getAttribute('data-desc');

    openEditModal(id, name, date, start, end, desc);
}
</script>

                            <!-- Edit Button with Icon -->
                            <button type="button"
            class="btn btn-success btn-sm"
            data-id="<?= htmlspecialchars($event['id_event']) ?>"
            data-name="<?= htmlspecialchars($event['name_event'], ENT_QUOTES) ?>"
            data-date="<?= date('Y-m-d', strtotime($event['date_event'])) ?>"
            data-start="<?= date('H:i', strtotime($event['event_start_time'])) ?>"
            data-end="<?= date('H:i', strtotime($event['event_end_time'])) ?>"
            data-desc="<?= htmlspecialchars($event['event_desc'], ENT_QUOTES) ?>"
            onclick="handleEditClick(this)">
        <i class="fas fa-edit"></i> Edit
    </button>

    <!-- Delete Button with Icon -->
    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="confirmDelete(<?php echo $event['id_event']; ?>)">
        <i class="fas fa-trash"></i> Delete
    </button>

    <!-- Add Attendance Button with Icon -->
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#attendanceModal" onclick="document.getElementById('add_id_event').value='<?php echo $event['id_event']; ?>';">
        <i class="bi bi-calendar-plus"></i> Add Attendance
    </button>

    <h5>Attendances:</h5>
    <div class="table-responsive">
      <table class="table table-sm table-striped">
        <thead>
          <tr>
            <th>Type</th>
            <th>Status</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Penalty Type</th>
            <th>Penalty Requirements</th>
            <th style="text-align: center;">Action buttons</th>
        </thead>
        <tbody id="attendanceTableBody_<?php echo $event['id_event']; ?>">
      <!-- Attendance records will be dynamically loaded here -->
    </tbody>

      </table>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const eventId = <?php echo $event['id_event']; ?>; 
        const attendanceTableBody = document.getElementById('attendanceTableBody_<?php echo $event['id_event']; ?>');

        function loadAttendances() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `././php/admin/fetch-attendances.php?event_id=${eventId}`, true);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    attendanceTableBody.innerHTML = xhr.responseText;
                } else {
                    console.error('Error loading attendance data.');
                }
            };

            xhr.send();
        }

        loadAttendances();
        setInterval(loadAttendances, 30000);
    });
    </script>


                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            endif; 
            ?>
        </div>
    </div>

    <!-- Tooltip Initialization Script (outside the loop) -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    });
</script>

    <script>
        function navigateToPage(page) {
            window.location.href = '?content=admin-index&admin=event-management&page=' + page;
        }
    </script>

<button type="button" class="btn" style="background-color: tomato; color: white; margin-top: 5px;" data-bs-toggle="modal" data-bs-target="#eventModal">
    Add Event
</button>
</div>


<script>

function confirmDelete(eventId) {
  document.getElementById('delete_event_id').value = eventId;
  var deleteModal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
  deleteModal.show();
}
</script>


<!-- Modal Add Event Structure -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventModalLabel">Add New Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="overflow: hidden;">
        <form method="POST" action="">
          <div class="mb-3">
            <label for="name_event" class="form-label">Event Name</label>
            <input type="text" class="form-control" id="name_event" name="name_event" placeholder="Enter event name" required>
          </div>
          <div class="mb-3">
            <label for="date_event" class="form-label">Event Date</label>
            <input type="date" class="form-control" id="date_event" name="date_event" required>
          </div>
          <div class="mb-3">
            <label for="event_start_time" class="form-label">Start Time</label>
            <input type="time" class="form-control" id="event_start_time" name="event_start_time" required>
          </div>
          <div class="mb-3">
            <label for="event_end_time" class="form-label">End Time</label>
            <input type="time" class="form-control" id="event_end_time" name="event_end_time" required>
          </div>
          <div class="mb-3">
            <label for="event_desc" class="form-label">Event Description</label>
            <textarea class="form-control" id="event_desc" name="event_desc" rows="3" placeholder="Enter event description"></textarea>
          </div>
          <button type="submit" name="create_event" class="btn btn-primary">Save Event</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<!-- Attendance Modal Structure -->
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="attendanceModalLabel">Add Attendance Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="">
          <div class="mb-3">
            <input type="hidden" class="form-control" id="add_id_event" name="id_event" value="" placeholder="Enter event ID" required>
          </div>
          <div class="mb-3">
            <label for="type_attendance" class="form-label">Type of Attendance</label>
            <select class="form-select" id="type_attendance" name="type_attendance" required>
              <option selected>Select Attendance Type</option>
              <option value="IN">IN</option>
              <option value="OUT">OUT</option>
              <option value="SA">Surprise Attendance</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="penalty_type" class="form-label">Penalty Type</label>
            <select class="form-select" id="penalty_type" name="penalty_type" required onchange="updatePenaltyRequirements()">
              <option selected>Select Penalty Type</option>
              <option value="Fee">Fee</option>
              <option value="Community Service">Community Service</option>
              <option value="Donate">Donate</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="penalty_requirements" class="form-label">Penalty Requirements</label>
            <input type="text" class="form-control" id="penalty_requirements" name="penalty_requirements" placeholder="Enter penalty requirements" required>
          </div>
          <div class="mb-3">
            <label for="start_time" class="form-label">Start Time</label>
            <input type="time" class="form-control" id="start_time" name="start_time" required>
          </div>
          <div class="mb-3">
            <label for="end_time" class="form-label">End Time</label>
            <input type="time" class="form-control" id="end_time" name="end_time" required>
          </div>
          <button type="submit" class="btn btn-primary">Save Attendance</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Attendance Edit Modal Structure -->
<div class="modal fade" id="EditAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editAttendanceModalLabel">Edit Attendance Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="">
          <input type="hidden" id="edit_id_attendance" name="id_attendance" value="">
          <div class="mb-3">
            <label for="edit_type_attendance" class="form-label">Type of Attendance</label>
            <select class="form-select" id="edit_type_attendance" name="type_attendance" required>
              <option selected>Select Attendance Type</option>
              <option value="IN">IN</option>
              <option value="OUT">OUT</option>
              <option value="SA">Surprise Attendance</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_penalty_type" class="form-label">Penalty Type</label>
            <select class="form-select" id="edit_penalty_type" name="penalty_type" required onchange="updateEditPenaltyRequirements()">
              <option selected>Select Penalty Type</option>
              <option value="Fee">Fee</option>
              <option value="Community Service">Community Service</option>
              <option value="Donate">Donate</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_penalty_requirements" class="form-label">Penalty Requirements</label>
            <input type="text" class="form-control" id="edit_penalty_requirements" name="penalty_requirements" placeholder="Enter penalty requirements" required>
          </div>
          <div class="mb-3">
            <label for="edit_start_time" class="form-label">Start Time</label>
            <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
          </div>
          <div class="mb-3">
            <label for="edit_end_time" class="form-label">End Time</label>
            <input type="time" class="form-control" id="edit_end_time" name="end_time" required>
          </div>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const attendanceForm = document.querySelector('#attendanceModal form');
    
    attendanceForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form values
        const id_event = document.getElementById('add_id_event').value;
        const type_attendance = document.getElementById('type_attendance').value;
        const penalty_type = document.getElementById('penalty_type').value;
        const penalty_requirements = document.getElementById('penalty_requirements').value;
        const start_time = document.getElementById('start_time').value;
        const end_time = document.getElementById('end_time').value;
        
        // Validate required fields
        if (!id_event || !type_attendance || !penalty_type || !penalty_requirements || !start_time || !end_time) {
            alert('Please fill in all required fields');
            return;
        }
        
        // If validation passes, submit the form
        this.submit();
    });
});
</script>

<!-- Floating Attendance Data Preview -->
<div id="attendanceDataDisplay" class="position-fixed top-50 start-50 translate-middle-y d-none"
     style="left: 70%; width: 300px; z-index: 1060; background: #f8f9fa; border: 1px solid tomato; border-radius: 10px; padding: 1rem;">
  <h5 class="mb-3" style="color: tomato;">Current Data Preview</h5>
  <ul class="list-group">
    <li class="list-group-item"><strong>Type:</strong> <span id="preview_type_attendance">-</span></li>
    <li class="list-group-item"><strong>Penalty Type:</strong> <span id="preview_penalty_type">-</span></li>
    <li class="list-group-item"><strong>Penalty Requirements:</strong> <span id="preview_penalty_requirements">-</span></li>
    <li class="list-group-item"><strong>Start Time:</strong> <span id="preview_start_time">-</span></li>
    <li class="list-group-item"><strong>End Time:</strong> <span id="preview_end_time">-</span></li>
  </ul>
</div>


<script>
function openEditModal(id, name, date, start_time, end_time, description) {
    // Set modal fields with the event data
    document.getElementById('edit_event_id').value = id;
    document.getElementById('edit_name_event').value = name;
    document.getElementById('edit_date_event').value = date;
    document.getElementById('edit_event_start_time').value = start_time;
    document.getElementById('edit_event_end_time').value = end_time;
    document.getElementById('edit_event_desc').value = description;
    
    // Show the modal using Bootstrap's modal method
    var editModal = new bootstrap.Modal(document.getElementById('editEventModal'));
    editModal.show();
}
</script>

<!-- Add time modal -->
<div class="modal fade" id="addTimeModal" tabindex="-1" aria-labelledby="addTimeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTimeModalLabel">Add Time to Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTimeForm">
                    <input type="hidden" id="attendanceId" name="attendanceId">
                    <div class="mb-3">
                        <label for="additionalTime" class="form-label">Additional Time (in minutes)</label>
                        <input type="number" class="form-control" id="additionalTime" name="additionalTime" min="1" required>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="submitAddTime()">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openEditTimeModal(attendanceId) {
    // Ensure the modal and input elements exist
    const attendanceIdInput = document.getElementById('attendanceId');
    const addTimeModal = new bootstrap.Modal(document.getElementById('addTimeModal'));

    if (attendanceIdInput) {
        attendanceIdInput.value = attendanceId;
        addTimeModal.show();  // Show the modal
    } else {
        console.error("Modal or input element not found.");
    }
}

function submitAddTime() {
    const attendanceId = document.getElementById('attendanceId').value;
    const additionalTime = document.getElementById('additionalTime').value;

    if (attendanceId && additionalTime) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '././php/admin/add_time_action.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert('An error occurred while processing the response.');
                }
            } else {
                console.error('Error updating time. Status:', xhr.status);
                alert('Error: Server returned status ' + xhr.status);
            }
        };

        xhr.onerror = function() {
            console.error('Request failed');
            alert('Request failed. Please check your connection.');
        };

        xhr.send(`attendanceId=${attendanceId}&additionalTime=${additionalTime}`);
    } else {
        alert('Please fill in all fields');
    }
}
</script>


<!-- Full-Screen Modal -->
<div class="modal fade" id="attendanceRecordsModal" tabindex="-1" aria-labelledby="attendanceRecordsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen"> 
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="attendanceRecordsModalLabel">Attendance Records</h5>
        
      <button type="button" class="btn btn-success ms-1" onclick="exportToCSV()">Export to CSV</button>
      <button type="button" class="btn btn-primary ms-1" id="fetchRecordsBtn">Fetch Records</button>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Search and Filter Inputs -->
        <div class="row mb-3">
          <div class="col-md-8">
            <input type="text" id="searchInput" class="form-control" placeholder="Search by ID, name, year, or status...">
          </div>
          <div class="col-md-4">
            <select id="statusFilter" class="form-select">
              <option value="">All Status</option>
              <option value="Absent">Absent</option>
              <option value="Present">Present</option>
              <option value="Cleared">Cleared</option>
            </select>
          </div>
        </div>

        <!-- Record Count Label -->
        <div class="row mb-3">
          <div class="col-12">
            <div id="recordCount" class="text-muted"></div>
          </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
          <table class="table table-sm table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Lastname</th>
                <th>Firstname</th>
                <th>Year Level</th>
                <th>Date and Time</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="attendanceBody" class="accordion" id="accordionTable">
            <tr><td colspan="6" class="text-center">Select an attendance record to display data.</td></tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination Controls - Moved outside the table -->
        <div class="d-flex justify-content-center mt-3">
          <nav aria-label="Attendance records pagination">
            <ul class="pagination" id="paginationControls"></ul>
          </nav>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript for Search Filter -->
<script>
const rowsPerPage = 25;
let currentPage = 1;
let allRows = []; // All original rows

// Search and filter input events
document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
    const searchFilter = document.getElementById('searchInput').value.toUpperCase();
    const statusFilter = document.getElementById('statusFilter').value;

    const filteredRows = [];

    for (let i = 0; i < allRows.length; i += 2) {
        const mainRow = allRows[i];
        const accordionRow = allRows[i + 1];

        if (!mainRow || !accordionRow) continue;

        const text = mainRow.innerText.toUpperCase();
        const status = mainRow.querySelector('td:nth-child(6)').innerText.trim(); // Get status from 6th column

        const matchesSearch = text.includes(searchFilter);
        const matchesStatus = !statusFilter || status === statusFilter;

        if (matchesSearch && matchesStatus) {
            filteredRows.push(mainRow);
            filteredRows.push(accordionRow);
        }
    }

    renderTable(filteredRows);
}

// Load and store rows
function paginateTable() {
    const attendanceBody = document.getElementById('attendanceBody');
    allRows = Array.from(attendanceBody.querySelectorAll('tr.accordion-item, tr.accordion-collapse'));
    
    renderTable(allRows);
}

// Display a specific set of rows
function renderTable(rows) {
    const attendanceBody = document.getElementById('attendanceBody');
    attendanceBody.innerHTML = '';

    // Calculate actual number of records (dividing by 2 since each record has 2 rows)
    const actualRows = Math.floor(rows.length / 2);
    const totalPages = Math.ceil(actualRows / rowsPerPage);
    currentPage = Math.min(currentPage, totalPages) || 1; // Reset page if needed

    const startIndex = (currentPage - 1) * rowsPerPage * 2;
    const endIndex = Math.min(startIndex + rowsPerPage * 2, rows.length);

    // Update record count label
    const recordCount = document.getElementById('recordCount');
    if (actualRows === 0) {
        recordCount.textContent = 'No records found';
    } else {
        const startRecord = Math.floor(startIndex / 2) + 1;
        const endRecord = Math.floor(endIndex / 2);
        recordCount.textContent = `Showing ${startRecord}-${endRecord} of ${actualRows} records`;
    }

    for (let i = startIndex; i < endIndex; i++) {
        attendanceBody.appendChild(rows[i]);
    }

    createPaginationControls(rows);
}

// Create pagination buttons
function createPaginationControls(rows) {
    const paginationControls = document.getElementById('paginationControls');
    paginationControls.innerHTML = '';

    const actualRows = Math.floor(rows.length / 2);
    const totalPages = Math.ceil(actualRows / rowsPerPage);
    if (totalPages <= 1) {
        paginationControls.style.display = 'none';
        return;
    }
    
    paginationControls.style.display = 'flex';

    // Add First Page button (<<)
    const firstLi = document.createElement('li');
    firstLi.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
    const firstA = document.createElement('a');
    firstA.className = 'page-link';
    firstA.href = "#";
    firstA.innerHTML = '&laquo;&laquo;';
    firstA.addEventListener('click', function(e) {
        e.preventDefault();
        if (currentPage !== 1) {
            currentPage = 1;
            renderTable(document.getElementById('searchInput').value.trim() === '' ? allRows : getFilteredRows());
        }
    });
    firstLi.appendChild(firstA);
    paginationControls.appendChild(firstLi);

    // Add Previous button (<)
    const prevLi = document.createElement('li');
    prevLi.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
    const prevA = document.createElement('a');
    prevA.className = 'page-link';
    prevA.href = "#";
    prevA.innerHTML = '&laquo;';
    prevA.addEventListener('click', function(e) {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            renderTable(document.getElementById('searchInput').value.trim() === '' ? allRows : getFilteredRows());
        }
    });
    prevLi.appendChild(prevA);
    paginationControls.appendChild(prevLi);

    // Add page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, startPage + 4);

    for (let i = startPage; i <= endPage; i++) {
        const pageLi = document.createElement('li');
        pageLi.className = 'page-item' + (i === currentPage ? ' active' : '');
        const pageA = document.createElement('a');
        pageA.className = 'page-link';
        pageA.href = "#";
        pageA.textContent = i;
        pageA.addEventListener('click', function(e) {
            e.preventDefault();
            currentPage = i;
            renderTable(document.getElementById('searchInput').value.trim() === '' ? allRows : getFilteredRows());
        });
        pageLi.appendChild(pageA);
        paginationControls.appendChild(pageLi);
    }

    // Add Next button (>)
    const nextLi = document.createElement('li');
    nextLi.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
    const nextA = document.createElement('a');
    nextA.className = 'page-link';
    nextA.href = "#";
    nextA.innerHTML = '&raquo;';
    nextA.addEventListener('click', function(e) {
        e.preventDefault();
        if (currentPage < totalPages) {
            currentPage++;
            renderTable(document.getElementById('searchInput').value.trim() === '' ? allRows : getFilteredRows());
        }
    });
    nextLi.appendChild(nextA);
    paginationControls.appendChild(nextLi);

    // Add Last Page button (>>)
    const lastLi = document.createElement('li');
    lastLi.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
    const lastA = document.createElement('a');
    lastA.className = 'page-link';
    lastA.href = "#";
    lastA.innerHTML = '&raquo;&raquo;';
    lastA.addEventListener('click', function(e) {
        e.preventDefault();
        if (currentPage !== totalPages) {
            currentPage = totalPages;
            renderTable(document.getElementById('searchInput').value.trim() === '' ? allRows : getFilteredRows());
        }
    });
    lastLi.appendChild(lastA);
    paginationControls.appendChild(lastLi);
}

// Helper: get currently filtered rows
function getFilteredRows() {
    const searchFilter = document.getElementById('searchInput').value.toUpperCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const filtered = [];

    for (let i = 0; i < allRows.length; i += 2) {
        const mainRow = allRows[i];
        const accordionRow = allRows[i + 1];

        if (!mainRow || !accordionRow) continue;

        const text = mainRow.innerText.toUpperCase();
        const status = mainRow.querySelector('td:nth-child(6)').innerText.trim();

        const matchesSearch = text.includes(searchFilter);
        const matchesStatus = !statusFilter || status === statusFilter;

        if (matchesSearch && matchesStatus) {
            filtered.push(mainRow);
            filtered.push(accordionRow);
        }
    }
    return filtered;
}
</script>

<!-- JavaScript for Loading Attendance -->
<script>
let currentEventName = "";
let currentAttendanceType = "";
let currentAttendanceId = null;
let currentStartTime = "";
let currentEndTime = "";

function showAttendanceRecords(id_attendance, event_name, type_attendance, start_time, end_time) {
    currentEventName = event_name;
    currentAttendanceType = type_attendance;
    currentAttendanceId = id_attendance;
    currentStartTime = start_time;
    currentEndTime = end_time;

    const formattedTitle = `${event_name} - ${type_attendance} (${start_time} - ${end_time})`;
    document.getElementById("attendanceRecordsModalLabel").textContent = formattedTitle;

    loadAttendanceRecords();
}

function loadAttendanceRecords() {
    if (!currentAttendanceId) return;
    
    $.ajax({
        url: "./php/admin/fetch-attendance-records.php",
        type: "GET",
        data: { id_attendance: currentAttendanceId },
        success: function(response) {
            $("#attendanceBody").html(response);
            paginateTable();
        },
        error: function() {
            console.error("Failed to fetch attendance records.");
        }
    });
}

// Add back the fetch records button functionality
$(document).on('click', '#fetchRecordsBtn', function() {
    if (!currentAttendanceId) {
        alert("No attendance record selected");
        return;
    }
    
    // Disable the button to prevent double-clicks
    const $button = $(this);
    $button.prop('disabled', true);
    
    // Confirm before adding all missing students
    if (confirm("Add all missing students to this attendance record?")) {
        // Show loading state
        $button.html('<i class="fas fa-spinner fa-spin"></i> Fetching...');
        
        $.ajax({
            url: "./php/admin/fetch-students-not-in-record.php",
            type: "GET",
            data: { id_attendance: currentAttendanceId },
            cache: false, // Prevent caching
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        // Append the new rows to the table
                        $("#attendanceBody").prepend(result.html);
                        // Show success message
                        alert(result.message);
                        // Refresh pagination
                        paginateTable();
                        // Reload the attendance records without page reload
                        loadAttendanceRecords();
                    } else {
                        alert(result.message || "Failed to fetch records");
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert("Error processing response");
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                alert("Error processing request. Please try again.");
            },
            complete: function() {
                // Re-enable the button and restore original text
                $button.prop('disabled', false);
                $button.html('Fetch Records');
            }
        });
    } else {
        // Re-enable the button if user cancels
        $button.prop('disabled', false);
    }
});

// Update the loadAttendanceRecords function to handle the response better
function loadAttendanceRecords() {
    if (!currentAttendanceId) return;
    
    $.ajax({
        url: "./php/admin/fetch-attendance-records.php",
        type: "GET",
        data: { id_attendance: currentAttendanceId },
        success: function(response) {
            $("#attendanceBody").html(response);
            paginateTable();
        },
        error: function() {
            console.error("Failed to fetch attendance records.");
        }
    });
}

// Add back the add attendance functionality
$(document).on('click', '.add-attendance', function() {
    var studentId = $(this).data('student-id');
    var attendanceId = $(this).data('attendance-id');

    $.ajax({
        url: './php/admin/add-attendance-record.php',
        type: 'POST',
        data: {
            student_id: studentId,
            attendance_id: attendanceId
        },
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    // Show success message
                    alert(result.message);
                    // Refresh the attendance records
                    loadAttendanceRecords();
                } else {
                    alert(result.message || "Failed to add attendance record");
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                alert("Error processing response");
            }
        },
        error: function() {
            alert("Error adding attendance record");
        }
    });
});

// Add new function to handle status updates
function updateAttendanceStatus(studentId, attendanceId, newStatus) {
    $.ajax({
        url: './php/admin/update-attendance-status.php',
        type: 'POST',
        data: {
            student_id: studentId,
            attendance_id: attendanceId,
            status: newStatus
        },
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    // Update the status in the UI without reloading
                    const statusCell = $(`#status-${studentId}-${attendanceId}`);
                    const actionCell = $(`#action-${studentId}-${attendanceId}`);
                    
                    // Update status text
                    statusCell.text(newStatus);
                    
                    // Update button based on new status
                    if (newStatus === 'Cleared') {
                        actionCell.html(`
                            <button class="btn btn-warning btn-sm" onclick="updateAttendanceStatus('${studentId}', '${attendanceId}', 'Present')">
                                <i class="fas fa-times"></i> Mark as Present
                            </button>
                        `);
                    } else {
                        actionCell.html(`
                            <button class="btn btn-success btn-sm" onclick="updateAttendanceStatus('${studentId}', '${attendanceId}', 'Cleared')">
                                <i class="fas fa-check"></i> Mark as Cleared
                            </button>
                        `);
                    }
                    
                    // Update status cell class for styling
                    statusCell.removeClass('text-danger text-success text-warning');
                    if (newStatus === 'Cleared') {
                        statusCell.addClass('text-success');
                    } else if (newStatus === 'Present') {
                        statusCell.addClass('text-warning');
                    } else {
                        statusCell.addClass('text-danger');
                    }
                } else {
                    alert(result.message || 'Failed to update status');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                alert('Error updating status');
            }
        },
        error: function() {
            alert('Error updating status');
        }
    });
}

// Update the exportToCSV function to use current data
function exportToCSV() {
    let table = document.querySelector("#attendanceBody").closest("table");
    let rows = Array.from(table.querySelectorAll("tr:not(.collapse)"));

    if (rows.length <= 1) {
        alert("No records available to export.");
        return;
    }

    let csvContent = "";
    const headers = ["ID", "Lastname", "Firstname", "Year Level", "Date and Time", "Status"];
    csvContent += headers.map(h => `"${h}"`).join(",") + "\r\n";

    rows.forEach(row => {
        const cols = row.querySelectorAll("td");
        if (cols.length === 7) {
            let data = [
                cols[0].innerText.trim(),
                cols[1].innerText.trim(),
                cols[2].innerText.trim(),
                cols[3].innerText.trim(),
                cols[4].innerText.trim(),
                cols[5].innerText.trim()
            ];
            csvContent += data.map(val => `"${val}"`).join(",") + "\r\n";
        }
    });

    const fileName = `${currentEventName} - ${currentAttendanceType}`.replace(/[\\/:*?"<>|]/g, "") + ".csv";

    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", fileName);
    link.style.display = "none";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<!-- Modal Edit Event Structure -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="">
          <input type="hidden" id="edit_event_id" name="edit_event_id">
          <div class="mb-3">
            <label for="edit_name_event" class="form-label">Event Name</label>
            <input type="text" class="form-control" id="edit_name_event" name="edit_name_event" placeholder="Enter event name" required>
          </div>
          <div class="mb-3">
            <label for="edit_date_event" class="form-label">Event Date</label>
            <input type="date" class="form-control" id="edit_date_event" name="edit_date_event" required>
          </div>
          <div class="mb-3">
            <label for="edit_event_start_time" class="form-label">Start Time</label>
            <input type="time" class="form-control" id="edit_event_start_time" name="edit_event_start_time" required>
          </div>
          <div class="mb-3">
            <label for="edit_event_end_time" class="form-label">End Time</label>
            <input type="time" class="form-control" id="edit_event_end_time" name="edit_event_end_time" required>
          </div>
          <div class="mb-3">
            <label for="edit_event_desc" class="form-label">Event Description</label>
            <textarea class="form-control" id="edit_event_desc" name="edit_event_desc" rows="3" placeholder="Enter event description"></textarea>
          </div>
          <button type="submit" name="update_event" class="btn btn-primary">Save Changes</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteEventModalLabel">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
        <div class="modal-body">
          Are you sure you want to delete this event?
          <input type="hidden" name="id_event" id="delete_event_id">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="delete_event" class="btn btn-danger">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openEditAttendanceModal(id_attendance, type_attendance, penalty_type, penalty_requirements, start_time, end_time) {
    // Set modal fields with the attendance data
    document.getElementById('edit_id_attendance').value = id_attendance;
    document.getElementById('edit_type_attendance').value = type_attendance;
    document.getElementById('edit_penalty_type').value = penalty_type;
    document.getElementById('edit_penalty_requirements').value = penalty_requirements;
    document.getElementById('edit_start_time').value = start_time;
    document.getElementById('edit_end_time').value = end_time;
    
    // Show the modal using Bootstrap's modal method
    var editModal = new bootstrap.Modal(document.getElementById('EditAttendanceModal'));
    editModal.show();
}

// Add event listener for when the edit attendance modal is hidden
document.getElementById('EditAttendanceModal').addEventListener('hidden.bs.modal', function () {
    // Remove the modal backdrop
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
    // Remove the modal-open class from body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});
</script>

<!-- Delete Attendance Confirmation Modal -->
<div class="modal fade" id="deleteAttendanceModal" tabindex="-1" aria-labelledby="deleteAttendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteAttendanceModalLabel">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
        <div class="modal-body">
          Are you sure you want to delete this attendance record?
          <input type="hidden" name="id_attendance" id="delete_attendance_id">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="delete_attendance" class="btn btn-danger">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function confirmDeleteAttendance(id_attendance) {
    document.getElementById('delete_attendance_id').value = id_attendance;
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteAttendanceModal'));
    deleteModal.show();
}

// Add event listener for when the delete attendance modal is hidden
document.getElementById('deleteAttendanceModal').addEventListener('hidden.bs.modal', function () {
    // Remove the modal backdrop
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
    // Remove the modal-open class from body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});
</script>
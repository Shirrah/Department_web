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


          // Redirect or display success message
          echo "<script>window.location.href='';</script>";
      } else {
          $error = "Error adding attendance record: " . $stmt->error;
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

  // First, delete the attendance records associated with the event
  $delete_attendance_stmt = $db->prepare("DELETE FROM attendances WHERE id_event = ?");
  $delete_attendance_stmt->bind_param("i", $id_event);

  if ($delete_attendance_stmt->execute()) {
      // Then, delete the event from the database
      $delete_event_stmt = $db->prepare("DELETE FROM events WHERE id_event = ?");
      $delete_event_stmt->bind_param("i", $id_event);

      if ($delete_event_stmt->execute()) {
          // Redirect back to the same page with success message
          header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Event+deleted+successfully.");
          exit();
      } else {
          $error = "Error deleting event: " . $delete_event_stmt->error;
          header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=danger&message=" . urlencode($error));
          exit();
      }
  } else {
      $error = "Error deleting attendances: " . $delete_attendance_stmt->error;
      header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=danger&message=" . urlencode($error));
      exit();
  }
}


// Handle attendance deletion
if (isset($_POST['delete_attendance'])) {
  $id_attendance = $_POST['id_attendance'];

  $delete_attendance_stmt = $db->prepare("DELETE FROM attendances WHERE id_attendance = ?");
  $delete_attendance_stmt->bind_param("i", $id_attendance);

  if ($delete_attendance_stmt->execute()) {
      header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Attendance+deleted+successfully.");
      exit();
  } else {
      $error = "Error deleting attendance: " . $delete_attendance_stmt->error;
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
                        <p><strong>Description:</strong> <?php echo $event['event_desc']; ?></p>

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
<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#attendanceModal" onclick="document.getElementById('id_event').value='<?php echo $event['id_event']; ?>';">
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
        <?php endwhile; ?>
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
            <input type="hidden" class="form-control" id="id_event" name="id_event" value="" placeholder="Enter event ID" required>
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

<script>
  function updatePenaltyRequirements() {
    const penaltyType = document.getElementById('penalty_type').value;
    const penaltyRequirements = document.getElementById('penalty_requirements');
    
    if (penaltyType === 'Fee') {
      penaltyRequirements.type = 'number';
      penaltyRequirements.placeholder = 'Enter amount (e.g., 4.00)';
      penaltyRequirements.step = '0.01'; // Allows decimals
    } else {
      penaltyRequirements.type = 'text';
      penaltyRequirements.placeholder = 'Enter penalty requirements';
      penaltyRequirements.removeAttribute('step');
    }
  }

</script>

<!-- Attendance Edit Modal Structure -->
<div class="modal fade" id="EditAttendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="attendanceModalLabel">Edit Attendance Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="">
          <input type="hidden" id="id_attendance" name="id_attendance" value="">
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
function openEditAttendanceModal(id, type, penaltyType, penaltyReq, start, end) {
  document.getElementById('id_attendance').value = id;
  document.getElementById('type_attendance').value = type;
  document.getElementById('penalty_type').value = penaltyType;
  document.getElementById('penalty_requirements').value = penaltyReq;
  document.getElementById('start_time').value = start;
  document.getElementById('end_time').value = end;

  // Format time to 12-hour
  document.getElementById('preview_type_attendance').textContent = type;
  document.getElementById('preview_penalty_type').textContent = penaltyType;
  document.getElementById('preview_penalty_requirements').textContent = penaltyReq;
  document.getElementById('preview_start_time').textContent = formatTo12Hour(start);
  document.getElementById('preview_end_time').textContent = formatTo12Hour(end);

  document.getElementById('attendanceDataDisplay').classList.remove('d-none');

  const modalElement = document.getElementById('EditAttendanceModal');
  modalElement.addEventListener('hidden.bs.modal', () => {
    document.getElementById('attendanceDataDisplay').classList.add('d-none');
  });

  updatePenaltyRequirements(penaltyType);
}

function formatTo12Hour(timeStr) {
  const [hour, minute] = timeStr.split(":").map(Number);
  const ampm = hour >= 12 ? "PM" : "AM";
  const hour12 = hour % 12 || 12;
  return `${hour12}:${minute.toString().padStart(2, "0")} ${ampm}`;
}


function updatePenaltyRequirements(penaltyType) {
  const penaltyRequirements = document.getElementById('penalty_requirements');
  
  if (penaltyType === 'Fee') {
    penaltyRequirements.type = 'number';
    penaltyRequirements.placeholder = 'Enter amount (e.g., 4.00)';
    penaltyRequirements.step = '0.01';
  } else {
    penaltyRequirements.type = 'text';
    penaltyRequirements.placeholder = 'Enter penalty requirements';
    penaltyRequirements.removeAttribute('step');
  }
}

</script>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteEventModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteEventModalLabel">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
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

<!-- Delete Attendance Confirmation Modal -->
<div class="modal fade" id="deleteAttendanceModal" tabindex="-1" aria-labelledby="deleteAttendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteAttendanceModalLabel">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
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

<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="alertModalLabel">Alert</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalMessage"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script>
  function confirmDeleteAttendance(attendanceId) {
    document.getElementById('delete_attendance_id').value = attendanceId;
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteAttendanceModal'));
    deleteModal.show();
}
</script>



<?php
// Check if there's a status and message in the URL
$status = isset($_GET['status']) ? $_GET['status'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<!-- Modal for displaying success or error message -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="alertModalContent">
      <div class="modal-header">
        <h5 class="modal-title" id="alertModalLabel">Alert</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalMessage">
        <!-- Success or error message will be injected here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Check if there's a status and message in the URL
  const status = '<?php echo $status; ?>';
  const message = '<?php echo $message; ?>';

  // Show the modal if a message exists
  if (status && message) {
    const modalMessage = document.getElementById('modalMessage');
    const modal = new bootstrap.Modal(document.getElementById('alertModal'));
    const modalContent = document.getElementById('alertModalContent');

    // Set the message content
    modalMessage.innerHTML = message;

    // Dynamically set the modal background color based on the content of the page
    const pageContent = document.getElementById('content') ? document.getElementById('content').className : ''; // Example to check content theme
    if (pageContent.includes('admin-index')) {
      modalContent.style.backgroundColor = "#f8f9fa";  // Example background color for admin index content
    } else if (pageContent.includes('admin-events')) {
      modalContent.style.backgroundColor = "#e9ecef";  // Another example background color for event management
    } else {
      modalContent.style.backgroundColor = "#ffffff";  // Default background color
    }

    // Show the modal
    modal.show();

    // Add an event listener for when the modal is hidden
    const modalElement = document.getElementById('alertModal');
    modalElement.addEventListener('hidden.bs.modal', function () {
      // Redirect to the desired page after the modal is closed
      window.location.href = '?content=admin-index&admin=event-management&admin_events=admin-events';
    });
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
      <div class="modal-body" style="overflow: hidden;">
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


<script>
function openEditModal(id, name, date, start_time, end_time, description) {
    console.log("Opening edit modal with:", id, name, date, start_time, end_time, description);
    
    // Set modal fields with the event data
    document.getElementById('edit_event_id').value = id;
    document.getElementById('edit_name_event').value = name;
    document.getElementById('edit_date_event').value = date;
    document.getElementById('edit_event_start_time').value = start_time;
    document.getElementById('edit_event_end_time').value = end_time;
    document.getElementById('edit_event_desc').value = description;
    
    // Show the modal
    var myModal = new bootstrap.Modal(document.getElementById('editEventModal'));
    myModal.show();
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
        <!-- Search Input -->
        <div class="mb-3">
          <input type="text" id="searchInput" class="form-control" placeholder="Search by ID, name, year, or status...">
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
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript for Search Filter -->
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let rows = document.querySelectorAll("#attendanceBody tr");
    
    // We need to process rows in pairs (main row and accordion row)
    for (let i = 0; i < rows.length; i += 2) {
        const mainRow = rows[i];
        const accordionRow = rows[i+1];
        
        if (!mainRow || !accordionRow) continue;
        
        let text = mainRow.innerText.toUpperCase();
        if (text.includes(filter)) {
            mainRow.style.display = "";
            // Only show accordion row if it's expanded
            if (mainRow.querySelector('button').getAttribute('aria-expanded') === 'true') {
                accordionRow.style.display = "";
            } else {
                accordionRow.style.display = "none";
            }
        } else {
            mainRow.style.display = "none";
            accordionRow.style.display = "none";
        }
    }
});
</script>



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
                alert('End time successfully updated!');
                location.reload();  // Reload the page to reflect changes
            } else {
                console.error('Error updating time.');
            }
        };

        xhr.send(`attendanceId=${attendanceId}&additionalTime=${additionalTime}`);
    }
}

</script>

<script>

// Define globally
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

    // Format title
    let formattedTitle = `${event_name} - ${type_attendance} (${start_time} - ${end_time})`;
    document.getElementById("attendanceRecordsModalLabel").textContent = formattedTitle;

    // Load attendance records
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
            $("#attendanceRecordsModal").modal("show");
        },
        error: function() {
            alert("Failed to fetch attendance records.");
        }
    });
}

$(document).on('click', '#fetchRecordsBtn', function() {
    if (!currentAttendanceId) {
        alert("No attendance record selected");
        return;
    }
    
    // Confirm before adding all missing students
    if (confirm("Add all missing students to this attendance record?")) {
        $.ajax({
            url: "./php/admin/fetch-students-not-in-record.php",
            type: "GET",
            data: { id_attendance: currentAttendanceId },
            success: function(response) {
                // Show success message
                $("#attendanceBody").html(response);
                
                // Refresh the attendance records after 1 second
                setTimeout(loadAttendanceRecords, 1000);
            },
            error: function() {
                alert("Failed to process records.");
            }
        });
    }
});

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
            alert(response.message);
            if (response.success) {
                // Refresh just the attendance records instead of full page reload
                loadAttendanceRecords();
            }
        },
        error: function() {
            alert("Error adding attendance record");
        }
    });
});
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
            // Ensure correct data from columns and capture status from the correct column
            let data = [
                cols[0].innerText.trim(), // ID
                cols[1].innerText.trim(), // Lastname
                cols[2].innerText.trim(), // Firstname
                cols[3].innerText.trim(), // Year Level
                // Combine the two date/time columns into one
                cols[4].innerText.trim(),
                cols[5].innerText.trim()
            ];

            // If the status is "Absent", make sure it's included properly
            if (data[5].toLowerCase() === "absent") {
                data[5] = "Absent"; // Ensure "Absent" status is used
            }

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
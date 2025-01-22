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
        $stmt = $db->db->prepare("INSERT INTO events (name_event, date_event, event_start_time, event_end_time, event_desc, semester_ID, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name_event, $date_event, $event_start_time, $event_end_time, $event_desc, $semester_ID, $user_id);

        if ($stmt->execute()) {
            // Redirect or display success message
            echo "<script>window.location.href='';</script>";
        } else {
            $error = "Error creating event: " . $stmt->error;
        }
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

  // Validate input fields
  if (empty($id_event) || empty($type_attendance) || empty($penalty_type)) {
      $error = "All fields are required.";
  } else {
      // Insert the attendance record into the database
      $stmt = $db->db->prepare("INSERT INTO attendances (id_event, type_attendance, attendance_status, penalty_type, penalty_requirements, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("issssss", $id_event, $type_attendance, $attendance_status, $penalty_type, $penalty_requirements, $start_time, $end_time);

      if ($stmt->execute()) {
          // Redirect or display success message
          echo "<script>window.location.href='';</script>";
      } else {
          $error = "Error adding attendance record: " . $stmt->error;
      }
  }
}


// Handle event deletion
if (isset($_POST['delete_event'])) {
  $id_event = $_POST['id_event'];

  // First, delete the attendance records associated with the event
  $delete_attendance_stmt = $db->db->prepare("DELETE FROM attendances WHERE id_event = ?");
  $delete_attendance_stmt->bind_param("i", $id_event);

  if ($delete_attendance_stmt->execute()) {
      // Then, delete the event from the database
      $delete_event_stmt = $db->db->prepare("DELETE FROM events WHERE id_event = ?");
      $delete_event_stmt->bind_param("i", $id_event);

      if ($delete_event_stmt->execute()) {
          // Redirect back to the same page with success message
          header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Event+deleted+successfully.");
          exit();
      } else {
          $error = "Error deleting event: " . $delete_event_stmt->error;
          // Redirect back to the same page with error message
          header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=danger&message=" . urlencode($error));
          exit();
      }
  } else {
      $error = "Error deleting attendances: " . $delete_attendance_stmt->error;
      // Redirect back to the same page with error message
      header("Location: " . $_SERVER['PHP_SELF'] . "?content=admin-index&admin=event-management&admin_events=admin-events&status=danger&message=" . urlencode($error));
      exit();
  }
}




// // Handle event deletion
// if (isset($_POST['delete_event'])) {
//     $event_id = $_POST['event_id'];

//     // Step 1: Get the ids of attendances associated with the event
//     $attendance_stmt = $db->db->prepare("SELECT id_attendance FROM attendances WHERE id_event = ?");
//     $attendance_stmt->bind_param("i", $event_id);
//     $attendance_stmt->execute();
//     $attendance_result = $attendance_stmt->get_result();

//     // Collecting all id_attendance into an array
//     $attendance_ids = [];
//     while ($row = $attendance_result->fetch_assoc()) {
//         $attendance_ids[] = $row['id_attendance'];
//     }

//     // Step 2: Delete associated attendances
//     $delete_attendance_stmt = $db->db->prepare("DELETE FROM attendances WHERE id_event = ?");
//     $delete_attendance_stmt->bind_param("i", $event_id);
//     $delete_attendance_stmt->execute();

//     // Step 3: Delete from student_attendance for each id_attendance
//     if (!empty($attendance_ids)) {
//         // Prepare the statement for deleting from student_attendance
//         $delete_student_attendance_stmt = $db->db->prepare("DELETE FROM student_attendance WHERE id_attendance = ?");
        
//         // Loop through each id_attendance and execute the delete
//         foreach ($attendance_ids as $id_attendance) {
//             $delete_student_attendance_stmt->bind_param("i", $id_attendance);
//             $delete_student_attendance_stmt->execute();
//         }
//     }

//     // Step 4: Delete the event after attendances are deleted
//     $delete_event_stmt = $db->db->prepare("DELETE FROM events WHERE id_event = ?");
//     $delete_event_stmt->bind_param("i", $event_id);

//     if ($delete_event_stmt->execute()) {
//         // Redirect or display success message
//         echo "<script>window.location.href='';</script>";
//     } else {
//         $error = "Error deleting event: " . $delete_event_stmt->error;
//     }
// }

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
            ORDER BY events.date_event DESC
        ";
        $events = $db->db->query($eventQuery);

        while ($event = $events->fetch_assoc()): 
        ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?php echo $event['id_event']; ?>">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $event['id_event']; ?>" aria-expanded="true" aria-controls="collapse<?php echo $event['id_event']; ?>">
                       <strong><?php echo $event['name_event']; ?> - <?php echo date("F j, Y", strtotime($event['date_event'])); ?></strong> 
                    </button>
                </h2>
                <div id="collapse<?php echo $event['id_event']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <p><strong>Event Name:</strong> <?php echo $event['name_event']; ?></p>
                        <p><strong>Event Date:</strong> <?php echo date("F j, Y", strtotime($event['date_event'])); ?></p>
                        <p><strong>Start Time:</strong> <?php echo date("h:i A", strtotime($event['event_start_time'])); ?></p>
                        <p><strong>End Time:</strong> <?php echo date("h:i A", strtotime($event['event_end_time'])); ?></p>
                        <p><strong>Created By:</strong> <?php echo $event['creator_firstname'] . ' ' . $event['creator_lastname']; ?></p>
                        <p><strong>Actions:</strong></p>
                        <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" onclick="openEditModal(
                            '<?php echo $event['id_event']; ?>',
                            '<?php echo addslashes($event['name_event']); ?>',
                            '<?php echo date('Y-m-d', strtotime($event['date_event'])); ?>',
                        )"><i class='fas fa-edit'></i></a>
                        <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="confirmDelete(<?php echo $event['id_event']; ?>)"><i class='fas fa-trash'></i></a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#attendanceModal" onclick="document.getElementById('id_event').value='<?php echo $event['id_event']; ?>';">Add Attendance</button>
                        <h5>Attendances:</h5>
                        <script>
                              const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
                              const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
                      </script>
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
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      // Fetch attendances associated with the current event (using event ID)
      $attendanceQuery = "SELECT id_attendance, type_attendance, attendance_status, penalty_type, penalty_requirements, start_time, end_time FROM attendances WHERE id_event = ?";
      $attendanceStmt = $db->db->prepare($attendanceQuery);
      $attendanceStmt->bind_param("i", $event['id_event']);
      $attendanceStmt->execute();
      $attendances = $attendanceStmt->get_result();

      // Display attendance records
      if ($attendances->num_rows > 0):
          while ($attendance = $attendances->fetch_assoc()): 
      ?>
        <tr>
          <td><?php echo $attendance['type_attendance']; ?></td>
          <td>
            <?php 
            if ($attendance['attendance_status'] == 'Ongoing') {
                echo '<span class="badge bg-primary">Ongoing</span>';
            } elseif ($attendance['attendance_status'] == 'Ended') {
                echo '<span class="badge bg-secondary">Ended</span>';
            } else {
                echo '<span class="badge bg-warning text-dark">Pending</span>';
            }
            ?>
          </td>
          <td><?php echo date("h:i A", strtotime($attendance['start_time'])); ?></td>
          <td><?php echo date("h:i A", strtotime($attendance['end_time'])); ?></td>
          <td><span class="badge bg-info"><?php echo $attendance['penalty_type']; ?></span></td>
          <td><?php echo $attendance['penalty_requirements']; ?></td>
          <td><button class="btn btn-primary btn-sm">Show Records</button></td>
          <!-- <a href="?content=admin-index&admin=attendance-records&id=<?php //echo $event['id_event']; ?>"><i class="fas fa-database"></i></a> -->
        </tr>
      <?php 
          endwhile;
      else: 
      ?>
        <tr>
          <td colspan="7" class="text-center">No attendance records yet</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

</div>


                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
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

    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal">
    Add Event
    </button>
    <button class="start-attendance-btn"><a href="./Barcode" target="_blank">Start Attendance</a></button>
</div>



<!-- Deleting event and attendances -->
<!-- <form id="delete-event-form" method="POST" action="">
    <input type="hidden" name="event_id" id="delete-event-id">
    <button type="submit" name="delete_event" style="display: none;"></button>
</form> -->



<script>
// Confirm deletion
// function confirmDelete(eventId) {
//     if (confirm('Are you sure you want to delete this event? \nNote that this will delete all the data assiocated with it!')) {
//         // Set the event ID in the hidden form and submit
//         document.getElementById('delete-event-id').value = eventId;
//         document.querySelector('#delete-event-form button').click();
//     }
// }

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

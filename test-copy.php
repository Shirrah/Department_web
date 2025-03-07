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



// Check for status messages
$status = isset($_GET['status']) ? $_GET['status'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';


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
                        <p><strong>Description:</strong> <?php echo $event['event_desc']; ?></p>

                        <p><strong>Created By:</strong> <?php echo $event['creator_firstname'] . ' ' . $event['creator_lastname']; ?></p>
                        <p><strong>Actions:</strong></p>
                        <!-- Edit Button with Icon -->
<button type="button" class="btn btn-success btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" onclick="openEditModal(
                          '<?php echo $event['id_event']; ?>',
                          '<?php echo addslashes($event['name_event']); ?>',
                          '<?php echo date('Y-m-d', strtotime($event['date_event'])); ?>',
                          '<?php echo date('H:i', strtotime($event['event_start_time'])); ?>',
                          '<?php echo date('H:i', strtotime($event['event_end_time'])); ?>',
                          '<?php echo addslashes($event['event_desc']); ?>'
                      )">
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
        <th>Actions</th>
      </tr>
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


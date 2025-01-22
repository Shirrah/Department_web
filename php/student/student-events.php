<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
require_once "././php/db-conn.php";
$db = new Database();

// Fetch events from the database
$sql = "SELECT id_event, name_event, date_event, event_desc, date_created FROM events";
$events = $db->db->query($sql);

// Handle showing attendances
$attendances = [];
if (isset($_GET['show_attendances'])) {
    $event_id = $_GET['id'];
    $attendance_sql = "SELECT type_attendance, time_in, time_out FROM attendances WHERE id_event = ?";
    $attendance_stmt = $db->db->prepare($attendance_sql);
    $attendance_stmt->bind_param("i", $event_id);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();
    
    // Store attendances in an array
    while ($attendance = $attendance_result->fetch_assoc()) {
        $attendances[] = $attendance;
    }
}
?>

<link rel="stylesheet" href=".//.//stylesheet/student/student-events.css">

<div class="event-management-con">
<div class="event-management-header">
        <span>Manage Events</span>
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
</div>

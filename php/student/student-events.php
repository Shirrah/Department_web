<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Get user ID (admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];
$is_student = isset($_SESSION['user_data']['id_student']); // Check if user is a student
?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Manage Events</h5>
        </div>
        <div class="card-body">
            <div class="accordion" id="accordionExample">
                <?php
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
                $events = $db->query($eventQuery);

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
                                                <th>Student Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            date_default_timezone_set('Asia/Manila');
                                            $current_time = date("H:i:s");
                                            $attendanceQuery = "SELECT id_attendance, type_attendance, penalty_type, penalty_requirements, start_time, end_time FROM attendances WHERE id_event = ?";
                                            $attendanceStmt = $db->prepare($attendanceQuery);
                                            $attendanceStmt->bind_param("i", $event['id_event']);
                                            $attendanceStmt->execute();
                                            $attendances = $attendanceStmt->get_result();

                                            if ($attendances->num_rows > 0):
                                                while ($attendance = $attendances->fetch_assoc()): 
                                                    $start_time = date("h:i A", strtotime($attendance['start_time']));
                                                    $end_time = date("h:i A", strtotime($attendance['end_time']));
                                                    
                                                    if ($current_time < $attendance['start_time']) {
                                                        $status_badge = '<span class="badge bg-warning text-dark">Pending</span>';
                                                    } elseif ($current_time >= $attendance['start_time'] && $current_time <= $attendance['end_time']) {
                                                        $status_badge = '<span class="badge bg-primary">Ongoing</span>';
                                                    } else {
                                                        $status_badge = '<span class="badge bg-secondary">Ended</span>';
                                                    }
                                                    
                                                    // Fetch attendance status only for the logged-in student
                                                    if ($is_student) {
                                                        $studentQuery = "SELECT status_attendance FROM student_attendance WHERE id_attendance = ? AND id_student = ?";
                                                        $studentStmt = $db->prepare($studentQuery);
                                                        $studentStmt->bind_param("ii", $attendance['id_attendance'], $user_id);
                                                        $studentStmt->execute();
                                                        $studentResult = $studentStmt->get_result();

                                                        if ($studentResult->num_rows > 0) {
                                                            $student = $studentResult->fetch_assoc();
                                                            $status_badge_class = ($student['status_attendance'] == 'Present') ? 'bg-success' : 'bg-danger';
                                                            $student_status = "<span class='badge $status_badge_class'>" . $student['status_attendance'] . "</span>";
                                                        } else {
                                                            $student_status = "<span class='badge bg-secondary'>No Record</span>";
                                                        }
                                                    } else {
                                                        $student_status = "<span class='badge bg-secondary'>Not a student</span>";
                                                    }
                                            ?>
                                            <tr>
                                                <td><?php echo $attendance['type_attendance']; ?></td>
                                                <td><?php echo $status_badge; ?></td>
                                                <td><?php echo $start_time; ?></td>
                                                <td><?php echo $end_time; ?></td>
                                                <td><span class="badge bg-info"> <?php echo $attendance['penalty_type']; ?> </span></td>
                                                <td><?php echo $attendance['penalty_requirements']; ?></td>
                                                <td><?php echo $student_status; ?></td>
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
</div>

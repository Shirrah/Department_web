<?php
date_default_timezone_set('Asia/Manila');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Get user ID (admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];
$is_student = isset($_SESSION['user_data']['id_student']);

// Get selected semester from session
$selected_semester = $_SESSION['selected_semester'][$user_id] ?? null;
if (!$selected_semester) {
    die("No semester selected in session");
}
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Manage Events (Semester: <?php echo htmlspecialchars($selected_semester); ?>)</h5>
        </div>
        <div class="card-body">
            <div class="accordion" id="accordionExample">
                <?php
                // Modified event query to filter by semester
                $eventQuery = "
                    SELECT DISTINCT
                        events.*, 
                        COALESCE(admins.firstname_admin, student.firstname_student) AS creator_firstname,
                        COALESCE(admins.lastname_admin, student.lastname_student) AS creator_lastname
                    FROM events
                    LEFT JOIN admins ON events.created_by = admins.id_admin
                    LEFT JOIN student ON events.created_by = student.id_student
                    WHERE events.semester_ID = ?
                    ORDER BY events.date_event DESC, events.id_event DESC
                ";
                $eventStmt = $db->prepare($eventQuery);
                $eventStmt->bind_param("s", $selected_semester);
                $eventStmt->execute();
                $events = $eventStmt->get_result();

                if ($events->num_rows === 0) {
                    echo '<div class="alert alert-info">No events found for semester: ' . htmlspecialchars($selected_semester) . '</div>';
                }

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
                                <p><strong>Semester:</strong> <?php echo $event['semester_ID']; ?></p>
                                
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
                                            $currentDateTime = new DateTime();
                                            $eventDate = new DateTime($event['date_event']);

                                            // Modified attendance query to ensure semester filtering
                                            $attendanceQuery = "
                                                SELECT a.id_attendance, a.type_attendance, a.penalty_type, 
                                                       a.penalty_requirements, a.start_time, a.end_time
                                                FROM attendances a
                                                JOIN events e ON a.id_event = e.id_event
                                                WHERE a.id_event = ? AND e.semester_ID = ?
                                            ";
                                            $attendanceStmt = $db->prepare($attendanceQuery);
                                            $attendanceStmt->bind_param("is", $event['id_event'], $selected_semester);
                                            $attendanceStmt->execute();
                                            $attendances = $attendanceStmt->get_result();

                                            if ($attendances->num_rows > 0):
                                                while ($attendance = $attendances->fetch_assoc()): 
                                                    $startTime = new DateTime($event['date_event'] . ' ' . $attendance['start_time']);
                                                    $endTime = new DateTime($event['date_event'] . ' ' . $attendance['end_time']);
                                                    $pendingTime = clone $startTime;
                                                    $pendingTime->modify('-30 minutes');

                                                    // Determine attendance status
                                                    if ($currentDateTime < $pendingTime) {
                                                        $status_badge = '<span class="badge bg-info">Not Yet Started</span>';
                                                    } elseif ($currentDateTime >= $pendingTime && $currentDateTime < $startTime) {
                                                        $status_badge = '<span class="badge bg-warning text-dark">Pending</span>';
                                                    } elseif ($currentDateTime >= $startTime && $currentDateTime <= $endTime) {
                                                        $status_badge = '<span class="badge bg-primary">Ongoing</span>';
                                                    } else {
                                                        $status_badge = '<span class="badge bg-secondary">Ended</span>';
                                                    }

                                                    // Fetch student attendance with semester check
                                                    if ($is_student) {
                                                        $studentQuery = "
                                                            SELECT sa.status_attendance 
                                                            FROM student_attendance sa
                                                            JOIN student s ON sa.id_student = s.id_student
                                                            WHERE sa.id_attendance = ? 
                                                            AND sa.id_student = ?
                                                            AND s.semester_ID = ?
                                                        ";
                                                        $studentStmt = $db->prepare($studentQuery);
                                                        $studentStmt->bind_param("iis", $attendance['id_attendance'], $user_id, $selected_semester);
                                                        $studentStmt->execute();
                                                        $studentResult = $studentStmt->get_result();

                                                        if ($studentResult->num_rows > 0) {
                                                            $student = $studentResult->fetch_assoc();
                                                            $status_badge_class = ($student['status_attendance'] == 'Present' || $student['status_attendance'] == 'Cleared') ? 'bg-success' : 'bg-danger';

                                                            $student_status = "<span class='badge $status_badge_class'>" . $student['status_attendance'] . "</span>";
                                                        } else {
                                                            $student_status = "<span class='badge bg-secondary'>No Record</span>";
                                                        }
                                                    } else {
                                                        $student_status = "<span class='badge bg-secondary'>Not a student</span>";
                                                    }
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($attendance['type_attendance']); ?></td>
                                                <td><?php echo $status_badge; ?></td>
                                                <td><?php echo $startTime->format("h:i A"); ?></td>
                                                <td><?php echo $endTime->format("h:i A"); ?></td>
                                                <td><span class="badge bg-info"><?php echo htmlspecialchars($attendance['penalty_type']); ?></span></td>
                                                <td><?php echo htmlspecialchars($attendance['penalty_requirements']); ?></td>
                                                <td><?php echo $student_status; ?></td>
                                            </tr>
                                            <?php 
                                                endwhile;
                                            else:
                                            ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No attendance records found for this event in semester <?php echo htmlspecialchars($selected_semester); ?></td>
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
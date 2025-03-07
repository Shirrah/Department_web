<?php
require_once "../../php/db-conn.php";
$db = new Database();

date_default_timezone_set('Asia/Manila');

if (isset($_GET['event_id'])) {
    $eventId = $_GET['event_id'];

    // Get the event date
    $eventDateQuery = "SELECT name_event, date_event FROM events WHERE id_event = ?";

    $eventDateStmt = $db->db->prepare($eventDateQuery);
    $eventDateStmt->bind_param("i", $eventId);
    $eventDateStmt->execute();
    $eventDateResult = $eventDateStmt->get_result();
    $eventDateRow = $eventDateResult->fetch_assoc();
    
    $eventName = $eventDateRow['name_event']; // Store the event name
    $eventDate = $eventDateRow['date_event']; // Store the event date
    

    $attendanceQuery = "SELECT id_attendance, type_attendance, penalty_type, penalty_requirements, start_time, end_time, attendance_status
                        FROM attendances WHERE id_event = ?";
    $attendanceStmt = $db->db->prepare($attendanceQuery);
    $attendanceStmt->bind_param("i", $eventId);
    $attendanceStmt->execute();
    $attendances = $attendanceStmt->get_result();

    $currentTime = new DateTime();

    if ($attendances->num_rows > 0):
        while ($attendance = $attendances->fetch_assoc()): 
            // Combine event date with start and end times
            $startTime = new DateTime("{$eventDate} {$attendance['start_time']}");
            $endTime = new DateTime("{$eventDate} {$attendance['end_time']}");
            $attendanceStatus = $attendance['attendance_status'];

            $pendingTime = clone $startTime;
            $pendingTime->modify('-30 minutes');

            if ($currentTime < $pendingTime) {
                $newStatus = 'Not Yet Started';
            } elseif ($currentTime >= $pendingTime && $currentTime < $startTime) {
                $newStatus = 'Pending';
            } elseif ($currentTime >= $startTime && $currentTime <= $endTime) {
                $newStatus = 'Ongoing';
            } else {
                $newStatus = 'Ended';
            }

            if ($attendanceStatus !== $newStatus) {
                $updateStatusQuery = "UPDATE attendances SET attendance_status = ? WHERE id_attendance = ?";
                $updateStmt = $db->db->prepare($updateStatusQuery);
                $updateStmt->bind_param("si", $newStatus, $attendance['id_attendance']);
                $updateStmt->execute();
            }
?>
            <tr>
                <td><?php echo htmlspecialchars($attendance['type_attendance']); ?></td>
                <td>
                    <?php 
                    if ($newStatus == 'Ongoing') {
                        echo '<span class="badge bg-primary">Ongoing</span>';
                    } elseif ($newStatus == 'Ended') {
                        echo '<span class="badge bg-secondary">Ended</span>';
                    } elseif ($newStatus == 'Pending') {
                        echo '<span class="badge bg-warning text-dark">Pending</span>';
                    } else {
                        echo '<span class="badge bg-info">Not Yet Started</span>';
                    }
                    ?>
                </td>
                <td><?php echo $startTime->format("h:i A"); ?></td>
                <td><?php echo $endTime->format("h:i A"); ?></td>
                <td><span class="badge bg-info"><?php echo htmlspecialchars($attendance['penalty_type']); ?></span></td>
                <td><?php echo htmlspecialchars($attendance['penalty_requirements']); ?></td>
                <td>
                <button class="btn btn-warning btn-sm" onclick="openEditTimeModal(<?php echo $attendance['id_attendance']; ?>)">
    <i class="fas fa-clock"></i> Add Time
</button>

                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#EditAttendanceModal"
                        onclick="openEditAttendanceModal(
                            <?php echo $attendance['id_attendance']; ?>,
                            '<?php echo $attendance['type_attendance']; ?>',
                            '<?php echo $attendance['penalty_type']; ?>',
                            '<?php echo addslashes($attendance['penalty_requirements']); ?>',
                            '<?php echo $attendance['start_time']; ?>',
                            '<?php echo $attendance['end_time']; ?>')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                   <button class="btn btn-danger btn-sm" onclick="confirmDeleteAttendance(<?php echo $attendance['id_attendance']; ?>)">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#attendanceRecordsModal"
    onclick="showAttendanceRecords(
        <?php echo $attendance['id_attendance']; ?>,
        '<?php echo addslashes($eventDateRow['name_event']); ?>',
        '<?php echo addslashes($attendance['type_attendance']); ?>',
        '<?php echo date("h:i A", strtotime($attendance['start_time'])); ?>',
        '<?php echo date("h:i A", strtotime($attendance['end_time'])); ?>'
    )">
    <i class="fas fa-database"></i> Show Records
</button>


                </td>
            </tr>
<?php
        endwhile;
    else:
        echo '<tr><td colspan="7" class="text-center">No attendance records yet</td></tr>';
    endif;
}
?>

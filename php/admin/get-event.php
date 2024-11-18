<?php
require_once "././php/db-conn.php";
$db = new Database();

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Fetch event details
    $event_stmt = $db->db->prepare("SELECT id_event, name_event, date_event, event_desc FROM events WHERE id_event = ?");
    $event_stmt->bind_param("i", $event_id);
    $event_stmt->execute();
    $event_result = $event_stmt->get_result()->fetch_assoc();

    // Fetch associated attendances
    $attendance_stmt = $db->db->prepare("SELECT type_attendance, time_in, time_out FROM attendances WHERE id_event = ?");
    $attendance_stmt->bind_param("i", $event_id);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();

    $attendances = [];
    while ($attendance = $attendance_result->fetch_assoc()) {
        $attendances[] = $attendance;
    }

    // Return the event data as JSON
    echo json_encode(array_merge($event_result, ['attendances' => $attendances]));
}
?>

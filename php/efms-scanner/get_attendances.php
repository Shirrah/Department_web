<?php
require_once './../php/db-conn.php';

header('Content-Type: application/json');

if (!isset($_GET['event_ID'])) {
    echo json_encode([]);
    exit;
}

$eventId = (int)$_GET['event_ID'];
$database = Database::getInstance();
$db = $database->db;

$query = "SELECT id_attendance, CONCAT(type_attendance, ' (', TIME_FORMAT(start_time, '%h:%i %p'), ' - ', TIME_FORMAT(end_time, '%h:%i %p'), ')') AS attendance_label 
          FROM attendances 
          WHERE id_event = ? 
          ORDER BY start_time";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $eventId);
$stmt->execute();
$result = $stmt->get_result();

$attendances = [];
while ($row = $result->fetch_assoc()) {
    $attendances[] = $row;
}

echo json_encode($attendances);
?>
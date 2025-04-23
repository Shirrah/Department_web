<?php
require_once './../php/db-conn.php';

if (!isset($_GET['semester_ID'])) {
    echo json_encode([]);
    exit;
}

$semesterId = intval($_GET['semester_ID']);

$database = Database::getInstance();
$db = $database->db;

$query = "SELECT id_event, name_event 
          FROM events 
          WHERE semester_ID = ? 
          ORDER BY date_event DESC, name_event ASC";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $semesterId);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode($events);
?>

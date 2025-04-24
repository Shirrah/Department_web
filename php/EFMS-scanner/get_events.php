<?php
require_once '../../php/db-conn.php';

if (!isset($_GET['semester_ID']) || empty($_GET['semester_ID'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing semester_ID']);
    exit;
}

$semester_ID = $db->real_escape_string($_GET['semester_ID']);

$database = Database::getInstance();
$db = $database->db;

// Modified query to correctly fetch events based on semester_ID
$query = "SELECT e.id_event, e.name_event 
          FROM events e
          WHERE e.semester_ID = ? 
          ORDER BY e.date_event DESC";

$stmt = $db->prepare($query);
$stmt->bind_param('s', $semester_ID); // Ensure you bind as a string
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode($events);
?>

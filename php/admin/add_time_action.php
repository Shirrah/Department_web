<?php
// Ensure no output before headers
require_once "../../php/db-conn.php";

// Set JSON header first
header('Content-Type: application/json');

// Create response array
$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_POST['attendanceId']) || !isset($_POST['additionalTime'])) {
        throw new Exception('Missing required parameters');
    }

    $attendanceId = intval($_POST['attendanceId']);
    $additionalTime = intval($_POST['additionalTime']);

    if ($attendanceId <= 0 || $additionalTime <= 0) {
        throw new Exception('Invalid input values');
    }

    // Get the database instance
    $db = Database::getInstance()->db;

    // Fetch current end time with date
    $query = "SELECT end_time FROM attendances WHERE id_attendance = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $attendanceId);
    
    if (!$stmt->execute()) {
        throw new Exception('Database query failed');
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Attendance record not found');
    }

    $row = $result->fetch_assoc();
    $currentEndTime = new DateTime($row['end_time']);

    // Add the additional time
    $interval = new DateInterval("PT{$additionalTime}M");
    $currentEndTime->add($interval);
    $newEndTime = $currentEndTime->format('H:i:s'); // Only store time, not date

    // Update the end time in the database
    $updateQuery = "UPDATE attendances SET end_time = ? WHERE id_attendance = ?";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bind_param("si", $newEndTime, $attendanceId);

    if ($updateStmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Time extended successfully';
    } else {
        throw new Exception('Failed to update end time');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Add Time Error: " . $e->getMessage());
}

// Ensure only JSON is output
echo json_encode($response);
exit();
?>
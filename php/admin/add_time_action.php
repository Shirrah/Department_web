<?php
require_once "../../php/db-conn.php";
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceId = intval($_POST['attendanceId']);
    $additionalTime = intval($_POST['additionalTime']); // Time in minutes

    if ($attendanceId > 0 && $additionalTime > 0) {
        // Fetch current end time
        $query = "SELECT end_time FROM attendances WHERE id_attendance = ?";
        $stmt = $db->db->prepare($query);
        $stmt->bind_param("i", $attendanceId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $currentEndTime = new DateTime($row['end_time']);

            // Add the additional time
            $currentEndTime->modify("+{$additionalTime} minutes");
            $newEndTime = $currentEndTime->format('H:i:s');

            // Update the end time in the database
            $updateQuery = "UPDATE attendances SET end_time = ? WHERE id_attendance = ?";
            $updateStmt = $db->db->prepare($updateQuery);
            $updateStmt->bind_param("si", $newEndTime, $attendanceId);

            if ($updateStmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Time extended successfuly']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update end time']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Attendance record not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

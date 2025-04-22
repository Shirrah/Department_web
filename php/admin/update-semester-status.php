<?php
require_once "../db-conn.php";
$db = Database::getInstance()->db;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester_ID = $_POST['semester_ID'] ?? '';
    $status = $_POST['status'] ?? 'inactive';

    if ($semester_ID !== '') {
        $query = "UPDATE semester SET status = ? WHERE semester_ID = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ss", $status, $semester_ID);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    }
}
?>

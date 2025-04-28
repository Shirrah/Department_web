<?php
session_start();
require_once "../../php/db-conn.php";

// Check if user is authorized (admin)
if (!isset($_SESSION['user_data']['id_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$paymentId = $_POST['id_payment'] ?? '';
$studentId = $_POST['id_student'] ?? '';
$semesterId = $_POST['semester_id'] ?? '';
$status = $_POST['status'] ?? '';

// Validate inputs
if (empty($paymentId) || empty($studentId) || empty($semesterId) || !in_array($status, ['0', '1'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$db = Database::getInstance()->db;

// Update payment status
$query = "UPDATE student_fees_record 
          SET status_payment = ? 
          WHERE id_payment = ? AND id_student = ? AND semester_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("ssss", $status, $paymentId, $studentId, $semesterId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
?>
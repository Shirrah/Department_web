<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

// Validate input
if (!isset($_POST['id_payment']) || !is_numeric($_POST['id_payment'])) {
    die(json_encode(['error' => 'Invalid payment ID']));
}
$id_payment = (int)$_POST['id_payment'];

// Get semester from session
$user_id = $_SESSION['user_data']['id_admin'] ?? null;
if (!$user_id) die(json_encode(['error' => 'User not logged in']));

$selected_semester = $_SESSION['selected_semester'][$user_id] ?? null;
if (!$selected_semester) die(json_encode(['error' => 'No semester selected']));

// 1. Get payment details (we need amount)
$paymentStmt = $db->prepare("SELECT payment_amount FROM payments WHERE id_payment = ? AND semester_ID = ?");
$paymentStmt->bind_param("is", $id_payment, $selected_semester);
$paymentStmt->execute();
$paymentResult = $paymentStmt->get_result();

if ($paymentResult->num_rows === 0) {
    die(json_encode(['error' => 'Payment not found for this semester']));
}

$payment_amount = $paymentResult->fetch_assoc()['payment_amount'];
$current_date = date('Y-m-d H:i:s');

// 2. Find students who should be added
$studentsToAdd = [];
$findStmt = $db->prepare("
    SELECT s.id_student 
    FROM student s
    WHERE s.semester_ID = ?
    AND NOT EXISTS (
        SELECT 1 FROM student_fees_record sfr
        WHERE sfr.id_student = s.id_student
        AND sfr.id_payment = ?
        AND sfr.semester_ID = ?
    )
");
$findStmt->bind_param("sis", $selected_semester, $id_payment, $selected_semester);
$findStmt->execute();
$result = $findStmt->get_result();

while ($row = $result->fetch_assoc()) {
    $studentsToAdd[] = $row['id_student'];
}

// 3. Insert missing records
if (!empty($studentsToAdd)) {
    $insertedCount = 0;
    $insertStmt = $db->prepare("
        INSERT INTO student_fees_record 
        (id_payment, id_student, semester_ID, status_payment, date_payment, payment_amount)
        VALUES (?, ?, ?, 0, ?, ?)
    ");
    
    $insertedCount = 0;
    
    foreach ($studentsToAdd as $studentId) {
        $insertStmt->bind_param(
            "iissd", 
            $id_payment, 
            $studentId, 
            $selected_semester, 
            $current_date, 
            $payment_amount
        );
        
        if ($insertStmt->execute()) {
            $insertedCount++;
        }
    }
    

    echo json_encode([
        'success' => true,
        'message' => "Successfully added $insertedCount students to payment records (marked as unpaid)",
        'count' => $insertedCount
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => "All students are already registered for this payment",
        'count' => 0
    ]);
}
?>
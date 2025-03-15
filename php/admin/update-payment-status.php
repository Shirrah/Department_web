<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

// Log incoming POST data
file_put_contents("debug_log.txt", print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = $_POST['student_id'] ?? null;
    $payment_id = $_POST['payment_id'] ?? null;
    $status_payment = isset($_POST['status_payment']) ? (int) $_POST['status_payment'] : null;

    // Check if values are present and valid
    if (!$student_id) {
        echo json_encode(["success" => false, "error" => "Missing student_id"]);
        exit;
    }
    if (!$payment_id) {
        echo json_encode(["success" => false, "error" => "Missing payment_id"]);
        exit;
    }
    if ($status_payment !== 0 && $status_payment !== 1) {
        echo json_encode(["success" => false, "error" => "Invalid status_payment"]);
        exit;
    }

    // Prepare SQL statement
    $stmt = $db->prepare("UPDATE student_fees_record SET status_payment = ? WHERE id_student = ? AND id_payment = ?");
    $stmt->bind_param("iii", $status_payment, $student_id, $payment_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Database update failed", "sql_error" => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}
?>

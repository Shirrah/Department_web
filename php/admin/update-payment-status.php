<?php
require_once "../../php/db-conn.php";
$db = new Database();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = $_POST['student_id'] ?? null;
    $payment_id = $_POST['payment_id'] ?? null;
    $status_payment = $_POST['status_payment'] ?? null;

    if ($student_id && $payment_id && ($status_payment === "0" || $status_payment === "1")) {
        $stmt = $db->db->prepare("UPDATE student_fees_record SET status_payment = ? WHERE id_student = ? AND id_payment = ?");
        $stmt->bind_param("iii", $status_payment, $student_id, $payment_id);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Database update failed"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Invalid input"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}
?>

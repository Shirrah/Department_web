<?php
// Include the database connection
require_once "..//..//php/db-conn.php";
$db = new Database();

// Check if form data is received
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_id = $_POST['payment_id'];
    $student_id = $_POST['student_id'];
    $status_payment = $_POST['status_payment'];

    // Update the status_payment in the database
    $query_update = "UPDATE student_fees_record SET status_payment = ? WHERE id_payment = ? AND id_student = ?";
    $stmt_update = $db->db->prepare($query_update);
    $stmt_update->bind_param("iii", $status_payment, $payment_id, $student_id);

    if ($stmt_update->execute()) {
        // Redirect back to the previous page with a success message
        $db->db->close();
        header("Location: http://localhost/Department_web/index.php?content=admin-index&admin=fee-records&payment_id=" . $payment_id);
        exit;
    } else {
        // Error updating status
        echo "Error updating status.";
    }
}
?>

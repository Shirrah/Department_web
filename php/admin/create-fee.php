<?php
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
require_once "././php/db-conn.php";
$db = new Database();

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_name = $_POST['payment_name'];
    $payment_amount = $_POST['payment_amount'];
    $date_payment = $_POST['date_payment'];

    // Validate inputs (this is a basic example, you might want to add more validation)
    if (!empty($payment_name) && !empty($payment_amount) && !empty($date_payment)) {
        // Insert into the database
        $stmt = $db->db->prepare("INSERT INTO payments (payment_name, payment_amount, date_payment) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $payment_name, $payment_amount, $date_payment);

        if ($stmt->execute()) {
            // Redirect to the fees management page with a success message
            $_SESSION['message'] = "Fee created successfully!";
            header("Location: admin-fees.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to create fee.";
            header("Location: admin-fees.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: admin-fees.php");
        exit();
    }
} else {
    // Redirect if the user tries to access the page directly
    header("Location: admin-fees.php");
    exit();
}
?>

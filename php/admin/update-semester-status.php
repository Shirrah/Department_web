<?php
require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $semester_ID = htmlspecialchars($_POST['semester_ID']);
    $status = htmlspecialchars($_POST['status']);

    // Validate status
    if (!in_array($status, ['active', 'inactive'])) {
        echo "Invalid status";
        exit();
    }

    // Update the selected semester's status
    $updateQuery = "UPDATE semester SET status = ? WHERE semester_ID = ?";
    $stmt = $db->prepare($updateQuery);
    $stmt->bind_param("ss", $status, $semester_ID);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error updating status: " . $stmt->error;
    }
} else {
    echo "Invalid request method";
}
?>

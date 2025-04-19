<?php
ob_start();


require_once "../../php/db-conn.php";

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405); // Method Not Allowed
    exit(json_encode(['success' => false, 'message' => 'Only POST requests allowed']));
}

// Get database connection
$db = Database::getInstance()->db;

// Sanitize and validate inputs
$id_student = filter_input(INPUT_POST, 'id_student', FILTER_SANITIZE_STRING);
$pass_student = filter_input(INPUT_POST, 'pass_student', FILTER_SANITIZE_STRING);
$lastname_student = filter_input(INPUT_POST, 'lastname_student', FILTER_SANITIZE_STRING);
$firstname_student = filter_input(INPUT_POST, 'firstname_student', FILTER_SANITIZE_STRING);
$year_student = filter_input(INPUT_POST, 'year_student', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 4]
]);

// Validate all required fields
if (!$id_student || !$pass_student || !$lastname_student || !$firstname_student || !$year_student) {
    http_response_code(400); // Bad Request
    exit(json_encode(['success' => false, 'message' => 'All fields are required']));
}

try {
    // Prepare and execute update statement
    $stmt = $db->prepare("UPDATE student SET 
        pass_student = ?, 
        lastname_student = ?, 
        firstname_student = ?, 
        year_student = ? 
        WHERE id_student = ?");
    
    $stmt->bind_param("sssss", $pass_student, $lastname_student, $firstname_student, $year_student, $id_student);
    
    if ($stmt->execute()) {
        // Return JSON response for AJAX handling
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
    } else {
        throw new Exception("Database update failed");
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()]);
}

$stmt->close();
$db->close();
ob_end_clean();
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
exit();
?>
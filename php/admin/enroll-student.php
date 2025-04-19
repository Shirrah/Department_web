<?php
require_once '..//..//php/db-conn.php'; // Include the database connection file

// Retrieve the connection from the Database singleton instance
$db = Database::getInstance()->db;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_student = htmlspecialchars($_POST['id_student']);
    $pass_student = htmlspecialchars($_POST['pass_student']);
    $lastname_student = htmlspecialchars($_POST['lastname_student']);
    $firstname_student = htmlspecialchars($_POST['firstname_student']);
    $year_student = htmlspecialchars($_POST['year_student']);
    $semester_ID = htmlspecialchars($_POST['semester_ID']);

    $insertQuery = "INSERT INTO student (id_student, semester_ID, pass_student, lastname_student, firstname_student, role_student, year_student) 
                    VALUES (?, ?, ?, ?, ?, 'Student', ?)";
                    
    $stmt = $db->prepare($insertQuery);
    $stmt->bind_param("sssssi", $id_student, $semester_ID, $pass_student, $lastname_student, $firstname_student, $year_student);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error enrolling student: " . $stmt->error;
    }
}
?>

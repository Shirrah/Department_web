<?php
session_start();

// Set session timeout to a very short period (in seconds)
ini_set('session.gc_maxlifetime', 10); // session expires after 10 seconds of inactivity

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username and password are empty
    if (empty($_POST['id']) || empty($_POST['psw'])) {
        $_SESSION['error_msg'] = 'Please fill in all fields!';
        header("location: ../index.php?content=log-in");
        exit();
    }

    require_once "db-conn.php";
    // Get the database connection instance
    $db = new Database();

    $id = ($_POST['id']);
    $pword = ($_POST['psw']);

    // Check against admins table
    $admin_query = "SELECT * FROM `admins` WHERE `id_admin` = '$id'";
    $admin_result = $db->db->query($admin_query);

    if ($admin_result && $admin_result->num_rows > 0) {
        $row = $admin_result->fetch_assoc();
        if ($pword == $row["pass_admin"]) {

            $_SESSION['logged_in'] = 'yes';
            $_SESSION['user_data'] = $row; // Storing admin details in the session
            header("location: ../index.php?content=admin-index");
            exit();
        }
    }

    // Check against students table
    $student_query = "SELECT * FROM `student` WHERE `id_student` = '$id'";
    $student_result = $db->db->query($student_query);

    if ($student_result && $student_result->num_rows > 0) {
        $row = $student_result->fetch_assoc();
        if ($pword == $row["pass_student"]) {

            $_SESSION['logged_in'] = 'yes';
            $_SESSION['user_data'] = $row; // Storing admin details in the session
            header("location: ../index.php?content=student-index");
            exit();
        }
    }

    $_SESSION['error_msg'] = 'Invalid id or password.';
    header("location: ../index.php?content=log-in");
    exit();
} else {
    // Redirect them to the login page or handle unauthorized access
    $_SESSION['error_msg'] = 'Please log in.';
    header("location: ../index.php?content=log-in");
    exit();
}
?>

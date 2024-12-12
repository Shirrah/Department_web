<?php
session_set_cookie_params(0); 
session_start();
// Set session timeout in seconds (e.g., 180 for 3 minutes)
$timeout = 180;

// Check for inactivity timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
    session_unset(); // Clear session variables
    session_destroy(); // Destroy session
    header("location: ../index.php?content=log-in"); // Redirect to login
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity timestamp

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

            // Get the latest semester
                header("location: ../index.php?content=admin-index&semester=$semester_id");
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
            $_SESSION['user_data'] = $row; // Storing student details in the session
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

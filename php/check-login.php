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

// Define the maximum allowed login attempts and lockout time (5 minutes)
$max_attempts = 5;
$lockout_time = 5 * 60; // 5 minutes in seconds

// Check if the form is submitted and process login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username and password are empty
    if (empty($_POST['id']) || empty($_POST['psw'])) {
        $_SESSION['error_msg'] = 'Please fill in all fields!';
        header("location: ../index.php?content=log-in");
        exit();
    }

    // Check if user is locked out
    if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
        $time_remaining_seconds = $_SESSION['lockout_time'] - time(); // Remaining time in seconds
        $minutes = floor($time_remaining_seconds / 60); // Convert seconds to minutes
        $seconds = $time_remaining_seconds % 60; // Get the remaining seconds

        // Format the remaining time as MM:SS
        $formatted_time = sprintf("%d:%02d", $minutes, $seconds);

        $_SESSION['error_msg'] = "Too many failed attempts. Please try again in $formatted_time minutes.";
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

            // Reset login attempts on successful login
            $_SESSION['login_attempts'] = 0;

            $_SESSION['logged_in'] = 'yes';
            $_SESSION['user_data'] = $row; // Storing admin details in the session

            // Fetch the latest semester (if it exists)
            $semester_query = "SELECT * FROM `semester` ORDER BY `date_created` DESC LIMIT 1";
            $semester_result = $db->db->query($semester_query);

            if ($semester_result && $semester_result->num_rows > 0) {
                $semester_row = $semester_result->fetch_assoc();
                $semester_id = $semester_row['semester_ID'];
                $_SESSION['semester_data'] = $semester_row; // Optional: Store semester details in session
                header("location: ../index.php?content=admin-index&semester=$semester_id");
            } else {
                // Proceed even if no semester is found
                $_SESSION['error_msg'] = 'No semester found, you can still proceed without a semester.';
                header("location: ../index.php?content=admin-index");
            }
            exit();
        }
    }

    // Check against students table
    $student_query = "SELECT * FROM `student` WHERE `id_student` = '$id'";
    $student_result = $db->db->query($student_query);

    if ($student_result && $student_result->num_rows > 0) {
        $row = $student_result->fetch_assoc();
        if ($pword == $row["pass_student"]) {

            // Reset login attempts on successful login
            $_SESSION['login_attempts'] = 0;

            $_SESSION['logged_in'] = 'yes';
            $_SESSION['user_data'] = $row; // Storing student details in the session
            header("location: ../index.php?content=student-index");
            exit();
        }
    }

    // If login failed, track attempts
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }

    $_SESSION['login_attempts']++;

    // If the maximum attempts have been reached, lock the user out for 5 minutes
    if ($_SESSION['login_attempts'] >= $max_attempts) {
        $_SESSION['lockout_time'] = time() + $lockout_time;
        $_SESSION['error_msg'] = 'Too many failed attempts. Please try again in 5 minutes.';
        header("location: ../index.php?content=log-in");
        exit();
    } else {
        $_SESSION['error_msg'] = 'Invalid ID or Password. ' . ( $max_attempts - $_SESSION['login_attempts']) . ' attempts remaining.';
        header("location: ../index.php?content=log-in");
        exit();
    }
} else {
    // Redirect them to the login page or handle unauthorized access
    $_SESSION['error_msg'] = 'Please log in.';
    header("location: ../index.php?content=log-in");
    exit();
}
?>

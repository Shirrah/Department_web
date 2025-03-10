<?php

session_set_cookie_params(0);
session_start();

require_once "db-conn.php";
$db = Database::getInstance()->db; // Use singleton pattern

// Set session timeout (3 minutes)
$timeout = 180;

// Check for inactivity timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
    session_unset();
    session_destroy();
    header("location: ../index.php?content=log-in");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();

// Max login attempts
$max_attempts = 5;
$lockout_time = 5 * 60;

// Check for lockout
if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    $_SESSION['error_msg'] = "Too many failed attempts. Try again later.";
    header("location: ../index.php?content=log-in");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['id']) || empty($_POST['psw'])) {
        $_SESSION['error_msg'] = 'Please fill in all fields!';
        header("location: ../index.php?content=log-in");
        exit();
    }

    $id = $_POST['id'];
    $pword = $_POST['psw'];

    // Check Admins Table
    $stmt = $db->prepare("SELECT * FROM `admins` WHERE `id_admin` = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $admin_result = $stmt->get_result();

    if ($admin_result->num_rows > 0) {
        $row = $admin_result->fetch_assoc();
        if ($pword === $row["pass_admin"]) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['logged_in'] = 'yes';
            $_SESSION['user_data'] = $row;

            // Get latest semester
            $stmt = $db->prepare("SELECT * FROM `semester` ORDER BY `date_created` DESC LIMIT 1");
            $stmt->execute();
            $semester_result = $stmt->get_result();

            if ($semester_result->num_rows > 0) {
                $semester_row = $semester_result->fetch_assoc();
                $_SESSION['semester_data'] = $semester_row;
                $semester_id = $semester_row['semester_ID'];
                header("location: ../index.php?content=admin-index&semester=$semester_id");
            } else {
                header("location: ../index.php?content=admin-index");
            }
            exit();
        }
    }

    // Check Students Table
    $stmt = $db->prepare("SELECT * FROM `student` WHERE `id_student` = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $student_result = $stmt->get_result();

    if ($student_result->num_rows > 0) {
        $row = $student_result->fetch_assoc();
        if ($pword === $row["pass_student"]) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['logged_in'] = 'yes';
            $_SESSION['user_data'] = $row;
            header("location: ../index.php?content=student-index");
            exit();
        }
    }

    // Failed login attempt
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

    if ($_SESSION['login_attempts'] >= $max_attempts) {
        $_SESSION['lockout_time'] = time() + $lockout_time;
        $_SESSION['error_msg'] = 'Too many failed attempts. Try again in 5 minutes.';
    } else {
        $_SESSION['error_msg'] = 'Invalid ID or Password. ' . ($max_attempts - $_SESSION['login_attempts']) . ' attempts remaining.';
    }

    header("location: ../index.php?content=log-in");
    exit();
}
?>

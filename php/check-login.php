<?php
session_set_cookie_params(0);
session_start();

header('Content-Type: application/json'); // Important for AJAX

require_once "db-conn.php";
$db = Database::getInstance()->db;

// Login lockout logic
$max_attempts = 5;
$lockout_time = 5 * 60;

if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Too many failed attempts. Try again later.'
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_POST['id']) || empty($_POST['psw'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please fill in all fields!'
        ]);
        exit();
    }

    $id = $_POST['id'];
    $pword = $_POST['psw'];

    // Check Admins
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

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful!',
                'redirect' => 'index.php?content=admin-index'
            ]);
            exit();
        }
    }

    // Check Students
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

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful!',
                'redirect' => 'index.php?content=student-index'
            ]);
            exit();
        }
    }

    // Handle failed login
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

    if ($_SESSION['login_attempts'] >= $max_attempts) {
        $_SESSION['lockout_time'] = time() + $lockout_time;
        $msg = 'Too many failed attempts. Try again in 5 minutes.';
    } else {
        $remaining = $max_attempts - $_SESSION['login_attempts'];
        $msg = "Invalid ID or Password. $remaining attempt(s) remaining.";
    }

    echo json_encode([
        'status' => 'error',
        'message' => $msg
    ]);
    exit();
}

<?php
session_start();

// Hardcoded credentials (for example purposes)
$valid_username = 'admin';
$valid_password = 'password123';

// Maximum allowed login attempts
$max_attempts = 3;

// Lockout time in seconds (5 minutes)
$lockout_time = 5 * 60;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the user is locked out
    if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
        $time_remaining = ($_SESSION['lockout_time'] - time()) / 60; // in minutes
        $error_message = "Too many failed attempts. Please try again in $time_remaining minutes.";
        header("Location: ?error=$error_message");
        exit();
    }

    // Reset attempts if lockout time has passed
    if (isset($_SESSION['login_attempts']) && time() > $_SESSION['last_attempt_time'] + $lockout_time) {
        $_SESSION['login_attempts'] = 0;
    }

    // Check credentials
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['login_attempts'] = 0;  // Reset login attempts on successful login
        echo "Welcome, $username!";
        // Redirect to another page (e.g., dashboard)
    } else {
        // Track login attempts
        $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
        $_SESSION['last_attempt_time'] = time();

        if ($_SESSION['login_attempts'] >= $max_attempts) {
            // Lock out user for 5 minutes
            $_SESSION['lockout_time'] = time() + $lockout_time;
            $error_message = "Too many failed attempts. Please try again after 5 minutes.";
            header("Location: ?error=$error_message");
        } else {
            $error_message = "Incorrect credentials. You have " . ($max_attempts - $_SESSION['login_attempts']) . " attempts remaining.";
            header("Location: ?error=$error_message");
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
</head>
<body>

    <h2>Login</h2>

    <form action="" method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br><br>
        <label for="password">Password:</label>
        <input type="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>

    <?php
    if (isset($_GET['error'])) {
        echo "<p style='color:red;'>".$_GET['error']."</p>";
    }
    ?>

</body>
</html>

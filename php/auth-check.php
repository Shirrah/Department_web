<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== 'yes') {
    // Redirect to login page with an optional message
    header("location: ../index.php?content=log-in&error=unauthorized");
    exit();
}
?>

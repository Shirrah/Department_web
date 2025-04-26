<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== 'yes') {
    // Optionally set an error message in session before redirecting
    $_SESSION['error_message'] = 'You must log in first.';
    
    // Redirect to login page with an optional error message
    header("location: ?content=log-in");
    exit();
}
?>

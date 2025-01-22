<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === 'yes') {
    // Redirect logged-in user to the dashboard or another page
    header("location: index.php?content=admin-index&admin=dashboard");  // Replace with your desired page
    exit();
}
?>

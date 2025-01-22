<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}



// Check if the user is not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== 'yes') {
    // Redirect to login page with an optional error message
    header("location: index.php?content=log-in");
    exit();
}
?>

<img src="../" alt="">
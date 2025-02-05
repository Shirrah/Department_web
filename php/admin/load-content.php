<?php
// index.php

if (isset($_GET['content']) && isset($_GET['admin'])) {
    $content = $_GET['content'];
    $admin = $_GET['admin'];

    // Load the corresponding content dynamically based on the 'admin' parameter
    switch ($admin) {
        case 'dashboard':
            include('dashboard.php');
            break;
        case 'student-management':
            include('student-management.php');
            break;
        case 'admin-access-management':
            include('admin-access-management.php');
            break;
        case 'ay-dashboard':
            include('ay-dashboard.php');
            break;
        case 'manage-events':
            include('manage-events.php');
            break;
        case 'manage-fees':
            include('manage-fees.php');
            break;
        case 'history':
            include('history.php');
            break;
        default:
            echo "Content not found.";
            break;
    }
} else {
    echo "Please select a valid option from the sidebar.";
}
?>

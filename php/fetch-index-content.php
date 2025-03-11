<?php
if (isset($_GET['content'])) {
    $content_pg = $_GET['content'];

    switch ($content_pg) {
        case "default":
            include 'php/default.php';
            break;
        case "log-in":
            include 'php/login-new.php';
            break;
        case "admin-index":
            include 'php/admin/adminindex.php';
            break;
        case "student-index":
            include 'php/student/student-index.php';
            break;
        default:
            echo "<h2>Error</h2><p>Page not found.</p>";
            break;
    }
}
?>

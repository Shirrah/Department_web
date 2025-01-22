<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start(); // Start output buffering
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SJC - College of Computer Studies</title>
    <link rel="icon" href="assets/images/ccslogo.png" type="image/icon type">
    <link rel="stylesheet" href="stylesheet/index.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <div class="header">
            <?php
            // Define pages where header should be excluded
            $exclude_header_pages = ['log-in', 'admin-index', 'student-index'];
            
            // Define pages where footer should be excluded
            $exclude_footer_pages = ['admin-index', 'log-in', 'student-index'];

            // Get the current content page
            $content_pg = isset($_GET['content']) ? $_GET['content'] : 'default';

            // Include header if the current page is not in the exclusion list
            if (!in_array($content_pg, $exclude_header_pages)) {
                echo '<div class="header">';
                require_once "php/header.php";
                echo '</div>';
            }
            ?>
    </div>

    <div class="content" >
        <?php
        if(isset($_GET['content'])){
            $content_pg = $_GET['content'];
        }else{
                $content_pg = "default";
            }

            switch($content_pg){
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
                case "logout":
                    session_destroy();
                    header("Location: index.php?content=log-in");
                    exit;
                    break;
                default:
                    include 'php/default.php'; // Fallback to default
                    break;
            }
        ?>
    </div>

    <div class="footer">
        <?php
        // Include footer if the current page is not in the exclusion list
        if (!in_array($content_pg, $exclude_footer_pages)) {
            echo '<div class="footer">';
            require_once "php/footer.php";
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>

<?php 
    session_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SJC - College of Computer Studies</title>
    <link rel="icon" href="assets/images/ccslogo.png" type="image/icon type">
    <link rel="stylesheet" href="stylesheet/index.css">
</head>
<body>
    <div class="header">
        <?php
        require_once "php/header.php";
        ?>
    </div>

    <div class="content">
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
                    include 'php/log-in.php';
                    break;
                case "admin-index":
                    include 'php/admin/admin-index.php';
                    break;
                case "student-index":
                    include 'php/student/student-index.php';
                    break;
                case "logout":
                    session_destroy();
                    header("Location: index.php?page=log-in");
                    break;
                default:
                    include 'php/default.php'; // Fallback to default
                    break;
            }
        ?>
    </div>

    <div class="footer">
        <?php
        require_once "php/footer.php";
        ?>
    </div>
</body>
</html>

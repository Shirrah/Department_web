<?php

// Include the database connection
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

include "././php/auth-check.php";
include_once __DIR__ . "/../toast-system.php"; // Update the path to use absolute path

// Fetch the user details from the session 
if (isset($_SESSION['user_data'])) {
    $user_data = $_SESSION['user_data'];

    // Check if user is an admin or a student and display their details accordingly
    if (isset($user_data['lastname_admin'])) {
        // Admin user
        $last_name = $user_data['lastname_admin'];
        $role = $user_data['role_admin'];
    } else if (isset($user_data['lastname_student'])) {
        // Student user
        $last_name = $user_data['lastname_student'];
        $role = $user_data['role_student'];
    }
}

?>


<link rel="stylesheet" href=".//.//stylesheet/student/student-index.css">

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<link rel="stylesheet" href="http://localhost/Department_web/stylesheet/admin/admin-index.css">

<style>
    /* Ensure the whole page takes the full height */
    html, body {
        height: 100%;
        margin: 0;
    }

    .main-content-con{
        overflow: hidden;
    }

    .main-content {
        display: flex;
        height: 100%; /* Ensure the main content takes up the full height */
    }

    .sidebar {
        width: 250px;
        background-color: rgba(var(--bs-dark-rgb), var(--bs-bg-opacity)) !important;
        padding: 15px;
        height: 100%; /* Sidebar takes the full height */
        overflow-y: auto; /* Allow scrolling inside sidebar if necessary */
    }

    .content {
        flex-grow: 1;
        background-color: #e9ecef;
        height: 100%; /* Content fills the rest of the height */
        overflow-y: auto; /* Make content scrollable when it overflows */
    }

    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }

    .nav-pills .nav-link.active {
        background-color: tomato;
        color: #ffffff;
    }

    .nav-link i.bi-chevron-down {
        transition: transform 0.3s ease;
    }

    .nav-link.collapsed i.bi-chevron-down {
        transform: rotate(180deg);
    }

    .nav-link:not(.collapsed) i.bi-chevron-down {
        transform: rotate(0deg);
    }

    .navbar .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        max-width: 250px;  /* Limit the width of the dropdown */
        overflow-x: auto;  /* Enable horizontal scrolling if content overflows */
    }

    .navbar, .admin-sidebar-con, .offcanvas {
        background-color: rgba(var(--bs-dark-rgb), var(--bs-bg-opacity)) !important;
    }
</style>

<div class="main-content-con">
<nav class="navbar bg-dark">
    <div class="container-fluid d-flex justify-content-between">
        <!-- Sidebar Toggle Button for Mobile -->
<button class="btn btn-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
    ☰
</button>

        <a class="navbar-brand text-white" href="#">
            <img src="././assets/images/sys-logo.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top">
            EFMS
        </a>
        <!-- Dropdown added to the right (flex end) -->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?php echo $last_name . ' - ' . $role; ?>
                </a>
                <!-- Dropdown menu with proper positioning -->
                <ul class="dropdown-menu position-absolute" style="right: 0; left: auto; z-index: 1000;">
                    <li><a class="dropdown-item" href="#">Version <?php echo $site_version; ?></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="?content=log-out">Sign out</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<div class="main-content">

<nav class="admin-sidebar-con bg-dark text-white vh-100 p-3 d-none d-lg-block" style="padding: 15px;">
<ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
            <a href="?content=student-index&student=student-dashboard" class="nav-link text-white">
                    <i class="bi bi-speedometer2"></i>
                    <span class="ms-1 d-none d-sm-inline">Dashboard</span>
                </a>
            </li>

            <li>
            <a href="?content=student-index&student=student-events" class="nav-link text-white">
                    <i class="bi bi-calendar"></i>
                    <span class="ms-1 d-none d-sm-inline">Events</span>
                </a>
            </li>
            <li>
            <a href="?content=student-index&student=student-fees" class="nav-link text-white">
                    <i class="bi bi-cash-coin"></i>
                    <span class="ms-1 d-none d-sm-inline">Fees</span>
                </a>
            </li>
            <li>
            <a href="?content=student-index&student=student-qrcode" class="nav-link text-white">
            <i class="bi bi-qr-code"></i>
                    <span class="ms-1 d-none d-sm-inline">Qr code</span>
                </a>
            </li>
            <li>
            <a href="?content=student-index&student=student-feedback" class="nav-link text-white">
                    <i class="bi bi-bug"></i>
                    <span class="ms-1 d-none d-sm-inline">Feedback</span>
                </a>
            </li>
            <!-- <li>
            <a href="?content=student-index&student=notifications" class="nav-link text-white">
                    <i class="bi bi-bell"></i>
                    <span class="ms-1 d-none d-sm-inline">Notifications</span>
                </a>
            </li> -->
        </ul>
</nav>

<!-- Offcanvas Sidebar for Mobile -->
<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileSidebarLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav nav-pills flex-column mb-auto">
            <li>
                <a href="?content=student-index&student=student-dashboard" class="nav-link text-white">
                    <i class="bi bi-speedometer2"></i>
                    <span class="ms-1">Dashboard</span>
                </a>
            </li>

            <li>
                <a href="?content=student-index&student=student-events" class="nav-link text-white">
                    <i class="bi bi-calendar"></i>
                    <span class="ms-1">Events</span>
                </a>
            </li>

            <li>
                <a href="?content=student-index&student=student-fees" class="nav-link text-white">
                    <i class="bi bi-cash-coin"></i>
                    <span class="ms-1">Fees</span>
                </a>
            </li>

            <li>
                <a href="?content=student-index&student=student-qrcode" class="nav-link text-white">
                    <i class="fa-solid fa-qrcode"></i>
                    <span class="ms-1">Qr code</span>
                </a>
            </li>

            <li>
                <a href="?content=student-index&student=student-feedback" class="nav-link text-white">
                    <i class="bi bi-bug"></i>
                    <span class="ms-1">Feedback</span>
                </a>
            </li>

            <!-- <li>
                <a href="?content=student-index&student=notifications" class="nav-link text-white">
                    <i class="bi bi-bell"></i>
                    <span class="ms-1">Notifications</span>
                </a>
            </li> -->
        </ul>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    let sidebarLinks = document.querySelectorAll(".sidebar-link");
    let mobileSidebar = document.getElementById("mobileSidebar");

    sidebarLinks.forEach(link => {
        link.addEventListener("click", function () {
            let offcanvas = bootstrap.Offcanvas.getInstance(mobileSidebar);
            if (offcanvas) {
                offcanvas.hide(); // Closes the sidebar
            }
        });
    });
});

</script>

<script>
   window.onload = function() {
    const currentURL = window.location.href;
    
    // Set active link based on query parameter
    document.querySelectorAll('.nav-link').forEach(link => {
        if (currentURL.includes(link.getAttribute('href'))) {
            link.classList.add('active'); // Add active class
            const parentMenu = link.closest('.collapse');
            if (parentMenu) {
                parentMenu.classList.add('show'); // Expand menu if needed
            }
        }
    });

    // Toggle menu visibility on link click
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelectorAll('.nav-link').forEach(item => item.classList.remove('active')); // Remove 'active' from all links
            this.classList.add('active'); // Add 'active' to clicked link
        });
    });
};
</script>

<div class="content" id="admin-content" style="position: relative; min-height: 300px;">
    <div id="loading-indicator" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="spinner-border" style="color: tomato; width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <?php
        $student_pg = $_GET['student'] ?? "default";
        
        // Handle success/error messages from URL parameters
        if (isset($_GET['status']) && isset($_GET['message'])) {
            $status = htmlspecialchars($_GET['status']);
            $message = htmlspecialchars($_GET['message']);
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    createToast('$status', '$message');
                });
            </script>";
        }
        
        switch ($student_pg) {
            case "default":
                include 'php/student/student-dashboard.php';
                break;
            case "student-events":
                include 'php/student/student-events.php';
                break;
            case "student-dashboard":
                include 'php/student/student-dashboard.php';
                break;
            case "student-fees":
                include 'php/student/student-fees.php';
                break;
            case "student-qrcode":
                include 'php/student/student-qrcode.php';
                break;
            case "student-feedback":
                include 'php/student/student-feedback.php';
                break;
            case "notifications":
                include 'php/student/notifications.php';
                break;
            default:
            include 'php/student/student-events.php';
                break;
        }
    ?>
</div>


</div>
    </div>
</body>
</html>


<?php

// Include the database connection
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

include "././php/auth-check.php";

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<style>
    .main-content-con{
        overflow: hidden;
    }

    .main-content {
        display: flex;
        height: 100%; /* Ensure the main content takes up the full height */
    }

    .sidebar {
        width: 250px;
        background-color: #343a40;
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

</style>

<div class="main-content-con">
    <div class="bg-dark">
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
        <?php // Include the semester selection component
require_once "././php/admin/semester-selection.php";
?>
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

    </div>

<div class="main-content">

<nav class="admin-sidebar-con bg-dark text-white vh-100 p-3 d-none d-lg-block" style="padding: 15px;">
<ul class="nav nav-pills flex-column mb-auto">

            <li class="nav-item">
                <a href="?content=admin-index&admin=dashboard" class="nav-link text-white action-btn">
                    <i class="bi bi-speedometer2"></i>
                    <span class="ms-1 d-none d-sm-inline">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#studentsMenu" class="nav-link text-white d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false">
                    <span>
                        <i class="bi bi-person"></i>
                        <span class="ms-1 d-none d-sm-inline">Students</span>
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse" id="studentsMenu">
                    <ul class="nav flex-column ps-3">
                        <li>
                            <a href="?content=admin-index&admin=student-management" class="nav-link text-white action-btn"><i class="bi bi-chevron-right"></i> <span class="d-none d-sm-inline">Manage Students</span></a>
                        </li>
                    </ul>
                </div>
            </li>

            <li>    
                <a href="#adminsMenu" class="nav-link text-white d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false">
                    <span>
                        <i class="bi bi-person"></i>
                        <span class="ms-1 d-none d-sm-inline">Admins & Roles</span>
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse" id="adminsMenu">
                    <ul class="nav flex-column ps-3">
                        <li>
                            <a href="?content=admin-index&admin=admin-management" class="nav-link text-white action-btn"><i class="bi bi-chevron-right"></i>  <span class="d-none d-sm-inline">Manage Admins</span></a>
                        </li>
                    </ul>
                </div>
            </li>

            <li>
                <a href="#eventsFeesMenu" class="nav-link text-white d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false">
                    <span>
                        <i class="bi bi-cash-coin"></i>
                        <span class="ms-1 d-none d-sm-inline">Events & Fees</span>
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse" id="eventsFeesMenu">
                    <ul class="nav flex-column ps-3">
                        <li>
                            <a href="?content=admin-index&admin=event-management&admin_events=admin-events" class="nav-link text-white action-btn"><i class="bi bi-chevron-right"></i>   <span class="d-none d-sm-inline">Manage Events</span></a>
                        </li>
                        <li>
                            <a href="?content=admin-index&admin=event-management&admin_events=admin-fees" class="nav-link text-white action-btn"><i class="bi bi-chevron-right"></i>   <span class="d-none d-sm-inline">Manage Fees</span></a>
                        </li>
                    </ul>
                </div>
            </li>

            <li>
                <a href="?content=admin-index&admin=financial-statement" class="nav-link text-white action-btn">
                <i class="bi bi-files"></i>
                    <span class="ms-1 d-none d-sm-inline">Financial Statement</span>
                </a>
            </li>

            <li>
                <a href="?content=admin-index&admin=ay-dashboard" class="nav-link text-white action-btn">
                    <i class="bi bi-calendar"></i>
                    <span class="ms-1 d-none d-sm-inline">Academic Year</span>
                </a>
            </li>

            <li>
                <a href="?content=admin-index&admin=admin-feedback" class="nav-link text-white action-btn">
                    <i class="bi bi-chat-dots"></i>
                    <span class="ms-1 d-none d-sm-inline">Feedback Management</span>
                </a>
            </li>
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
            <li class="nav-item">
                <a href="?content=admin-index&admin=dashboard" class="nav-link text-white action-btn sidebar-link">
                    <i class="bi bi-speedometer2"></i>
                    <span class="">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#studentsMenu" class="nav-link text-white d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false">
                    <span>
                        <i class="bi bi-person"></i>
                        <span class="">Students</span>
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse" id="studentsMenu">
                    <ul class="nav flex-column ps-3">
                        <li>
                            <a href="?content=admin-index&admin=student-management" class="nav-link text-white action-btn sidebar-link"><i class="bi bi-chevron-right"></i> <span class="">Manage Students</span></a>
                        </li>
                    </ul>
                </div>
            </li>
            <li>    
                <a href="#adminsMenu" class="nav-link text-white d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false">
                    <span>
                        <i class="bi bi-person"></i>
                        <span class="">Admins & Roles</span>
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse" id="adminsMenu">
                    <ul class="nav flex-column ps-3">
                        <li>
                            <a href="?content=admin-index&admin=admin-management" class="nav-link text-white action-btn sidebar-link"><i class="bi bi-chevron-right"></i>  <span class="">Manage Admins</span></a>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <a href="#eventsFeesMenu" class="nav-link text-white d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false">
                    <span>
                        <i class="bi bi-cash-coin"></i>
                        <span class="">Events & Fees</span>
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse" id="eventsFeesMenu">
                    <ul class="nav flex-column ps-3">
                        <li>
                            <a href="?content=admin-index&admin=event-management&admin_events=admin-events" class="nav-link text-white action-btn sidebar-link"><i class="bi bi-chevron-right"></i>   <span class="">Manage Events</span></a>
                        </li>
                        <li>
                            <a href="?content=admin-index&admin=event-management&admin_events=admin-fees" class="nav-link text-white action-btn sidebar-link"><i class="bi bi-chevron-right"></i>   <span class="">Manage Fees</span></a>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <a href="?content=admin-index&admin=financial-statement" class="nav-link text-white action-btn">
                <i class="bi bi-files"></i>
                    <span class="">Financial Statement</span>
                </a>
            </li>
            <li>
                <a href="?content=admin-index&admin=ay-dashboard" class="nav-link text-white action-btn sidebar-link">
                    <i class="bi bi-calendar"></i>
                    <span class="">Academic Year</span>
                </a>
            </li>
            <li>
                <a href="?content=admin-index&admin=admin-feedback" class="nav-link text-white action-btn sidebar-link">
                    <i class="bi bi-chat-dots"></i>
                    <span class="">Feedback Management</span>
                </a>
            </li>
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
        const href = link.getAttribute('href');
        // Check for exact URL match for dashboard
        if (href === '?content=admin-index&admin=dashboard' && 
            (currentURL.endsWith('index.php?content=admin-index') || 
             currentURL.endsWith('index.php?content=admin-index&admin=dashboard'))) {
            link.classList.add('active');
        } 
        // For other links, check if URL contains the href
        else if (currentURL.includes(href)) {
            link.classList.add('active');
            const parentMenu = link.closest('.collapse');
            if (parentMenu) {
                parentMenu.classList.add('show');
            }
        }
    });

    // Toggle menu visibility on link click
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelectorAll('.nav-link').forEach(item => item.classList.remove('active'));
            this.classList.add('active');
        });
    });
};
</script>

<div class="content" id="admin-content">
    <div id="loading-indicator" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="spinner-border" style="color: tomato; width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <?php
        $admin_pg = $_GET['admin'] ?? "dashboard";
        switch ($admin_pg) {
            case "ay-dashboard":
                include 'php/admin/ay-dashboard.php';
                break;
            case "admin-management":
                include 'php/admin/admin-management.php';
                break;
            case "admin-access-management":
                include 'php/admin/admin-access-management.php';
                break;
            case "student-management":
                include 'php/admin/student-management.php';
                break;
            case "event-management":
                include 'php/admin/event-management.php';
                break;
            case "notifications":
                include 'php/admin/notifications.php';
                break;
            case "attendance-records":
                include 'php/admin/show-attendance-records.php';
                break;
            case "fee-records":
                include 'php/admin/show-fee-records.php';
                break;
            case "financial-statement":
                include 'php/admin/Financial Statement/financial-statement.php';
                break;
            case "admin-feedback":
                include 'php/admin/admin-feedback.php';
                break;
            default:
                include 'php/admin/dashboard.php';
        }
    ?>
</div>


</div>
    </div>
</body>
</html>


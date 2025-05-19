<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}  

require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

$page_url = $_SERVER['REQUEST_URI']; // Current page URL

// First, check if the page_url already exists
$sql = "SELECT visit_count FROM page_counter WHERE page_url = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("s", $page_url);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
    // Page exists, increment the counter
    $sql = "UPDATE page_counter SET visit_count = visit_count + 1 WHERE page_url = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $page_url);
    $stmt->execute();
    $stmt->close();
} else {
    // Page doesn't exist yet, insert new record
    $sql = "INSERT INTO page_counter (page_url, visit_count) VALUES (?, 1)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $page_url);
    $stmt->execute();
    $stmt->close();
}

// Now fetch the updated count
$sql = "SELECT visit_count FROM page_counter WHERE page_url = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("s", $page_url);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
ob_start(); // Start output buffering
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS - Event and Fee Management System</title>
    <link rel="icon" href="assets/images/ccslogo.png" type="image/icon type">
    <link rel="stylesheet" href="stylesheet/index.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add jQuery (CDN version) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Add NProgress -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
    <style>
        #nprogress .bar {
            background: tomato !important;
            height: 3px !important;
            position: fixed !important;
            z-index: 1031 !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
        }
        #nprogress .peg {
            box-shadow: 0 0 10px tomato, 0 0 5px tomato !important;
        }
        #nprogress .spinner {
            display: none !important;
        }
    </style>
<?php
$versionFile = file_get_contents(__DIR__ . '/version.json');
$versionData = json_decode($versionFile, true);
$site_version = $versionData['version'];
?> 


    
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <div class="main-container">
        <?php
        $exclude_header_pages = ['log-in', 'admin-index', 'student-index', 'efms-scanner-index', 'efms-scanner-app', 'efms-scanner-login'];
        $exclude_footer_pages = ['admin-index', 'log-in', 'student-index', 'efms-scanner-index', 'efms-scanner-app', 'efms-scanner-login'];
        $content_pg = isset($_GET['content']) ? $_GET['content'] : 'default';

        // Include header if not excluded
        if (!in_array($content_pg, $exclude_header_pages)) {
            require_once "php/header.php";
        }
        ?>

<div class="content" id="main-content">
    <?php
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
        case "efms-scanner-index":
            include 'php/EFMS-scanner/index.php';
            break;
        case "efms-scanner-app":
            include 'php/EFMS-scanner/efms-scanner.php';
            break;
        case "log-out":
            'php/log-out.php';
            header("Location: index.php?content=log-in");
            session_destroy();
            break;
        case "logout":
            include 'php/EFMS-scanner/scanner-logout.php';
            header("Location: index.php?content=efms-scanner-login");
            session_destroy();
            exit;
            break;
        default:
            include 'php/404.php';
            break;
    }
    ?>
</div>


        <?php
        // Include footer if not excluded
        if (!in_array($content_pg, $exclude_footer_pages)) {
            require_once "php/footer.php";
        }
        ?>
    </div>
</body>

<script>
$(document).ready(function(){
    // Configure NProgress
    NProgress.configure({ 
        showSpinner: false,
        minimum: 0.08,
        easing: 'linear',
        speed: 200,
        trickle: false,
        trickleSpeed: 200
    });

    // Safety function to ensure NProgress completes
    function ensureNProgressComplete() {
        setTimeout(function() {
            if (NProgress.isStarted()) {
                NProgress.done();
            }
        }, 1000); // Force complete after 1 second
    }

    // Start progress bar on page load
    NProgress.start();
    NProgress.set(0.4);
    ensureNProgressComplete();

    // Complete progress bar when page is fully loaded
    $(window).on('load', function() {
        NProgress.set(0.8);
        NProgress.done();
    });

    // Handle AJAX requests
    $(document).ajaxStart(function() {
        NProgress.start();
        NProgress.set(0.4);
    });

    $(document).ajaxStop(function() {
        NProgress.set(0.8);
        NProgress.done();
        ensureNProgressComplete();
    });

    // Handle form submissions
    $(document).on('submit', 'form', function() {
        NProgress.start();
        NProgress.set(0.4);
    });

    // Handle navigation
    function handleNavigation(page) {
        NProgress.start();
        NProgress.set(0.4);
        
        // Force complete after 1 second as a safety measure
        ensureNProgressComplete();

        $.ajax({
            url: `index.php?content=${page}`,
            method: 'GET',
            cache: false, // Prevent caching issues
            success: function(response) {
                try {
                    const tempDiv = $('<div>').html(response);
                    const newContent = tempDiv.find('.content > *');
                    $('#main-content').html(newContent);
                    
                    NProgress.set(0.8);
                    NProgress.done();
                } catch (e) {
                    console.error('Navigation error:', e);
                    NProgress.done();
                }
            },
            error: function() {
                NProgress.done();
            },
            complete: function() {
                ensureNProgressComplete();
            }
        });
    }

    // Handle browser back/forward buttons
    window.onpopstate = function(event) {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('content') || 'default';
        handleNavigation(page);
    };

    // Add click handler for navigation links
    $(document).on('click', 'a[href*="content="]', function(e) {
        const href = $(this).attr('href');
        const match = href.match(/content=([^&]+)/);
        if (match) {
            e.preventDefault();
            const page = match[1];
            handleNavigation(page);
            // Update URL without reload
            history.pushState({}, '', href);
        }
    });

    // Handle visibility change (for mobile apps)
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            // If NProgress is stuck when app becomes visible, force complete
            if (NProgress.isStarted()) {
                NProgress.done();
            }
        }
    });

    // Handle page unload
    window.addEventListener('beforeunload', function() {
        if (NProgress.isStarted()) {
            NProgress.done();
        }
    });
});
</script>


</html>

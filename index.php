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
<?php
$versionFile = file_get_contents(__DIR__ . '/version.json');
$versionData = json_decode($versionFile, true);
$site_version = $versionData['version'];
?> 


    
    <link rel="manifest" href="manifest.json">
    <style>
        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .bouncing-logo {
            width: 100px;
            height: 100px;
            animation: bounce 1s infinite;
        }
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-50px);
            }
        }
    </style>
</head>
<body>
    <div id="loading-overlay">
        <img src="./assets/images/sys-logo.png" alt="Loading..." class="bouncing-logo">
    </div>
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
// Function to check if device is desktop
function isDesktopDevice() {
    return !(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));
}

$(document).ready(function(){
    // Show loading overlay when page starts loading
    $('#loading-overlay').css('display', 'flex');
    
    // Hide loading overlay only when all resources are loaded
    $(window).on('load', function() {
        // Add a small delay to ensure everything is truly loaded
        setTimeout(function() {
            $('#loading-overlay').fadeOut(500);
        }, 500);
    });

    // Handle browser back/forward buttons
    window.onpopstate = function () {
        $('#loading-overlay').css('display', 'flex');
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('content') || 'default';

        $('#main-content').load(`index.php?content=${page} .content > *`, function () {
            // Show/hide header and footer based on content
            if (page === 'log-in') {
                $('#header, #footer').hide();
            } else {
                $('#header, #footer').show();
            }
            // Add a small delay before hiding the loading overlay
            setTimeout(function() {
                $('#loading-overlay').fadeOut(500);
            }, 500);
        });
    };
});
</script>


</html>


<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}  
// Include the database connection
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Capture visit data
$page_url = $_SERVER['REQUEST_URI'];           // Current page URL
$visitor_ip = $_SERVER['REMOTE_ADDR'];         // Visitor's IP address

// Insert visit record into the database
$sql = "INSERT INTO page_visits (page_url, visitor_ip) VALUES (?, ?)";
$stmt = $db->prepare($sql);
$stmt->bind_param("ss", $page_url, $visitor_ip);
$stmt->execute();
$stmt->close();

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
    <!-- Add jQuery (CDN version) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <div class="main-container">
        <?php
        $exclude_header_pages = ['log-in', 'admin-index', 'student-index'];
        $exclude_footer_pages = ['admin-index', 'log-in', 'student-index'];
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
        case "logout":
            session_destroy();
            header("Location: index.php?content=log-in");
            exit;
            break;
        default:
            include 'php/default.php';
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
    $('#login-link').click(function(e){
  e.preventDefault();

   // Show spinner, hide text
   $('#login-text').addClass('d-none');
    $('#login-spinner').removeClass('d-none');



   // Add 2-second delay before loading login content
   setTimeout(function() {
      $('#main-content').load('index.php?content=log-in .content > *', function() {
        // After content is loaded
    
        $('#login-spinner').addClass('d-none');
        $('#login-text').removeClass('d-none');
        // Hide header and footer
        $('#header, #footer').hide(); // to hide
      });

      // Update browser URL
      history.pushState(null, '', '?content=log-in');
    }, 2000); // 2000 milliseconds = 2 seconds
});

});

window.onpopstate = function () {
  const urlParams = new URLSearchParams(window.location.search);
  const page = urlParams.get('content') || 'default';

  $('#main-content').load(`index.php?content=${page} .content > *`, function () {
    // Show/hide header and footer based on content
    if (page === 'log-in') {
        $('#header, #footer').hide(); // to hide
    } else {
        $('#header, #footer').show(); // to show
    }
  });
};

</script>


</html>

<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
    require_once "php/auth-check-login.php";
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="stylesheet" href="stylesheet/default.css">
</head>
<body>
    <div class="default_body">
        <div class="default-content">
            <div class="default-content-display">
                <span class="default-ccs">College of Computer Studies</span>

                <img src=".//.//assets/images/ccslogo.png" alt="Logo of College of Computer Studies" class="ccslogo" loading="lazy"> 
                
                
               
                <span class="web-name">Event & Fee Management System (EFMS)</span>
            </div>
        </div>
    </div>
</body>
</html>

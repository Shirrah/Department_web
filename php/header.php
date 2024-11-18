<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="stylesheet/header.css">
</head>
<body>
    <div class="headerbody">
        <div class="header_logo">
            <img src="assets/images/ccslogo.png" alt="CCS Logo" class="logo">
            <span>
                <p class="dept-name">COLLEGE OF COMPUTER STUDIES</p>
                <p class="system-name">Event & Fee Management System (EFMS)</p>
            </span>
        </div>

        <div class="navbar">
            <?php
            require_once "db-conn.php";
            $db = new Database();

            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == 'yes') {
                $user_data = $_SESSION['user_data'];
                $role = $user_data['role_admin'] ?? $user_data['role_student'] ?? 'Unknown Role';
                $lastname = $user_data['lastname_admin'] ?? $user_data['lastname_student'] ?? 'Unknown Lastname';
                $id = $user_data['id_admin'] ?? $user_data['id_student'] ?? 'Unknown ID';

                echo '
                <div class="logout-btn-dropdown">
                    <button class="dropbtn" onclick="toggleDropdown()">
                        <img src="assets/images/user.png" alt="User Icon">
                        ' . htmlspecialchars($role) . ' - ' . htmlspecialchars($lastname) . ' ' . htmlspecialchars($id) . '
                        <span id="arrow" class="arrow-down">&#9661;</span>
                    </button>
                    <div id="logout-Dropdown" class="logout-Dropdown">
                        <a href="?content=logout">Logout</a>
                    </div>
                </div>';
            } else {
                echo '<button><a href="?content=default"><img src="assets/images/home.png" alt="Home Icon">Home</a></button>';
                echo '<button><a href="?content=log-in"><img src="assets/images/user.png" alt="Login Icon">Login</a></button>';
            }
            ?>
        </div>
    </div>

    <script>
    function toggleDropdown() {
        var dropdown = document.getElementById("logout-Dropdown");
        dropdown.classList.toggle("show");

        var arrow = document.getElementById("arrow");
        arrow.innerHTML = dropdown.classList.contains("show") ? "&#9651;" : "&#9661;";
    }

    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            var dropdowns = document.getElementsByClassName("logout-Dropdown");
            for (var i = 0; i < dropdowns.length; i++) {
                dropdowns[i].classList.remove('show');
            }
            document.getElementById("arrow").innerHTML = "&#9661;";
        }
    }
    </script>
</body>
</html>

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<link rel="stylesheet" href="stylesheet/header.css">

<div class="headerbody">
    <div class="header_logo">
        <img src="assets/images/ccslogo.png" alt="" class="logo">
        <span><p class="dept-name">COLLEGE OF COMPUTER STUDIES</p>
        <p class="system-name">Event & Fee Management System (EFMS)</p>
        </span>
    </div>

    <div class="navbar">
        <?php
        require_once "db-conn.php";
        // Get the database connection instance
        $db = new Database();

        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == 'yes') {
            // Fetch user details from session
            $user_data = $_SESSION['user_data'];

            // Check if expected keys exist
            $role = isset($user_data['role_admin']) ? $user_data['role_admin'] : (isset($user_data['role_student']) ? $user_data['role_student'] : 'Unknown Role');
            $lastname = isset($user_data['lastname_admin']) ? $user_data['lastname_admin'] : (isset($user_data['lastname_student']) ? $user_data['lastname_student'] : 'Unknown Lastname');
            $id = isset($user_data['id_admin']) ? $user_data['id_admin'] : (isset($user_data['id_student']) ? $user_data['id_student'] : 'Unknown ID');

            // Display "Administrator" link only for admins
            if ($role == 'Admin') {

            }

            // Display user role, last name, and ID with a dropdown for logout
            echo '
            <div class="logout-btn-dropdown">
                <button class="dropbtn" onclick="toggleDropdown()">
                    <img src=".//.//assets/images/user.png" alt="">
                    ' . htmlspecialchars($role) . ' - ' . htmlspecialchars($lastname) . ' ' . htmlspecialchars($id) . '
                    <span id="arrow" class="arrow-down">&#9661;</span>
                </button>
                <div id="logout-Dropdown" class="logout-Dropdown">
                    <a href="?content=logout">Logout</a>
                </div>
            </div>';
            
        } else {
            // No session, only "Home" and "Login" are accessible
            echo '<button><a href="?content=default"><img src=".//.//assets/images/home.png" alt="">Home</a></button>';
            echo '<button><a href="?content=log-in"><img src=".//.//assets/images/user.png" alt="">Login</a></button>';
        }
        ?>
    </div>
</div>

<script>
function toggleDropdown() {
    var dropdown = document.getElementById("logout-Dropdown");
    dropdown.classList.toggle("show");
    
    var arrow = document.getElementById("arrow");
    if (dropdown.classList.contains("show")) {
        arrow.innerHTML = "&#9651;"; // Arrow up
    } else {
        arrow.innerHTML = "&#9661;"; // Arrow down
    }
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
    if (!event.target.matches('.dropbtn')) {
        var dropdowns = document.getElementsByClassName("logout-Dropdown");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
        
        var arrow = document.getElementById("arrow");
        arrow.innerHTML = "&#9660;"; // Reset arrow to down
    }
}
</script>

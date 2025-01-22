<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>


<div class="headerbody" style="background-color: #f94415;">
    <div class="header_logo">
        <img src=".//.//assets/images/SJC-LOGO-NEWER-1536x1024.png" alt="Logo of the Saint Joseph College" class="sjclogo" loading="lazy">
        <img src="assets/images/ccslogo.png" alt="" class="logo">
        <span>
        <p class="school-name">SAINT JOSEPH COLLEGE</p>
        <p class="dept-name">College of Computer Studies</p>
        </span>
    </div>
    <button id="installBtn" class="dnbtn" style="display:none;"><i class="fas fa-download"></i> Install App</button>

    <div class="navbar-con">
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
                    ' . htmlspecialchars($role) . ' - ' . htmlspecialchars($lastname) . '
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
    
    // var arrow = document.getElementById("arrow");
    // if (dropdown.classList.contains("show")) {
    //     arrow.innerHTML = "&#9651;"; // Arrow up
    // } else {
    //     arrow.innerHTML = "&#9661;"; // Arrow down
    // }
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

<script>
let deferredPrompt; // To store the beforeinstallprompt event
const installBtn = document.getElementById('installBtn');

// Listen for the beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (event) => {
  // Prevent the default mini-infobar from appearing on mobile
  event.preventDefault();
  // Store the event so it can be triggered later
  deferredPrompt = event;
  // Show the install button
  installBtn.style.display = 'block';
});

// Add click listener to the install button
installBtn.addEventListener('click', () => {
  // Make sure the deferredPrompt is available
  if (deferredPrompt) {
    // Show the install prompt
    deferredPrompt.prompt();
    // Wait for the user to respond to the prompt
    deferredPrompt.userChoice.then((choiceResult) => {
      if (choiceResult.outcome === 'accepted') {
        console.log('User accepted the install prompt');
      } else {
        console.log('User dismissed the install prompt');
      }
      // Clear the deferredPrompt so it can't be reused
      deferredPrompt = null;
    });
  }
});
</script>

<style>
    @font-face {
    font-family: Poppins-Regular;
    src: url('../assets/fonts/poppins/Poppins-Regular.ttf'); 
  }
  
  @font-face {
    font-family: Poppins-Bold;
    src: url('../assets/fonts/poppins/Poppins-Bold.ttf'); 
  }
  
  @font-face {
    font-family: Poppins-Medium;
    src: url('../assets/fonts/poppins/Poppins-Medium.ttf'); 
  }
  
  @font-face {
    font-family: Montserrat-Bold;
    src: url('../assets/fonts/montserrat/Montserrat-Bold.ttf'); 
  }

.headerbody{
    width: 100%;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    box-shadow: 0px 0px 3px black;
    z-index: -1;
    align-items: center;
}

.header_logo{
    width: auto;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
}

.logo, .sjclogo{
    width: 60px;
    padding: 10px;
}

.school-name{
    font-size: 1.3rem;
    color: white;
    font-family: Poppins-Bold;
    visibility: hidden;
    
}
.school-name::before {
    content: "SAINT JOSEPH COLLEGE";
    visibility: visible;
}

.dept-name{
    font-size: 1rem;
    color: white;
    font-family: Poppins-Medium;
    visibility: hidden;
}
.dept-name::before{
    content: "College of Computer Studies";
    visibility: visible;
}
.navbar-con{
    width: auto;
    display: flex;
    align-items: center;
}

.navbar-con button{
    border: 0px;
}

.navbar-con button img{
    width: 15px;
    margin-right: 5px;
    filter: invert(100%);
}

.navbar-con button a{
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #fdfdfd;
    height: 100%;
    padding: 18px 11px 20px 11px;
    font-size: .9rem;
    cursor: pointer;
    background-color: #f94415;
    font-family: tahoma;
}

.navbar-con button a:hover{
    border-bottom: 2px solid white;
}

.logout-btn-dropdown {
    position: relative;
    display: inline-block;
}

.dropbtn {
    background-color: #f94415;
    color: white;
    border: none;
    padding: 10px;
    font-size: .9rem;
    cursor: pointer;
    font-family: Tahoma;
    display: flex;
    align-items: center;
}

.dropbtn span{
    margin-left: 5px;
}

.arrow-down{
    pointer-events: none;
    user-select: none;
}

/* Dropdown content (hidden by default) */
.logout-Dropdown {
    display: none;
    position: absolute;
    background-color: #f94415;
    min-width: 160px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
}

.logout-Dropdown.show {
    display: block;
}

.logout-Dropdown a {
    color: rgb(255, 255, 255);
    padding: 12px 16px;
    font-weight: bold;
    text-decoration: none;
    border: 1px solid white;
    display: block;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

/* Install button */
#installBtn{
    appearance: none;
    background-color: #FFFFFF;
    border-radius: 40em;
    border-style: none;
    box-shadow: #ADCFFF 0 -12px 6px inset;
    box-sizing: border-box;
    color: #000000;
    cursor: pointer;
    display: inline-block;
    font-family: Poppins-Medium;
    font-size: 1 rem;
    font-weight: 700;
    letter-spacing: -.24px;
    margin: 0;
    outline: none;
    padding: 10px;
    quotes: auto;
    text-align: center;
    text-decoration: none;
    transition: all .15s;
    user-select: none;
    -webkit-user-select: none;
    touch-action: manipulation;
  
}

#installBtn:hover{
  transform: scale(1.125);

}

#installBtn:active{
    transform: scale(1.025);
}
/* Change color of dropdown links on hover */
.logout-Dropdown a:hover {
    background-color: #f1f1f1;
    color: black;
}

/* 1. Extra Small Devices (Phones in Portrait Mode) */
@media (max-width: 480px) {
    /* Mobile phones (portrait) */
    /* Adjust styles for very small screens */
    .headerbody{
        height: 100px;
        justify-content: space-evenly;
    }
    .school-name{
        font-size: 1rem;
        
    }
    .school-name::before{
        content: "sdsdssddssds ";
    }
    .dept-name{
        font-size: .5rem;
    }
    .dept-name::before{
        content: "";
    }
    .navbar{
        display: flex;
        align-items: center;
    }
    .header-logo span{
        width: 100%;
        height: 100%;
    }
}

/* 2. Small Devices (Phones in Landscape Mode) */
@media (min-width: 481px) and (max-width: 767px) {
    /* Mobile phones (landscape) */
    /* Adjust styles for larger phones */
    .headerbody{
        height: 100px;
        justify-content: space-evenly;
    }
    .school-name{
        font-size: 2rem;
    }
    .school-name::before{
        content: "";
    }
    .dept-name{
        font-size: .5rem;
    }
    
    .dept-name::before{
        content: "";
    }
    .navbar{
        display: flex;
        align-items: center;
    }
    .header-logo span{
        width: 100%;
        height: 100%;
    }
}
</style>
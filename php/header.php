<link rel="stylesheet" href=".//.//stylesheet/header.css">
<title>SJC - College of Computer Studies</title>

<!-- Header Section -->
<nav class="navbar navbar-expand-lg fixed-top shadow-sm">
<div class="container-fluid d-flex align-items-center align-items-center h-100" style="height: 100px;">
    <!-- Logo and Brand -->
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="./assets/images/sys-logo.png" alt="Logo" class="me-2" style="height: 40px;">
      <div class="container d-flex flex-column align-items-start text-start py-4">
        <span class="h4" id="college-name"></span>
        <span class="h5 text-muted" id="app-name"></span>
      </div>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="header-nav-con collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
      <li class="nav-item">
  <a id="installBtn" role="button" title="Progress Web App" class="btn btn-primary">
    <i class="bi bi-download me-1"></i>Install PWA App
  </a>
</li>

        <?php 
          require_once "php/db-conn.php";
          $db = Database::getInstance()->db;
          if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == 'yes') {
              $user_data = $_SESSION['user_data'];
              $role = $user_data['role_admin'] ?? $user_data['role_student'] ?? 'Unknown Role';
              $lastname = $user_data['lastname_admin'] ?? $user_data['lastname_student'] ?? 'Unknown Lastname';
              $id = $user_data['id_admin'] ?? $user_data['id_student'] ?? 'Unknown ID';
          } else {
              echo '<li class="nav-item"><a class="nav-link active" href="?content=default">Home</a></li>';
              echo '<li class="nav-item"><a class="nav-link" href="?content=log-in">Login</a></li>';
          }
        ?>
      </ul>
    </div>
  </div>
</nav>
<style>
  
</style>
<script>
let deferredPrompt;
const installBtn = document.getElementById('installBtn');

// Function to check if PWA is installed
function checkIfInstalled() {
  const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
  
  if (isStandalone) {
    installBtn.style.display = 'none'; // Hide install button if installed
  } else {
    installBtn.style.display = 'block'; // Show button if not installed
  }
}

// Listen for beforeinstallprompt event (works in Chrome, Edge, etc.)
window.addEventListener('beforeinstallprompt', (event) => {
  event.preventDefault();
  deferredPrompt = event;

  // Show button only if PWA is not installed
  checkIfInstalled();
});

// Handle install button click (works in Chrome, Edge, etc.)
installBtn.addEventListener('click', () => {
  if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then((choiceResult) => {
      if (choiceResult.outcome === 'accepted') {
        console.log('User accepted the install prompt');
        installBtn.style.display = 'none'; // Hide button after install
      }
      deferredPrompt = null;
    });
  }
});

// Hide the button when the app is installed (Chrome, Edge)
window.addEventListener('appinstalled', () => {
  console.log('PWA was installed');
  installBtn.style.display = 'none';
});

// Run check on page load
window.onload = checkIfInstalled;
</script>


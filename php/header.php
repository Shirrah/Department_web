<link rel="stylesheet" href=".//.//stylesheet/header.css">

<link rel="stylesheet" href="././stylesheet/header.css">

<!-- Header Section -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top bg-light shadow-sm">
<div class="container-fluid d-flex align-items-center align-items-center h-100 bg-light" style="height: 100px;">
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
          <a id="installBtn" role="button" title="Progress Web App" class="btn btn-primary"><i class="bi bi-download me-1"></i>Install PWA App</a>
        </li>
        <?php 
          require_once "php/db-conn.php";
          $db = new Database();
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
<!-- HEAD Section -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" type="text/css" href="stylesheet/login-new-main.css">


<?php
$error = '';
$toastType = '';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['error_msg'])) {
    $error = $_SESSION['error_msg'];
    $toastType = isset($_SESSION['toast_type']) ? $_SESSION['toast_type'] : 'error';
    unset($_SESSION['error_msg']);
    unset($_SESSION['toast_type']);
}
?>

<!-- Login UI -->
<div class="limiter">
    <div class="container-login100">
        <div class="close-login-form">
            <a href="index.php" class="btn-close btn-close-white rounded-circle bg-warning p-3" aria-label="Close"></a>
        </div>
        <div class="wrap-login100">
            <div class="login100-pic js-tilt" data-tilt>
                <img src="./assets/images/sys-logo.png" alt="IMG">
            </div>
            <form id="loginForm" class="login100-form validate-form">
                <span class="login100-form-title">Welcome to EFMS!</span>
                <div class="wrap-input100 validate-input">
                    <input class="input100" type="text" name="id" placeholder="Identification number" autocomplete="username">
                    <span class="focus-input100"></span>
                    <span class="symbol-input100">
                        <i class="fa fa-user" aria-hidden="true"></i>
                    </span>
                </div>
                <div class="wrap-input100 validate-input">
                    <input class="input100" type="password" name="psw" placeholder="Password" autocomplete="current-password">
                    <span class="focus-input100"></span>
                    <span class="symbol-input100">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                    </span>
                </div>
                <div class="container-login100-form-btn">
                    <button type="submit" id="loginBtn" class="login100-form-btn">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('php/toast-system.php'); ?>

<?php if ($error): ?>
createToast("<?php echo $toastType; ?>", "<?php echo addslashes($error); ?>");
<?php endif; ?>

<!-- AJAX Login Handling -->
<script>
document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.querySelector('[name="id"]').value;
    const psw = document.querySelector('[name="psw"]').value;

    fetch('php/check-login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ id: id, psw: psw })
    })
    .then(res => res.json())
    .then(data => {
        createToast(data.status, data.message);
        if (data.status === 'success') {
            setTimeout(() => window.location.href = data.redirect, 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        createToast("error", "An error occurred while logging in.");
    });
});
</script>

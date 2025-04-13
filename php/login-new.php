<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="stylesheet/login-new-main.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<?php
// Start the session
    $error = '';
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['error_msg'])) {
        $error = $_SESSION['error_msg'];
        unset($_SESSION['error_msg']); // Clear the session after using it
    } else {
        $error = ''; // No error
    }
?>
<div class="limiter">
    <div class="container-login100">
        <div class="close-login-form"><a href="index.php" class="btn-close btn-close-white rounded-circle bg-warning p-3" aria-label="Close"></a></div>
        <div class="wrap-login100">
            <div class="login100-pic js-tilt" data-tilt>
                <img src="./assets/images/sys-logo.png" alt="IMG">
            </div>

            <form class="login100-form validate-form" action="php/check-login.php" method="post">
                <span class="login100-form-title">
                    Welcome to EFMS!
                </span>

                <div class="wrap-input100 validate-input">
                    <input class="input100" type="text" name="id" placeholder="Identification number" autocomplete="username">
                    <span class="focus-input100"></span>
                    <span class="symbol-input100">
                        <i class="fa fa-user" aria-hidden="true"></i>
                    </span>
                </div>

                <div class="wrap-input100 validate-input" data-validate = "Password is required">
                    <input class="input100" type="password" name="psw" placeholder="Password" autocomplete="current-password">
                    <span class="focus-input100"></span>
                    <span class="symbol-input100">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                    </span>
                </div>

                <div class="container-login100-form-btn">
                    <button class="login100-form-btn">
                        Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="loginToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">EFMS</strong>
            <small>Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

<script>
    // Show Toast if there is an error
    <?php if ($error): ?>
        const toastMessage = '<?php echo $error; ?>';
        const toastElement = document.getElementById('loginToast');
        const toastBody = document.getElementById('toastMessage');
        toastBody.textContent = toastMessage;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
    <?php endif; ?>
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>

</body>
</html>

<?php
ob_end_flush();
?>

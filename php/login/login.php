<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="../../assets/images/icons/favicon.ico"/>
	<link rel="stylesheet" type="text/css" href="login-new-main.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>


<?php
// Start the session
	$error ='';
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
			<div class="wrap-login100">
				<div class="login100-pic js-tilt" data-tilt>
					<img src="../../assets/images/sys-logo.png" alt="IMG">
				</div>

				<form class="login100-form validate-form" action="php/check-login.php" method="post">
					<span class="login100-form-title">
						Welcome to EFMS!
					</span>

					<div class="wrap-input100 validate-input">
						<input class="input100" type="text" name="id" placeholder="Identification number">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-user" aria-hidden="true"></i>
						</span>
					</div>

					<div class="wrap-input100 validate-input" data-validate = "Password is required">
						<input class="input100" type="password" name="psw" placeholder="Password">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-lock" aria-hidden="true"></i>
						</span>
					</div>
						<!-- Display the error message if there is one -->
						<?php if ($error): ?>
							<div class="error-message"><?php echo $error; ?></div>
						<?php endif; ?>

						<style>
							.error-message {
								color: red;
								background-color: #f8d7da;
								border: 1px solid #f5c6cb;
								border-radius: 10px;
							}
						</style>
					<div class="container-login100-form-btn">
						<button class="login100-form-btn">
							Login
						</button>
					</div>

					<div class="text-center p-t-12" type="submit">
						<span class="txt1">
							Forgot
						</span>
						<a class="txt2" href="#">
							Identification number / Password?
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	

	
	
<!--===============================================================================================-->	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/4.0.0-beta/jquery.min.js"></script>
<!--===============================================================================================-->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
<!--===============================================================================================-->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/tilt.js/1.2.1/tilt.jquery.min.js"></script>
	<script >
		$('.js-tilt').tilt({
			scale: 1.1
		})
	</script>
<!--===============================================================================================-->
	<script src="../../js/login-main.js"></script>

</body>
</html>
<?php
// Start the session
	$error ='';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['error_msg'])){
	$error = $_SESSION['error_msg'];
}
?>


<link rel="stylesheet" href="stylesheet/log-in.css">   
<script src="js/login-button-form.js"></script> 

<div class="log-inbody">
    <div class="login-con">
        <div class="login-con-form">
            <div class="login-form" id="loginForm">
                <div class="login-logo-imgcon">
                <img src="/assets/images/ccslogo.png" alt="">
                </div>
                <div class="login-title"><p><span class="log-tit-1">CCS (EFMS)</span><span class="log-tit-2">Event & Fee Management System</span></p></div>
                
                <form action="php/check-login.php" method="post">

  <div class="login-form-container">
    <input type="text"placeholder="IDENTIFICATION NUMBER (ID)" name="id" required>
    <input type="password" placeholder="PASSWORD" name="psw" required>

    <button class="sign-in-btn" type="submit">Sign in</button>
    <div class="loginteadinvalid"><?php echo $error; ?></div>
  </div>

  <div class="container" style="background-color:#f1f1f1">
  </div>
</form>
            </div>
        </div>
    </div>
</div>


<?php
	$_SESSION['error_msg'] = '';
?>
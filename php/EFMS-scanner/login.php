<!-- login-tomato.php -->
<?php
$error = '';
$toastType = '';
if (session_status() == PHP_SESSION_NONE) session_start();
if (isset($_SESSION['error_msg'])) {
    $error = $_SESSION['error_msg'];
    $toastType = $_SESSION['toast_type'] ?? 'error';
    unset($_SESSION['error_msg'], $_SESSION['toast_type']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EFMS-Scanner</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #fff5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(255, 99, 71, 0.2);
            width: 350px;
            padding: 40px;
            text-align: center;
        }
        
        .logo {
            color: #ff6347;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #ff6347;
            margin-bottom: 30px;
            font-size: 1.8rem;
        }
        
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        label {
            display: block;
            color: #ff6347;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ffdbd5;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        input:focus {
            border-color: #ff6347;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 99, 71, 0.2);
        }
        
        .btn {
            background-color: #ff6347;
            color: white;
            border: none;
            padding: 12px 0;
            width: 100%;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn:hover {
            background-color: #ff4520;
            transform: translateY(-2px);
        }
        
        .btn-back {
            background-color: white;
            color: #ff6347;
            border: 2px solid #ffdbd5;
            padding: 12px 0;
            width: 100%;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-back:hover {
            background-color: #fff5f5;
            border-color: #ff6347;
        }
        
        .links {
            margin-top: 25px;
            font-size: 0.9rem;
        }
        
        .links a {
            color: #ff6347;
            text-decoration: none;
            font-weight: 500;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .divider {
            margin: 0 10px;
            color: #ccc;
        }
        .login-body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #fff5f5;
        }
    </style>
</head>
<body>
<div class="login-body">
    <div class="login-container">
        <div class="logo"></div>
        <h1>EFMS-Scanner</h1>

        <form id="loginForm">
            <div class="input-group">
                <label for="ID">ID</label>
                <input type="text" id="ID" name="id" placeholder="Enter your Identification Number">
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="psw" placeholder="Enter your password">
            </div>
            <button type="submit" class="btn">Login</button>
            <button type="button" class="btn-back" onclick="window.location.href='index.php'">Back</button>
        </form>
    </div>
</div>

<?php include('./php/toast-system.php'); ?>

<?php if ($error): ?>
<script>createToast("<?= $toastType ?>", "<?= addslashes($error) ?>");</script>
<?php endif; ?>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.querySelector('[name="id"]').value;
    const psw = document.querySelector('[name="psw"]').value;

    fetch('./php/scanner-check-login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ id, psw })
    })
    .then(res => res.json())
    .then(data => {
        createToast(data.status, data.message);
        if (data.status === 'success') {
            setTimeout(() => window.location.href = data.redirect, 1500);
        }
    })
    .catch(err => {
        console.error('Login error:', err);
        createToast("error", "An error occurred. Please try again.");
    });
});
</script>
</body>
</html>
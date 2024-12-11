<?php
// If form is submitted, check password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_password = 'your-admin-password'; // Replace with your admin password

    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php?content=admin-index&admin=admin-management"); // Redirect to the admin dashboard or intended page
        exit();
    } else {
        $error_message = 'Incorrect password!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="page-content">
        <!-- Your page content goes here -->
    </div>

    <!-- Modal Form -->
    <div id="admin-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Enter Admin Password</h2>
            <form method="POST" action="">
                <input type="password" name="password" placeholder="Password" required>
                <?php if (isset($error_message)) { echo "<p style='color: red;'>$error_message</p>"; } ?>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <script>
    // Show the modal when the page loads
    window.onload = function() {
        document.getElementById("admin-modal").style.display = "block";
    };

    // Close the modal when the "X" button is clicked and redirect
    function closeModal() {
        document.getElementById("admin-modal").style.display = "none";
        // Redirect the user to a specific page when the modal is closed
        window.location.href = 'index.php?content=admin-index&admin=dashboard'; // Replace with the desired URL
    }
</script>

</body>
</html>

<style>
    /* Modal Styles admin password */
    .modal-admin-password {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7); /* Darker overlay */
        padding-top: 100px;
        box-sizing: border-box;
    }

    .modal-content-admin-password {
        background-color: #fff;
        padding: 20px;
        margin: auto;
        width: 400px;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .close-admin-password {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .close-admin-password:hover,
    .close-admin-password:focus {
        color: #333;
        text-decoration: none;
    }

    .modal-title {
        text-align: center;
        font-size: 24px;
        color: #333;
        margin-bottom: 20px;
    }

    .admin-password-input {
        width: 100%;
        padding: 15px;
        margin-bottom: 15px;
        border: 2px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    .admin-password-input:focus {
        border-color: #007bff;
        outline: none;
    }

    .admin-password-submit {
        width: 100%;
        padding: 15px;
        background-color: #007bff;
        color: white;
        font-size: 18px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .admin-password-submit:hover {
        background-color: #0056b3;
    }

    .admin-password-submit:focus {
        outline: none;
    }

    .error-message {
        color: red;
        font-size: 14px;
        text-align: center;
    }

    @media (max-width: 600px) {
        .modal-content-admin-password {
            width: 90%;
        }

        .admin-password-input {
            padding: 12px;
            font-size: 14px;
        }

        .admin-password-submit {
            padding: 12px;
            font-size: 16px;
        }
    }

    /* Modal Styles for Admin Password */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Overlay */
    }

    .modal-content {
        background-color: white;
        padding: 20px;
        margin: auto;
        width: 300px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        
        /* Center the modal content */
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    /* Input and button styles */
    input[type="password"] {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    button {
        width: 100%;
        padding: 10px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #45a049;
    }
</style>
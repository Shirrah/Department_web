<?php
// Start the session
session_start();

// If form is submitted, check password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_password = 'your-admin-password'; // Replace with your admin password

    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php"); // Redirect to the admin dashboard or intended page
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

// Close the modal when the "X" button is clicked
function closeModal() {
    document.getElementById("admin-modal").style.display = "none";
}

    </script>
</body>
</html>

<style>
    /* Modal styles */
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
    margin: 10% auto;
    width: 300px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
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
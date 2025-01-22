<?php
ob_start();

// Include the Database class file
require_once 'php/db-conn.php';

// If form is submitted, check password and role
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Instantiate the Database class to get the DB connection
    $database = new Database();
    $conn = $database->db;  // Get the database connection from the class

    // Get admin details from the database based on the entered password
    $admin_password = $_POST['password']; // Get the password entered by the admin
    $sql = "SELECT * FROM admins WHERE pass_admin = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $admin_password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if admin with the provided password exists
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Check if the admin's role is 'Dean' or 'Governor'
        if ($admin['role_admin'] == 'Dean' || $admin['role_admin'] == 'Governor') {
            $_SESSION['admin_logged_in'] = true;
            
            header("Location: ?content=admin-index&admin=admin-management"); 
            exit();
        } else {
            $error_message = 'Access denied. Only Dean or Governor roles are allowed.';
        }
    } else {
        $error_message = 'Incorrect password or admin not found!';
    }

    // Close the prepared statement
    $stmt->close();
}
ob_end_flush();
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Modal Form -->
<div id="admin-modal" class="admin-modal">
    <div class="admin-modal-content">
        <span class="admin-close" onclick="closeModal()">&times;</span>
        <h2 class="admin-modal-title">Admin Access</h2>
        <form method="POST" action="" class="admin-form">
            <input type="password" name="password" placeholder="Password" class="admin-input" required>
            <?php if (isset($error_message)) { echo "<p class='admin-error-message'>$error_message</p>"; } ?>
            <button type="submit" class="admin-submit-btn">Submit</button>
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

<style>
/* Modal Styles for Admin Password */
.admin-modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Overlay */
}

.admin-modal-content {
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

.admin-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.admin-close:hover,
.admin-close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Input and button styles */
.admin-input {
    width: 100%;
    padding: 10px 0px 10px 0px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.admin-submit-btn {
    width: 100%;
    padding: 10px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.admin-submit-btn:hover {
    background-color: #45a049;
}

/* Error message styling */
.admin-error-message {
    color: red;
    font-size: 14px;
    margin-top: 5px;
}

/* Modal title styling */
.admin-modal-title {
    text-align: center;
    font-size: 18px;
    margin-bottom: 15px;
}

/* Form styling */
.admin-form {
    text-align: center;
}
</style>

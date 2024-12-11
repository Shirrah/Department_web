<?php
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
            header("Location: index.php?content=admin-index&admin=admin-management"); // Redirect to the admin dashboard
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
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <!-- Modal Form -->
    <div id="admin-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Admin Access</h2>
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



<style>

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
        padding: 10px 0px 10px 0px;
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
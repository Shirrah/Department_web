<?php
ob_start();  // Start output buffering
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if it's not already started
}

require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Handle form submission to enroll a new admin
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate user inputs to prevent SQL injection and XSS
    $id_admin = htmlspecialchars($_POST['id_admin']);
    $pass_admin = htmlspecialchars($_POST['pass_admin']);
    $role_admin = htmlspecialchars($_POST['role_admin']);
    $lastname_admin = htmlspecialchars($_POST['lastname_admin']);
    $firstname_admin = htmlspecialchars($_POST['firstname_admin']);

    // Use prepared statement for inserting admin data
    $insertQuery = "INSERT INTO admins (id_admin, pass_admin, role_admin, lastname_admin, firstname_admin) 
                    VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($insertQuery);
    $stmt->bind_param("sssss", $id_admin, $pass_admin, $role_admin, $lastname_admin, $firstname_admin);

    if ($stmt->execute()) {
        header("Location: ?content=admin-index&admin=admin-management");
        exit();
    } else {
        echo "<script>alert('Error enrolling admin: " . $db->error . "');</script>";
    }
}

// Handle admin deletion
if (isset($_GET['delete_id'])) {
    // Sanitize the delete ID
    $delete_id = htmlspecialchars($_GET['delete_id']);
    $deleteQuery = "DELETE FROM admins WHERE id_admin = ?";
    $stmt = $db->prepare($deleteQuery);
    $stmt->bind_param("s", $delete_id);

    if ($stmt->execute()) {
        header("Location: ?content=admin-index&admin=admin-management");
        exit();
    } else {
        echo "<script>alert('Error deleting admin: " . $db->error . "');</script>";
    }
}

// Search logic
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$searchQuery = "SELECT id_admin, pass_admin, role_admin, lastname_admin, firstname_admin 
                 FROM admins WHERE id_admin LIKE ? OR role_admin LIKE ? OR lastname_admin LIKE ? OR firstname_admin LIKE ?";
$stmt = $db->prepare($searchQuery);
$searchPattern = "%$search%";
$stmt->bind_param("ssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern);
$stmt->execute();
$admins = $stmt->get_result();

ob_end_flush();  // End output buffering and send output to the browser
?>

<!-- Add Bootstrap CSS for styling -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href=".//.//stylesheet/admin/admin-management.css">

<div class="">
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
      <!-- Toggle Button on the Left -->
      <a class="navbar-brand" href="#">Manage Admins</a>
      <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Collapsible Navbar Content -->
      <div class="collapse navbar-collapse" id="navbarContent">
        <div class="navbar-nav ms-auto">
          <div class="divider"></div>
          <!-- <a class="nav-link" href="#"><i class="bi bi-box-arrow-down"></i>Export</a> -->
          <div class="divider"></div>
          <!-- <a class="nav-link" href="#" ><i class="bi bi-box-arrow-in-up"></i>Import</a> -->
          <div class="divider"></div>
          <!-- Enroll Button to Trigger Modal -->
<button id="enrollButton" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollFormModal">
    <i class="bi bi-box-arrow-in-up"></i> Add a New Admin
</button>

          <div class="divider"></div>
        </div>
      </div>
    </div>
  </nav>

    
    <div class="admin-management-body">
    <!-- Admin Table -->
    <table class="admin-table">
            <!-- Search Bar -->
            <div class="form-group">
    <form method="GET" action="">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
            <input type="text" class="form-control" name="search" placeholder="Search for admins" value="<?= $search ?>" style="padding-left: 30px;">
        </div>
        <button type="submit" class="btn btn-primary mt-2">Search</button>
    </form>
</div>

        <thead>
            <tr>
                <th onclick="sortTable(0)">Identification Number</th>
                <th onclick="sortTable(1)">Password</th>
                <th onclick="sortTable(2)">Role</th>
                <th onclick="sortTable(3)">Last Name</th>
                <th onclick="sortTable(4)">First Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="adminTable">
            <?php
            if ($admins->num_rows > 0) {
                while ($row = $admins->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id_admin']) . "</td>";
                    echo "<td>
                        <span class='password-mask'>" . str_repeat('•', strlen($row['pass_admin'])) . "</span>
                        <span class='password-full' style='display:none;'>" . htmlspecialchars($row['pass_admin']) . "</span>
                        <button class='btn btn-link' onclick='togglePassword(this)' title='Show Password'>
                            <i class='fas fa-eye'></i>
                        </button>
                    </td>";
                    echo "<td>" . htmlspecialchars($row['role_admin']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['lastname_admin']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['firstname_admin']) . "</td>";
                    echo "<td>
                        <a href='?content=admin-index&admin=admin-management&edit_id=" . htmlspecialchars($row['id_admin']) . "' class='btn btn-warning btn-sm'>
                            <i class='fas fa-edit'></i> Edit
                        </a>
                        <a href='?content=admin-index&admin=admin-management&delete_id=" . htmlspecialchars($row['id_admin']) . "' class='btn btn-danger btn-sm' onclick='return confirmDelete()'>
                            <i class='fas fa-trash'></i> Delete
                        </a>
                    </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>No admins found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Modal for adding admin -->
<div class="modal fade" id="enrollFormModal" tabindex="-1" aria-labelledby="enrollFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enrollFormModalLabel">Add New Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="id_admin">Identification Number (ID):</label>
                        <input type="text" class="form-control" id="id_admin" name="id_admin" required>
                    </div>
                    <div class="form-group">
                        <label for="pass_admin">Password:</label>
                        <input type="password" class="form-control" id="pass_admin" name="pass_admin" required>
                    </div>
                    <div class="form-group">
                        <label for="role_admin">Role:</label>
                        <select class="form-control" id="role_admin" name="role_admin" required>
                            <option value="">Select Role</option>
                            <option value="Governor">Governor</option>
                            <option value="Dean">Dean</option>
                            <option value="Secretary">Secretary</option>
                            <option value="Treasurer">Treasurer</option>
                            <option value="Guest Admin">Guest Admin</option>
                            <option value="Developer">Developer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="lastname_admin">Last Name:</label>
                        <input type="text" class="form-control" id="lastname_admin" name="lastname_admin" required>
                    </div>
                    <div class="form-group">
                        <label for="firstname_admin">First Name:</label>
                        <input type="text" class="form-control" id="firstname_admin" name="firstname_admin" required>
                    </div>
                    <button type="submit" class="btn btn-success">Save Admin</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add JavaScript to handle password visibility -->
<script>
    function togglePassword(button) {
        const passwordField = button.closest('td').querySelector('.password-mask');
        const passwordFull = button.closest('td').querySelector('.password-full');
        if (passwordField.style.display === 'none') {
            passwordField.style.display = 'inline';
            passwordFull.style.display = 'none';
        } else {
            passwordField.style.display = 'none';
            passwordFull.style.display = 'inline';
        }
    }

    function confirmDelete() {
        return confirm("Are you sure you want to delete this admin?");
    }
</script>

<style>
    /* Table styling */
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        border: 1px solid #e8ecf1;
    }

    .admin-table th,
    .admin-table td {
        padding: 12px;
        text-align: left;
        font-size: 14px;
        border-bottom: 1px solid #e8ecf1;
        font-family: "Open Sans", sans-serif;
    }

    .admin-table th {
        background-color: #f2f2f2;
        color: black;
        font-weight: bold;
        cursor: pointer;
        position: sticky;
        user-select: none;
        top: 0;
    }

    .admin-table th:hover {
        background-color: #e1e1e1;
    }

    .admin-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .admin-table tr:hover {
        background-color: #f1f1f1;
    }

    .admin-table td {
        color: black;
    }

    .admin-table td a:hover {
        text-decoration: underline;
    }
</style>

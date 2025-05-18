<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Handle admin update FIRST
if (isset($_POST['update_admin'])) {
    $edit_id_admin = htmlspecialchars($_POST['edit_id_admin']);
    $edit_pass_admin = htmlspecialchars($_POST['edit_pass_admin']);
    $edit_role_admin = htmlspecialchars($_POST['edit_role_admin']);
    $edit_lastname_admin = htmlspecialchars($_POST['edit_lastname_admin']);
    $edit_firstname_admin = htmlspecialchars($_POST['edit_firstname_admin']);

    $updateQuery = "UPDATE admins 
                    SET pass_admin = ?, role_admin = ?, lastname_admin = ?, firstname_admin = ?
                    WHERE id_admin = ?";
    $stmt = $db->prepare($updateQuery);
    $stmt->bind_param("sssss", $edit_pass_admin, $edit_role_admin, $edit_lastname_admin, $edit_firstname_admin, $edit_id_admin);

    if ($stmt->execute()) {
        header("Location: ?content=admin-index&admin=admin-management");
        exit();
    } else {
        echo "<script>alert('Error updating admin: " . $db->error . "');</script>";
    }
}
// Handle form submission to enroll a new admin (ONLY if not update)
else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_admin = htmlspecialchars($_POST['id_admin']);
    $pass_admin = htmlspecialchars($_POST['pass_admin']);
    $role_admin = htmlspecialchars($_POST['role_admin']);
    $lastname_admin = htmlspecialchars($_POST['lastname_admin']);
    $firstname_admin = htmlspecialchars($_POST['firstname_admin']);

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

// Deletion
if (isset($_GET['delete_id'])) {
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

// Search
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$searchQuery = "SELECT id_admin, pass_admin, role_admin, lastname_admin, firstname_admin 
                 FROM admins WHERE id_admin LIKE ? OR role_admin LIKE ? OR lastname_admin LIKE ? OR firstname_admin LIKE ?";
$stmt = $db->prepare($searchQuery);
$searchPattern = "%$search%";
$stmt->bind_param("ssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern);
$stmt->execute();
$admins = $stmt->get_result();

ob_end_flush();
?>


<!-- Add Bootstrap CSS for styling -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href=".//.//stylesheet/admin/admin-management.css">

<div class="">
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container-fluid">
    <!-- Toggle Button on the Left -->
    <a class="navbar-brand" href="#">Manage Admins</a>
    <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible Navbar Content -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <div class="navbar-nav ms-auto d-flex align-items-center">
        
        <!-- Search Bar with Form -->
        <div class="input-group">
          <input type="text" class="form-control" id="searchAdminInput" placeholder="Search admins..." aria-label="Search admins" aria-describedby="admin-search-addon">
          <span class="input-group-text" id="admin-search-addon"><i class="fas fa-search"></i></span>
        </div>

        <!-- Add New Admin Button -->
        <button class="btn btn-outline-primary ms-3 w-100" id="enrollButton" data-bs-toggle="modal" data-bs-target="#enrollFormModal">
          <i class="bi bi-box-arrow-in-up"></i> Add a New Admin
        </button>
      </div>
    </div>
  </div>
</nav>

    
    <div class="admin-management-body">
    <!-- Admin Table -->
    <div class="table-responsive">
    <table class="table admin-table">

    <script>
    const searchInput = document.getElementById('searchAdminInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#adminTable tr');
            let hasMatches = false;
            
            rows.forEach(row => {
                if (row.id === 'noResultsRow') return; // Skip the "no results" row for now
                
                const cells = row.getElementsByTagName('td');
                let rowMatches = false;
                
                for (let j = 0; j < cells.length - 1; j++) { // exclude Actions column
                    const cellText = cells[j].textContent.toLowerCase();
                    if (cellText.includes(searchTerm)) {
                        rowMatches = true;
                        hasMatches = true;
                        break;
                    }
                }
                
                row.style.display = rowMatches ? '' : 'none';
            });
            
            const noResultsRow = document.getElementById('noResultsRow');
            if (noResultsRow) {
                if (searchTerm && !hasMatches) {
                    noResultsRow.style.display = '';
                } else {
                    noResultsRow.style.display = 'none';
                }
            }
        });
    }
</script>

</script>
        <thead>
            <tr>
                <th>Identification Number</th>
                <th>Password</th>
                <th>Role</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Action</th>
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
        echo "<tr id='noResultsRow'><td colspan='6' class='text-center'>No admins found</td></tr>";
    }
    ?>
    <!-- Hidden no results row -->
    <tr id="noResultsRow" style="display:none;">
        <td colspan="6" class="text-center">No matching admins found</td>
    </tr>
</tbody>

    </table>
        </div>
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
                            <option value="Class Mayor">Class Mayor</option>
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


<!-- Modal for editing admin -->
<div class="modal fade" id="editFormModal" tabindex="-1" aria-labelledby="editFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFormModalLabel">Edit Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Hidden input for Admin ID -->
                    <input type="hidden" name="edit_id_admin" id="edit_id_admin">

                    <div class="form-group">
                        <label for="edit_pass_admin">Password:</label>
                        <input type="password" class="form-control" id="edit_pass_admin" name="edit_pass_admin" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_role_admin">Role:</label>
                        <select class="form-control" id="edit_role_admin" name="edit_role_admin" required>
                            <option value="">Select Role</option>
                            <option value="Governor">Governor</option>
                            <option value="Dean">Dean</option>
                            <option value="Class Mayor">Class Mayor</option>
                            <option value="Developer">Developer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_lastname_admin">Last Name:</label>
                        <input type="text" class="form-control" id="edit_lastname_admin" name="edit_lastname_admin" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_firstname_admin">First Name:</label>
                        <input type="text" class="form-control" id="edit_firstname_admin" name="edit_firstname_admin" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_admin" class="btn btn-primary">Update Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if URL has an edit_id parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('edit_id')) {
        const editId = urlParams.get('edit_id');

        // Fetch admin details based on editId (from the page itself)
        const rows = document.querySelectorAll('#adminTable tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length > 0 && cells[0].textContent.trim() === editId) {
                document.getElementById('edit_id_admin').value = cells[0].textContent.trim();
                document.getElementById('edit_pass_admin').value = cells[1].querySelector('.password-full').textContent.trim();
                document.getElementById('edit_role_admin').value = cells[2].textContent.trim();
                document.getElementById('edit_lastname_admin').value = cells[3].textContent.trim();
                document.getElementById('edit_firstname_admin').value = cells[4].textContent.trim();
                
                // Show the modal
                var editModal = new bootstrap.Modal(document.getElementById('editFormModal'));
                editModal.show();
            }
        });
    }
});
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

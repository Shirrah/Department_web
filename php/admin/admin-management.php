<?php
ob_start();  // Start output buffering
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if it's not already started
}

require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

$query = "SELECT id_admin, pass_admin, role_admin, lastname_admin, firstname_admin FROM admins";
$students = $db->query($query);

// Handle form submission to enroll a new student
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_admin = htmlspecialchars($_POST['id_admin']);
    $pass_admin = htmlspecialchars($_POST['pass_admin']);
    $role_admin = htmlspecialchars($_POST['role_admin']);
    $lastname_admin = htmlspecialchars($_POST['lastname_admin']);
    $firstname_admin = htmlspecialchars($_POST['firstname_admin']);
    
    // Insert the new student data into the database
    $insertQuery = "INSERT INTO admins (id_admin, pass_admin, role_admin, lastname_admin, firstname_admin) 
                    VALUES ('$id_admin', '$pass_admin','$role_admin', '$lastname_admin', '$firstname_admin')";
    if ($db->query($insertQuery) === TRUE) {

        header("Location: ?content=admin-index&admin=admin-management");
        exit();
    } else {
        echo "<script>alert('Error enrolling student: " . $db->error . "');</script>";
    }
}

// Handle student deletion
if (isset($_GET['delete_id'])) {
    $delete_id = htmlspecialchars($_GET['delete_id']);
    $deleteQuery = "DELETE FROM admins WHERE id_admin = '$delete_id'";

    if ($db->query($deleteQuery) === TRUE) {

        header("Location: ?content=admin-index&admin=admin-management");
        exit();
    } else {
        echo "<script>alert('Error deleting student: " . $db->error . "');</script>";
    }
}

// Initialize pagination variables
$limit = 10; // Records per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Current page, default to 1
$_SESSION['page'] = $page; // Store page in session
$offset = ($page - 1) * $limit;

// Fetch total records and calculate total pages
$countQuery = "SELECT COUNT(*) as total FROM admins";
$stmt = $db->prepare($countQuery);
$stmt->execute();
$totalResult = $stmt->get_result();
$row = $totalResult->fetch_assoc();
$totalRecords = $row ? (int)$row['total'] : 0; // Ensure totalRecords is assigned
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;

// Fetch records for the current page
if (isset($_GET['show_all']) && $_GET['show_all'] == 'true') {
    // Query to fetch all student records (no pagination)
    $query = "SELECT id_admin, pass_admin, role_admin, lastname_admin, firstname_admin FROM admins";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $students = $stmt->get_result();
    $totalPages = 1; // Only one page when showing all records
    $page = 1; // Reset to the first page
} else {
    // Query to fetch paginated student records
    $query = "SELECT id_admin, pass_admin, role_admin, lastname_admin, firstname_admin
              FROM admins LIMIT $limit OFFSET $offset";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $students = $stmt->get_result();
}


ob_end_flush();  // End output buffering and send output to the browser
?>

<link rel="stylesheet" href=".//.//stylesheet/admin/student-management.css">
<!-- Add Font Awesome for icons if it's not already included -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


<div class="student-management-body">
        <div class="student-table-con">
        <div class="student-management-header">
        <span>Manage Admin</span>
        <div class="location">
            <a href="?content=admin-index&admin=dashboard">Dashboard</a>
            /
            <span>Manage Admins</span>
        </div>
    </div>
            
            <!-- Enroll button to trigger modal -->
            <button id="enrollButton" onclick="openEnrollForm()">Add a new admin</button>

            <div class="search-students">
            <input class="search-student-input" type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for admins" title="Type to search" style="padding-left: 30px;">
            </div>
       
            <table class="student-table" id="studentTable">
            <thead>
                <tr>
                    <th title="Click to sort" onclick="sortTable(0)">Identification Number <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th title="Click to sort" onclick="sortTable(1)">Password <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th title="Click to sort" onclick="sortTable(2)">Role <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th title="Click to sort" onclick="sortTable(3)">Last Name <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th title="Click to sort" onclick="sortTable(4)">First Name <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th>Actions</th>
                </tr>
            </thead>

    <tbody>

    <style>
        </style>
        <?php
        // Display each student in a table row
        if ($students->num_rows > 0) {
            while ($row = $students->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id_admin']) . "</td>";
                echo "<td>
                    <span class='password-mask'>" . str_repeat('•', strlen($row['pass_admin'])) . "</span>
                    <span class='password-full' style='display:none;'>" . htmlspecialchars($row['pass_admin']) . "</span>
                    <button class='toggle-password-btn' onclick='togglePassword(this)' title='Show Password'>
                        <i class='fas fa-eye'></i>
                    </button>
                </td>";
                echo "<td>" . htmlspecialchars($row['role_admin']) . "</td>";
                echo "<td>" . htmlspecialchars($row['lastname_admin']) . "</td>";
                echo "<td>" . htmlspecialchars($row['firstname_admin']) . "</td>";
                echo "<td>
                    <a href='?content=admin-index&admin=admin-management&edit_id=" . htmlspecialchars($row['id_admin']) . "' class='edit-btn'><i class='fas fa-edit'></i></a>
                    <a href='?content=admin-index&admin=admin-management&delete_id=" . htmlspecialchars($row['id_admin']) . "' class='delete-btn'><i class='fas fa-trash'></i></a>
                </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No students found</td></tr>";
        }
        ?>
    </tbody>
</table>

<script>
    function togglePassword(button) {
        const passwordMask = button.parentElement.querySelector('.password-mask');
        const passwordFull = button.parentElement.querySelector('.password-full');
        const isHidden = passwordFull.style.display === 'none';

        if (isHidden) {
            passwordMask.style.display = 'none';
            passwordFull.style.display = 'inline';
            button.innerHTML = '<i class="fas fa-eye-slash"></i>';
            button.title = 'Hide Password';
        } else {
            passwordMask.style.display = 'inline';
            passwordFull.style.display = 'none';
            button.innerHTML = '<i class="fas fa-eye"></i>';
            button.title = 'Show Password';
        }
    }
</script>


            <!-- No records found message -->
            <p id="noRecordMsg" style="display:none;">No records found</p>
            <div class="pagination">
    <button <?php if($page <= 1) echo 'disabled'; ?> onclick="navigateToPage(1)">First</button>
    <button <?php if($page <= 1) echo 'disabled'; ?> onclick="navigateToPage(<?php echo $page - 1; ?>)">Previous</button>
    <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
    <button <?php if($page >= $totalPages) echo 'disabled'; ?> onclick="navigateToPage(<?php echo $page + 1; ?>)">Next</button>
    <button <?php if($page >= $totalPages) echo 'disabled'; ?> onclick="navigateToPage(<?php echo $totalPages; ?>)">Last</button>

    <!-- Show All Button -->
    <button onclick="showAll()">Show All</button>

    <script>
    function navigateToPage(page) {
        window.location.href = '?content=admin-index&admin=admin-management&page=' + page;
    }

    // Function for Show All button to remove pagination limit
    function showAll() {
        window.location.href = '?content=admin-index&admin=admin-management&show_all=true';
    }
    </script>
</div>

        </div>
</div>

<!-- Enrollment Form Modal -->
<div id="enrollFormModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEnrollForm()">&times;</span>
        <h2>Add New Admin</h2>
        <form class="enrollForm" method="POST" action="">
            <label for="id_admin">Identification Number (ID):</label>
            <input type="text" id="id_admin" name="id_admin" required>

            <label for="pass_admin">Password:</label>
            <input type="password" id="pass_admin" name="pass_admin" required>

            <label for="role_admin">Role:</label>
            <select id="role_admin" name="role_admin" required>
                <option value="" disabled selected>Select Role</option>
                <option value="Governor">Governor</option>
                <option value="Dean">Dean</option>
                <option value="Secretary">Secretary</option>
                <option value="Treasurer">Treasurer</option>
                <option value="Guest Admin">Guest Admin</option>
                <option value="Developer">Developer</option>
                </select>

            <label for="lastname_admin">Lastname:</label>
            <input type="text" id="lastname_admin" name="lastname_admin" required>

            <label for="firstname_amdin">Firstname:</label>
            <input type="text" id="firstname_admin" name="firstname_admin" required>
            

            <input type="submit" value="Add Student">
        </form>
    </div>
</div>



<script>
// JavaScript to open the modal
function openEnrollForm() {
    var modal = document.getElementById("enrollFormModal");
    modal.style.display = "block";
}

// JavaScript to close the modal
function closeEnrollForm() {
    var modal = document.getElementById("enrollFormModal");
    modal.style.display = "none";
}

// Close modal if clicking outside of modal content
window.onclick = function(event) {
    var modal = document.getElementById("enrollFormModal");
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// Add confirmation before deleting a student
document.querySelectorAll('.delete-btn').forEach(function(button) {
    button.addEventListener('click', function(e) {
        if (!confirm("Are you sure you want to delete this student?")) {
            e.preventDefault(); // Prevent the link from being followed
        }
    }); 
});

</script>



<script>
// JavaScript function to sort the table
function sortTable(columnIndex) {
    var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    table = document.getElementById("studentTable");
    switching = true;
    dir = "asc"; // Set the sorting direction to ascending initially

    // Reset all arrow icons to down
    var arrows = table.querySelectorAll('.sort-arrow');
    arrows.forEach(function(arrow) {
        arrow.classList.remove('asc', 'desc');
        arrow.classList.add('desc'); // Reset all arrows to down
    });

    while (switching) {
        switching = false;
        rows = table.rows;

        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[columnIndex];
            y = rows[i + 1].getElementsByTagName("TD")[columnIndex];

            // Check if the two rows should switch place based on the direction
            if (dir == "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir == "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }
        }

        if (shouldSwitch) {
            // If a switch is needed, make the switch and mark that a switch was made
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++;
        } else {
            // If no switching happened and the direction is "asc", change direction to "desc"
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }

    // Toggle the arrow for the clicked column
    var header = table.rows[0].cells[columnIndex];
    var arrow = header.querySelector('.sort-arrow');
    if (dir === "asc") {
        arrow.classList.remove('desc');
        arrow.classList.add('asc');
    } else {
        arrow.classList.remove('asc');
        arrow.classList.add('desc');
    }
}



// JavaScript function to search the table
function searchTable() {
    var input, filter, table, tr, td, i, j, txtValue, visibleRowCount = 0;
    input = document.getElementById("searchInput");
    filter = input.value.toLowerCase();
    table = document.getElementById("studentTable");
    tr = table.getElementsByTagName("tr");

    // Loop through all table rows (except the first, which contains table headers)
    for (i = 1; i < tr.length; i++) {
        tr[i].style.display = "none"; // Hide all rows initially
        td = tr[i].getElementsByTagName("td");

        // Loop through each cell in the row
        for (j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = ""; // Show the row if any cell matches the search query
                    visibleRowCount++; // Count visible rows
                    break;
                }
            }
        }
    }

    // Show "No records found" message if no rows are visible
    if (visibleRowCount === 0) {
        document.getElementById("noRecordMsg").style.display = "block";
    } else {
        document.getElementById("noRecordMsg").style.display = "none";
    }
}
</script>

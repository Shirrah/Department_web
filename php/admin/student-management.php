<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "././php/db-conn.php";
$db = new Database();


// Query to fetch students data
$query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student FROM student";
$students = $db->db->query($query);

// Handle form submission to enroll a new student
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_student = htmlspecialchars($_POST['id_student']);
    $pass_student = htmlspecialchars($_POST['pass_student']);
    $lastname_student = htmlspecialchars($_POST['lastname_student']);
    $firstname_student = htmlspecialchars($_POST['firstname_student']);
    $role_student = htmlspecialchars($_POST['role_student']);
    $year_student = htmlspecialchars($_POST['year_student']);
    
    // Insert the new student data into the database
    $insertQuery = "INSERT INTO student (id_student, pass_student, lastname_student, firstname_student, role_student, year_student) 
                    VALUES ('$id_student', '$pass_student', '$lastname_student', '$firstname_student', '$role_student', '$year_student')";
    if ($db->db->query($insertQuery) === TRUE) {
        header("Location: ?content=admin-index&admin=student-management");
        exit();
    } else {
        echo "<script>alert('Error enrolling student: " . $db->db->error . "');</script>";
    }
}


// Handle student deletion
if (isset($_GET['delete_id'])) {
    $delete_id = htmlspecialchars($_GET['delete_id']);
    $deleteQuery = "DELETE FROM student WHERE id_student = '$delete_id'";

    if ($db->db->query($deleteQuery) === TRUE) {
        header("Location: ?content=admin-index&admin=student-management");
        exit();
    } else {
        echo "<script>alert('Error deleting student: " . $db->db->error . "');</script>";
    }
}

// Handle student edit
if (isset($_GET['edit_id'])) {
    $edit_id = htmlspecialchars($_GET['edit_id']);
    $editQuery = "SELECT * FROM student WHERE id_student = '$edit_id'";
    $editResult = $db->db->query($editQuery);
    $editStudent = $editResult->fetch_assoc();
}

// Handle form submission to update student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_student'])) {
    $id_student = htmlspecialchars($_POST['id_student']);
    $pass_student = htmlspecialchars($_POST['pass_student']);
    $lastname_student = htmlspecialchars($_POST['lastname_student']);
    $firstname_student = htmlspecialchars($_POST['firstname_student']);
    $year_student = htmlspecialchars($_POST['year_student']);

    $updateQuery = "UPDATE student SET pass_student='$pass_student', lastname_student='$lastname_student', firstname_student='$firstname_student', year_student='$year_student' WHERE id_student='$id_student'";

    if ($db->db->query($updateQuery) === TRUE) {
        header("Location: ?content=admin-index&admin=student-management");
        exit();
    } else {
        echo "<script>alert('Error updating student: " . $db->db->error . "');</script>";
    }
}

// Get the selected semester from the URL
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : '';

// Initialize pagination variables
$limit = 10; // Records per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Current page, default to 1
$offset = ($page - 1) * $limit;

// Fetch total records and calculate total pages for the selected semester
$countQuery = "SELECT COUNT(*) as total FROM student WHERE semester_ID = ?";
$stmt = $db->db->prepare($countQuery);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$totalResult = $stmt->get_result();
$row = $totalResult->fetch_assoc();
$totalRecords = $row ? (int)$row['total'] : 0; // Ensure totalRecords is assigned
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;

// Fetch records for the current page for the selected semester
if (isset($_GET['show_all']) && $_GET['show_all'] == 'true') {
    // Query to fetch all records for the selected semester (no pagination)
    $query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
              FROM student WHERE semester_ID = ?";
    $stmt = $db->db->prepare($query);
    $stmt->bind_param("s", $selected_semester);
    $stmt->execute();
    $students = $stmt->get_result();
    $totalPages = 1;
    $page = 1;
} else {
    // Query to fetch paginated records for the selected semester
    $query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
              FROM student WHERE semester_ID = ? LIMIT $limit OFFSET $offset";
    $stmt = $db->db->prepare($query);
    $stmt->bind_param("s", $selected_semester);
    $stmt->execute();
    $students = $stmt->get_result();
}


?>

<link rel="stylesheet" href=".//.//stylesheet/admin/student-management.css">
<!-- Add Font Awesome for icons if it's not already included -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


<div class="student-management-body">
        <div class="student-table-con">
        <div class="student-management-header">
        <span>Manage Student</span>
        <div class="location">
            <a href="?content=admin-index&admin=dashboard">Dashboard</a>
            /
            <span>Manage Students</span>
        </div>
    </div>
            
            <!-- Enroll button to trigger modal -->
            <button id="enrollButton" onclick="openEnrollForm()">Add Student</button>

            <div class="search-students">
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for students..." title="Type to search" style="padding-left: 30px;">
            </div>
       
            <table class="student-table" id="studentTable">
            <thead>
                <tr>
                    <th onclick="sortTable(0)">Identification Number <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th onclick="sortTable(1)">Password <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th onclick="sortTable(2)">Last Name <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th onclick="sortTable(3)">First Name <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th onclick="sortTable(4)">Year <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th>Actions</th>
                </tr>
            </thead>

    <tbody>

    <style>

.sort-arrow {
    margin-left: 5px;
    font-size: 14px;
    opacity: 0.5;
}

.sort-arrow.asc {
    transform: rotate(180deg); /* Flip the arrow for ascending */
}

.sort-arrow.desc {
    transform: rotate(0deg); /* Default, downward arrow for descending */
}

            .arrow {
    margin-left: 5px;
    font-size: 12px;
    color: #aaa;
}

.arrow-up::after {
    content: "▲"; /* Up arrow */
}

.arrow-down::after {
    content: "▼"; /* Down arrow */
}

th {
    cursor: pointer;
}

th:hover .arrow-up, th:hover .arrow-down {
    color: #333; /* Change color on hover */
}

        </style>
        <?php
        // Display each student in a table row
        if ($students->num_rows > 0) {
            while ($row = $students->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id_student']) . "</td>";
                echo "<td>
                    <span class='password-mask'>" . str_repeat('•', strlen($row['pass_student'])) . "</span>
                    <span class='password-full' style='display:none;'>" . htmlspecialchars($row['pass_student']) . "</span>
                    <button class='toggle-password-btn' onclick='togglePassword(this)' title='Show Password'>
                        <i class='fas fa-eye'></i>
                    </button>
                </td>";
                echo "<td>" . htmlspecialchars($row['lastname_student']) . "</td>";
                echo "<td>" . htmlspecialchars($row['firstname_student']) . "</td>";
                echo "<td>" . htmlspecialchars($row['year_student']) . "</td>";
                echo "<td>
                    <a href='?content=admin-index&admin=student-management&edit_id=" . htmlspecialchars($row['id_student']) . "' class='edit-btn'><i class='fas fa-edit'></i></a>
                    <a href='?content=admin-index&admin=student-management&delete_id=" . htmlspecialchars($row['id_student']) . "' class='delete-btn'><i class='fas fa-trash'></i></a>
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

<style>
    .password-mask,
    .password-full {
        margin-right: 5px;
    }

    .toggle-password-btn {
        border: none;
        background: none;
        cursor: pointer;
        font-size: 16px;
    }

    .toggle-password-btn i {
        color: #007bff;
    }

    .toggle-password-btn:hover i {
        color: #0056b3;
    }
</style>


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
        window.location.href = '?content=admin-index&admin=student-management&page=' + page;
    }

    // Function for Show All button to remove pagination limit
    function showAll() {
        window.location.href = '?content=admin-index&admin=student-management&show_all=true';
    }
    </script>
</div>

        </div>
</div>

<!-- Enrollment Form Modal -->
<div id="enrollFormModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEnrollForm()">&times;</span>
        <h2>Add Student</h2>
        <form class="enrollForm" method="POST" action="">
            <label for="id_student">Identification Number (ID):</label>
            <input type="text" id="id_student" name="id_student" required>

            <label for="pass_student">Password:</label>
            <input type="password" id="pass_student" name="pass_student" required>

            <label for="lastname_student">Lastname:</label>
            <input type="text" id="lastname_student" name="lastname_student" required>

            <label for="firstname_student">Firstname:</label>
            <input type="text" id="firstname_student" name="firstname_student" required>
            
            <input type="hidden" id="role_student" name="role_student" value="Student" required>

            <label for="year_student">Year:</label>
                <select id="year_student" name="year_student" required>
                <option value="" disabled selected>Select Year</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
                </select>


            <input type="submit" value="Add Student">
        </form>
    </div>
</div>

<!-- Edit Student Form Modal -->
<div id="editFormModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditForm()">&times;</span>
        <h2>Edit Student</h2>
        <form method="POST" action="">
            <input type="hidden" name="id_student" value="<?php echo htmlspecialchars($editStudent['id_student']); ?>">

            <label for="pass_student">Password:</label>
            <input type="password" id="pass_student" name="pass_student" value="<?php echo htmlspecialchars($editStudent['pass_student']); ?>" required>

            <label for="lastname_student">Lastname:</label>
            <input type="text" id="lastname_student" name="lastname_student" value="<?php echo htmlspecialchars($editStudent['lastname_student']); ?>" required>

            <label for="firstname_student">Firstname:</label>
            <input type="text" id="firstname_student" name="firstname_student" value="<?php echo htmlspecialchars($editStudent['firstname_student']); ?>" required>

            <label for="year_student">Year:</label>
            <select id="year_student" name="year_student" required>
                <option value="1" <?php echo ($editStudent['year_student'] == 1) ? 'selected' : ''; ?>>1st Year</option>
                <option value="2" <?php echo ($editStudent['year_student'] == 2) ? 'selected' : ''; ?>>2nd Year</option>
                <option value="3" <?php echo ($editStudent['year_student'] == 3) ? 'selected' : ''; ?>>3rd Year</option>
                <option value="4" <?php echo ($editStudent['year_student'] == 4) ? 'selected' : ''; ?>>4th Year</option>
            </select>

            <input type="submit" name="update_student" value="Update Student">
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

// JavaScript to open the edit modal
function openEditForm() {
    var modal = document.getElementById("editFormModal");
    modal.style.display = "block";
}

// JavaScript to close the edit modal
function closeEditForm() {
    var modal = document.getElementById("editFormModal");
    modal.style.display = "none";
}

// Close modal if clicking outside of modal content
window.onclick = function(event) {
    var modal = document.getElementById("editFormModal");
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

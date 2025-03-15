<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "././php/db-conn.php";
$db = Database::getInstance()->db;


// Get the user ID from the session (either admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'] ?? null;

if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    $_SESSION['selected_semester'] = $_GET['semester'];
}

$selected_semester = $_SESSION['selected_semester'] ?? null;

// Use the selected semester from the session or default to the latest semester
if (isset($_SESSION['selected_semester'][$user_id]) && !empty($_SESSION['selected_semester'][$user_id])) {
    $selected_semester = $_SESSION['selected_semester'][$user_id];
} else {
    // Get the latest semester from the database
    $query = "SELECT semester_ID, academic_year, semester_type FROM semester ORDER BY semester_ID DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $selected_semester = $row['semester_ID'];
    } else {
        $selected_semester = null;
    }
}

// Fetch all semesters for dropdown
$sql = "SELECT semester_ID, academic_year, semester_type FROM semester";
$stmt = $db->prepare($sql);
$stmt->execute();
$allSemesters = $stmt->get_result();

// Handle form submission to enroll a new student
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate user inputs to prevent SQL injection and XSS
    $id_student = htmlspecialchars($_POST['id_student']);
    $pass_student = htmlspecialchars($_POST['pass_student']);
    $lastname_student = htmlspecialchars($_POST['lastname_student']);
    $firstname_student = htmlspecialchars($_POST['firstname_student']);
    $year_student = htmlspecialchars($_POST['year_student']);
    $semester_ID = htmlspecialchars($_POST['semester_ID']);

    // Use parameterized queries to prevent SQL injection
    $insertQuery = "INSERT INTO student (id_student, semester_ID, pass_student, lastname_student, firstname_student, role_student, year_student) 
                    VALUES (?, ?, ?, ?, ?, 'Student', ?)";
    $stmt = $db->prepare($insertQuery);
    $stmt->bind_param("sssssi", $id_student, $semester_ID, $pass_student, $lastname_student, $firstname_student, $year_student);

    if ($stmt->execute()) {
        header("Location: ?content=admin-index&admin=student-management");
        exit();
    } else {
        echo "<script>alert('Error enrolling student: " . $stmt->error . "');</script>";
    }
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    // Sanitize the delete ID
    $delete_id = htmlspecialchars($_GET['delete_id']);
    $deleteQuery = "DELETE FROM student WHERE id_student = ?";
    $stmt = $db->prepare($deleteQuery);
    $stmt->bind_param("s", $delete_id);

    if ($stmt->execute()) {
        header("Location: ?content=admin-index&admin=student-management");
        exit();
    } else {
        echo "<script>alert('Error deleting student: " . $stmt->error . "');</script>";
    }
}

// Pagination and filtering logic
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$show_all = isset($_GET['show_all']) && $_GET['show_all'] === 'true';
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Query students with parameterized search to prevent SQL injection
$search_term = "%$search%";
if ($show_all) {
    $query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
              FROM student 
              WHERE semester_ID = ? AND 
                    (id_student LIKE ? OR lastname_student LIKE ? OR firstname_student LIKE ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param("ssss", $selected_semester, $search_term, $search_term, $search_term);
} else {
    $query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
              FROM student 
              WHERE semester_ID = ? AND 
                    (id_student LIKE ? OR lastname_student LIKE ? OR firstname_student LIKE ?)
              LIMIT ? OFFSET ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("ssssii", $selected_semester, $search_term, $search_term, $search_term, $limit, $offset);
}
$stmt->execute();
$students = $stmt->get_result();

// Total records with parameterized query
$countQuery = "SELECT COUNT(*) as total FROM student WHERE semester_ID = ? AND 
                (id_student LIKE ? OR lastname_student LIKE ? OR firstname_student LIKE ?)";
$stmt = $db->prepare($countQuery);
$stmt->bind_param("ssss", $selected_semester, $search_term, $search_term, $search_term);
$stmt->execute();
$totalResult = $stmt->get_result();
$totalRecords = $totalResult->fetch_assoc()['total'] ?? 0;
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;

ob_end_flush();
?>


<!-- Styles and Scripts -->
<link rel="stylesheet" href=".//.//stylesheet/admin/student-management.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


<style>
    .navbar-nav .nav-link {
      display: flex;
      align-items: center;
      padding: 0.5rem 1rem;
    }
    .navbar-nav .nav-link i {
      margin-right: 6px;
    }
    .divider {
      border-left: 1px solid #ddd;
      height: 24px;
      margin: auto 0;
    }
  </style>

  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
      <!-- Toggle Button on the Left -->
      <a class="navbar-brand" href="#">Manage Students</a>
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
    <i class="bi bi-box-arrow-in-up"></i> Enroll Student
</button>

          <div class="divider"></div>
        </div>
      </div>
    </div>
  </nav>


<!-- HTML Content -->
<div class="student-management-body">
    <div class="student-table-con">

        <!-- Enroll Form -->
        <!-- <button id="enrollButton" onclick="openEnrollForm()">Add Student</button> -->


        <!-- Search & Show All -->
         <div class="manage-student-menu">
            <div class="search-students">
                <form method="GET" action="">
                    <div class="search-student-con">
                    <input type="hidden" name="content" value="admin-index">
                    <input type="hidden" name="admin" value="student-management">
                    <input class="search-student-input" type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" />
                    <button type="submit">Search</button>
                    </div>
                    <label>
                        <input type="checkbox" name="show_all" value="true" <?= $show_all ? 'checked' : '' ?> onchange="this.form.submit()"> Show all records
                    </label>
                </form>
            </div>
            <div class="manage-student-menu-list">
                <div class="import-container">
                    <!-- Hidden file input -->
                    <input type="file" id="studentFile" name="studentFile" accept=".csv, .xlsx" required>
                    <!-- Import Button that triggers the file input -->
                    <button title="Import (xlsx, csv)" class="btn-import" id="importButton"><i class="fas fa-file-excel"></i>Import</button>
                    <div id="response"></div>
                </div>
            </div>
        </div>

<!-- Student Table -->
<table class="student-table" id="studentTable">
    <thead>
        <tr>
            <th onclick="sortTable(0)">ID</th>
            <th>Password</th>
            <th onclick="sortTable(2)">Last Name</th>
            <th onclick="sortTable(3)">First Name</th>
            <th onclick="sortTable(4)">Year</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($students->num_rows > 0): ?>
            <?php while ($row = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id_student']) ?></td>
                    <td>
                        <span class="password-mask"><?= str_repeat('•', strlen($row['pass_student'])) ?></span>
                        <span class="password-full" style="display:none;"><?= htmlspecialchars($row['pass_student']) ?></span>
                        <button class="toggle-password-btn" onclick="togglePassword(this)"><i class="fas fa-eye"></i></button>
                    </td>
                    <td><?= htmlspecialchars($row['lastname_student']) ?></td>
                    <td><?= htmlspecialchars($row['firstname_student']) ?></td>
                    <td><?= htmlspecialchars($row['year_student']) ?></td>
                    <td>
                        <button class="btn btn-warning edit-student-btn" 
                                data-id="<?= $row['id_student'] ?>"
                                data-password="<?= $row['pass_student'] ?>"
                                data-lastname="<?= $row['lastname_student'] ?>"
                                data-firstname="<?= $row['firstname_student'] ?>"
                                data-year="<?= $row['year_student'] ?>"
                                data-bs-toggle="modal" 
                                data-bs-target="#editStudentModal">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="?content=admin-index&admin=student-management&delete_id=<?= $row['id_student'] ?>" class="btn btn-danger delete-btn">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                        <button class="btn btn-primary show-report-btn" data-id="<?= $row['id_student'] ?>" data-bs-toggle="modal" data-bs-target="#reportModal">
                            Show Report
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No students found</td></tr>
        <?php endif; ?>
    </tbody>
</table>

        <!-- Pagination -->
        <div class="pagination">
            <ul>
                <?php if ($page > 1): ?>
                    <li><a href="?content=admin-index&admin=student-management&page=1&search=<?= urlencode($search) ?>">First</a></li>
                    <li><a href="?content=admin-index&admin=student-management&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Prev</a></li>
                <?php endif; ?>
                <li>Page <?= $page ?> of <?= $totalPages ?></li>
                <?php if ($page < $totalPages): ?>
                    <li><a href="?content=admin-index&admin=student-management&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a></li>
                    <li><a href="?content=admin-index&admin=student-management&page=<?= $totalPages ?>&search=<?= urlencode($search) ?>">Last</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Attendance & Fee Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reportContent">
                <p class="text-center">Loading report...</p>
            </div>
        </div>
    </div>
</div>


<script>
    document.querySelectorAll('.show-report-btn').forEach(button => {
    button.addEventListener('click', function () {
        const studentId = this.getAttribute('data-id');
        document.getElementById('reportContent').innerHTML = "<p class='text-center'>Loading report...</p>";

        // Fetch the student report via AJAX
        fetch("php/admin/fetch-student-report.php?id_student=" + studentId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('reportContent').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('reportContent').innerHTML = "<p class='text-danger text-center'>Error loading report.</p>";
                console.error("Error fetching report:", error);
            });
    });
});

</script>

<script>
    document.getElementById('importButton').addEventListener('click', function() {
        // Trigger the file input click when the import button is clicked
        document.getElementById('studentFile').click();
    });

   document.getElementById('studentFile').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('studentFile', file);

    document.getElementById('response').innerHTML = 'Uploading...';

    fetch('php/admin/upload-students.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        document.getElementById('response').innerHTML = `<span style="color: green;">${result}</span>`;
        location.reload(); // Reload page on successful upload
    })
    .catch(error => {
        document.getElementById('response').innerHTML = `<span style="color: red;">Error: ${error.message}</span>`;
    });
});

</script>

    <script>
    function navigateToPage(page) {
        window.location.href = '?content=admin-index&admin=student-management&page=' + page;
    }

    // Function for Show All button to remove pagination limit
    function showAll() {
        window.location.href = '?content=admin-index&admin=student-management&show_all=true';
    }
    </script>

    
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
</div>

        </div>
</div>

<!-- Bootstrap Enrollment Form Modal -->
<div class="modal fade" id="enrollFormModal" tabindex="-1" aria-labelledby="enrollFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enrollFormModalLabel">Add Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="overflow: hidden;">
                <form class="enrollForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="id_student" class="form-label">Identification Number (ID):</label>
                        <input type="text" class="form-control" id="id_student" name="id_student" required>
                    </div>
                    
                    <input type="hidden" id="semester_ID" name="semester_ID" value="<?php echo htmlspecialchars($selected_semester); ?>" required>
                    
                    <div class="mb-3">
                        <label for="pass_student" class="form-label">Password:</label>
                        <input type="password" class="form-control" id="pass_student" name="pass_student" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="lastname_student" class="form-label">Lastname:</label>
                        <input type="text" class="form-control" id="lastname_student" name="lastname_student" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="firstname_student" class="form-label">Firstname:</label>
                        <input type="text" class="form-control" id="firstname_student" name="firstname_student" required>
                    </div>
                    
                    <input type="hidden" id="role_student" name="role_student" value="Student" required>
                    
                    <div class="mb-3">
                        <label for="year_student" class="form-label">Year:</label>
                        <select class="form-select" id="year_student" name="year_student" required>
                            <option value="" disabled selected>Select Year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Add Student</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
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

</script>


<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editStudentForm" method="POST" action="././php/admin/update_student.php">
                    <input type="hidden" id="edit_id_student" name="id_student">
                    
                    <div class="mb-3">
                        <label for="edit_pass_student" class="form-label">Password:</label>
                        <input type="password" class="form-control" id="edit_pass_student" name="pass_student" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_lastname_student" class="form-label">Lastname:</label>
                        <input type="text" class="form-control" id="edit_lastname_student" name="lastname_student" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_firstname_student" class="form-label">Firstname:</label>
                        <input type="text" class="form-control" id="edit_firstname_student" name="firstname_student" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_year_student" class="form-label">Year:</label>
                        <select class="form-select" id="edit_year_student" name="year_student" required>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Update Student</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
    const editButtons = document.querySelectorAll(".edit-student-btn");

    editButtons.forEach(button => {
        button.addEventListener("click", function() {
            document.getElementById("edit_id_student").value = this.getAttribute("data-id");
            document.getElementById("edit_pass_student").value = this.getAttribute("data-password");
            document.getElementById("edit_lastname_student").value = this.getAttribute("data-lastname");
            document.getElementById("edit_firstname_student").value = this.getAttribute("data-firstname");
            document.getElementById("edit_year_student").value = this.getAttribute("data-year");
        });
    });
});

</script>
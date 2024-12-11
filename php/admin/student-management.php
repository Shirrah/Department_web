<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "././php/db-conn.php";
$db = new Database();

// Handle form submission to enroll a new student
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_student = htmlspecialchars($_POST['id_student']);
    $pass_student = htmlspecialchars($_POST['pass_student']);
    $lastname_student = htmlspecialchars($_POST['lastname_student']);
    $firstname_student = htmlspecialchars($_POST['firstname_student']);
    $year_student = htmlspecialchars($_POST['year_student']);
    
    // Insert query
    $insertQuery = "INSERT INTO student (id_student, pass_student, lastname_student, firstname_student, role_student, year_student) 
                    VALUES (?, ?, ?, ?, 'Student', ?)";
    $stmt = $db->db->prepare($insertQuery);
    $stmt->bind_param("sssss", $id_student, $pass_student, $lastname_student, $firstname_student, $year_student);

    if ($stmt->execute()) {
        header("Location: ?content=admin-index&admin=student-management");
        exit();
    } else {
        echo "<script>alert('Error enrolling student: " . $db->db->error . "');</script>";
    }
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = htmlspecialchars($_GET['delete_id']);
    $deleteQuery = "DELETE FROM student WHERE id_student = ?";
    $stmt = $db->db->prepare($deleteQuery);
    $stmt->bind_param("s", $delete_id);

    if ($stmt->execute()) {
        header("Location: ?content=admin-index&admin=student-management");
        exit();
    } else {
        echo "<script>alert('Error deleting student: " . $db->db->error . "');</script>";
    }
}

// Get semester and search parameters
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : (isset($_SESSION['selected_semester']) ? $_SESSION['selected_semester'] : '');
$_SESSION['selected_semester'] = $selected_semester;
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$show_all = isset($_GET['show_all']) && $_GET['show_all'] === 'true';

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Query based on "Show All" state
$search_term = "%$search%";
if ($show_all) {
    $query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
              FROM student 
              WHERE semester_ID = ? AND 
                    (id_student LIKE ? OR lastname_student LIKE ? OR firstname_student LIKE ?)";
    $stmt = $db->db->prepare($query);
    $stmt->bind_param("ssss", $selected_semester, $search_term, $search_term, $search_term);
} else {
    $query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
              FROM student 
              WHERE semester_ID = ? AND 
                    (id_student LIKE ? OR lastname_student LIKE ? OR firstname_student LIKE ?)
              LIMIT ? OFFSET ?";
    $stmt = $db->db->prepare($query);
    $stmt->bind_param("ssssii", $selected_semester, $search_term, $search_term, $search_term, $limit, $offset);
}
$stmt->execute();
$students = $stmt->get_result();

// Total records for pagination
$countQuery = "SELECT COUNT(*) as total FROM student WHERE semester_ID = ? AND 
                (id_student LIKE ? OR lastname_student LIKE ? OR firstname_student LIKE ?)";
$stmt = $db->db->prepare($countQuery);
$stmt->bind_param("ssss", $selected_semester, $search_term, $search_term, $search_term);
$stmt->execute();
$totalResult = $stmt->get_result();
$row = $totalResult->fetch_assoc();
$totalRecords = $row['total'] ?? 0;
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;

ob_end_flush();
?>

<!-- Styles and Scripts -->
<link rel="stylesheet" href=".//.//stylesheet/admin/student-management.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- HTML Content -->
<div class="student-management-body">
    <div class="student-table-con">
        <div class="student-management-header">
            <span>Manage Students</span>
            <div class="location">
                <a href="?content=admin-index&admin=dashboard">Dashboard</a> / <span>Manage Students</span>
            </div>
        </div>
        
        <!-- Enroll Form -->
        <button id="enrollButton" onclick="openEnrollForm()">Add Student</button>

        <!-- Search & Show All -->
        <div class="search-students">
            <form method="GET" action="">
                <input type="hidden" name="content" value="admin-index">
                <input type="hidden" name="admin" value="student-management">
                <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" />
                <button type="submit">Search</button>
                <label>
                    <input type="checkbox" name="show_all" value="true" <?= $show_all ? 'checked' : '' ?> onchange="this.form.submit()"> Show All
                </label>
            </form>
            <div class="import-container">
                <h2>Import Students</h2>
                <!-- Hidden file input -->
                <input type="file" id="studentFile" name="studentFile" accept=".csv, .xlsx" required>
                <!-- Import Button that triggers the file input -->
                <button class="btn-import" id="importButton">Import</button>
                <div id="response"></div>
            </div>
            <style>
        body {
            font-family: Arial, sans-serif;
        }
        .import-container {
            margin: 50px auto;
            width: 400px;
            text-align: center;
        }
        input[type="file"] {
            display: none; /* Hide the file input */
        }
        .btn-import {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        .btn-import:hover {
            background-color: #45a049;
        }
        #response {
            margin-top: 20px;
        }
    </style>
        <script>
        document.getElementById('importButton').addEventListener('click', function() {
            // Trigger the file input click when the import button is clicked
            document.getElementById('studentFile').click();
        });

        document.getElementById('studentFile').addEventListener('change', function(event) {
            // Automatically submit the form after the file is selected
            const formData = new FormData();
            formData.append('studentFile', event.target.files[0]);

            fetch('upload-students.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                document.getElementById('response').innerHTML = result;
            })
            .catch(error => {
                document.getElementById('response').innerHTML = 'Error uploading file: ' + error;
            });
        });
    </script>
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
                                <a href="?content=admin-index&admin=student-management&edit_id=<?= $row['id_student'] ?>"><i class="fas fa-edit"></i></a>
                                <a href="?content=admin-index&admin=student-management&delete_id=<?= $row['id_student'] ?>" class="delete-btn"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No students found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if (!$show_all): ?>
            <div class="pagination">
                <button <?= $page <= 1 ? 'disabled' : '' ?> onclick="navigateToPage(1)">First</button>
                <button <?= $page <= 1 ? 'disabled' : '' ?> onclick="navigateToPage(<?= $page - 1 ?>)">Previous</button>
                <span>Page <?= $page ?> of <?= $totalPages ?></span>
                <button <?= $page >= $totalPages ? 'disabled' : '' ?> onclick="navigateToPage(<?= $page + 1 ?>)">Next</button>
                <button <?= $page >= $totalPages ? 'disabled' : '' ?> onclick="navigateToPage(<?= $totalPages ?>)">Last</button>
            </div>
        <?php endif; ?>
    </div>
</div>

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

<!-- Enrollment Form Modal -->
<div id="enrollFormModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEnrollForm()">&times;</span>
        <h2>Add Student</h2>
        <form class="enrollForm" method="POST" action="">
            <label for="id_student">Identification Number (ID):</label>
            <input type="text" id="id_student" name="id_student" required>

            <input type="hidden" id="semester_ID" name="role_student" value="Student" required>

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
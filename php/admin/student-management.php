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

$query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
          FROM student 
          WHERE semester_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$students = $stmt->get_result();

// No need for the count query or pagination variables
ob_end_flush();
?>


<!-- Styles and Scripts -->
<link rel="stylesheet" href=".//.//stylesheet/admin/student-management.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="js/upload-students.js"></script>


<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container-fluid">
    <!-- Toggle Button on the Left -->
    <a class="navbar-brand" href="#">Manage Students</a>
    <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible Navbar Content -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <div class="navbar-nav ms-auto">
        <!-- Divider Removed for Simplicity -->
        
        <!-- Search Bar with Form -->
        <div class="d-flex align-items-center">
        <div class="input-group">
            <input type="text" class="form-control" id="searchStudentInput" placeholder="Search students..." 
                aria-label="Search students" aria-describedby="basic-addon2">
            <span class="input-group-text" id="basic-addon2"><i class="fas fa-search"></i></span>
        </div>
    </div>


        <!-- Import Button -->
        <button class="btn btn-outline-success ms-3" id="enrollButton" data-bs-toggle="modal" data-bs-target="#enrollCsvModal">
          <i class="fas fa-file-csv"></i> Import
        </button>

        <!-- Enroll Button to Trigger Modal -->
        <button class="btn btn-outline-primary ms-3" id="enrollButton" data-bs-toggle="modal" data-bs-target="#enrollFormModal">
          <i class="bi bi-box-arrow-in-up"></i> Enroll Student
        </button>
      </div>
    </div>
  </div>
</nav>


<!-- import csv modal -->
<div class="modal fade" id="enrollCsvModal" tabindex="-1" aria-labelledby="enrollCsvModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="enrollCsvModalLabel">Import Students by CSV</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="import-container">
          <!-- Hidden semester input -->
          <input type="hidden" id="csv_semester_ID" name="semester_ID" value="<?php echo htmlspecialchars($selected_semester); ?>">
          
          <p class="text-muted small mb-3">
            Download template: 
            <a href="templates/student-import-template.csv" download class="text-primary">
              <i class="fas fa-file-csv"></i> CSV Template
            </a> or 
            <a href="templates/student-import-template.xlsx" download class="text-success">
              <i class="fas fa-file-excel"></i> Excel Template
            </a>
          </p>
          
          <!-- File input -->
          <input type="file" id="studentFile" name="studentFile" accept=".csv, .xlsx" required style="display: none;">
          
          <button title="Import (xlsx, csv)" class="btn btn-primary" id="importButton">
            <i class="fas fa-file-excel"></i> Choose File
          </button>
          
          <div id="fileInfo" class="small mt-2 text-muted" style="display: none;"></div>
          <div id="response" class="mt-3"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- HTML Content -->
<div class="student-management-body">
    <div class="student-table-con">
<!-- Student Table -->
<table class="student-table" id="studentTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Password</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Year</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="studentTableBody">
        <!-- This will be populated by JavaScript -->
    </tbody>
</table>

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
            <form class="enrollForm" id="enrollForm">

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


<?php include_once "././php/toast-system.php"; ?>


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
                    <div class="mb-3">
                        <label for="" class="form-label">Student ID:</label>
                        <input type="text" class="form-control" id="edit_id_student" name="id_student" readonly>
                    </div>
                    
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

<script src="js/delete-student.js"></script>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this student? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>


<script>
// Pass PHP session value to JavaScript
const selectedSemester = '<?php echo $_SESSION['selected_semester'][$user_id] ?? ''; ?>';

document.addEventListener('DOMContentLoaded', function() {
    // Load students initially with the selected semester
    loadStudents(selectedSemester || null);

    // Function to load students via AJAX
    function loadStudents(semester = null) {
        const tbody = document.getElementById('studentTableBody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Loading students...</td></tr>';
        
        // Build the URL with semester parameter if provided
        let url = 'php/admin/fetch-student-records.php';
        if (semester) {
            url += `?semester=${semester}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6">No students found</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                data.forEach(student => {
                    const row = document.createElement('tr');
                    row.id = `student-row-${escapeHtml(student.id_student)}`; // Add unique row id
                    row.innerHTML = `
                        <td>${escapeHtml(student.id_student)}</td>
                        <td>
                            <span class="password-mask">${'•'.repeat(student.pass_student.length)}</span>
                            <span class="password-full" style="display:none;">${escapeHtml(student.pass_student)}</span>
                            <button class="toggle-password-btn" onclick="togglePassword(this)"><i class="fas fa-eye"></i></button>
                        </td>
                        <td>${escapeHtml(student.lastname_student)}</td>
                        <td>${escapeHtml(student.firstname_student)}</td>
                        <td>${escapeHtml(student.year_student)}</td>
                        <td>
                            <button class="btn btn-warning edit-student-btn btn-sm" 
                                    data-id="${escapeHtml(student.id_student)}"
                                    data-password="${escapeHtml(student.pass_student)}"
                                    data-lastname="${escapeHtml(student.lastname_student)}"
                                    data-firstname="${escapeHtml(student.firstname_student)}"
                                    data-year="${escapeHtml(student.year_student)}"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editStudentModal">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger delete-btn btn-sm" 
                                onclick="showDeleteConfirmation('${escapeHtml(student.id_student)}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <button class="btn btn-primary show-report-btn btn-sm" 
                                    data-id="${escapeHtml(student.id_student)}" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#reportModal">
                                Show Report
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                // Reattach event listeners for the new elements
                attachEventListeners();
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = '<tr><td colspan="6" class="text-danger">Error loading students</td></tr>';
            });
    }

    // Helper function to escape HTML
    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    // Function to attach event listeners to dynamic elements
    function attachEventListeners() {
        // Report button click handlers
        document.querySelectorAll('.show-report-btn').forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.getAttribute('data-id');
                document.getElementById('reportContent').innerHTML = "<p class='text-center'>Loading report...</p>";
                
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
    
        
        // Edit button handlers
        document.querySelectorAll('.edit-student-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('edit_id_student').value = this.getAttribute('data-id');
                document.getElementById('edit_pass_student').value = this.getAttribute('data-password');
                document.getElementById('edit_lastname_student').value = this.getAttribute('data-lastname');
                document.getElementById('edit_firstname_student').value = this.getAttribute('data-firstname');
                document.getElementById('edit_year_student').value = this.getAttribute('data-year');
            });
        });
    }
    
    // If you have a semester dropdown, add change handler
    const semesterDropdown = document.getElementById('semesterDropdown');
    if (semesterDropdown) {
        semesterDropdown.addEventListener('change', function() {
            loadStudents(this.value);
        });
    }

    
// Search functionality
const searchInput = document.getElementById('searchStudentInput');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#studentTableBody tr');
        let hasMatches = false;
        
        rows.forEach(row => {
            if (row.id === 'noResultsRow') return; // Skip the no results row
            
            const cells = row.getElementsByTagName('td');
            let rowMatches = false;
            
            // Check each cell except the last one (actions)
            for (let j = 0; j < cells.length - 1; j++) {
                const cellText = cells[j].textContent.toLowerCase();
                if (cellText.includes(searchTerm)) {
                    rowMatches = true;
                    hasMatches = true;
                    break;
                }
            }
            
            row.style.display = rowMatches ? '' : 'none';
        });
        
        // Handle no results message
        const noResultsRow = document.getElementById('noResultsRow');
        if (noResultsRow) {
            if (searchTerm && !hasMatches) {
                // Create the row if it doesn't exist
                if (!noResultsRow) {
                    const tbody = document.querySelector('#studentTableBody');
                    noResultsRow = document.createElement('tr');
                    noResultsRow.id = 'noResultsRow';
                    noResultsRow.innerHTML = `<td colspan="${document.querySelector('#studentTable thead th').length}" class="text-center">No results found</td>`;
                    tbody.appendChild(noResultsRow);
                }
                noResultsRow.style.display = '';
            } else {
                noResultsRow.style.display = 'none';
            }
        }
    });
}
    // Reload students after form submission
    document.getElementById('enrollForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('php/admin/enroll-student.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            if (result.trim() === 'success') {
                this.reset();
                const modal = bootstrap.Modal.getInstance(document.getElementById('enrollFormModal'));
                modal.hide();
                createToast('success', 'Student enrolled successfully!');
                loadStudents(); // Reload the student list
            } else {
                createToast('error', result);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            createToast('error', 'Something went wrong.');
        });
    });
    
    // Handle edit form submission
    document.getElementById('editStudentForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('php/admin/update_student.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            if (result.trim() === 'success') {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editStudentModal'));
                modal.hide();
                createToast('success', 'Student updated successfully!');
                loadStudents(); // Reload the student list
            } else {
                createToast('error', result);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            createToast('error', 'Something went wrong.');
        });
    });
});

// Global function for password toggling
function togglePassword(button) {
    const row = button.closest('tr');
    const passwordMask = row.querySelector('.password-mask');
    const passwordFull = row.querySelector('.password-full');
    
    if (passwordMask.style.display === 'none') {
        passwordMask.style.display = '';
        passwordFull.style.display = 'none';
        button.innerHTML = '<i class="fas fa-eye"></i>';
    } else {
        passwordMask.style.display = 'none';
        passwordFull.style.display = '';
        button.innerHTML = '<i class="fas fa-eye-slash"></i>';
    }
}
</script>
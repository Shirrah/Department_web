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
    <a class="navbar-brand fw-bold" href="#">Manage Students</a>
    <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible Navbar Content -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <div class="navbar-nav ms-auto">
        <!-- Divider Removed for Simplicity -->
        
        <!-- Search Bar with Form -->
        <!-- <div class="d-flex align-items-center">
        <div class="input-group">
            <input type="text" class="form-control" id="searchStudentInput" placeholder="Search students..." 
                aria-label="Search students" aria-describedby="basic-addon2">
            <span class="input-group-text" id="basic-addon2"><i class="fas fa-search"></i></span>
        </div>
    </div> -->

    <!-- Search Bar with Form -->
<form id="searchForm" class="d-flex align-items-center">
    <div class="input-group">
        <input type="text" class="form-control" id="searchStudentInput" name="search" placeholder="Search students..." 
               aria-label="Search students" aria-describedby="basic-addon2">
        <span class="input-group-text" id="basic-addon2"><i class="fas fa-search"></i></span>
    </div>
</form>
      </div>
    </div>
  </div>
</nav>



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
            <th>Actions
                
            </th>
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
                        <button class="btn btn-warning edit-student-btn btn-sm" 
                                data-id="<?= $row['id_student'] ?>"
                                data-password="<?= $row['pass_student'] ?>"
                                data-lastname="<?= $row['lastname_student'] ?>"
                                data-firstname="<?= $row['firstname_student'] ?>"
                                data-year="<?= $row['year_student'] ?>"
                                data-bs-toggle="modal" 
                                data-bs-target="#editStudentModal">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="?content=admin-index&admin=student-management&delete_id=<?= $row['id_student'] ?>" class="btn btn-danger delete-btn btn-sm">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                        <button class="btn btn-primary show-report-btn btn-sm" data-id="<?= $row['id_student'] ?>" data-bs-toggle="modal" data-bs-target="#reportModal">
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

    </div>
</div>

<!-- Add this script after your existing scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchStudentInput');
    const studentTable = document.getElementById('studentTable');
    const rows = studentTable.getElementsByTagName('tr');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        // Start from index 1 to skip header row
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let shouldShow = false;
            
            // Check each cell in the row (except the last one with actions)
            for (let j = 0; j < cells.length - 1; j++) {
                const cellText = cells[j].textContent.toLowerCase();
                if (cellText.includes(searchTerm)) {
                    shouldShow = true;
                    break;
                }
            }
            
            row.style.display = shouldShow ? '' : 'none';
        }
    });
});

// Keep your existing togglePassword function
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

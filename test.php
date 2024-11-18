<?php
require_once "././php/db-conn.php";
$db = new Database();

// Pagination variables
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $limit; // Offset for SQL query

// Fetch total number of records
$countQuery = "SELECT COUNT(*) as total FROM student";
$totalResult = $db->db->query($countQuery);
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit); // Total pages

// Fetch students for the current page
$query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
          FROM student LIMIT $limit OFFSET $offset";
$students = $db->db->query($query);
?>

<link rel="stylesheet" href=".//.//stylesheet/admin/student-management.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="body">
<div class="student-management-body">
    <div class="student-table-con">
        <div class="student-management-header">
            <span>Manage Student</span>
            <div class="location">
                <a href="?content=admin-index&admin=dashboard">Dashboard</a> / <span>Manage Students</span>
            </div>
        </div>
        
        <button id="enrollButton" onclick="openEnrollForm()">Add Student</button>
        
        <div class="search-students">
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for students..." style="padding-left: 30px;">
        </div>
        
        <table class="student-table" id="studentTable">
            <thead>
                <tr>
                    <th>Identification Number</th>
                    <th>Password</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Year</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students->num_rows > 0): ?>
                    <?php while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_student']); ?></td>
                            <td><?php echo str_repeat('•', strlen($row['pass_student'])); ?></td>
                            <td><?php echo htmlspecialchars($row['lastname_student']); ?></td>
                            <td><?php echo htmlspecialchars($row['firstname_student']); ?></td>
                            <td><?php echo htmlspecialchars($row['year_student']); ?></td>
                            <td>
                                <a href="?content=admin-index&admin=student-management&edit_id=<?php echo htmlspecialchars($row['id_student']); ?>" class="edit-btn"><i class="fas fa-edit"></i></a>
                                <a href="?content=admin-index&admin=student-management&delete_id=<?php echo htmlspecialchars($row['id_student']); ?>" class="delete-btn"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No students found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination controls -->
        <div class="pagination">
            <button <?php if($page <= 1) echo 'disabled'; ?> onclick="navigateToPage(1)">First</button>
            <button <?php if($page <= 1) echo 'disabled'; ?> onclick="navigateToPage(<?php echo $page - 1; ?>)">Previous</button>
            <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
            <button <?php if($page >= $totalPages) echo 'disabled'; ?> onclick="navigateToPage(<?php echo $page + 1; ?>)">Next</button>
            <button <?php if($page >= $totalPages) echo 'disabled'; ?> onclick="navigateToPage(<?php echo $totalPages; ?>)">Last</button>
        </div>
    </div>
</div>
</div>

<script>
function navigateToPage(page) {
    window.location.href = '?content=admin-index&admin=student-management&page=' + page;
}
</script>



<style>
    .body{
        height: 100vh;
    }
    .student-management-body {
    width: 100%;
    height: 100vh;
    overflow: auto;
}

.student-table-con {
    margin-top: 20px;
    padding: 1.8rem;
    background-color: white;
}

.student-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.student-table th, .student-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e8ecf1;
}

.student-table th {
    background-color: #f2f2f2;
    font-weight: bold;
    position: sticky;
    top: 0;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.pagination button {
    padding: 10px;
    cursor: pointer;
    background-color: #007BFF;
    color: white;
    border: none;
    border-radius: 4px;
}

.pagination button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

</style>
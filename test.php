<?php
require_once "././php/db-conn.php";
$db = new Database();

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Handle search input
$search = isset($_GET['search']) ? $db->db->real_escape_string($_GET['search']) : '';

// Modify the query to include search
$search_condition = $search ? "WHERE id_student LIKE '%$search%' 
    OR pass_student LIKE '%$search%' 
    OR lastname_student LIKE '%$search%' 
    OR firstname_student LIKE '%$search%' 
    OR year_student LIKE '%$search%'" : '';

// Query to fetch total number of students with search condition
$total_query = "SELECT COUNT(*) AS total FROM student $search_condition";
$total_result = $db->db->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Query to fetch paginated students data
$query = "SELECT id_student, pass_student, lastname_student, firstname_student, year_student 
          FROM student $search_condition 
          LIMIT $offset, $records_per_page";
$students = $db->db->query($query);
?>


<link rel="stylesheet" href=".//.//stylesheet/admin/student-management.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="student-management-body">
    <div class="student-table-con">
        <div class="student-management-header">
            <span>Manage Student</span>
            <div class="location">
                <a href="?content=admin-index&admin=dashboard">Dashboard</a> /
                <span>Manage Students</span>
            </div>
        </div>

        <div class="search-students">
    <form method="GET" action="">
        <input type="hidden" name="content" value="manage-students">
        <input type="text" name="search" id="searchInput" placeholder="Search for students..." value="<?= htmlspecialchars($search) ?>" style="padding-left: 30px;">
        <button type="submit">Search</button>
    </form>
</div>


        <table class="student-table" id="studentTable">
            <thead>
                <tr>
                    <th title="Click to sort" onclick="sortTable(0)">Identification Number <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th>Password</th>
                    <th title="Click to sort" onclick="sortTable(1)">Last Name <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th title="Click to sort" onclick="sortTable(2)">First Name <i class="fas fa-arrow-down sort-arrow"></i></th>
                    <th title="Click to sort" onclick="sortTable(3)">Year <i class="fas fa-arrow-down sort-arrow"></i></th>
                </tr>
            </thead>

            <tbody>
                <?php
                if ($students->num_rows > 0) {
                    while ($row = $students->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id_student']) . "</td>";
                        echo "<td>
                            <span class='password-mask'>" . str_repeat('•', strlen($row['pass_student'])) . "</span>
                            <button class='toggle-password-btn' onclick='togglePassword(this)' title='Show Password'>
                                <i class='fas fa-eye'></i>
                            </button>
                        </td>";
                        echo "<td>" . htmlspecialchars($row['lastname_student']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['firstname_student']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['year_student']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No students found</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <p id="noRecordMsg" style="display:none;">No records found</p>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?content=manage-students&page=<?= $page - 1 ?>" class="pagination-prev">&laquo; Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?content=manage-students&page=<?= $i ?>" class="pagination-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?content=manage-students&page=<?= $page + 1 ?>" class="pagination-next">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function togglePassword(button) {
    const passwordMask = button.parentElement.querySelector('.password-mask');
    const isHidden = passwordMask.style.display !== 'none';

    if (isHidden) {
        passwordMask.style.display = 'none';
        button.title = 'Hide Password';
        button.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        passwordMask.style.display = 'inline';
        button.title = 'Show Password';
        button.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

function searchTable() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("studentTable");
    const rows = table.getElementsByTagName("tr");
    let visibleRowCount = 0;

    for (let i = 1; i < rows.length; i++) {
        rows[i].style.display = "none";
        const cells = rows[i].getElementsByTagName("td");

        for (let j = 0; j < cells.length; j++) {
            if (cells[j] && cells[j].innerText.toLowerCase().indexOf(filter) > -1) {
                rows[i].style.display = "";
                visibleRowCount++;
                break;
            }
        }
    }

    document.getElementById("noRecordMsg").style.display = visibleRowCount === 0 ? "block" : "none";
}
</script>

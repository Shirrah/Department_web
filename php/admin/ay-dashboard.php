<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "././php/db-conn.php";
$db = new Database();

$query = "SELECT semester_ID, academic_year, semester_type, date_created FROM semester";
$semester = $db->db->query($query);

?>




<link rel="stylesheet" href=".//.//stylesheet/admin/ay-dashboard.css">

<div class="ay-dashboard-body">
<div class="ay-dashboard-con">
<div class="ay-dashboard-header">
        <span>Academic Year and Semester</span>
        <div class="location">
            <a href="?content=admin-index&admin=dashboard">Dashboard</a>
            /
            <span>Academic year & Sem</span>
        </div>
    </div>



    <table class="semester-table">
                <thead>
                    <tr>
                        <th>Semester ID</th>
                        <th>Academic Year</th>
                        <th>Semester Type</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    <?php
    // Display each student in a table row
    if ($semester->num_rows > 0) {
        while ($row = $semester->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['semester_ID']) . "</td>";
            echo "<td>" . htmlspecialchars($row['academic_year']) . "</td>";
            echo "<td>" . htmlspecialchars($row['semester_type']) . "</td>";
            echo "<td>" . date("Y-m-d h:i A", strtotime($row['date_created'])) . "</td>";

            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No semester found</td></tr>";
    }
    ?>
</tbody>
</table>


</div>
</div>
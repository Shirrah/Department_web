<?php
require_once "../db-conn.php";
$db = Database::getInstance()->db;

// Fetch semesters
$query = "SELECT semester_ID, academic_year, semester_type FROM semester";
$semesterResult = $db->query($query);

if ($semesterResult->num_rows > 0) {
    while ($row = $semesterResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['semester_ID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['academic_year']) . "</td>";
        echo "<td>" . htmlspecialchars($row['semester_type']) . "</td>";
        echo "<td>";
        echo "<button class='btn btn-warning btn-sm me-1 edit-btn' 
                data-id='" . $row["semester_ID"] . "' 
                data-year='" . $row["academic_year"] . "' 
                data-type='" . $row["semester_type"] . "' 
                data-bs-toggle='modal' 
                data-bs-target='#editTermModal'>
                <i class='fas fa-edit'></i> Edit
              </button>";
        echo "<button class='btn btn-danger btn-sm delete-btn' onclick=\"showDeleteConfirmation('" . $row["semester_ID"] . "')\">
                <i class='fas fa-trash'></i> Delete
              </button>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No semester found</td></tr>";
}
?>

<?php
require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

// Fetch the semesters from the database
$query = "SELECT semester_ID, academic_year, semester_type, status FROM semester";
$semester = $db->query($query);

if ($semester->num_rows > 0) {
    while ($row = $semester->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['semester_ID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['academic_year']) . "</td>";
        echo "<td>" . htmlspecialchars($row['semester_type']) . "</td>";
        echo "<td>
            <div class='form-check form-switch'>
                <input class='form-check-input status-toggle' type='checkbox'
                    data-id='" . $row["semester_ID"] . "'
                    " . ($row['status'] === 'active' ? 'checked' : '') . ">
                <label class='form-check-label'>" . ucfirst($row['status']) . "</label>
            </div>
        </td>";
        echo "<td>";
        echo "<button class='btn btn-warning btn-sm me-1 edit-btn'
            data-id='" . $row["semester_ID"] . "'
            data-year='" . $row["academic_year"] . "'
            data-type='" . $row["semester_type"] . "'
            data-bs-toggle='modal'
            data-bs-target='#editTermModal'>
            <i class='fas fa-edit'></i> Edit
        </button>";
        echo "<button class='btn btn-danger btn-sm delete-btn' 
            data-bs-toggle='modal' 
            data-bs-target='#deleteConfirmationModal' 
            data-id='" . htmlspecialchars($row["semester_ID"]) . "'>
            <i class='fas fa-trash'></i> Delete
        </button>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center'>No semester found</td></tr>";
}
?>

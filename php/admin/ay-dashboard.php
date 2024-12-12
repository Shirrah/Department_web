<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start(); // Start output buffering

require_once "././php/db-conn.php";
$db = new Database();

$query = "SELECT semester_ID, academic_year, semester_type FROM semester";
$semester = $db->db->query($query);

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = htmlspecialchars($_GET['delete_id']);
    
    // First, delete students related to the semester
    $deleteStudentsQuery = "DELETE FROM student WHERE semester_ID = ?";
    $stmt = $db->db->prepare($deleteStudentsQuery);
    $stmt->bind_param("s", $delete_id);
    
    if ($stmt->execute()) {
        // Now, delete the semester
        $deleteQuery = "DELETE FROM semester WHERE semester_ID = ?";
        $stmt = $db->db->prepare($deleteQuery);
        $stmt->bind_param("s", $delete_id);

        if ($stmt->execute()) {
            header("Location: ?content=admin-index&admin=ay-dashboard");
            exit();
        } else {
            echo "<script>alert('Error deleting term: " . $db->db->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error deleting students: " . $db->db->error . "');</script>";
    }
}


// Edit functionality
$editData = null;
if (isset($_GET['edit_id'])) {
    $edit_id = htmlspecialchars($_GET['edit_id']);
    $editQuery = "SELECT * FROM semester WHERE semester_ID = ?";
    $stmt = $db->db->prepare($editQuery);
    $stmt->bind_param("s", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $editData = $result->fetch_assoc();
    } else {
        echo "<script>alert('Semester not found');</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $semester_id = htmlspecialchars($_POST['semester_ID']);
    $academic_year = htmlspecialchars($_POST['academic_year']);
    $semester_type = htmlspecialchars($_POST['semester_type']);
    
    // Extract the start and end year from academic_year input
    list($start_year, $end_year) = explode('-', $academic_year);

    // Generate the new semester_ID based on the input values
    $generated_semester_id = "AY" . $start_year . "-" . $end_year . "-" . strtolower(str_replace(" ", "", $semester_type));

    if (!empty($semester_id)) {
        // If we're updating the record, use the new generated semester_ID
        $stmt = $db->db->prepare("UPDATE semester SET semester_ID = ?, academic_year = ?, semester_type = ? WHERE semester_ID = ?");
        $stmt->bind_param("ssss", $generated_semester_id, $academic_year, $semester_type, $semester_id);
        if ($stmt->execute()) {
            header("Location: ?content=admin-index&admin=ay-dashboard");
            exit();
        } else {
            echo "<script>alert('Error updating term: " . $stmt->error . "');</script>";
        }
    } else {
        // Insert a new record if no semester_ID is provided
        $stmt = $db->db->prepare("INSERT INTO semester (semester_ID, academic_year, semester_type) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $generated_semester_id, $academic_year, $semester_type);
        if ($stmt->execute()) {
            header("Location: ?content=admin-index&admin=dashboard");
            exit();
        } else {
            echo "<script>alert('Error adding new term: " . $stmt->error . "');</script>";
        }
    }
}

$current_year = date("Y");

// Generate a range of years for the academic year selection
$years = [];
for ($i = $current_year - 1; $i <= $current_year + 5; $i++) {
    $next_year = $i + 1;
    $years[] = "$i-$next_year";
}

ob_end_flush(); // End output buffering
?>

<link rel="stylesheet" href=".//.//stylesheet/admin/ay-dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($semester->num_rows > 0) {
                    while ($row = $semester->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['semester_ID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['academic_year']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['semester_type']) . "</td>";
                        echo "<td>";
                        echo "<a href='?content=admin-index&admin=ay-dashboard&edit_id=" . $row["semester_ID"] . "'><i class='fas fa-edit'></i></a>";
                        echo "<a href='?content=admin-index&admin=ay-dashboard&delete_id=" . $row["semester_ID"] . "' class='delete-btn'><i class='fas fa-trash'></i></a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No semester found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <button id="create-term-btn" onclick="openEnrollForm()">Start a New Term</button>

    </div>
</div>

<!-- Enrollment Form Modal -->
<div id="enrollFormModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEnrollForm()">&times;</span>
        <h2 id="modal-title"><?= isset($editData) ? "Edit Term" : "Add New Term" ?></h2>
        <form class="enrollForm" method="POST" action="">
            <input type="hidden" id="semester_ID" name="semester_ID" value="<?= isset($editData) ? $editData['semester_ID'] : '' ?>">

            <label for="academic_year">Academic Year</label>
            <select id="academic_year" name="academic_year" required>
                <option value="" disabled selected>Select Academic Year</option>
                <?php
                foreach ($years as $year) {
                    $selected = isset($editData) && $editData['academic_year'] == $year ? "selected" : "";
                    echo "<option value=\"$year\" $selected>$year</option>";
                }
                ?>
            </select>

            <label for="semester_type">Term:</label>
            <select id="semester_type" name="semester_type" required>
                <option value="" disabled selected>Select Term</option>
                <option value="1st Semester" <?= isset($editData) && $editData['semester_type'] == '1st Semester' ? 'selected' : '' ?>>1st Semester</option>
                <option value="2nd Semester" <?= isset($editData) && $editData['semester_type'] == '2nd Semester' ? 'selected' : '' ?>>2nd Semester</option>
                <option value="Summer" <?= isset($editData) && $editData['semester_type'] == 'Summer' ? 'selected' : '' ?>>Summer</option>
            </select>
            <input type="submit" value="<?= isset($editData) ? 'Update Term' : 'Add New Term' ?>">
        </form>
    </div>
</div>

<script>
// Modify the modal open function to check if we are editing
function openEnrollForm(edit = false) {
    var modal = document.getElementById("enrollFormModal");
    modal.style.display = "block";

    if (edit) {
        document.getElementById('modal-title').textContent = "Edit Term";
        // Optionally, you can add logic here to load the values if editing
    } else {
        document.getElementById('modal-title').textContent = "Add New Term";
    }
}

// Open the modal for editing specific term
<?php if (isset($editData)) { ?>
    openEnrollForm(true);
<?php } ?>


    function closeEnrollForm() {
        var modal = document.getElementById("enrollFormModal");
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        var modal = document.getElementById("enrollFormModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

// Add confirmation before deleting a student
document.querySelectorAll('.delete-btn').forEach(function(button) {
    button.addEventListener('click', function(e) {
        if (!confirm("Are you sure you want to delete this term?")) {
            e.preventDefault(); // Prevent the link from being followed
        }
    });
});
</script>

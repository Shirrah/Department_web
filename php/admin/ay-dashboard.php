<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ob_start(); // Start output buffering

require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Get the user ID from the session (either admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

// Handle the semester selection from GET request and store it in session for this user
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    // Store the selected semester for the user in session
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
}

// Fetch the semesters from the database
$query = "SELECT semester_ID, academic_year, semester_type, status FROM semester";
$semester = $db->query($query);

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = htmlspecialchars($_GET['delete_id']);
    
    // First, delete students related to the semester
    $deleteStudentsQuery = "DELETE FROM student WHERE semester_ID = ?";
    $stmt = $db->prepare($deleteStudentsQuery);
    $stmt->bind_param("s", $delete_id);
    
    if ($stmt->execute()) {
        // Now, delete the semester
        $deleteQuery = "DELETE FROM semester WHERE semester_ID = ?";
        $stmt = $db->prepare($deleteQuery);
        $stmt->bind_param("s", $delete_id);

        if ($stmt->execute()) {
            header("Location: ?content=admin-index&admin=ay-dashboard");
            exit();
        } else {
            echo "<script>alert('Error deleting term: " . $db->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error deleting students: " . $db->error . "');</script>";
    }
}

// Edit functionality
$editData = null;
if (isset($_GET['edit_id'])) {
    $edit_id = htmlspecialchars($_GET['edit_id']);
    $editQuery = "SELECT * FROM semester WHERE semester_ID = ?";
    $stmt = $db->prepare($editQuery);
    $stmt->bind_param("s", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $editData = $result->fetch_assoc();
    } else {
        echo "<script>alert('Semester not found');</script>";
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
<?php include_once "././php/toast-system.php"; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    // Function to reload the table data
    function reloadTableData() {
        fetch('././php/admin/fetch-semesters.php')
            .then(response => response.text())
            .then(html => {
                $('.semester-table tbody').html(html);
                // Reattach event listeners to the new elements
                attachEventListeners();
            })
            .catch(error => {
                console.error('Error:', error);
                createToast('error', 'Failed to reload data');
            });
    }

    // Function to attach event listeners to table elements
    function attachEventListeners() {
        // Reattach status toggle listeners
        $('.status-toggle').on('change', function () {
            const semesterId = $(this).data('id');
            const status = $(this).is(':checked') ? 'active' : 'inactive';
            const $toggle = $(this);

            fetch('././php/admin/update-semester-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `semester_ID=${semesterId}&status=${status}`
            })
            .then(response => response.text())
            .then(result => {
                if (result.trim() === 'success') {
                    createToast('success', 'Status updated successfully!');
                    reloadTableData(); // Reload the table after successful update
                } else {
                    createToast('error', 'Failed to update status');
                    $toggle.prop('checked', !$toggle.prop('checked')); // Revert the toggle
                }
            })
            .catch(error => {
                console.error('Error:', error);
                createToast('error', 'Something went wrong.');
                $toggle.prop('checked', !$toggle.prop('checked')); // Revert the toggle
            });
        });

        // Reattach edit button listeners
        $('.edit-btn').on('click', function () {
            const id = $(this).data('id');
            const year = $(this).data('year');
            const type = $(this).data('type');

            $('#edit_semester_ID').val(id);
            $('#edit_academic_year').val(year);
            $('#edit_semester_type').val(type);
        });
    }

    // Initial attachment of event listeners
    attachEventListeners();

    // Handle form submission
    $('#addTermForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('././php/admin/handle-term.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            if (result.trim() === 'success') {
                this.reset();
                const modal = bootstrap.Modal.getInstance(document.getElementById('enrollFormModal'));
                modal.hide();
                createToast('success', 'Term added successfully!');
                reloadTableData(); // Reload the table after successful addition
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
    $('#editTermForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('././php/admin/handle-term.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            if (result.trim() === 'success') {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editTermModal'));
                modal.hide();
                createToast('success', 'Term updated successfully!');
                reloadTableData(); // Reload the table after successful update
            } else {
                createToast('error', result);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            createToast('error', 'Something went wrong.');
        });
    });

    // Handle deletion confirmation
    const deleteModal = document.getElementById('deleteConfirmationModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const semesterID = button.getAttribute('data-id');
        const confirmBtn = deleteModal.querySelector('#deleteConfirmBtn');
        confirmBtn.setAttribute('href', '?content=admin-index&admin=ay-dashboard&delete_id=' + semesterID);
    });
});
</script>

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
                    <th>Status</th>
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
            </tbody>
        </table>
        <button id="create-term-btn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollFormModal">
            <i class="fas fa-plus"></i> Start a New Term
        </button>
    </div>
</div>

<!-- Add New Term Modal -->
<div class="modal fade" id="enrollFormModal" tabindex="-1" aria-labelledby="enrollFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enrollFormModalLabel">Add New Term</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="enrollForm" id="addTermForm">
                    <div class="mb-3">
                        <label for="academic_year" class="form-label">Academic Year</label>
                        <select class="form-select" id="academic_year" name="academic_year" required>
                            <option value="" disabled selected>Select Academic Year</option>
                            <?php
                            foreach ($years as $year) {
                                echo "<option value=\"$year\">$year</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="semester_type" class="form-label">Term:</label>
                        <select class="form-select" id="semester_type" name="semester_type" required>
                            <option value="" disabled selected>Select Term</option>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add New Term</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Term Modal -->
<div class="modal fade" id="editTermModal" tabindex="-1" aria-labelledby="editTermModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTermModalLabel">Edit Term</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTermForm">
                    <input type="hidden" name="semester_ID" id="edit_semester_ID">
                    
                    <div class="mb-3">
                        <label for="edit_academic_year" class="form-label">Academic Year</label>
                        <select class="form-select" name="academic_year" id="edit_academic_year" required>
                            <option value="" disabled>Select Academic Year</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?= $year ?>"><?= $year ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_semester_type" class="form-label">Term</label>
                        <select class="form-select" name="semester_type" id="edit_semester_type" required>
                            <option value="" disabled>Select Term</option>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Term</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this semester and its associated students? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="" id="deleteConfirmBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>
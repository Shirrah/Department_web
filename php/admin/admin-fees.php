<link rel="stylesheet" href=".//.//stylesheet/admin/admin-fees.css">
<div class="fee-management-con">

    <div class="fee-management-header">
        <span>Manage Fees</span>
        <div class="location">
            <a href="?content=admin-index&admin=dashboard">Dashboard</a>
            /
            <span>Events & Fees</span>
            /
            <span>Manage Events</span>
        </div>
    </div>

    <div class="list-fees-con">

    <?php
    // Start the session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Include the database connection
    require_once "././php/db-conn.php";
    $db = new Database();

    // Get the user ID from the session (either admin or student)
    $user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

    // Handle the semester selection from GET request and store it in session for this user
    if (isset($_GET['semester']) && !empty($_GET['semester'])) {
        // Store the selected semester for the user in session
        $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
    }

    // Retrieve the selected semester from session or set to an empty string if not available
    $selected_semester = $_SESSION['selected_semester'][$user_id] ?? '';
// Handle fee creation
if (isset($_POST['create_fee'])) {
    $payment_name = $_POST['payment_name'];
    $payment_amount = $_POST['payment_amount'];

    // Insert into the payments table
    $stmt = $db->db->prepare("INSERT INTO payments (payment_name, payment_amount, date_payment, semester_ID) VALUES (?, ?, NOW(), ?)");
    $stmt->bind_param("sss", $payment_name, $payment_amount, $selected_semester);

    if ($stmt->execute()) {
        // Get the last inserted payment ID and the date_payment from the payments table
        $payment_id = $stmt->insert_id;

        // Fetch the date_payment from the payments table for the inserted record
        $paymentDateQuery = "SELECT date_payment FROM payments WHERE id_payment = ?";
        $stmt = $db->db->prepare($paymentDateQuery);
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $paymentDateRow = $result->fetch_assoc();
        $date_payment = $paymentDateRow['date_payment'];  // This will get the `date_payment` from the `payments` table

        // Fetch all students for the selected semester
        $studentsQuery = "SELECT id_student FROM student WHERE semester_ID = ?";
        $stmt = $db->db->prepare($studentsQuery);
        $stmt->bind_param("s", $selected_semester);
        $stmt->execute();
        $studentsResult = $stmt->get_result();

        // Insert a record in the student_fees_record table for each student
        while ($student = $studentsResult->fetch_assoc()) {
            $student_id = $student['id_student'];

            // Insert into student_fees_record with the payment_date from the payments table
            $insertFeeStmt = $db->db->prepare("INSERT INTO student_fees_record (id_student, semester_ID, id_payment, status_payment, payment_amount, date_payment) VALUES (?, ?, ?, 0, ?, ?)");
            $insertFeeStmt->bind_param("ssids", $student_id, $selected_semester, $payment_id, $payment_amount, $date_payment);
            $insertFeeStmt->execute();
        }

        echo "<script>window.location.href='';</script>";
    } else {
        $error = "Error creating fee: " . $stmt->error;
    }
}


    // Handle fee deletion
    if (isset($_POST['delete_fee'])) {
        $id_payment = $_POST['id_payment'];

        // Delete the fee from the database
        $stmt = $db->db->prepare("DELETE FROM payments WHERE id_payment = ?");
        $stmt->bind_param("i", $id_payment);

        if ($stmt->execute()) {
            echo "<script>window.location.href='';</script>";
        } else {
            $error = "Error deleting fee: " . $stmt->error;
        }
    }

// Handle fee editing
if (isset($_POST['edit_fee'])) {
    $id_payment = $_POST['id_payment'];
    $payment_name = $_POST['payment_name'];
    $payment_amount = $_POST['payment_amount'];
    $date_payment = $_POST['date_payment'];

    // Update the fee in the payments table
    $stmt = $db->db->prepare("UPDATE payments SET payment_name = ?, payment_amount = ?, date_payment = ? WHERE id_payment = ?");
    $stmt->bind_param("sdsi", $payment_name, $payment_amount, $date_payment, $id_payment);

    if ($stmt->execute()) {
        // Update the student_fees_record table with the new payment_amount
        $stmt2 = $db->db->prepare("UPDATE student_fees_record SET payment_amount = ? WHERE id_payment = ?");
        $stmt2->bind_param("di", $payment_amount, $id_payment);

        if ($stmt2->execute()) {
            echo "<script>window.location.href='';</script>";
        } else {
            $error = "Error updating student fees record: " . $stmt2->error;
        }
    } else {
        $error = "Error editing fee: " . $stmt->error;
    }
}


    // Initialize pagination variables
    $limit = 7; // Records per page
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : (isset($_SESSION['page']) ? $_SESSION['page'] : 1); // Default to page 1 if not set
    $_SESSION['page'] = $page; // Store page number in session
    $offset = ($page - 1) * $limit;

    // Fetch total records and calculate total pages for the selected semester
    $countQuery = "SELECT COUNT(*) as total FROM payments WHERE semester_ID = ?";
    $stmt = $db->db->prepare($countQuery);
    $stmt->bind_param("s", $selected_semester);
    $stmt->execute();
    $totalResult = $stmt->get_result();
    $row = $totalResult->fetch_assoc();
    $totalRecords = $row ? (int)$row['total'] : 0; // Ensure totalRecords is assigned
    $totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;

    // Fetch payments for the current page for the selected semester
    if (isset($_GET['show_all']) && $_GET['show_all'] == 'true') {
        // Query to fetch all payments for the selected semester (no pagination)
        $query = "SELECT id_payment, payment_name, payment_amount, date_payment FROM payments WHERE semester_ID = ?";
        $stmt = $db->db->prepare($query);
        $stmt->bind_param("s", $selected_semester);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalPages = 1; // Only one page when showing all records
        $page = 1; // Reset to the first page
    } else {
        // Query to fetch paginated payments for the selected semester
        $query = "SELECT id_payment, payment_name, payment_amount, date_payment 
                  FROM payments WHERE semester_ID = ? LIMIT $limit OFFSET $offset";
        $stmt = $db->db->prepare($query);
        $stmt->bind_param("s", $selected_semester);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    // Display payments in a table
    if ($result && mysqli_num_rows($result) > 0) {
        echo '
        <table class="fees-table">
            <thead>
                <tr>
                    <th>Fee Name</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            $id_payment = $row['id_payment'];
            $payment_name = $row['payment_name'];
            $payment_amount = $row['payment_amount'];
            $date_payment = date('F d, Y', strtotime($row['date_payment']));
            echo '
            <tr>
                <td>' . htmlspecialchars($payment_name) . '</td>
                <td>' . $date_payment . '</td>
                <td>PHP ' . number_format($payment_amount, 2) . '</td>
                <td>
                    <a href="#" onclick="openEditModal(' . $id_payment . ', \'' . htmlspecialchars($payment_name) . '\', ' . $payment_amount . ', \'' . $row['date_payment'] . '\')">Edit</a> |
                    <a href="#" onclick="confirmDeleteFee(' . $id_payment . ')">Delete</a> |
                    <a href="?content=admin-index&admin=fee-records&payment_id=' . $id_payment . '">Show records</a>

                    

                </td>
            </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No fees found.</p>';
    }
    ?>

    </div>

    <div class="admin-fees-action">
        <button id="createFeeBtn">Create fee</button>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <button <?php if($page <= 1) echo 'disabled'; ?> onclick="navigateToPage(1)">First</button>
        <button <?php if($page <= 1) echo 'disabled'; ?> onclick="navigateToPage(<?php echo $page - 1; ?>)">Previous</button>
        <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
        <button <?php if($page >= $totalPages) echo 'disabled'; ?> onclick="navigateToPage(<?php echo $page + 1; ?>)">Next</button>
        <button <?php if($page >= $totalPages) echo 'disabled'; ?> onclick="navigateToPage(<?php echo $totalPages; ?>)">Last</button>
    </div>

    <script>
        function navigateToPage(page) {
            window.location.href = '?content=admin-index&admin=event-management&admin_events=admin-fees&page=' + page;
        }
    </script>
</div>

<form id="delete-fee-form" method="POST" action="">
    <input type="hidden" name="id_payment" id="delete-fee-id">
    <button type="submit" name="delete_fee" style="display: none;"></button>
</form>
<!-- Modal for Create Fee -->
<div id="createFeeModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Create Fee</h3>
        <form action="" method="POST">
            <label for="payment_name">Fee Name:</label>
            <input type="text" id="payment_name" name="payment_name" required>

            <label for="payment_amount">Amount (PHP):</label>
            <input type="number" id="payment_amount" name="payment_amount" step="0.01" required>

            <label for="date_payment">Date:</label>
            <input type="date" id="date_payment" name="date_payment" required>

            <button type="submit" name="create_fee">Create Fee</button>
        </form>
    </div>
</div>

<!-- Modal for Edit Fee -->
<div id="editFeeModal" class="modal">
    <div class="modal-content">
        <span class="close-edit">&times;</span>
        <h3>Edit Fee</h3>
        <form action="" method="POST">
            <input type="hidden" id="edit_fee_id" name="id_payment">

            <label for="edit_payment_name">Fee Name:</label>
            <input type="text" id="edit_payment_name" name="payment_name" required>

            <label for="edit_payment_amount">Amount (PHP):</label>
            <input type="number" id="edit_payment_amount" name="payment_amount" step="0.01" required>

            <label for="edit_date_payment">Date:</label>
            <input type="date" id="edit_date_payment" name="date_payment" required>

            <button type="submit" name="edit_fee">Save Changes</button>
        </form>
    </div>
</div>

<script>
// Get the modals and buttons
var createModal = document.getElementById('createFeeModal');
var editModal = document.getElementById('editFeeModal');
var createBtn = document.getElementById('createFeeBtn');
var closeCreate = document.getElementsByClassName('close')[0];
var closeEdit = document.getElementsByClassName('close-edit')[0];

// Open the create fee modal
createBtn.onclick = function() {
    createModal.style.display = 'block';
}

// Close the create fee modal
closeCreate.onclick = function() {
    createModal.style.display = 'none';
}

// Close the edit fee modal
closeEdit.onclick = function() {
    editModal.style.display = 'none';
}

// Close modals when clicking outside of them
window.onclick = function(event) {
    if (event.target == createModal) {
        createModal.style.display = 'none';
    } else if (event.target == editModal) {
        editModal.style.display = 'none';
    }
}

// Open the edit fee modal and populate it with the current fee data
function openEditModal(id_payment, payment_name, payment_amount, date_payment) {
    document.getElementById('edit_fee_id').value = id_payment;
    document.getElementById('edit_payment_name').value = payment_name;
    document.getElementById('edit_payment_amount').value = payment_amount;
    document.getElementById('edit_date_payment').value = date_payment;
    editModal.style.display = 'block';
}

// Delete fee confirmation
function confirmDeleteFee(id_payment) {
    if (confirm('Are you sure you want to delete this fee?')) {
        document.getElementById('delete-fee-id').value = id_payment;
        document.querySelector('#delete-fee-form button').click();
    }
}
</script>

<style>
/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.close, .close-edit {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover, .close-edit:hover {
    color: black;
    cursor: pointer;
}

form input,
form textarea,
form button {
    display: block;
    width: 100%;
    margin: 10px 0;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

form button {
    background-color: #4CAF50;
    color: white;
    font-size: 16px;
    cursor: pointer;
}

form button:hover {
    background-color: #45a049;
}
</style>

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
    // Start the session
    $error = '';
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Include the database connection
    require_once "././php/db-conn.php";
    $db = new Database();

    // Handle fee creation
    if (isset($_POST['create_fee'])) {
        $payment_name = $_POST['payment_name'];
        $payment_amount = $_POST['payment_amount'];
        $date_payment = $_POST['date_payment'];

        // Insert into the payments table
        $stmt = $db->db->prepare("INSERT INTO payments (payment_name, payment_amount, date_payment) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $payment_name, $payment_amount, $date_payment);

        if ($stmt->execute()) {
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

        // Update the fee in the database
        $stmt = $db->db->prepare("UPDATE payments SET payment_name = ?, payment_amount = ?, date_payment = ? WHERE id_payment = ?");
        $stmt->bind_param("sdsi", $payment_name, $payment_amount, $date_payment, $id_payment);

        if ($stmt->execute()) {
            echo "<script>window.location.href='';</script>";
        } else {
            $error = "Error editing fee: " . $stmt->error;
        }
    }

    // Fetch payments from the payments table
    $query = "SELECT id_payment, payment_name, payment_amount, date_payment FROM payments";
    $result = $db->db->query($query);


    // Initialize pagination variables
$limit = 7; // Records per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Current page, default to 1
$offset = ($page - 1) * $limit;

// Fetch total records and calculate total pages
$countQuery = "SELECT COUNT(*) as total FROM payments";
$totalResult = $db->db->query($countQuery);
$totalRecords = ($totalResult && $row = $totalResult->fetch_assoc()) ? (int)$row['total'] : 0;
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;

// Fetch records for the current page
$query = "SELECT id_payment, payment_name, payment_amount, date_payment FROM payments LIMIT $limit OFFSET $offset";
$result = $db->db->query($query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $id_payment = $row['id_payment'];
            $payment_name = $row['payment_name'];
            $payment_amount = $row['payment_amount'];
            $date_payment = date('F d, Y', strtotime($row['date_payment']));

            echo '
            <div class="fee">
                <div class="name-date">
                    <h4>' . htmlspecialchars($payment_name) . '</h4>
                    <p>' . $date_payment . '</p>
                </div>
                <div class="date-created">
                    <p>Amount: PHP ' . number_format($payment_amount, 2) . '</p>
                </div>
                <div class="action-btn">
                    <a href="#" onclick="openEditModal(' . $id_payment . ', \'' . htmlspecialchars($payment_name) . '\', ' . $payment_amount . ', \'' . $row['date_payment'] . '\')"><img src=".//.//assets/images/edit.png" alt=""></a>
                    | |
                    <a href="#" onclick="confirmDeleteFee(' . $id_payment . ')"><img src=".//.//assets/images/delete.png" alt=""></a>
                </div>
            </div>';
        }
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
    background-color: #f94316;
    color: white;
    border: none;
    cursor: pointer;
}

form button:hover {
    background-color: #d8360d;
}
</style>

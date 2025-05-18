<link rel="stylesheet" href=".//.//stylesheet/admin/admin-fees.css">

<div class="fee-management-con">

    <div class="fee-management-header">
        <span>Manage Fees</span>
        <div class="location">
            <a href="?content=admin-index&admin=dashboard">Dashboard</a>
            /
            <span>Events & Fees</span>
            /
            <span>Manage Fees</span>
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
    $db = Database::getInstance()->db;

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
        $stmt = $db->prepare("INSERT INTO payments (payment_name, payment_amount, date_payment, semester_ID) VALUES (?, ?, NOW(), ?)");
        $stmt->bind_param("sss", $payment_name, $payment_amount, $selected_semester);

        if ($stmt->execute()) {
            // Get the last inserted payment ID and the date_payment from the payments table
            $payment_id = $stmt->insert_id;

            // Fetch the date_payment from the payments table for the inserted record
            $paymentDateQuery = "SELECT date_payment FROM payments WHERE id_payment = ?";
            $stmt = $db->prepare($paymentDateQuery);
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $paymentDateRow = $result->fetch_assoc();
            $date_payment = $paymentDateRow['date_payment'];  // This will get the `date_payment` from the `payments` table

            // Fetch all students for the selected semester
            $studentsQuery = "SELECT id_student FROM student WHERE semester_ID = ?";
            $stmt = $db->prepare($studentsQuery);
            $stmt->bind_param("s", $selected_semester);
            $stmt->execute();
            $studentsResult = $stmt->get_result();

            // Insert a record in the student_fees_record table for each student
            while ($student = $studentsResult->fetch_assoc()) {
                $student_id = $student['id_student'];

                // Insert into student_fees_record with the payment_date from the payments table
                $insertFeeStmt = $db->prepare("INSERT INTO student_fees_record (id_student, semester_ID, id_payment, status_payment, payment_amount, date_payment) VALUES (?, ?, ?, 0, ?, ?)");
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

        // Start a transaction
        $db->begin_transaction();

        try {
            // First delete the associated records from student_fees_record
            $stmt = $db->prepare("DELETE FROM student_fees_record WHERE id_payment = ?");
            $stmt->bind_param("i", $id_payment);
            $stmt->execute();

            // Then delete the fee from the payments table
            $stmt = $db->prepare("DELETE FROM payments WHERE id_payment = ?");
            $stmt->bind_param("i", $id_payment);
            $stmt->execute();

            // If both operations succeed, commit the transaction
            $db->commit();
            echo "<script>window.location.href='';</script>";
        } catch (Exception $e) {
            // If any operation fails, rollback the transaction
            $db->rollback();
            $error = "Error deleting fee: " . $e->getMessage();
        }
    }

    // Handle fee editing
    if (isset($_POST['edit_fee'])) {
        $id_payment = $_POST['id_payment'];
        $payment_name = $_POST['payment_name'];
        $payment_amount = $_POST['payment_amount'];
        $date_payment = $_POST['date_payment'];

        // Update the fee in the payments table
        $stmt = $db->prepare("UPDATE payments SET payment_name = ?, payment_amount = ?, date_payment = ? WHERE id_payment = ?");
        $stmt->bind_param("sdsi", $payment_name, $payment_amount, $date_payment, $id_payment);

        if ($stmt->execute()) {
            // Update the student_fees_record table with the new payment_amount
            $stmt2 = $db->prepare("UPDATE student_fees_record SET payment_amount = ? WHERE id_payment = ?");
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

    // Fetch payments for the selected semester
    $query = "SELECT id_payment, payment_name, payment_amount, date_payment 
              FROM payments WHERE semester_ID = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $selected_semester);
    $stmt->execute();
    $result = $stmt->get_result();

    // Display payments in a table
    if ($result && mysqli_num_rows($result) > 0) {
        echo '
        <table class="fees-table align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>Fee Name</th>
                    <th>Due Date</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            $id_payment = (int)$row['id_payment'];
            $payment_name = htmlspecialchars($row['payment_name'], ENT_QUOTES);
            $payment_amount = (float)$row['payment_amount'];
            $raw_date = $row['date_payment'];
            $date_payment = date('F d, Y', strtotime($raw_date));

            echo '
            <tr>
                <td class="fw-semibold">' . $payment_name . '</td>
                <td>' . htmlspecialchars($date_payment, ENT_QUOTES) . '</td>
                <td><span class="badge bg-success fs-6">PHP ' . number_format($payment_amount, 2) . '</span></td>
                <td>
                    <div class="d-flex justify-content-center flex-wrap gap-2">
                        <button class="btn btn-primary btn-sm" 
                            onclick="openEditModal(' . $id_payment . ', 
                                \'' . addslashes($payment_name) . '\', 
                                ' . $payment_amount . ', 
                                \'' . addslashes($raw_date) . '\')">
                            <i class="fas fa-edit me-1"></i> Edit
                        </button>

                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteFee(' . $id_payment . ')">
                            <i class="fas fa-trash me-1"></i> Delete
                        </button>

                        <button class="btn btn-info btn-sm" 
                            data-bs-toggle="modal" 
                            data-bs-target="#fullScreenModal" 
                            onclick="loadPaymentRecords(' . $id_payment . ', \'' . addslashes($payment_name) . '\', ' . $payment_amount . ')">
                            <i class="fas fa-database me-1"></i> Show Records
                        </button>
                    </div>
                </td>
            </tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-light text-dark text-center" role="alert">No fees found.</div>';
    }
    ?>

    <div class="admin-fees-action">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createFeeModal">
            Create Fee
        </button>
    </div>
</div>

<!-- Full-Screen Modal -->
<div class="modal fade" id="fullScreenModal" tabindex="-1" aria-labelledby="fullScreenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullScreenModalLabel">Payment Records</h5>
                <button type="button" class="btn btn-success ms-1" onclick="exportToCSV()">Export to CSV</button>
                <button type="button" class="btn btn-primary ms-1" id="fetchRecordsBtn">Fetch Records</button>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search and Filter Inputs -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by ID, name, year, or status...">
                    </div>
                    <div class="col-md-4">
                        <select id="statusFilter" class="form-select" onchange="filterRecords()">
                            <option value="">All Status</option>
                            <option value="1">Paid</option>
                            <option value="0">Unpaid</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <div id="modal-body-content">
                        <div class="text-center">Loading records...</div>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <span id="pagination-info">Showing 0-0 of 0 records</span>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <li class="page-item">
                                <button class="page-link" onclick="changePage('<<')"><<</button>
                            </li>
                            <li class="page-item">
                                <button class="page-link" id="prev-page" onclick="changePage('<')"><</button>
                            </li>
                            <li class="page-item">
                                <span class="page-link" id="current-page">Page 1</span>
                            </li>
                            <li class="page-item">
                                <button class="page-link" id="next-page" onclick="changePage('>')">></button>
                            </li>
                            <li class="page-item">
                                <button class="page-link" onclick="changePage('>>')">>></button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Fee Modal -->
<div class="modal fade" id="createFeeModal" tabindex="-1" aria-labelledby="createFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createFeeModalLabel">Create Fee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="payment_name" class="form-label">Fee Name</label>
                        <input type="text" class="form-control" id="payment_name" name="payment_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">Amount (PHP)</label>
                        <input type="number" class="form-control" id="payment_amount" name="payment_amount" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label for="date_payment" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="date_payment" name="date_payment" required>
                    </div>

                    <button type="submit" name="create_fee" class="btn btn-primary">Create Fee</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Fee Modal -->
<div class="modal fade" id="editFeeModal" tabindex="-1" aria-labelledby="editFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFeeModalLabel">Edit Fee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" id="edit_fee_id" name="id_payment">

                    <div class="mb-3">
                        <label for="edit_payment_name" class="form-label">Fee Name</label>
                        <input type="text" class="form-control" id="edit_payment_name" name="payment_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_payment_amount" class="form-label">Amount (PHP)</label>
                        <input type="number" class="form-control" id="edit_payment_amount" name="payment_amount" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_date_payment" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="edit_date_payment" name="date_payment" required>
                    </div>

                    <button type="submit" name="edit_fee" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<form id="delete-fee-form" method="POST" action="">
    <input type="hidden" name="id_payment" id="delete-fee-id">
    <button type="submit" name="delete_fee" style="display: none;"></button>
</form>

<script>
let currentPage = 1;
let totalPages = 1;
let recordsPerPage = 10;
let currentPaymentId = null;
let currentPaymentName = null;
let currentPaymentAmount = null;
let currentSearch = '';

function loadPaymentRecords(paymentId, paymentName, paymentAmount, page = 1, search = '', status = '') {
    currentPage = page;
    currentPaymentId = paymentId;
    currentPaymentName = paymentName;
    currentPaymentAmount = paymentAmount;
    currentSearch = search;
    
    document.getElementById('modal-body-content').innerHTML = '<div class="text-center">Loading records...</div>';
    document.getElementById('fullScreenModalLabel').innerText = paymentName + ' (PHP ' + paymentAmount.toFixed(2) + ') Records';

    // Add search and status parameters to the fetch URL
    const searchParam = search ? '&search=' + encodeURIComponent(search) : '';
    const statusParam = status ? '&status=' + encodeURIComponent(status) : '';
    
    fetch('././php/admin/fetch-payment-records.php?payment_id=' + paymentId + '&page=' + page + '&per_page=' + recordsPerPage + searchParam + statusParam)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modal-body-content').innerHTML = data.html;
                totalPages = Math.ceil(data.total_records / recordsPerPage);
                updatePaginationControls();
                updatePaginationInfo(data.total_records, data.start_record, data.end_record);
            } else {
                document.getElementById('modal-body-content').innerHTML = '<div class="text-center text-danger">Error loading records</div>';
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            document.getElementById('modal-body-content').innerHTML = '<div class="text-center text-danger">Error loading records</div>';
        });
}

function updatePaginationControls() {
    const prevButton = document.getElementById('prev-page');
    const nextButton = document.getElementById('next-page');
    const currentPageSpan = document.getElementById('current-page');

    // Update current page display
    currentPageSpan.textContent = `Page ${currentPage}`;

    // Update button states
    prevButton.disabled = currentPage <= 1;
    nextButton.disabled = currentPage >= totalPages;

    // Add/remove disabled class for styling
    prevButton.classList.toggle('disabled', currentPage <= 1);
    nextButton.classList.toggle('disabled', currentPage >= totalPages);
}

function updatePaginationInfo(totalRecords, startRecord, endRecord) {
    const paginationInfo = document.getElementById('pagination-info');
    paginationInfo.textContent = `Showing ${startRecord}-${endRecord} of ${totalRecords} records`;
}

function changePage(delta) {
    let newPage;
    if (delta === '<<') {
        // First page
        newPage = 1;
    } else if (delta === '>>') {
        // Last page
        newPage = totalPages;
    } else if (delta === '<') {
        // Previous page
        newPage = currentPage - 1;
    } else if (delta === '>') {
        // Next page
        newPage = currentPage + 1;
    } else {
        // Direct page number
        newPage = delta;
    }
    
    if (newPage >= 1 && newPage <= totalPages) {
        loadPaymentRecords(currentPaymentId, currentPaymentName, currentPaymentAmount, newPage, currentSearch, currentPaymentAmount);
    }
}

// Update the filterRecords function to include status filter
let searchTimeout;
function filterRecords() {
    const searchInput = document.getElementById("searchInput");
    const statusFilter = document.getElementById("statusFilter");
    const searchTerm = searchInput.value.trim();
    const statusValue = statusFilter.value;
    
    // Clear previous timeout
    clearTimeout(searchTimeout);
    
    // Set new timeout to prevent too many requests
    searchTimeout = setTimeout(() => {
        // Reset to first page when filtering
        loadPaymentRecords(currentPaymentId, currentPaymentName, currentPaymentAmount, 1, searchTerm, statusValue);
    }, 300); // 300ms delay
}

// Export to CSV functionality
function exportToCSV() {
    const table = document.querySelector("#modal-body-content").closest("table");
    const rows = Array.from(table.querySelectorAll("tr:not(.collapse)"));

    if (rows.length <= 1) {
        alert("No records available to export.");
        return;
    }

    let csvContent = "";
    const headers = ["ID", "Lastname", "Firstname", "Year Level", "Payment Status"];
    csvContent += headers.map(h => `"${h}"`).join(",") + "\r\n";

    rows.forEach(row => {
        const cells = row.querySelectorAll("td");
        if (cells.length > 0) {
            const rowData = Array.from(cells)
                .slice(0, 5) // Only take the first 5 columns
                .map(cell => {
                    // For the status column (5th column), clean up the text
                    if (cell.querySelector('.status-text')) {
                        const statusText = cell.querySelector('.status-text').innerText;
                        // Remove any icons and return just "Paid" or "Unpaid"
                        return `"${statusText.replace(/[✔✖]/g, '').trim()}"`;
                    }
                    // For other columns, just remove HTML tags and escape quotes
                    const text = cell.innerText.replace(/<[^>]*>/g, '').replace(/"/g, '""');
                    return `"${text}"`;
                });
            csvContent += rowData.join(",") + "\r\n";
        }
    });

    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `${currentPaymentName}_records.csv`);
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Add event listeners for both search and status filter
document.getElementById('searchInput').addEventListener('keyup', filterRecords);
document.getElementById('statusFilter').addEventListener('change', filterRecords);
</script>

<script>
// SEARCH FUNCTION
function filterRecords() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("#paymentRecordsTable tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

// SORT FUNCTION
let sortDirection = {}; // Store sort direction for each column
let activeColumn = -1; // Track which column is being sorted

function sortTable(columnIndex) {
    let table = document.getElementById("paymentRecordsTable");
    let tbody = table.querySelector("tbody");
    let rows = Array.from(tbody.rows);

    // Reset previous column arrow to default if a new column is sorted
    if (activeColumn !== columnIndex) {
        document.querySelectorAll(".sort-icon").forEach(icon => icon.innerHTML = "⇅");
        activeColumn = columnIndex;
    }

    // Toggle sort direction (asc/desc)
    sortDirection[columnIndex] = !sortDirection[columnIndex];

    // Sort the rows based on the selected column
    rows.sort((rowA, rowB) => {
        let cellA = rowA.cells[columnIndex].innerText.trim();
        let cellB = rowB.cells[columnIndex].innerText.trim();

        // Convert numeric values for proper sorting
        if (!isNaN(cellA) && !isNaN(cellB)) {
            cellA = parseFloat(cellA.replace(/[^0-9.]/g, ''));
            cellB = parseFloat(cellB.replace(/[^0-9.]/g, ''));
        } else {
            cellA = cellA.toLowerCase();
            cellB = cellB.toLowerCase();
        }

        return sortDirection[columnIndex] ? cellA.localeCompare(cellB, undefined, { numeric: true }) : cellB.localeCompare(cellA, undefined, { numeric: true });
    });

    // Append sorted rows back into the table
    tbody.innerHTML = "";
    rows.forEach(row => tbody.appendChild(row));

    // Update arrow indicator
    let sortIcon = document.querySelector(`.sort-icon[data-column="${columnIndex}"]`);
    sortIcon.innerHTML = sortDirection[columnIndex] ? "⬆" : "⬇";
}

</script>

<script>
// Ensure the DOM is fully loaded before executing scripts
document.addEventListener("DOMContentLoaded", function () {
    // Open the edit fee modal and populate it with the current fee data
    window.openEditModal = function (id_payment, payment_name, payment_amount, date_payment) {
        document.getElementById("edit_fee_id").value = id_payment;
        document.getElementById("edit_payment_name").value = payment_name;
        document.getElementById("edit_payment_amount").value = payment_amount;
        document.getElementById("edit_date_payment").value = date_payment;

        // Show the Bootstrap modal
        let editModal = new bootstrap.Modal(document.getElementById("editFeeModal"));
        editModal.show();
    };

    // Delete fee confirmation
    window.confirmDeleteFee = function (id_payment) {
        if (confirm("Are you sure you want to delete this fee?")) {
            document.getElementById("delete-fee-id").value = id_payment;
            document.querySelector("#delete-fee-form button").click();
        }
    };
});

</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (event) {
        if (event.target.classList.contains('update-status')) {
            let selectedOption = event.target;
            let studentId = selectedOption.getAttribute('data-student-id');
            let paymentId = selectedOption.getAttribute('data-payment-id');
            let newStatus = selectedOption.getAttribute('data-value');

            let dropdownButton = selectedOption.closest('.dropdown').querySelector('.btn');
            let statusTextSpan = dropdownButton.querySelector('.status-text');

            // Save original text and set loading state
            let originalText = statusTextSpan.innerHTML;
            statusTextSpan.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Updating...`;
            dropdownButton.disabled = true; // Disable button interaction

            fetch('././php/admin/update-payment-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `student_id=${studentId}&payment_id=${paymentId}&status_payment=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                console.log("Response:", data);
                if (data.success) {
                    // Remove previous styles
                    dropdownButton.classList.remove('btn-success', 'btn-danger');

                    // Update button appearance based on new status
                    if (newStatus == '1') {
                        dropdownButton.classList.add('btn-success');
                        statusTextSpan.innerHTML = "✔ Paid";
                    } else {
                        dropdownButton.classList.add('btn-danger');
                        statusTextSpan.innerHTML = "✖ Unpaid";
                    }
                } else {
                    statusTextSpan.innerHTML = originalText; // Restore text on failure
                    console.error('Error updating status:', data.error);
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                statusTextSpan.innerHTML = originalText; // Restore text on failure
            })
            .finally(() => {
                dropdownButton.disabled = false; // Re-enable button
            });
        }
    });
});

</script>

<script>
console.log("JavaScript is loaded and running!");
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Add event listener for the Fetch Records button
    document.getElementById('fetchRecordsBtn').addEventListener('click', function() {
        if (!currentPaymentId) {
            alert('Please select a payment first');
            return;
        }

        // Show loading state
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Fetching...';

        // Make the AJAX request
        fetch('././php/admin/fetch-students-not-in-payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id_payment=' + currentPaymentId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Reload the current page of records to show the new entries
                loadPaymentRecords(currentPaymentId, currentPaymentName, currentPaymentAmount, currentPage, currentSearch);
            } else {
                alert(data.error || 'An error occurred while fetching records');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching records');
        })
        .finally(() => {
            // Reset button state
            this.disabled = false;
            this.innerHTML = 'Fetch Records';
        });
    });
});
</script>



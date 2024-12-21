<link rel="stylesheet" href=".//.//stylesheet/admin/show-fee-records.css">
<div class="fee-management-list-con">
    <div class="title">
        <button class="back-button" onclick="window.location.href='?content=admin-index&admin=event-management&admin_events=admin-fees'">Back</button>
        
    </div>

    <div class="fee-list-con">
<?php

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
require_once "././php/db-conn.php";
$db = new Database();

// Get the payment_id from the URL
$payment_id = $_GET['payment_id'] ?? null;

if ($payment_id) {
    // Query to fetch student fee records associated with the selected payment
    $query_fees = "SELECT sfr.id_payment, sfr.id_student, sfr.semester_ID, sfr.status_payment, sfr.date_payment, sfr.payment_amount
                   FROM student_fees_record sfr
                   WHERE sfr.id_payment = ?";

    $stmt_fees = $db->db->prepare($query_fees);
    $stmt_fees->bind_param("i", $payment_id);
    $stmt_fees->execute();
    $result_fees = $stmt_fees->get_result();

    if ($result_fees && mysqli_num_rows($result_fees) > 0) {
        // Display the header for the records
        echo '
        <h3>Student Records for Payment ID: ' . htmlspecialchars($payment_id) . '</h3>
        <table class="student-records-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Amount Paid</th>
                </tr>
            </thead>
            <tbody>';

        // Iterate through the student fee records
        while ($row_fees = mysqli_fetch_assoc($result_fees)) {
            // Query to fetch student details for the current student ID
            $query_student = "SELECT firstname_student, lastname_student
                              FROM student
                              WHERE id_student = ?";

            $stmt_student = $db->db->prepare($query_student);
            $stmt_student->bind_param("i", $row_fees['id_student']);
            $stmt_student->execute();
            $result_student = $stmt_student->get_result();

            if ($result_student && mysqli_num_rows($result_student) > 0) {
                // Fetch student name from the student table
                $row_student = mysqli_fetch_assoc($result_student);
                $student_name = $row_student['firstname_student'] . ' ' . $row_student['lastname_student']; // Full name
            } else {
                $student_name = 'Unknown'; // If no student found
            }

            // Prepare other details for display
            $status = $row_fees['status_payment'] == 0 ? 'Unpaid' : 'Paid';
            $date_payment = date('F d, Y', strtotime($row_fees['date_payment']));
            $payment_amount = number_format($row_fees['payment_amount'], 2);

            echo "
            <tr>
                <td>" . htmlspecialchars($student_name) . "</td>
                <td>" . htmlspecialchars($row_fees['id_student']) . "</td>
                        <td>
            <form method='POST' action='php/admin/update-status.php'>
                <select name='status_payment' onchange='this.form.submit()'>
                    <option value='0' " . ($row_fees['status_payment'] == 0 ? 'selected' : '') . ">Unpaid</option>
                    <option value='1' " . ($row_fees['status_payment'] == 1 ? 'selected' : '') . ">Paid</option>
                    <option value='2' " . ($row_fees['status_payment'] == 2 ? 'selected' : '') . "></option>
                </select>
                <input type='hidden' name='payment_id' value='" . htmlspecialchars($row_fees['id_payment']) . "'>
                <input type='hidden' name='student_id' value='" . htmlspecialchars($row_fees['id_student']) . "'>
            </form>
        </td>
                <td>" . $date_payment . "</td>
                <td>PHP " . $payment_amount . "</td>
            </tr>";
        }

        echo '</tbody></table>';
    } else {
        echo '<p>No student records found for this payment.</p>';
    }
} else {
    echo '<p>No payment selected.</p>';
}
?>
</div>
</div>
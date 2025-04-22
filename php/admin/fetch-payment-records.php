<?php
require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];

    // Get selected semester from session (assuming it's stored in session)
    session_start();
    $selected_semester = $_SESSION['selected_semester'][$_SESSION['user_data']['id_admin'] ?? null] ?? null;
    if (!$selected_semester) {
        die("No semester selected in session");
    }

    // Fetch student fee records associated with the selected payment and semester
    $stmt = $db->prepare("
        SELECT s.id_student, s.lastname_student, s.firstname_student, s.year_student, 
               sfr.status_payment, sfr.date_payment, sfr.payment_amount 
        FROM student_fees_record sfr
        JOIN student s ON sfr.id_student = s.id_student
        JOIN payments p ON sfr.id_payment = p.id_payment
        WHERE sfr.id_payment = ? 
        AND s.semester_ID = ?
        AND p.semester_ID = ?
    ");
    $stmt->bind_param("iss", $payment_id, $selected_semester, $selected_semester);
    $stmt->execute();
    $result = $stmt->get_result();
    ?>
    
    <div class="alert alert-info mb-3">
        Showing payment records for semester: <?php echo htmlspecialchars($selected_semester); ?>
    </div>
    
    <table class="table table-striped" id="paymentRecordsTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)">Student ID <span class="sort-icon" data-column="0">⇅</span></th>
                <th onclick="sortTable(1)">Last Name <span class="sort-icon" data-column="1">⇅</span></th>
                <th onclick="sortTable(2)">First Name <span class="sort-icon" data-column="2">⇅</span></th>
                <th onclick="sortTable(3)">Year Level <span class="sort-icon" data-column="3">⇅</span></th>
                <th onclick="sortTable(5)">Status <span class="sort-icon" data-column="5">⇅</span></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $id_student = htmlspecialchars($row['id_student']);
                    $lastname = htmlspecialchars($row['lastname_student']);
                    $firstname = htmlspecialchars($row['firstname_student']);
                
                    // Convert year_student to readable format
                    $year_levels = [1 => "1st Year", 2 => "2nd Year", 3 => "3rd Year", 4 => "4th Year"];
                    $year_student = $year_levels[$row['year_student']] ?? "Unknown";
                
                    // Payment status
                    $isPaid = ($row['status_payment'] == 1);
                    $badgeClass = $isPaid ? 'btn-success' : 'btn-danger';
                    $statusText = $isPaid ? '✔ Paid' : '✖ Unpaid';
                
                    echo "<tr>
                            <td>{$id_student}</td>
                            <td>{$lastname}</td>
                            <td>{$firstname}</td>
                            <td>{$year_student}</td>
                            <td>
                                <div class='dropdown'>
                                    <button class='btn {$badgeClass} btn-sm dropdown-toggle' type='button' id='dropdownMenu{$id_student}' 
                                        data-bs-toggle='dropdown' aria-expanded='false'>
                                        <span class='status-text'>{$statusText}</span>
                                    </button>
                                    <ul class='dropdown-menu'>
                                        <li>
                                            <a class='dropdown-item text-success update-status' data-value='1' 
                                                data-student-id='{$id_student}' data-payment-id='{$payment_id}' 
                                                style='background-color: #d4edda; cursor: pointer;'>
                                                ✔ Paid
                                            </a>
                                        </li>
                                        <li>
                                            <a class='dropdown-item text-danger update-status' data-value='0' 
                                                data-student-id='{$id_student}' data-payment-id='{$payment_id}' 
                                                style='background-color: #f8d7da; cursor: pointer;'>
                                                ✖ Unpaid
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No payment records found for semester: " . htmlspecialchars($selected_semester) . "</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <?php
}
?>
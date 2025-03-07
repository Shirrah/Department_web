<?php
require_once "../../php/db-conn.php";
$db = new Database();

if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];

    // Fetch student fee records associated with the selected payment
    $stmt = $db->db->prepare("
        SELECT s.id_student, s.lastname_student, s.firstname_student, s.year_student, 
               sfr.status_payment, sfr.date_payment, sfr.payment_amount 
        FROM student_fees_record sfr
        JOIN student s ON sfr.id_student = s.id_student
        WHERE sfr.id_payment = ?
    ");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    ?>
    
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

                    // Convert date_payment to readable format
                    $date_payment = date("F d, Y", strtotime($row['date_payment']));

                    // Convert status_payment to readable format
                    $status_payment = ($row['status_payment'] == 1) ? 'Paid' : 'Unpaid';
                    $badgeClass = ($row['status_payment'] == 1) ? 'bg-success' : 'bg-danger';

                    // Format payment amount
                    $payment_amount = "PHP " . number_format($row['payment_amount'], 2);

                    echo "<tr>
                            <td>{$id_student}</td>
                            <td>{$lastname}</td>
                            <td>{$firstname}</td>
                            <td>{$year_student}</td>
                            <td><span class='badge {$badgeClass}'>{$status_payment}</span></td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>No payment records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <?php
}
?>

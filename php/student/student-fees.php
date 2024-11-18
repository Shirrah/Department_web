<link rel="stylesheet" href=".//.//stylesheet/student/student-fees.css">

<div class="fee-management-con">
<div class="fee-management-header">
        <span>Manage Fees</span>
    </div>
    <div class="list-fees-con">

    <?php
    // Start the session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Include the database connection
    require_once "././php/db-conn.php";
    $db = new Database();


    // Fetch fees related to the logged-in student
    $query = "SELECT id_payment, payment_name, payment_amount, date_payment 
              FROM payments";
    $stmt = $db->db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

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
                    <p>Due: ' . $date_payment . '</p>
                </div>
                <div class="date-created">
                    <p>Amount: PHP ' . number_format($payment_amount, 2) . '</p>
                </div>
            </div>';
        }
    } else {
        echo '<p>No fees found.</p>';
    }
    ?>

    </div>

</div>

<style>


</style>

<link rel="stylesheet" href=".//.//stylesheet/student/student-fees.css">

<div class="fee-management-con">

    <div class="title">
        <h3>Your Fees</h3>
        <span>View your pending department fees</span>
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
/* Custom student fee styles */
.fee-management-con {
    width: 100%;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
}

.title h3 {
    font-size: 24px;
    margin-bottom: 10px;
}

.title span {
    font-size: 14px;
    color: #666;
}

.list-fees-con {
    margin-top: 20px;
}

.fee {
    background-color: #fff;
    padding: 15px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.name-date h4 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.name-date p {
    font-size: 14px;
    color: #666;
}

.date-created p {
    font-size: 16px;
    color: #000;
}

</style>

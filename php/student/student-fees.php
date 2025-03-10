<link rel="stylesheet" href=".//.//stylesheet/student/student-fees.css">

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Manage Fees</h5>
        </div>
        <div class="card-body">
            <div class="list-group">
                <?php
                // Start the session
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                // Include the database connection
                require_once "././php/db-conn.php";
                $db = Database::getInstance()->db;

                if (!isset($_SESSION['user_data']['id_student'])) {
                    echo '<p class="text-center text-danger">Error: Student not logged in.</p>';
                    exit;
                }
                
                $id_student = $_SESSION['user_data']['id_student']; // Correct way to get student ID
                

                // Fetch fees related to the logged-in student
                $query = "SELECT p.id_payment, p.payment_name, p.payment_amount, p.date_payment, 
                                 sfr.status_payment 
                          FROM payments p
                          LEFT JOIN student_fees_record sfr 
                          ON p.id_payment = sfr.id_payment AND sfr.id_student = ?
                          ORDER BY p.date_payment ASC";

                $stmt = $db->prepare($query);
                $stmt->bind_param("i", $id_student);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id_payment = $row['id_payment'];
                        $payment_name = $row['payment_name'];
                        $payment_amount = $row['payment_amount'];
                        $date_payment = date('F d, Y', strtotime($row['date_payment']));
                        
                        // Determine payment status
                        $status_payment = isset($row['status_payment']) ? $row['status_payment'] : 0;
                        $status_text = ($status_payment == 1) ? "Paid" : "Not Paid";
                        $status_class = ($status_payment == 1) ? "bg-success" : "bg-danger";

                        echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                        echo '<div>';
                        echo '<h6 class="mb-1">' . htmlspecialchars($payment_name) . '</h6>';
                        echo '<small class="text-muted">Due: ' . $date_payment . '</small>';
                        echo '</div>';
                        echo '<div class="text-end">';
                        echo '<span class="badge bg-primary d-block">PHP ' . number_format($payment_amount, 2) . '</span>';
                        echo '<span class="badge ' . $status_class . ' mt-1">' . $status_text . '</span>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="text-center text-muted">No fees found.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

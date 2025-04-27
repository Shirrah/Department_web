<?php
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Get the user ID from the session (either admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'] ?? null;

// Handle semester selection from GET
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
}

// Retrieve selected semester
$selected_semester = $_SESSION['selected_semester'][$user_id] ?? '';

$fees = [];
$grand_total = 0; // Total collectibles
$grand_total_collected = 0; // Initialize collected total here
$fines = []; // To store fines data
$grand_total_fines = 0; // Initialize fines total
$collected_fines = []; // To store collected fines data
$grand_total_collected_fines = 0; // Initialize collected fines total

if (!empty($selected_semester)) {
    // Fetching events and attendances for the selected semester
    $stmt = $db->prepare("
        SELECT e.name_event, a.type_attendance, a.Penalty_type, a.Penalty_requirements, a.id_attendance
        FROM events e
        LEFT JOIN attendances a ON e.id_event = a.id_event
        WHERE e.semester_ID = ? AND a.Penalty_type = 'Fee'
    ");
    $stmt->bind_param("s", $selected_semester);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // If Penalty type is 'Fee', consider it for fines
        if ($row['Penalty_type'] === 'Fee') {
            // Count the number of "Absent" records for the specific attendance
            $absent_stmt = $db->prepare("
                SELECT COUNT(*) AS absent_count
                FROM student_attendance 
                WHERE id_attendance = ? AND status_attendance = 'Absent' AND semester_ID = ?
            ");
            $absent_stmt->bind_param("ss", $row['id_attendance'], $selected_semester);
            $absent_stmt->execute();
            $absent_result = $absent_stmt->get_result();
            $absent_row = $absent_result->fetch_assoc();
            $absent_count = (int)$absent_row['absent_count'];

            // Calculate the total fines based on absent count and penalty requirements
            $total_fines = $absent_count * (float)$row['Penalty_requirements'];
            $grand_total_fines += $total_fines; // Add to fines grand total

            // Store the fines details
            $fines[] = [
                'name_event' => $row['name_event'],
                'type_attendance' => $row['type_attendance'],
                'penalty_type' => $row['Penalty_type'],
                'penalty_requirements' => $row['Penalty_requirements'],
                'total_fines' => $total_fines,
                'id_attendance' => $row['id_attendance'],
                'absent_count' => $absent_count  // Store the count of absent students
            ];

            // Count the number of "Cleared" records with Penalty_requirements = 0 for this attendance
            $cleared_stmt = $db->prepare("
                SELECT COUNT(*) AS cleared_count
                FROM student_attendance 
                WHERE id_attendance = ? AND status_attendance = 'Cleared' AND Penalty_requirements = 0 AND semester_ID = ?
            ");
            $cleared_stmt->bind_param("ss", $row['id_attendance'], $selected_semester);
            $cleared_stmt->execute();
            $cleared_result = $cleared_stmt->get_result();
            $cleared_row = $cleared_result->fetch_assoc();
            $cleared_count = (int)$cleared_row['cleared_count'];

            // Calculate the total collected fines for this attendance
            $total_collected_fines = $cleared_count * (float)$row['Penalty_requirements'];

            if ($cleared_count > 0) {
                $collected_fines[] = [
                    'name_event' => $row['name_event'],
                    'type_attendance' => $row['type_attendance'],
                    'penalty_requirements' => $row['Penalty_requirements'],
                    'cleared_count' => $cleared_count,
                    'total_collected_fines' => $total_collected_fines,
                    'id_attendance' => $row['id_attendance']
                ];
                $grand_total_collected_fines += $total_collected_fines;
            }
        }
    }

    // Now retrieve the fee details for the selected semester
    $stmt = $db->prepare("SELECT id_payment, payment_name, payment_amount FROM payments WHERE semester_ID = ?");
    $stmt->bind_param("s", $selected_semester);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // For each payment, check unpaid students
        $count_stmt = $db->prepare("SELECT COUNT(*) AS unpaid_count FROM student_fees_record WHERE id_payment = ? AND semester_ID = ? AND status_payment = 0");
        $count_stmt->bind_param("ss", $row['id_payment'], $selected_semester);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $count_row = $count_result->fetch_assoc();
        $unpaid_count = (int)$count_row['unpaid_count'];

        // Calculate total collectible for this fee
        $total_collectible = $unpaid_count * (float)$row['payment_amount'];

        // Check paid students for this fee
        $paid_stmt = $db->prepare("SELECT COUNT(*) AS paid_count FROM student_fees_record WHERE id_payment = ? AND semester_ID = ? AND status_payment = 1");
        $paid_stmt->bind_param("ss", $row['id_payment'], $selected_semester);
        $paid_stmt->execute();
        $paid_result = $paid_stmt->get_result();
        $paid_row = $paid_result->fetch_assoc();
        $paid_count = (int)$paid_row['paid_count'];
        $total_collected = $paid_count * (float)$row['payment_amount'];

        // Save to fees array
        $fees[] = [
            'id_payment' => $row['id_payment'],
            'payment_name' => $row['payment_name'],
            'payment_amount' => $row['payment_amount'],
            'unpaid_count' => $unpaid_count,
            'total_collectible' => $total_collectible,
            'paid_count' => $paid_count,
            'total_collected' => $total_collected,
        ];

        // Add to totals
        $grand_total += $total_collectible;
        $grand_total_collected += $total_collected;
    }
}
?>

<div class="container py-4" style="margin-bottom: 150px;">
    <!-- Header -->
    <div class="text-center mb-5">
        <h2 class="fw-bold">Financial Statement</h2>
        <p class="text-muted">Overview of collections and balances</p>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-start border-info border-4">
                <div class="card-body">
                    <h6 class="text-muted">Total Collectibles</h6>
                    <h3 class="text-info">₱ <?= number_format($grand_total + $grand_total_fines, 2) ?></h3>
                    <small class="text-muted">Total fees and fines to collect</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-muted">Total Collected</h6>
                    <h3 class="text-success">₱ <?= number_format($grand_total_collected + $grand_total_collected_fines, 2) ?></h3>
                    <small class="text-muted">Amount already collected</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-warning border-4">
                <div class="card-body">
                    <h6 class="text-muted">Remaining Balance</h6>
                    <h3 class="text-warning">₱ <?= number_format(($grand_total + $grand_total_fines) - ($grand_total_collected + $grand_total_collected_fines), 2) ?></h3>
                    <small class="text-muted">Amount yet to collect</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Collectibles Section -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Collectibles</h5>
        </div>
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-md-6 p-3 border-end">
                    <h6 class="text-muted mb-3">Fees to Collect</h6>
                    <ul class="list-unstyled">
                        <?php if (!empty($fees)): ?>
                            <?php foreach ($fees as $fee): ?>
                                <li class="d-flex justify-content-between py-2">
                                    <span><?= htmlspecialchars($fee['payment_name']) ?> (<?= $fee['unpaid_count'] ?> students)</span>
                                    <span class="fw-bold">₱ <?= number_format($fee['total_collectible'], 2) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-muted py-2">No fees available.</li>
                        <?php endif; ?>
                    </ul>

                    <hr>
                    <h6 class="text-end text-success">Total Fees: ₱ <?= number_format($grand_total, 2) ?></h6>
                </div>

                <div class="col-md-6 p-3">
                    <h6 class="text-muted mb-3">Fines</h6>
                    <ul class="list-unstyled">
                        <?php if (!empty($fines)): ?>
                            <?php foreach ($fines as $fine): ?>
                                <li class="d-flex justify-content-between py-2">
                                    <span><?= htmlspecialchars($fine['name_event']) ?> (<?= $fine['type_attendance'] ?>) (<?= $fine['absent_count'] ?> students)</span>
                                    <span class="fw-bold">₱ <?= number_format($fine['total_fines'], 2) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-muted py-2">No fines data yet.</li>
                        <?php endif; ?>
                    </ul>
                    <hr>
                    <h6 class="text-end text-success">Total Fines: ₱ <?= number_format($grand_total_fines, 2) ?></h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Collected Section -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Collected</h5>
        </div>
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-md-6 p-3 border-end">
                    <h6 class="text-muted mb-3">Fees Collected</h6>
                    <ul class="list-unstyled">
                        <?php
                        $grand_total_collected = 0; // total collected amount for fees

                        if (!empty($fees)):
                            foreach ($fees as $fee):
                                // Prepare statement to count PAID students for this fee
                                $stmt = $db->prepare("SELECT COUNT(*) AS paid_count FROM student_fees_record WHERE id_payment = ? AND semester_ID = ? AND status_payment = 1");
                                $stmt->bind_param("ss", $fee['id_payment'], $selected_semester);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $count_row = $result->fetch_assoc();
                                $paid_count = (int)$count_row['paid_count'];

                                // Calculate collected amount for this fee
                                $total_collected = $paid_count * (float)$fee['payment_amount'];

                                // Add to grand collected total
                                $grand_total_collected += $total_collected;

                                if ($paid_count > 0):
                        ?>
                            <li class="d-flex justify-content-between py-2">
                                <span><?= htmlspecialchars($fee['payment_name']) ?> (<?= $paid_count ?> students)</span>
                                <span class="fw-bold text-success">₱ <?= number_format($total_collected, 2) ?></span>
                            </li>
                        <?php
                                endif;
                            endforeach;
                        else:
                        ?>
                            <li class="text-muted py-2">No collected payments yet.</li>
                        <?php endif; ?>
                    </ul>

                    <hr>
                    <h6 class="text-end text-success">Total Fees Collected: ₱ <?= number_format($grand_total_collected, 2) ?></h6>
                </div>

                <div class="col-md-6 p-3">
                    <h6 class="text-muted mb-3">Fines Collected</h6>
                    <ul class="list-unstyled">
                        <?php if (!empty($collected_fines)): ?>
                            <?php foreach ($collected_fines as $fine): ?>
                                <li class="d-flex justify-content-between py-2">
                                    <span><?= htmlspecialchars($fine['name_event']) ?> (<?= $fine['type_attendance'] ?>) (<?= $fine['cleared_count'] ?> students)</span>
                                    <span class="fw-bold text-success">₱ <?= number_format($fine['total_collected_fines'], 2) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-muted py-2">No fines collected yet.</li>
                        <?php endif; ?>
                    </ul>
                    <hr>
                    <h6 class="text-end text-success">Total Fines Collected: ₱ <?= number_format($grand_total_collected_fines, 2) ?></h6>
                </div>
            </div>
        </div>
    </div>
</div>
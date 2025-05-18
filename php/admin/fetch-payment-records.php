<?php
require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $per_page;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';

    session_start();
    $selected_semester = $_SESSION['selected_semester'][$_SESSION['user_data']['id_admin'] ?? null] ?? null;
    if (!$selected_semester) {
        echo json_encode(['success' => false, 'error' => 'No semester selected in session']);
        exit;
    }

    // Fetch payment name and amount
    $stmtPayment = $db->prepare("SELECT payment_name, payment_amount FROM payments WHERE id_payment = ?");
    $stmtPayment->bind_param("i", $payment_id);
    $stmtPayment->execute();
    $stmtPayment->bind_result($payment_name, $payment_amount);
    $stmtPayment->fetch();
    $stmtPayment->close();

    if (!$payment_name) {
        echo json_encode(['success' => false, 'error' => 'Payment not found']);
        exit;
    }

    // Base query conditions
    $baseConditions = "WHERE sfr.id_payment = ? AND s.semester_ID = ? AND p.semester_ID = ?";
    $searchConditions = "";
    $statusCondition = "";
    $params = [$payment_id, $selected_semester, $selected_semester];
    $types = "iss";

    // Add search conditions if search term is provided
    if (!empty($search)) {
        $searchConditions = " AND (
            s.id_student LIKE ? OR 
            s.lastname_student LIKE ? OR 
            s.firstname_student LIKE ? OR 
            s.year_student LIKE ?
        )";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $types .= "ssss";
    }

    // Add status filter if provided
    if ($status !== '') {
        $statusCondition = " AND sfr.status_payment = ?";
        $params[] = $status;
        $types .= "i";
    }

    // Get total records count with search and status filter
    $countQuery = "
        SELECT COUNT(*) as total
        FROM student_fees_record sfr
        JOIN student s ON sfr.id_student = s.id_student
        JOIN payments p ON sfr.id_payment = p.id_payment
        $baseConditions
        $searchConditions
        $statusCondition
    ";
    
    $countStmt = $db->prepare($countQuery);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();

    // Now fetch the student fee records with pagination, search, and status filter
    $query = "
        SELECT s.id_student, s.lastname_student, s.firstname_student, s.year_student, 
               sfr.status_payment, sfr.date_payment, sfr.payment_amount 
        FROM student_fees_record sfr
        JOIN student s ON sfr.id_student = s.id_student
        JOIN payments p ON sfr.id_payment = p.id_payment
        $baseConditions
        $searchConditions
        $statusCondition
        ORDER BY s.lastname_student, s.firstname_student
        LIMIT ? OFFSET ?
    ";

    $stmt = $db->prepare($query);
    $params[] = $per_page;
    $params[] = $offset;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    // Start building the HTML output
    $html = "";
    
    if (!empty($search)) {
        $html .= "<div class='alert alert-secondary mb-3'>
            Search results for: " . htmlspecialchars($search) . "
        </div>";
    }

    if ($status !== '') {
        $statusText = $status == '1' ? 'Paid' : 'Unpaid';
        $html .= "<div class='alert alert-info mb-3'>
            Filtered by status: " . htmlspecialchars($statusText) . "
        </div>";
    }
    
    $html .= "<table class='table table-striped' id='paymentRecordsTable'>
        <thead>
            <tr>
                <th onclick='sortTable(0)'>Student ID <span class='sort-icon' data-column='0'>⇅</span></th>
                <th onclick='sortTable(1)'>Last Name <span class='sort-icon' data-column='1'>⇅</span></th>
                <th onclick='sortTable(2)'>First Name <span class='sort-icon' data-column='2'>⇅</span></th>
                <th onclick='sortTable(3)'>Year Level <span class='sort-icon' data-column='3'>⇅</span></th>
                <th onclick='sortTable(4)'>Status <span class='sort-icon' data-column='4'>⇅</span></th>
            </tr>
        </thead>
        <tbody>";

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
        
            $html .= "<tr>
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
        $html .= "<tr><td colspan='5' class='text-center'>No payment records found" . 
                (!empty($search) ? " for search term: " . htmlspecialchars($search) : "") . 
                ($status !== '' ? " with status: " . ($status == '1' ? 'Paid' : 'Unpaid') : "") . 
                "</td></tr>";
    }

    $html .= "</tbody></table>";

    // Calculate start and end record numbers
    $start_record = $offset + 1;
    $end_record = min($offset + $per_page, $totalRecords);

    // Return JSON response
    echo json_encode([
        'success' => true,
        'html' => $html,
        'total_records' => $totalRecords,
        'start_record' => $start_record,
        'end_record' => $end_record
    ]);
}
?>
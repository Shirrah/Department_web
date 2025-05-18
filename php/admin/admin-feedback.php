<?php
// Include necessary files
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Handle feedback status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $feedback_id = $_POST['feedback_id'] ?? null;
    $new_status = $_POST['status'] ?? null;
    
    if ($feedback_id && $new_status) {
        $sql = "UPDATE feedback SET status = ? WHERE feedback_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("si", $new_status, $feedback_id);
        
        if ($stmt->execute()) {
            echo "<script>createToast('success', 'Feedback status updated successfully');</script>";
        } else {
            echo "<script>createToast('error', 'Failed to update feedback status');</script>";
        }
    }
}

// Fetch all feedback with student information
$sql = "SELECT f.*, s.firstname_student, s.lastname_student 
        FROM feedback f 
        LEFT JOIN student s ON f.id_student = s.id_student 
        ORDER BY f.created_at DESC";
$result = $db->query($sql);
$all_feedback = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $all_feedback[] = $row;
    }
}
?>

<link rel="stylesheet" href=".//.//stylesheet/admin/admin-feedback.css">

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Manage Feedback</a>
        <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="filter-group">
                            <label class="form-label small text-muted mb-1">Status</label>
                            <select class="form-select form-select-sm shadow-none" id="statusFilter">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="form-label small text-muted mb-1">Type</label>
                            <select class="form-select form-select-sm shadow-none" id="typeFilter">
                                <option value="all">All Types</option>
                                <option value="bug">Bug Report</option>
                                <option value="feature">Feature Request</option>
                                <option value="improvement">Improvement</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">ID</th>
                                    <th class="border-0">Title</th>
                                    <th class="border-0">Type</th>
                                    <th class="border-0">Priority</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Submitted By</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_feedback as $feedback): ?>
                                    <tr class="feedback-row" 
                                        data-status="<?php echo htmlspecialchars($feedback['status']); ?>"
                                        data-type="<?php echo htmlspecialchars($feedback['feedback_type']); ?>">
                                        <td class="text-muted">#<?php echo htmlspecialchars($feedback['feedback_id']); ?></td>
                                        <td>
                                            <a href="#" class="text-decoration-none text-dark fw-medium" 
                                               data-bs-toggle="modal" 
                                               data-bs-target="#feedbackModal<?php echo $feedback['feedback_id']; ?>">
                                                <?php echo htmlspecialchars($feedback['feedback_title']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?php echo ucfirst(htmlspecialchars($feedback['feedback_type'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($feedback['feedback_priority']) {
                                                    'critical' => 'danger',
                                                    'high' => 'warning',
                                                    'medium' => 'info',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst(htmlspecialchars($feedback['feedback_priority'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm status-select shadow-none" 
                                                    data-feedback-id="<?php echo $feedback['feedback_id']; ?>">
                                                <option value="pending" <?php echo $feedback['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in_progress" <?php echo $feedback['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="resolved" <?php echo $feedback['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                                <option value="closed" <?php echo $feedback['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span><?php echo htmlspecialchars($feedback['firstname_student'] . ' ' . $feedback['lastname_student']); ?></span>
                                            </div>
                                        </td>
                                        <td class="text-muted"><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-light" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#feedbackModal<?php echo $feedback['feedback_id']; ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Feedback Detail Modal -->
                                    <div class="modal fade" id="feedbackModal<?php echo $feedback['feedback_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-light">
                                                    <h5 class="modal-title">
                                                        <i class="bi bi-chat-square-text me-2"></i>Feedback Details
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-4">
                                                        <h6 class="text-muted mb-2">Title</h6>
                                                        <p class="mb-0 fw-medium"><?php echo htmlspecialchars($feedback['feedback_title']); ?></p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <h6 class="text-muted mb-2">Description</h6>
                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($feedback['feedback_description'])); ?></p>
                                                    </div>
                                                    <div class="row g-4">
                                                        <div class="col-md-4">
                                                            <h6 class="text-muted mb-2">Type</h6>
                                                            <span class="badge bg-light text-dark border">
                                                                <?php echo ucfirst(htmlspecialchars($feedback['feedback_type'])); ?>
                                                            </span>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <h6 class="text-muted mb-2">Priority</h6>
                                                            <span class="badge bg-<?php 
                                                                echo match($feedback['feedback_priority']) {
                                                                    'critical' => 'danger',
                                                                    'high' => 'warning',
                                                                    'medium' => 'info',
                                                                    default => 'secondary'
                                                                };
                                                            ?>">
                                                                <?php echo ucfirst(htmlspecialchars($feedback['feedback_priority'])); ?>
                                                            </span>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <h6 class="text-muted mb-2">Status</h6>
                                                            <span class="badge bg-light text-dark border">
                                                                <?php echo ucfirst(htmlspecialchars($feedback['status'])); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row g-4">
                                                        <div class="col-md-6">
                                                            <h6 class="text-muted mb-2">Submitted By</h6>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-circle bg-primary text-white me-2">
                                                                    <?php echo strtoupper(substr($feedback['firstname_student'], 0, 1) . substr($feedback['lastname_student'], 0, 1)); ?>
                                                                </div>
                                                                <span><?php echo htmlspecialchars($feedback['firstname_student'] . ' ' . $feedback['lastname_student']); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="text-muted mb-2">Date Submitted</h6>
                                                            <p class="mb-0"><?php echo date('M d, Y H:i', strtotime($feedback['created_at'])); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.navbar {
    padding: 0.5rem 1rem;
}

.navbar-brand {
    font-weight: 600;
    color: #333;
}

.card {
    border: none;
    border-radius: 8px;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem;
}

.table td {
    font-size: 0.875rem;
    padding: 1rem;
}

.status-select {
    min-width: 120px;
    border-radius: 20px;
    padding: 0.25rem 0.5rem;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.8em;
    border-radius: 6px;
}

.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 500;
}

.filter-group {
    min-width: 150px;
}

.form-label {
    font-size: 0.75rem;
}

.modal-content {
    border: none;
    border-radius: 8px;
}

.modal-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
}

.modal-footer {
    border-top: 1px solid rgba(0,0,0,.125);
}

.modal-body h6 {
    font-size: 0.75rem;
    font-weight: 600;
}

.btn-light {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}

.btn-light:hover {
    background-color: #e9ecef;
}

/* Status colors */
.status-select option[value="pending"] {
    color: #6c757d;
}

.status-select option[value="in_progress"] {
    color: #0d6efd;
}

.status-select option[value="resolved"] {
    color: #198754;
}

.status-select option[value="closed"] {
    color: #6c757d;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle status updates
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const feedbackId = this.dataset.feedbackId;
            const newStatus = this.value;
            
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('feedback_id', feedbackId);
            formData.append('status', newStatus);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(() => {
                // The PHP script will handle the toast notification
            })
            .catch(error => {
                console.error('Error:', error);
                createToast('error', 'Failed to update status');
            });
        });
    });

    // Handle filters
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    
    function applyFilters() {
        const selectedStatus = statusFilter.value;
        const selectedType = typeFilter.value;
        
        document.querySelectorAll('.feedback-row').forEach(row => {
            const status = row.dataset.status;
            const type = row.dataset.type;
            
            const statusMatch = selectedStatus === 'all' || status === selectedStatus;
            const typeMatch = selectedType === 'all' || type === selectedType;
            
            row.style.display = statusMatch && typeMatch ? '' : 'none';
        });
    }
    
    statusFilter.addEventListener('change', applyFilters);
    typeFilter.addEventListener('change', applyFilters);
});
</script> 
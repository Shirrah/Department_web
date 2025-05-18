<?php
// Prevent any output before JSON response
ob_start();

// Include necessary files and check authentication if needed
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Function to send JSON response
function sendJsonResponse($success, $message) {
    // Clear any previous output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Send JSON response
    $response = json_encode(['success' => $success, 'message' => $message]);
    error_log("Sending JSON response: " . $response);
    echo $response;
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start output buffering
    ob_start();
    
    try {
        // Debug: Log POST data
        error_log("POST Data: " . print_r($_POST, true));
        
        if (!isset($_SESSION['user_data'])) {
            sendJsonResponse(false, "User not logged in");
        }

        $feedback_type = $_POST['feedbackType'] ?? '';
        $feedback_priority = $_POST['feedbackPriority'] ?? '';
        $feedback_title = $_POST['feedbackTitle'] ?? '';
        $feedback_description = $_POST['feedbackDescription'] ?? '';
        $id_student = $_SESSION['user_data']['id_student'] ?? null;
        $status = 'pending';
        $created_at = date('Y-m-d H:i:s');

        // Validate required fields
        if (empty($feedback_type) || empty($feedback_priority) || empty($feedback_title) || empty($feedback_description)) {
            sendJsonResponse(false, "All fields are required");
        }

        // Debug: Log session data
        error_log("Session Data: " . print_r($_SESSION['user_data'], true));
        error_log("Student ID: " . $id_student);

        $sql = "INSERT INTO feedback (
            id_student,
            feedback_type,
            feedback_priority,
            feedback_title,
            feedback_description,
            status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        // Debug: Log SQL query
        error_log("SQL Query: " . $sql);

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            sendJsonResponse(false, "Database error: " . $db->error);
        }

        $stmt->bind_param("sssssss", 
            $id_student,
            $feedback_type,
            $feedback_priority,
            $feedback_title,
            $feedback_description,
            $status,
            $created_at
        );

        if ($stmt->execute()) {
            $feedback_id = $db->insert_id;
            error_log("Inserted Feedback ID: " . $feedback_id);

            // Handle file uploads if any
            if (!empty($_FILES['feedbackAttachment']['name'][0])) {
                $upload_dir = './uploads/feedback/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    if (!mkdir($upload_dir, 0777, true)) {
                        sendJsonResponse(false, "Failed to create upload directory");
                    }
                }

                foreach ($_FILES['feedbackAttachment']['tmp_name'] as $key => $tmp_name) {
                    $file_name = $_FILES['feedbackAttachment']['name'][$key];
                    $file_size = $_FILES['feedbackAttachment']['size'][$key];
                    
                    // Generate unique filename
                    $unique_filename = uniqid() . '_' . $file_name;
                    $file_path = $upload_dir . $unique_filename;

                    if (move_uploaded_file($tmp_name, $file_path)) {
                        // Save file information to database
                        $sql = "INSERT INTO feedback_attachments (
                            feedback_id,
                            file_name,
                            file_path,
                            file_size,
                            uploaded_at
                        ) VALUES (?, ?, ?, ?, ?)";

                        $stmt = $db->prepare($sql);
                        if (!$stmt) {
                            sendJsonResponse(false, "Database error: " . $db->error);
                        }

                        $stmt->bind_param("issis", 
                            $feedback_id,
                            $file_name,
                            $file_path,
                            $file_size,
                            $created_at
                        );

                        if (!$stmt->execute()) {
                            sendJsonResponse(false, "Database error: " . $stmt->error);
                        }
                    } else {
                        sendJsonResponse(false, "Failed to move uploaded file: " . error_get_last()['message']);
                    }
                }
            }

            sendJsonResponse(true, 'Feedback submitted successfully');
        } else {
            sendJsonResponse(false, 'Failed to submit feedback. Please try again.');
        }
    } catch (Exception $e) {
        error_log("Feedback Error: " . $e->getMessage());
        sendJsonResponse(false, 'Error submitting feedback: ' . $e->getMessage());
    }
}

// Only proceed with HTML output if this is not an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    // Fetch recent feedback
    try {
        $sql = "SELECT f.*, s.firstname_student, s.lastname_student 
                FROM feedback f 
                LEFT JOIN student s ON f.id_student = s.id_student 
                ORDER BY f.created_at DESC 
                LIMIT 5";
        $result = $db->query($sql);
        $recent_feedback = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $recent_feedback[] = $row;
            }
        }
    } catch (Exception $e) {
        $recent_feedback = [];
    }
?>

<div class="container mt-4" style="margin-bottom: 100px;">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="alert alert-info mb-4">
                <p class="mb-0">Your feedback helps us improve the system. Please share your thoughts, report any issues, or suggest new features.</p>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Submit Feedback</h4>
                </div>
                <div class="card-body">
                    <form id="feedbackForm" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="feedbackType" class="form-label">Feedback Type</label>
                                <select class="form-select" id="feedbackType" name="feedbackType" required>
                                    <option value="">Select type...</option>
                                    <option value="bug">Bug Report</option>
                                    <option value="feature">Feature Request</option>
                                    <option value="improvement">Improvement Suggestion</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="feedbackPriority" class="form-label">Priority</label>
                                <select class="form-select" id="feedbackPriority" name="feedbackPriority" required>
                                    <option value="">Select priority...</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="feedbackTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="feedbackTitle" name="feedbackTitle"
                                   placeholder="Brief description of your feedback" required>
                        </div>

                        <div class="mb-3">
                            <label for="feedbackDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="feedbackDescription" name="feedbackDescription" rows="6" 
                                      placeholder="Please provide detailed information about your feedback..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="feedbackAttachment" class="form-label">Attachments (Optional)</label>
                            <input type="file" class="form-control" id="feedbackAttachment" name="feedbackAttachment[]" multiple>
                            <small class="text-muted">You can attach screenshots or other relevant files (Max size: 5MB)</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Feedback Button -->
            <div class="text-center mt-4">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#recentFeedbackModal">
                    <i class="bi bi-clock-history me-2"></i>View Recent Feedback
                </button>
            </div>

            <!-- Recent Feedback Modal -->
            <div class="modal fade" id="recentFeedbackModal" tabindex="-1" aria-labelledby="recentFeedbackModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="recentFeedbackModalLabel">Recent Feedback</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="list-group">
                                <?php if (empty($recent_feedback)): ?>
                                    <div class="text-center text-muted py-4">
                                        <p class="mb-0">No feedback submitted yet</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_feedback as $feedback): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($feedback['feedback_title']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($feedback['created_at'])); ?>
                                                </small>
                                            </div>
                                            <p class="mb-1"><?php echo htmlspecialchars($feedback['feedback_description']); ?></p>
                                            <small class="text-muted">
                                                By: <?php echo htmlspecialchars($feedback['firstname_student'] . ' ' . $feedback['lastname_student']); ?> |
                                                Type: <?php echo ucfirst($feedback['feedback_type']); ?> |
                                                Priority: <?php echo ucfirst($feedback['feedback_priority']); ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.card-header {
    border-bottom: 1px solid #dee2e6;
    padding: 1rem;
}

.card-body {
    padding: 1.5rem;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-primary {
    padding: 0.5rem 1rem;
}

/* Modal Styles */
.modal-content {
    border-radius: 8px;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.list-group-item {
    border-left: none;
    border-right: none;
    padding: 1rem;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}
</style>

<script>
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    
    // Disable submit button and show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = 'Submitting...';
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            createToast('success', data.message);
            this.reset();
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            createToast('error', data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        createToast('error', 'An error occurred while submitting feedback. Please try again.');
    })
    .finally(() => {
        // Re-enable submit button and restore original text
        submitButton.disabled = false;
        submitButton.innerHTML = 'Submit Feedback';
    });
});
</script>
<?php
} 
<?php
// Include the database connection
require_once "././php/db-conn.php";
$db = Database::getInstance()->db;

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != 'yes') {
    header("location: ../index.php?content=log-in");
    exit();
}

// Get user ID (admin or student)
$user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

try {
    $query = "SELECT 
                id_student, 
                semester_ID, 
                pass_student, 
                lastname_student, 
                firstname_student, 
                role_student, 
                year_student 
              FROM student 
              WHERE id_student = ?";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student_data = $result->fetch_assoc();
        ?>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <!-- QR Code CDN -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

        <div class="container my-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h3 class="card-title text-center mb-0 text-dark">QR Code</h3>
                </div>
                
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- QR Code Column -->
                        <div class="col-md-5">
                            <div class="bg-white p-4 rounded-3 text-center border shadow-sm">
                                <div id="qrcode" class="mb-3 mx-auto" style="display: flex; align-items: center; justify-content:center;"></div>
                                <p class="text-muted mb-0">
                                    Scan this QR code for your Attendance
                                </p>
                            </div>
                        </div>
                        
                        <!-- Student Info Column -->
                        <div class="col-md-7">
                            <div class="list-group list-group-flush bg-white rounded-3 border shadow-sm">
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0">
                                    <span class="fw-bold text-dark">Student ID:</span>
                                    <span class="text-end text-dark"><?php echo htmlspecialchars($student_data['id_student']); ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0">
                                    <span class="fw-bold text-dark">Full Name:</span>
                                    <span class="text-end text-dark"><?php echo htmlspecialchars($student_data['firstname_student'] . ' ' . $student_data['lastname_student']); ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0">
                                    <span class="fw-bold text-dark">Year Level:</span>
                                    <span class="text-end text-dark"><?php echo htmlspecialchars($student_data['year_student']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Generate QR code when page loads
            document.addEventListener('DOMContentLoaded', function() {
                generateQRCode();
            });
            
            // Make QR code responsive on window resize
            window.addEventListener('resize', function() {
                generateQRCode();
            });
            
            function generateQRCode() {
                const qrElement = document.getElementById("qrcode");
                if (qrElement) {
                    qrElement.innerHTML = ''; // Clear existing QR code
                    
                    // Calculate size based on container width
                    const containerWidth = qrElement.parentElement.clientWidth;
                    const qrSize = Math.min(containerWidth - 40, 300);
                    
                    new QRCode(qrElement, {
                        text: "<?php echo $student_data['id_student']; ?>",
                        width: qrSize,
                        height: qrSize,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            }
        </script>
        <?php
    } else {
        echo '<div class="alert alert-warning mt-4">No student information found.</div>';
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo '<div class="alert alert-danger mt-4">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    error_log("Error fetching student data: " . $e->getMessage());
}
?>
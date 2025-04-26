<?php
// This must be the VERY FIRST LINE in your script
header('Content-Type: application/json');

// Disable HTML error rendering
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/efms-sync-errors.log');

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'A fatal error occurred',
            'details' => $error['message']
        ]);
    }
});

try {
    require_once '../../php/db-conn.php';
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    if (!isset($data['id_student']) || !isset($data['id_attendance'])) {
        throw new Exception('Missing required fields');
    }

    $database = Database::getInstance();
    $db = $database->db;
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }

    // Use custom date_attendance if provided, otherwise use NOW()
    $date_attendance = isset($data['date_attendance']) ? $data['date_attendance'] : date('Y-m-d H:i:s');

    $stmt = $db->prepare("
        UPDATE student_attendance 
        SET date_attendance = ?, 
            status_attendance = 'Present', 
            Penalty_requirements = 0 
        WHERE id_attendance = ? AND id_student = ?
    ");

    $success = $stmt->execute([$date_attendance, $data['id_attendance'], $data['id_student']]);
    
    if ($db->affected_rows === 0) {
        throw new Exception('No matching record found to update');
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

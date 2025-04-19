<?php
require_once __DIR__ . '/../../php/db-conn.php';
$db = Database::getInstance()->db;

header('Content-Type: application/json');

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
    exit;
}

$file = $_FILES['file']['tmp_name'];
$fileType = $_POST['fileType'] ?? pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$semester_ID = $_POST['semester_ID'] ?? null;

// Validate semester exists
if (!$semester_ID) {
    echo json_encode(['success' => false, 'message' => 'No semester selected.']);
    exit;
}

// Verify semester exists in database
$checkSemester = $db->prepare("SELECT semester_ID FROM semester WHERE semester_ID = ?");
$checkSemester->bind_param("s", $semester_ID);
$checkSemester->execute();

if ($checkSemester->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid semester selected.']);
    exit;
}

$inserted = 0;
$skipped = 0;
$errors = [];

try {
    if ($fileType === 'xlsx') {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        array_shift($rows); // Remove header
        
        foreach ($rows as $row) {
            if (count($row) < 5) continue;
            
            $result = processStudent([
                'id_student' => $row[0] ?? '',
                'pass_student' => $row[1] ?? '',
                'lastname_student' => $row[2] ?? '',
                'firstname_student' => $row[3] ?? '',
                'year_student' => $row[4] ?? 1
            ], $semester_ID, $db);
            
            if ($result === true) {
                $inserted++;
            } elseif ($result === false) {
                $skipped++;
            } else {
                $errors[] = $result;
            }
        }
    } else {
        if (($handle = fopen($file, "r")) !== FALSE) {
            fgetcsv($handle); // Skip header
            
            while (($data = fgetcsv($handle)) !== FALSE) {
                if (count($data) < 5) continue;
                
                $result = processStudent([
                    'id_student' => $data[0] ?? '',
                    'pass_student' => $data[1] ?? '',
                    'lastname_student' => $data[2] ?? '',
                    'firstname_student' => $data[3] ?? '',
                    'year_student' => $data[4] ?? 1
                ], $semester_ID, $db);
                
                if ($result === true) {
                    $inserted++;
                } elseif ($result === false) {
                    $skipped++;
                } else {
                    $errors[] = $result;
                }
            }
            fclose($handle);
        }
    }
    
    echo json_encode([
        'success' => true,
        'inserted' => $inserted,
        'skipped' => $skipped,
        'errors' => $errors,
        'message' => 'Import completed.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error processing file: ' . $e->getMessage()
    ]);
}

function processStudent($data, $semester_ID, $db) {
    // Validate required fields
    $required = ['id_student', 'pass_student', 'lastname_student', 'firstname_student'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return "Missing $field for student";
        }
    }
    
    // Validate year (1-4)
    $year = intval($data['year_student'] ?? 1);
    if ($year < 1 || $year > 4) {
        $data['year_student'] = 1; // Default to 1 if invalid
    }
    
    // Check for duplicate student in THIS SEMESTER
    $checkQuery = "SELECT id_student FROM student WHERE id_student = ? AND semester_ID = ?";
    $stmt = $db->prepare($checkQuery);
    $stmt->bind_param("ss", $data['id_student'], $semester_ID);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return false; // Skip duplicate in this semester
    }
    
    // Insert new student with semester_ID
    $insertQuery = "INSERT INTO student 
                   (id_student, semester_ID, pass_student, lastname_student, firstname_student, role_student, year_student) 
                   VALUES (?, ?, ?, ?, ?, 'Student', ?)";
    $stmt = $db->prepare($insertQuery);
    $stmt->bind_param("sssssi", 
        $data['id_student'], 
        $semester_ID,
        $data['pass_student'], 
        $data['lastname_student'], 
        $data['firstname_student'], 
        $data['year_student']
    );
    
    if ($stmt->execute()) {
        return true;
    } else {
        return 'Error inserting student ' . $data['id_student'] . ': ' . $stmt->error;
    }
}
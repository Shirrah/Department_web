<?php

require_once '..//..//php/db-conn.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['studentFile'])) {
    $file = $_FILES['studentFile'];
    $allowedExtensions = ['csv', 'xlsx'];

    // Check file extension
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (!in_array($fileExtension, $allowedExtensions)) {
        die("Invalid file format. Only CSV or Excel files are allowed.");
    }

    // Move uploaded file to a temporary location
    $filePath = $file['tmp_name'];

    require '..//..//vendor/autoload.php'; // Include PHPSpreadsheet library for Excel files

    $db = new Database();

    // Start the session
    session_start();

    // Get the user ID from the session (either admin or student)
    $user_id = $_SESSION['user_data']['id_admin'] ?? $_SESSION['user_data']['id_student'];

    // Handle the semester selection from GET request and store it in session for this user
    if (isset($_GET['semester']) && !empty($_GET['semester'])) {
        // Store the selected semester for the user in session
        $_SESSION['selected_semester'][$user_id] = $_GET['semester'];
    }

    // Check if semester is set in the session for this user
    if (!isset($_SESSION['selected_semester'][$user_id]) || empty($_SESSION['selected_semester'][$user_id])) {
        die("No semester selected. Please select a semester before importing students.");
    }
    $selected_semester = $_SESSION['selected_semester'][$user_id];

    // Process CSV file
    if ($fileExtension == 'csv') {
        $fileHandle = fopen($filePath, 'r');
        if ($fileHandle === false) {
            die("Error opening file.");
        }

        fgetcsv($fileHandle); // Skip header row

        while (($row = fgetcsv($fileHandle, 1000, ',')) !== FALSE) {
            // Clean and sanitize data
            $id_student = htmlspecialchars($row[0]);
            $pass_student = htmlspecialchars($row[1]);
            $lastname_student = htmlspecialchars($row[2]);
            $firstname_student = htmlspecialchars($row[3]);
            $role_student = htmlspecialchars($row[4]);
            $year_student = htmlspecialchars($row[5]);

            // Prepare and execute SQL query for each student
            $query = "INSERT INTO student (id_student, semester_ID, pass_student, lastname_student, firstname_student, role_student, year_student)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->db->prepare($query);
            if ($stmt === false) {
                die("Error preparing statement.");
            }
            $stmt->bind_param("sssssss", $id_student, $selected_semester, $pass_student, $lastname_student, $firstname_student, $role_student, $year_student);
            $stmt->execute();
        }

        fclose($fileHandle);
        header("Location: ?content=admin-index&admin=student-management");
        exit();
    }

    // Process Excel file (XLSX)
    elseif ($fileExtension == 'xlsx') {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            foreach ($data as $index => $row) {
                if ($index == 0) continue; // Skip header row

                // Clean and sanitize data
                $id_student = htmlspecialchars($row[0]);
                $pass_student = htmlspecialchars($row[1]);
                $lastname_student = htmlspecialchars($row[2]);
                $firstname_student = htmlspecialchars($row[3]);
                $role_student = htmlspecialchars($row[4]);
                $year_student = htmlspecialchars($row[5]);

                // Prepare and execute SQL query for each student
                $query = "INSERT INTO student (id_student, semester_ID, pass_student, lastname_student, firstname_student, role_student, year_student)
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->db->prepare($query);
                if ($stmt === false) {
                    die("Error preparing statement.");
                }
                $stmt->bind_param("sssssss", $id_student, $selected_semester, $pass_student, $lastname_student, $firstname_student, $role_student, $year_student);
                $stmt->execute();
            }

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            die("Error loading Excel file: " . $e->getMessage());
        }

        header("Location: ?content=admin-index&admin=student-management");
        exit();
    }

    // Unsupported file format
    else {
        echo "Unsupported file format.";
    }
}
?>

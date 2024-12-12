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

    // Get selected semester from session
    session_start();
    if (!isset($_SESSION['selected_semester'])) {
        die("No semester selected. Please select a semester before importing students.");
    }
    $selected_semester = $_SESSION['selected_semester'];

    if ($fileExtension == 'csv') {
        $fileHandle = fopen($filePath, 'r');
        fgetcsv($fileHandle); // Skip header row

        while (($row = fgetcsv($fileHandle, 1000, ',')) !== FALSE) {
            $id_student = $row[0];
            $pass_student = $row[1];
            $lastname_student = $row[2];
            $firstname_student = $row[3];
            $role_student = $row[4];
            $year_student = $row[5];

            $query = "INSERT INTO student (id_student, semester_ID, pass_student, lastname_student, firstname_student, role_student, year_student)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->db->prepare($query);
            $stmt->bind_param("sssssss", $id_student, $selected_semester, $pass_student, $lastname_student, $firstname_student, $role_student, $year_student);
            $stmt->execute();
        }
        fclose($fileHandle);
        header("Location: ?content=admin-index&admin=student-management");
        exit();
    } elseif ($fileExtension == 'xlsx') {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        foreach ($data as $index => $row) {
            if ($index == 0) continue; // Skip header row

            $id_student = $row[0];
            $pass_student = $row[1];
            $lastname_student = $row[2];
            $firstname_student = $row[3];
            $role_student = $row[4];
            $year_student = $row[5];

            $query = "INSERT INTO student (id_student, semester_ID, pass_student, lastname_student, firstname_student, role_student, year_student)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->db->prepare($query);
            $stmt->bind_param("sssssss", $id_student, $selected_semester, $pass_student, $lastname_student, $firstname_student, $role_student, $year_student);
            $stmt->execute();
        }
        exit();
    } else {
        echo "Unsupported file format.";
    }
    header("Location: ?content=admin-index&admin=student-management");
    exit();
}
?>

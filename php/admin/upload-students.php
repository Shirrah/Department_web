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

    if ($fileExtension == 'csv') {
        $fileHandle = fopen($filePath, 'r');
        fgetcsv($fileHandle); // Skip header row

        while (($row = fgetcsv($fileHandle, 1000, ',')) !== FALSE) {
            $id_student = $row[0];
            $semester_ID = $row[1];
            $pass_student = $row[2];
            $lastname_student = $row[3];
            $firstname_student = $row[4];
            $role_student = $row[5];
            $year_student = $row[6];

            $query = "INSERT INTO student (id_student, semester_ID, pass_student, lastname_student, firstname_student, role_student, year_student)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->db->prepare($query);
            $stmt->bind_param("sssssss", $id_student, $semester_ID, $pass_student, $lastname_student, $firstname_student, $role_student, $year_student);
            $stmt->execute();
        }
        fclose($fileHandle);
        echo "CSV file imported successfully!";
    } elseif ($fileExtension == 'xlsx') {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        foreach ($data as $index => $row) {
            if ($index == 0) continue; // Skip header row

            $id_student = $row[0];
            $semester_ID = $row[1];
            $pass_student = $row[2];
            $lastname_student = $row[3];
            $firstname_student = $row[4];
            $role_student = $row[5];
            $year_student = $row[6];

            $query = "INSERT INTO student (id_student, semester_ID, pass_student, lastname_student, firstname_student, role_student, year_student)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->db->prepare($query);
            $stmt->bind_param("sssssss", $id_student, $semester_ID, $pass_student, $lastname_student, $firstname_student, $role_student, $year_student);
            $stmt->execute();
        }
        echo "Excel file imported successfully!";
    } else {
        echo "Unsupported file format.";
    }
}
?>

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $semester_id = htmlspecialchars($_POST['semester_ID']);
    $academic_year = htmlspecialchars($_POST['academic_year']);
    $semester_type = htmlspecialchars($_POST['semester_type']);
    
    list($start_year, $end_year) = explode('-', $academic_year);
    $generated_semester_id = "AY" . $start_year . "-" . $end_year . "-" . strtolower(str_replace(" ", "", $semester_type));

    if (!empty($semester_id)) {
        // UPDATE existing term
        $stmt = $db->prepare("UPDATE semester SET semester_ID = ?, academic_year = ?, semester_type = ? WHERE semester_ID = ?");
        $stmt->bind_param("ssss", $generated_semester_id, $academic_year, $semester_type, $semester_id);
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            echo "<script>alert('Error updating term: " . $stmt->error . "'); window.history.back();</script>";
        }
    } else {
        // INSERT new term
        $date_created = date("Y-m-d H:i:s");
        $stmt = $db->prepare("INSERT INTO semester (semester_ID, academic_year, semester_type, date_created) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $generated_semester_id, $academic_year, $semester_type, $date_created);        
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            echo "<script>alert('Error adding new term: " . $stmt->error . "'); window.history.back();</script>";
        }
    }
}
?>

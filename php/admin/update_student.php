<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../../php/db-conn.php";
$db = Database::getInstance()->db;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_student = $_POST['id_student'];
    $pass_student = $_POST['pass_student'];
    $lastname_student = $_POST['lastname_student'];
    $firstname_student = $_POST['firstname_student'];
    $year_student = $_POST['year_student'];

    $stmt = $db->prepare("UPDATE student SET pass_student=?, lastname_student=?, firstname_student=?, year_student=? WHERE id_student=?");
    $stmt->bind_param("ssssi", $pass_student, $lastname_student, $firstname_student, $year_student, $id_student);

    if ($stmt->execute()) {
        echo "<script>window.location.href='https://www.ccsportal.online/index.php?content=admin-index&admin=student-management';</script>";
    } else {
        echo "<script>alert('Update failed!');</script>";
    }

    $stmt->close();
    $db->close();
}
?>

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "././php/db-conn.php";
$db = new Database();



?>
<link rel="stylesheet" href=".//.//stylesheet/student/student-events-payments.css">

<div class="student-events-payments-body">
    <div class="events-payments-content">
        <div class="events-payments-display">
            <div class="student-payment-event-menu">
                <a href="?content=student-index&student-payment-event=student-events">Events</a>
                <a href="?content=student-index&student-payment-event=student-fees">Fees</a>
            </div>
        <?php
        if(isset($_GET['student-payment-event'])){
            $student_payment_event_pg = $_GET['student-payment-event'];
        }else{
            $student_payment_event_pg = "";
            }

            switch($student_payment_event_pg){
                case "default":
                    include 'php/student/student-events.php';
                    break;
                case "student-events":
                    include 'php/student/student-events.php';
                    break;
                case "student-fees":
                    include 'php/student/student-fees.php';
                    break;
                default:
                    include 'php/student/student-events.php';
                    break;
            }
        ?>
        </div>
    </div>
</div>

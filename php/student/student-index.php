<link rel="stylesheet" href=".//.//stylesheet/student/student-index.css">

<div class="student-body">
    <div class="studentcon">
        <div class="student-left-navbar">
            <a href="?content=student-index&student=student-events-payments" class=""><img src=".//.//assets/images/income.png" alt="">Events & Fees</a>
            <a href="?content=student-index&student=notifications" class=""><img src=".//.//assets/images/ringing.png" alt="">Notification & History</a>
        </div>
        <div class="student-navbar-display">
        <?php
        if(isset($_GET['student'])){
            $student_pg = $_GET['student'];
        }else{
                $student_pg = "clearance";
            }

            switch($student_pg){
                case "default":
                    include 'php/student/student-events-payments.php';
                case "student-events-payments":
                    include 'php/student/student-events-payments.php';
                    break;
                case "student-attendance-records":
                    include 'php/student/student-attendance-records.php';
                    break;
                case "notifications":
                    include 'php/admin/notifications.php';
                    break;
                default:
                    include 'php/student/student-events-payments.php';
                    break;
            }
        ?>
        </div>
    </div>
</div>
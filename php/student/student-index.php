<link rel="stylesheet" href=".//.//stylesheet/student/student-index.css">

<div class="student-body">
    <div class="studentcon">
        <div class="student-left-navbar">
        <a href="" class="title">DASHBOARD</a>
            <a href="?content=student-index&student=student-fees" class=""><img src=".//.//assets/images/income.png" alt="">Events</a>
            <a href="?content=student-index&student=student-fees" class=""><img src=".//.//assets/images/income.png" alt="">Fees</a>
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
                    include 'php/student/student-events.php';
                    break;
                case "student-events":
                    include 'php/student/student-events.php';
                    break;
                case "student-fees":
                    include 'php/student/student-fees.php';
                    break;
                case "notifications":
                    include 'php/admin/notifications.php';
                    break;
                default:
                include 'php/student/student-events.php';
                    break;
            }
        ?>
        </div>
    </div>
</div>
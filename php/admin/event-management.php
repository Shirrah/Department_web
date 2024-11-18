<link rel="stylesheet" href=".//.//stylesheet/admin/event-management.css">

<div class="event-management-body">
    <div class="event-management-con-body">
<?php
        if(isset($_GET['admin_events'])){
            $admin_pg = $_GET['admin_events'];
        }else{
                $admin_pg = "admin-events";
            }

            switch($admin_pg){
                case "admin-events":
                    include 'php/admin/admin-events.php';
                    break;
                case "admin-fees":
                    include 'php/admin/admin-fees.php';
                    break;
            }
        ?>
        </div>
</div> 
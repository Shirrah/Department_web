<link rel="stylesheet" href=".//.//stylesheet/admin/admin-index.css">

<div class="admin-body">
    <div class="admincon">
        <div class="admin-left-navbar">
            <a href="" class="title">DASHBOARD</a>
            <div class="semester-select">
    <form method="GET" action="">
        <label for="semester">Select Semester</label>
        <select name="semester" id="semester" onchange="this.form.submit()">  
            <?php
            // Include the database connection class
            include_once('db-conn.php');

            // Instantiate the Database class to establish the connection
            $db_instance = new Database();
            $db = $db_instance->db; // Access the $db property for the connection

            // Fetch semester data from the database
            $sql = "SELECT semester_ID, academic_year, semester_type FROM semester";
            $result = $db->query($sql);

            // Get the selected semester ID from the query string
            $selected_semester = isset($_GET['semester']) ? $_GET['semester'] : '';

            // Loop through the results and populate the dropdown
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $semester_id = $row['semester_ID'];
                    $academic_year = $row['academic_year'];
                    $semester_type = $row['semester_type'];

                    // Check if this semester is the selected one
                    $selected = ($semester_id == $selected_semester) ? 'selected' : '';
                    echo "<option value='$semester_id' $selected>$semester_type - $academic_year</option>";
                }
            } else {
                echo "<option value=''>No semesters available</option>";
            }
            ?>
        </select>
    </form>
</div>

            <a href="?content=admin-index&admin=dashboard" class="admin-nav-dashboard">Dashboard</a>
            <a href="?content=admin-index&admin=student-management" class="admin-nav-dashboard">Students</a>
            <a href="?content=admin-index&admin=ay-dashboard" class="admin-nav-dashboard">Academic year</a>
            <a href="javascript:void(0);" class="admin-nav-dashboard" id="events-fees-toggle">Events & Fees</a>
            <div class="dropdown">
                <a href="?content=admin-index&admin=event-management&admin_events=admin-events" class="dropdown-link"> > Manage Events </a>
                <a href="?content=admin-index&admin=event-management&admin_events=admin-fees" class="dropdown-link"> > Manage Fees</a>
            </div>
            <a href="?content=admin-index&admin=notifications" class="admin-nav-dashboard">Notifications</a>
        </div>

        <div class="admin-navbar-display">
            <?php
            if(isset($_GET['admin'])){
                $admin_pg = $_GET['admin'];
            } else {
                $admin_pg = "dashboard";
            }

            switch($admin_pg){
                case "default":
                    include 'php/admin/dashboard.php';
                    break;
                case "ay-dashboard":
                    include 'php/admin/ay-dashboard.php';
                    break;
                case "student-management":
                    include 'php/admin/student-management.php';
                    break;
                case "event-management":
                    include 'php/admin/event-management.php';
                    break;
                case "notifications":
                    include 'php/admin/notifications.php';
                    break;
                case "attendance-records":
                    include 'php/admin/show-attendance-records.php';
                    break;
                default:
                    include 'php/admin/dashboard.php'; // Fallback to default
                    break;
            }
            ?>
        </div>
    </div>
</div>

<script>
    document.getElementById("events-fees-toggle").addEventListener("click", function () {
        const dropdown = this.nextElementSibling;
        dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
    });

    const toggleButton = document.getElementById("events-fees-toggle");
    const dropdown = toggleButton.nextElementSibling;

    toggleButton.addEventListener("click", function () {
        if (dropdown.classList.contains("slide-down")) {
            // If dropdown is open, slide it up
            dropdown.classList.remove("slide-down");
            dropdown.classList.add("slide-up");
        } else {
            // If dropdown is closed, slide it down
            dropdown.classList.remove("slide-up");
            dropdown.classList.add("slide-down");
        }
    });
</script>

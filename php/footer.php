<link rel="stylesheet" href="stylesheet/footer.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="footer-body text-light py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="version">
            <p class="mb-0">Version 1.0.0 | © Copyright College of Computer Studies</p>
        </div>

        <!-- Display Total Page Visits -->
        <div class="visits">
            <?php
            // Include the database connection
            require_once "././php/db-conn.php";
            $db = new Database();

            // Get the total number of visits
            $result = $db->db->query("SELECT COUNT(*) AS total_visits FROM page_visits");
            $row = $result->fetch_assoc();
            $total_visits = $row['total_visits'];

            // Display total visits with an icon
            echo '<p class="mb-0">Page Visits: <span class="badge bg-light text-dark">' . $total_visits . '</span></p>';

            $db->db->close();
            ?>
        </div>
    </div>
</div>

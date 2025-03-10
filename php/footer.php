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
            $db = Database::getInstance()->db;

            // Get the total number of visits
            $result = $db->query("SELECT COUNT(*) AS total_visits FROM page_visits");
            $row = $result->fetch_assoc();
            $total_visits = $row['total_visits'];

            // Display total visits with an icon
            echo '<p class="mb-0">Page Visits: <span class="badge bg-light text-dark">' . $total_visits . '</span></p>';
            ?>
        </div>
    </div>
</div>

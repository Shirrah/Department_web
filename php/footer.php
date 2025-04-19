<link rel="stylesheet" href="stylesheet/footer.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="footer-body text-light py-3" id="footer">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="version">
            <p class="mb-0">Version: <?php echo $site_version; ?> | © Copyright College of Computer Studies</p>
        </div>

        <!-- Display Total Page Visits -->
        <div class="visits">
            <?php
            $db = Database::getInstance()->db;

            // Initialize total visits
            $total_visits = 0;

            // Get the total number of visits with error handling
            $result = $db->query("SELECT COUNT(*) AS total_visits FROM page_visits");
            if ($result) {
                $row = $result->fetch_assoc();
                $total_visits = $row['total_visits'];
            } else {
                // Log error (optional) and set a fallback message
                error_log("Failed to fetch page visits: " . $db->error);
                $total_visits = "N/A";
            }

            // Display total visits with an icon and badge
            echo '<p class="mb-0">Page Visits: <span class="badge bg-success text-light">';
            echo is_numeric($total_visits) ? number_format($total_visits) : $total_visits;
            echo '</span> <i class="bi bi-eye"></i></p>';
            ?>
        </div>
    </div>
</div>

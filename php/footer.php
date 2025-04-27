<style>
.footer-body {
    width: 100%;
    background: linear-gradient(135deg, #f94415 0%, #e63900 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    border-top: 3px solid rgba(255,255,255,0.1);
    box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
}

.footer-body h6 {
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.5px;
    position: relative;
    padding-bottom: 8px;
    margin-bottom: 15px;
}

.footer-body h6::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 40px;
    height: 2px;
    background: rgba(255,255,255,0.5);
}

.footer-body p, 
.footer-body li, 
.footer-body a {
    font-size: 0.95rem;
    color: rgba(255,255,255,0.9);
    transition: all 0.3s ease;
}

.footer-body a:hover {
    text-decoration: none;
    color: #fff;
    transform: translateX(3px);
}

.footer-body .badge {
    font-size: 0.85rem;
    background-color: rgba(0,0,0,0.25) !important;
    padding: 5px 8px;
    border-radius: 4px;
}

.page-visits {
    background-color: rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    padding: 8px 12px;
    border-radius: 6px;
    display: inline-block;
}

.page-visits:hover {
    transform: scale(1.02);
    background-color: rgba(0,0,0,0.25);
}

.page-visits i {
    font-size: 1.5rem;
    vertical-align: middle;
}

.page-visits span {
    font-size: 1.1rem;
    font-weight: 500;
}

.footer-body .list-unstyled li {
    margin-bottom: 8px;
    padding-left: 5px;
    border-left: 2px solid transparent;
    transition: all 0.3s ease;
}

.footer-body .list-unstyled li:hover {
    border-left: 2px solid rgba(255,255,255,0.5);
}

.footer-body .bi {
    font-size: 1.1rem;
    margin-right: 10px;
    vertical-align: middle;
}

@media (max-width: 768px) {
    .footer-body .col-md-4 {
        margin-bottom: 25px;
    }
    
    .footer-body h6 {
        font-size: 1.1rem;
    }
}
</style>

<footer class="footer-body text-light py-5 mt-auto" id="footer">
    <div class="container">
        <div class="row justify-content-between">
            <!-- Left: Info Section -->
            <div class="col-md-4 mb-3 mb-md-0">
                <h6>About</h6>
                <p class="mb-2"><i class="bi bi-building"></i> College of Computer Studies</p>
                <p class="mb-2"><i class="bi bi-tag"></i> Version: <?php echo $site_version; ?></p>
                <p class="mb-0"><i class="bi bi-c-circle"></i> <?php echo date('Y'); ?> All rights reserved.</p>
            </div>

            <!-- Center: Quick Links -->
            <div class="col-md-4 mb-3 mb-md-0">
                <h6>Quick Links</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="" class="text-light text-decoration-none"><i class="bi bi-house-door"></i>Home</a></li>
                    <li><a href="" class="text-light text-decoration-none"><i class="bi bi-info-circle"></i>About</a></li>
                    <li><a href="" class="text-light text-decoration-none"><i class="bi bi-envelope"></i>Contact</a></li>
                    <li><a href="" class="text-light text-decoration-none"><i class="bi bi-question-circle"></i>Help</a></li>
                </ul>
            </div>

            <!-- Right: Stats & Contact -->
            <div class="col-md-4">
                <h6>Statistics</h6>
                <div class="page-visits mb-3">
                    <i class="bi bi-eye"></i>
                    <span style="user-select: none; cursor: default;">Page Visits: </span>
                    <span class="badge" style="user-select: none; cursor: default;">
                        <?php
                        $db = Database::getInstance()->db;
                        $total_visits = 0;
                        $result = $db->query("SELECT COUNT(*) AS total_visits FROM page_visits");
                        if ($result) {
                            $row = $result->fetch_assoc();
                            $total_visits = $row['total_visits'];
                        } else {
                            error_log("Failed to fetch page visits: " . $db->error);
                            $total_visits = "N/A";
                        }
                        echo is_numeric($total_visits) ? number_format($total_visits) : $total_visits;
                        ?>
                    </span>
                </div>
                <p class="mb-2"><i class="bi bi-telephone"></i>+63 992 280 1253</p>
                <p class="mb-0"><i class="bi bi-geo-alt"></i>Rosales St., Tunga-Tunga, Maasin City, Southern Leyte 6600</p>
            </div>
        </div>
    </div>
</footer>

<!-- Keep your existing CDN links -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
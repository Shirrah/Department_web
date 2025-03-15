<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #ff5722;
            --primary-foreground: #ffffff;
            --background: #ffffff;
            --foreground: #ff5722;
        }
        body {
            background: var(--background);
            color: var(--foreground);
        }
        .navbar {
            background: var(--background);
            border-bottom: 2px solid var(--primary);
        }
        .btn-primary {
            background: var(--primary);
            border: none;
        }
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        .btn-outline-primary:hover {
            background: var(--primary);
            color: var(--primary-foreground);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg shadow-sm">
        <div class="container">
            <a class="navbar-brand text-primary fw-bold" href="#">CCS Manager</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link text-dark" href="#">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="#">Events</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="#">Fees</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="#">Reports</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="#">Help</a></li>
                </ul>
                <button class="btn btn-outline-primary mx-2">Sign In</button>
                <button class="btn btn-primary">Get Started</button>
            </div>
        </div>
    </nav>

    <section class="container text-center py-5">
        <h1 class="fw-bold text-primary">College of Computer Studies</h1>
        <p>Streamline event planning and fee management with our comprehensive system.</p>
        <button class="btn btn-primary me-2">Get Started</button>
        <button class="btn btn-outline-primary">Learn More</button>
    </section>

    <footer class="text-center py-3 bg-primary text-white">
        <p>&copy; 2025 College of Computer Studies. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

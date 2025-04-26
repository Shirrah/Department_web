<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>

<style>
    .bg-tomato {
        background-color: #ff6347 !important;
    }
    .btn-tomato {
        background-color: #ff6347;
        border-color: #ff6347;
        color: white;
    }
    .btn-tomato:hover {
        background-color: #e5533d;
        border-color: #e5533d;
    }
    .text-tomato {
        color: #ff6347;
    }
    .card {
        background-color: #2a2a2a;
        color: white;
    }
    .badge-custom {
        background-color: #ff6347;
        color: white;
    }
</style>
<title>CCS - Event and Fee Management System</title>
<div class="">
    <div class="container-fluid bg-dark py-5 text-white">
        <div class="row align-items-center" style="margin-top: 143px;">
            <!-- Left Content --> 
            <div class="col-md-6 text-start px-5">
                <h1 class="fw-bold text-tomato">College of Computer Studies</h1>
                <p class="lead">
                    Streamline event planning and fee management with our 
                    comprehensive system designed for the College of Computer Studies.
                </p>
                <div class="mt-4">
                    <a href="?content=log-in" class="btn btn-outline-light">Get Started</a>
                </div>
            </div>

            <!-- Right Image -->
            <div class="col-md-6 text-center px-5">
                <img src="./assets/images/ccslogo.png" alt="College of Computer Studies Logo" class="img-fluid" style="max-width: 80%;">
            </div>
        </div>
    </div>

    <div class="container-fluid bg-white py-5">
        <div class="container mt-5 text-center">
            <span class="badge badge-custom py-2 px-3">Features</span>
            <h2 class="fw-bold mt-3 text-tomato">All-in-One Management System</h2>
            <p class="text-muted">
                Our platform provides comprehensive tools for event planning, fee collection, and student management.
            </p>

            <div class="row mt-4">
                <!-- Event Management Card -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4">
                        <div class="d-flex justify-content-center">
                            <span class="bg-tomato p-3 text-white">
                                <i class="bi bi-calendar-event fs-3"></i>
                            </span>
                        </div>
                        <h5 class="fw-bold mt-3">Event Management</h5>
                        <p class="text-muted">
                            Schedule, organize, and manage department events with ease.
                        </p>
                    </div>
                </div>

                <!-- Fee Collection Card -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4">
                        <div class="d-flex justify-content-center">
                            <span class="bg-tomato p-3 text-white">
                                <i class="bi bi-credit-card fs-3"></i>
                            </span>
                        </div>
                        <h5 class="fw-bold mt-3">Fee Collection</h5>
                        <p class="text-muted">
                            Streamline fee collection and payment tracking for all students.
                        </p>
                    </div>
                </div>

                <!-- Student Portal Card -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4">
                        <div class="d-flex justify-content-center">
                            <span class="bg-tomato p-3 text-white">
                                <i class="bi bi-people fs-3"></i>
                            </span>
                        </div>
                        <h5 class="fw-bold mt-3">Student Portal</h5>
                        <p class="text-muted">
                            Give students access to their event registrations and payment history.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row align-items-center" style="margin-bottom: 100px;">
            <!-- Left Content -->
            <div class="col-md-6">
                <h2 class="fw-bold text-tomato">Fee Management Made Simple</h2>
                <p class="text-muted">
                    Our fee management system simplifies the collection and tracking of various fees for both administrators and students.
                </p>
                <ul class="list-unstyled">
                    <li class="d-flex align-items-center mb-2">
                        <i class="bi bi-file-earmark-text fs-5 me-2 text-tomato"></i> Transparent fee structure
                    </li>
                    <li class="d-flex align-items-center mb-2">
                        <i class="bi bi-calendar-event fs-5 me-2 text-tomato"></i> Payment reminders and schedules
                    </li>
                    <li class="d-flex align-items-center mb-2">
                        <i class="bi bi-people fs-5 me-2 text-tomato"></i> Individual student accounts
                    </li>
                </ul>
            </div>

<!-- Right Image -->
<div class="col-md-6">
    <div class="d-flex align-items-center justify-content-center feather-container">
        <div id="logoCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="./assets/images/SJC-LOGO-NEWER-1536x1024.png" class="d-block mx-auto logo-img" alt="Logo 1">
                </div>
                <div class="carousel-item">
                    <img src="./assets/images/ccslogo.png" class="d-block mx-auto logo-img" alt="Logo 2">
                </div>
                <div class="carousel-item">
                    <img src="./assets/images/sys-logo.png" class="d-block mx-auto logo-img" alt="Logo 3">
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Custom CSS for feather effect -->
<style>
    .logo-img {
        height: 300px;
    }
</style>

        </div>
    </div>
</div>

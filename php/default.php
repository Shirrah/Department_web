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
        transition: all 0.3s ease;
    }
    .btn-tomato:hover {
        background-color: #e5533d;
        border-color: #e5533d;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(255, 99, 71, 0.2);
    }
    .text-tomato {
        color: #ff6347;
    }
    .card {
        background-color: #2a2a2a;
        color: white;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    .badge-custom {
        background-color: #ff6347;
        color: white;
        border-radius: 20px;
        padding: 8px 16px;
        font-weight: 500;
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 2rem 1rem;
        }
        .container-fluid.bg-dark {
            padding-top: 100px !important; /* Increased padding for mobile */
            min-height: calc(100vh - 100px) !important; /* Adjust height to account for header */
        }
        .row {
            margin: 0;
        }
        .col-md-6 {
            padding: 1rem;
        }
        h1 {
            font-size: 2rem;
        }
        .lead {
            font-size: 1rem;
        }
    }

    /* Modern UI enhancements */
    .feature-icon {
        width: 70px;
        height: 70px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        transition: all 0.3s ease;
        background-color: #ff6347;
    }

    .feature-icon:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(255, 99, 71, 0.3);
    }

    .feature-icon i {
        font-size: 1.8rem;
    }

    .card {
        transition: all 0.3s ease;
        border: none;
        background-color: #2a2a2a;
        color: white;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .card .text-muted {
        color: #a0a0a0 !important;
    }

    .team-member-card {
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .team-member-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .team-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        transition: all 0.3s ease;
    }

    .team-avatar:hover {
        transform: scale(1.1);
    }

    /* Smooth scrolling */
    html {
        scroll-behavior: smooth;
    }

    /* Improved button styles */
    .btn {
        border-radius: 25px;
        padding: 10px 25px;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    .btn-outline-light {
        border-width: 2px;
        transition: all 0.3s ease;
    }

    .btn-outline-light:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(255, 255, 255, 0.2);
    }

    /* Add styles for team member images */
    .team-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    .team-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        margin: 0 auto;
        border: 3px solid #ff6347;
        transition: all 0.3s ease;
    }
    .team-avatar:hover {
        transform: scale(1.1);
        box-shadow: 0 0 15px rgba(255, 99, 71, 0.3);
    }

    /* Update social media icons styles */
    .social-links {
        margin-top: 15px;
    }
    .social-links a {
        color: #2a2a2a;
        font-size: 1.5rem;
        margin: 0 8px;
        transition: all 0.3s ease;
    }
    .social-links a:hover {
        color: #ff6347;
        transform: translateY(-3px);
    }

    /* Add padding for fixed header */
    .container-fluid.bg-dark {
        padding-top: 225px !important;
    }
</style>
<title>CCS - Event and Fee Management System</title>
<div class="">
    <div class="container-fluid bg-dark py-5 text-white">
        <div class="row align-items-center min-vh-100">
            <!-- Left Content --> 
            <div class="col-md-6 text-start px-5">
                <h1 class="fw-bold text-tomato display-4">College of Computer Studies</h1>
                <p class="lead my-4">
                    Streamline event planning and fee management with our 
                    comprehensive system designed for the College of Computer Studies.
                </p>
                <div class="mt-4">
                    <a href="?content=log-in" class="btn btn-outline-light btn-lg">Get Started</a>
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
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="feature-icon bg-tomato mb-3">
                            <i class="bi bi-calendar-event text-white fs-3"></i>
                        </div>
                        <h5 class="fw-bold mt-3">Event Management</h5>
                        <p class="text-muted">
                            Schedule, organize, and manage department events with ease.
                        </p>
                    </div>
                </div>

                <!-- Fee Collection Card -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="feature-icon bg-tomato mb-3">
                            <i class="bi bi-credit-card text-white fs-3"></i>
                        </div>
                        <h5 class="fw-bold mt-3">Fee Collection</h5>
                        <p class="text-muted">
                            Streamline fee collection and payment tracking for all students.
                        </p>
                    </div>
                </div>

                <!-- Student Portal Card -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="feature-icon bg-tomato mb-3">
                            <i class="bi bi-people text-white fs-3"></i>
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
    <div class="d-flex align-items-center justify-content-center">
        <div id="logoCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="logo-wrapper">
                        <img src="./assets/images/SJC-LOGO-NEWER-1536x1024.png" class="logo-img" alt="SJC Logo">
                        <div class="sweep"></div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="logo-wrapper">
                        <img src="./assets/images/ccslogo.png" class="logo-img" alt="CCS Logo">
                        <div class="sweep"></div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="logo-wrapper">
                        <img src="./assets/images/sys-logo.png" class="logo-img" alt="System Logo">
                        <div class="sweep"></div>
                    </div>
                </div>
            </div>
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#logoCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#logoCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#logoCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for carousel -->
<style>
    #logoCarousel {
        width: 100%;
        max-width: 500px;
    }

    .carousel-inner {
        border-radius: 10px;
        overflow: hidden;
    }

    .carousel-item {
        height: 340px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
    }

    .logo-wrapper {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .logo-img {
        max-height: 300px;
        max-width: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .carousel-item.active .logo-img {
        transform: scale(1.05);
    }

    .carousel-indicators {
        margin-bottom: 0;
    }

    .carousel-indicators button {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #ff6347;
        opacity: 0.5;
        margin: 0 5px;
    }

    .carousel-indicators button.active {
        opacity: 1;
    }

    .sweep {
        position: absolute;
        top: 0;
        left: -100%;
        width: 50%;
        height: 100%;
        background: linear-gradient(
            to right,
            rgba(255, 255, 255, 0) 0%,
            rgba(255, 255, 255, 0.3) 50%,
            rgba(255, 255, 255, 0) 100%
        );
        transform: skewX(-25deg);
        animation: sweep 4s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes sweep {
        0% {
            left: -100%;
        }
        50% {
            left: 200%;
        }
        100% {
            left: 200%;
        }
    }
</style>

        </div>
    </div>
</div>

<!-- Development Team Section -->
<div class="container-fluid bg-light py-5" id="developer-team">
    <div class="container">
        <div class="text-center">
            <span class="badge badge-custom py-2 px-3">Our Team</span>
            <h2 class="fw-bold mt-3 text-tomato">Development Team</h2>
            <p class="text-muted mb-5">
                Meet the talented individuals behind the CCS EFMS system
            </p>
        </div>

        <div class="row">
            <!-- Team Lead -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 team-member-card">
                    <div class="card-body text-center">
                        <div class="team-avatar mb-3">
                            <img src="./assets/images/devs/Orais, Harrish.jpg" alt="Harrish P. Orais" onerror="this.src='./assets/images/user.png'">
                        </div>
                        <h5 class="fw-bold">Harrish P. Orais</h5>
                        <p class="text-tomato mb-3">Lead Programmer</p>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Full Stack Development</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Team Coordination</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Code Review</li>
                        </ul>
                        <div class="social-links">
                            <a href="https://www.facebook.com/orais.harrish/" target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Member 2 -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 team-member-card">
                    <div class="card-body text-center">
                        <div class="team-avatar mb-3">
                            <img src="./assets/images/devs/Jaula, Janine Mhyles.jpg" alt="Janine Mhyles Jaula" onerror="this.src='./assets/images/user.png'">
                        </div>
                        <h5 class="fw-bold">Janine Mhyles Jaula</h5>
                        <p class="text-tomato mb-3">Developer</p>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Frontend Development</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>UI/UX Design</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>User Testing</li>
                        </ul>
                        <div class="social-links">
                            <a href="https://www.facebook.com/mhylesoyxsc" target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Member 3 -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 team-member-card">
                    <div class="card-body text-center">
                        <div class="team-avatar mb-3">
                            <img src="./assets/images/devs/Costillas, Christian.jpg" alt="Christian M. Costillas" onerror="this.src='./assets/images/user.png'">
                        </div>
                        <h5 class="fw-bold">Christian M. Costillas</h5>
                        <p class="text-tomato mb-3">Developer</p>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Backend Development</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Database Management</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>API Integration</li>
                        </ul>
                        <div class="social-links">
                            <a href="https://www.facebook.com/christiancling25" target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Member 4 -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 team-member-card">
                    <div class="card-body text-center">
                        <div class="team-avatar mb-3">
                            <img src="./assets/images/devs/ringcodo.jpg" alt="Angelica Ringcodo" onerror="this.src='./assets/images/user.png'">
                        </div>
                        <h5 class="fw-bold">Angelica Ringcodo</h5>
                        <p class="text-tomato mb-3">Developer</p>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Frontend Development</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Documentation</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Quality Assurance</li>
                        </ul>
                        <div class="social-links">
                            <a href="https://www.facebook.com/angelica.ringcodo" target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Member 5 -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 team-member-card">
                    <div class="card-body text-center">
                        <div class="team-avatar mb-3">
                            <img src="./assets/images/devs/Tangcuangco,Rey Ann, P.jpeg" alt="Rey Ann Maturan" onerror="this.src='./assets/images/user.png'">
                        </div>
                        <h5 class="fw-bold">Rey Ann Tangcuangco</h5>
                        <p class="text-tomato mb-3">Developer</p>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Backend Development</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>System Testing</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Bug Fixing</li>
                        </ul>
                        <div class="social-links">
                            <a href="https://www.facebook.com/TangcuangcoRey14" target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Member 6 -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 team-member-card">
                    <div class="card-body text-center">
                        <div class="team-avatar mb-3">
                            <img src="./assets/images/devs/Golpe, Dave Brian.jpeg" alt="Dave Brian Golpe" onerror="this.src='./assets/images/user.png'">
                        </div>
                        <h5 class="fw-bold">Dave Brian Golpe</h5>
                        <p class="text-tomato mb-3">Developer</p>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Frontend Development</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>User Interface</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Responsive Design</li>
                        </ul>
                        <div class="social-links">
                            <a href="https://www.facebook.com/davebrian.golpe9" target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Member 7 -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 team-member-card">
                    <div class="card-body text-center">
                        <div class="team-avatar bg-tomato mb-3">
                            <span class="text-white fw-bold fs-4">IM</span>
                        </div>
                        <h5 class="fw-bold">Ian Kyle Maturan</h5>
                        <p class="text-tomato mb-3">Developer</p>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Backend Development</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>Database Design</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-tomato me-2"></i>System Security</li>
                        </ul>
                        <div class="social-links">
                            <a href="https://www.facebook.com/IanKyleee" target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer Note -->
<div class="container text-center py-4">
    <p class="text-muted mb-0">
        This project was developed as part of our Bachelor of Science in Information Technology capstone requirements at Saint Joseph College
    </p>
</div>

<!-- Custom CSS for feather effect -->
<style>
    .logo-img {
        height: 300px;
    }
    
    .card {
        transition: transform 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
</style>

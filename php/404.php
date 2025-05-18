<!DOCTYPE html>
<div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 100vh; padding-top: 80px;">
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Animated SVG -->
                <div class="mb-4">
                    <svg width="200" height="200" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                        <!-- Lost Character -->
                        <circle cx="100" cy="100" r="50" fill="#FFD93D" class="bounce">
                            <animate attributeName="cy" values="100;90;100" dur="1s" repeatCount="indefinite"/>
                        </circle>
                        <!-- Eyes -->
                        <circle cx="80" cy="90" r="5" fill="#333"/>
                        <circle cx="120" cy="90" r="5" fill="#333"/>
                        <!-- Mouth -->
                        <path d="M85 110 Q100 120 115 110" stroke="#333" stroke-width="3" fill="none">
                            <animate attributeName="d" 
                                values="M85 110 Q100 120 115 110;M85 115 Q100 105 115 115;M85 110 Q100 120 115 110" 
                                dur="2s" 
                                repeatCount="indefinite"/>
                        </path>
                        <!-- Question Mark -->
                        <text x="100" y="160" text-anchor="middle" font-size="24" fill="#333" class="rotate">
                            ?
                        </text>
                        <!-- Map -->
                        <path d="M40 40 L160 40 L160 160 L40 160 Z" stroke="#333" stroke-width="2" fill="none" stroke-dasharray="5,5">
                            <animate attributeName="stroke-dashoffset" values="0;100" dur="3s" repeatCount="indefinite"/>
                        </path>
                    </svg>
                </div>
                <h1 class="display-1 text-danger">404</h1>
                <h2 class="mb-4">Page Not Found</h2>
                <p class="lead mb-4">Oops! The page you're looking for doesn't exist or has been moved.</p>
                <p class="text-muted mb-4">Redirecting to home page in <span id="countdown">10</span> seconds...</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-house-door"></i> Go Home
                    </a>
                    <button onclick="history.back()" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

@keyframes rotate {
    0% { transform: rotate(0deg); }
    25% { transform: rotate(10deg); }
    75% { transform: rotate(-10deg); }
    100% { transform: rotate(0deg); }
}

.bounce {
    animation: bounce 1s infinite;
}

.rotate {
    animation: rotate 2s infinite;
}
</style>

<script>
let timeLeft = 10;
const countdownElement = document.getElementById('countdown');

const countdown = setInterval(() => {
    timeLeft--;
    countdownElement.textContent = timeLeft;
    
    if (timeLeft <= 0) {
        clearInterval(countdown);
        window.location.href = 'index.php';
    }
}, 1000);
</script> 
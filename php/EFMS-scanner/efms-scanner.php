<?php
include "././php/auth-check.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EFMS-SCAN | Attendance QR Scanner</title>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./php/EFMS-scanner/style.css">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script> <!-- Replace with your FA kit if needed -->
</head>
<body class="bg-light text-dark">

<div class="container py-4">
<header class="bg-danger text-white p-3 rounded-top shadow-sm position-relative">
    <div class="d-flex justify-content-center align-items-center flex-column">
        <div class="fs-3 fw-bold">EFMS-SCAN</div>
        <div class="fs-6">Attendance QR Code Scanner</div>
    </div>
    <a href="?content=log-out" class="btn btn-outline-light btn-sm position-absolute top-0 end-0 m-3">
        <i class="fas fa-sign-out-alt me-1"></i> Logout
    </a>
</header>


    <!-- Scanner -->
    <div class="scanner-container bg-white rounded-bottom p-4 shadow-sm mb-4">
        <div class="video-container mx-auto mb-3 position-relative border border-danger rounded overflow-hidden" style="max-width: 400px; aspect-ratio: 1;">
            <video id="video" class="w-100 h-100 object-fit-cover" playsinline></video>
            <div class="scan-frame position-absolute top-50 start-50 translate-middle border border-danger rounded"></div>
        </div>

        <div class="controls d-flex flex-column gap-2 mx-auto" style="max-width: 400px;">
            <button id="scan-button" class="btn btn-danger fw-bold">Start Scanner</button>
        </div>
    </div>

    <!-- Status -->
    <div class="status-container bg-white rounded p-3 shadow-sm mb-4 border-start border-4 border-danger">
        <div class="d-flex align-items-center mb-2 text-danger fw-bold">
            <div id="status-icon" class="status-icon rounded-circle me-2" style="width: 12px; height: 12px;"></div>
            <span>System Status</span>
        </div>
        <div id="status-message" class="small">Initializing scanner...</div>
    </div>

    <!-- Results -->
    <div class="results-container bg-white rounded p-3 shadow-sm">
        <div class="results-title d-flex justify-content-between align-items-center mb-3 text-danger border-bottom pb-2">
            <span class="fw-bold">Scan Results</span>
            <span id="scan-count" class="badge bg-danger">0 scans</span>
        </div>
        <div id="results">
            <div class="empty-results text-center text-muted py-4 fst-italic">No scans yet. Start the scanner to begin.</div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center text-muted small mt-4">
        EFMS-SCAN v2.0 | &copy; 2023
    </footer>
</div>

<style>
    .bg-danger {
        background-color: #FF6347 !important;
    }
    .border-danger {
        border-color: #FF6347 !important;
    }
    .text-danger {
        color: #FF6347 !important;
    }
    .btn-danger {
        background-color: #FF6347 !important;
        border-color: #FF6347 !important;
    }
    .btn-danger:hover {
        background-color: #e5533d !important;
        border-color: #e5533d !important;
    }
</style>


<!-- Bootstrap JS (optional if you use Bootstrap components like modals, toasts) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

    <audio id="scan-audio" preload="auto" style="display:none">
        <source src="./assets/sounds/beep-07a.mp3" type="audio/mpeg">
    </audio>

    <script>
        // Configuration
        const SCAN_DELAY = 1000;
        
        // App state
        let scanning = false;
        let stream = null;
        let scanCooldown = false;
        let scanSound = null;
        let scanCount = 0;
        
        // Get attendance ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const attendanceId = urlParams.get('attendance_id');
        
        // DOM elements
        const video = document.getElementById("video");
        const scanButton = document.getElementById("scan-button");
        const statusIcon = document.getElementById("status-icon");
        const statusMessage = document.getElementById("status-message");
        const resultsContainer = document.getElementById("results");
        const scanCountElement = document.getElementById("scan-count");

        // Extract student ID from scanned data
        function extractStudentId(scanData) {
            return scanData; // Return the raw scan data as-is
        }

        // Process scan data
        async function processScan(scanData) {
            const idStudent = scanData;
            
            const scan = {
                studentId: idStudent,
                rawData: scanData,
                attendanceId: attendanceId,
                timestamp: Date.now(),
                status: "pending"
            };
            
            scanCount++;
            updateScanCount();
            addResultToUI(scan);
            
            if (navigator.onLine) {
                await syncScan(scan);
            }
            
            return scan;
        }

        // Sync scan with server
        async function syncScan(scan) {
            updateStatus("Syncing scan with server...", "online");
            
            try {
                const response = await fetch('./php/EFMS-scanner/efms-sync.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_student: scan.studentId,
                        id_attendance: scan.attendanceId || attendanceId
                    })
                });

                const result = await response.json();
                
                if (response.ok && result.success) {
                    updateScanInUI(scan.timestamp, "synced");
                    updateStatus("Sync complete", "online");
                    return true;
                } else {
                    updateScanInUI(scan.timestamp, "error", result.error || "Server rejected the scan");
                    updateStatus("Sync failed: " + (result.error || "Server error"), "offline");
                    return false;
                }
            } catch (error) {
                console.error("Sync error:", error);
                updateScanInUI(scan.timestamp, "error", error.message);
                updateStatus("Sync failed: " + error.message, "offline");
                return false;
            }
        }

        // Setup QR code scanner
        function setupScanner() {
            scanButton.addEventListener("click", async () => {
                if (scanning) {
                    stopScanner();
                    return;
                }
                
                if (!attendanceId) {
                    updateStatus("Error: No attendance ID specified", "offline");
                    return;
                }
                
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { facingMode: "environment" } 
                    });
                    video.srcObject = stream;
                    await video.play();
                    
                    scanButton.textContent = "Stop Scanner";
                    scanButton.classList.add("btn-stop");
                    scanning = true;
                    updateStatus("Scanner active - point at QR code", "online");
                    
                    const canvas = document.createElement("canvas");
                    const context = canvas.getContext("2d");
                    
                    function scanFrame() {
                        if (!scanning) return;
                        
                        if (video.readyState === video.HAVE_ENOUGH_DATA) {
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            context.drawImage(video, 0, 0, canvas.width, canvas.height);
                            
                            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                            const code = jsQR(imageData.data, imageData.width, imageData.height);
                            
                            if (code && !scanCooldown) {
                                scanCooldown = true;
                                scanButton.classList.add("btn-disabled");
                                
                                const scannedData = code.data;
                                updateStatus(`Data detected: ${scannedData}`, "online");
                                playScanSound();
                                
                                processScan(scannedData)
                                    .then(() => {
                                        if (navigator.onLine) {
                                            updateStatus(`Processing: ${scannedData}`, "online");
                                        } else {
                                            updateStatus(`Processed (offline): ${scannedData}`, "offline");
                                        }
                                    })
                                    .catch(e => {
                                        updateStatus("Scan processing failed", "offline");
                                        console.error("Scan error:", e);
                                    });
                                
                                setTimeout(() => {
                                    scanCooldown = false;
                                    scanButton.classList.remove("btn-disabled");
                                    updateStatus("Ready to scan", "online");
                                }, SCAN_DELAY);
                            }
                        }
                        requestAnimationFrame(scanFrame);
                    }
                    scanFrame();
                } catch (err) {
                    console.error("Scanner error:", err);
                    updateStatus("Camera access denied", "offline");
                    stopScanner();
                }
            });
        }

        // Stop the scanner
        function stopScanner() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            scanning = false;
            scanCooldown = false;
            scanButton.textContent = "Start Scanner";
            scanButton.classList.remove("btn-stop", "btn-disabled");
            updateStatus("Scanner stopped", navigator.onLine ? "online" : "offline");
        }

        // Add scan result to UI
        function addResultToUI(scan) {
            const emptyMessage = resultsContainer.querySelector(".empty-results");
            if (emptyMessage) {
                resultsContainer.removeChild(emptyMessage);
            }
            
            const resultElement = document.createElement("div");
            resultElement.className = "scan-result";
            resultElement.id = `scan-${scan.timestamp}`;
            resultElement.innerHTML = `
                <div class="scan-data">
                    <span class="student-id">${scan.studentId}</span>
                    <span class="scan-status status-${scan.status}">
                        ${scan.status === "synced" ? "Synced" : 
                          scan.status === "error" ? "Error" : "Pending"}
                    </span>
                </div>
                <div class="scan-time">${new Date(scan.timestamp).toLocaleString()}</div>
                ${scan.error ? `<div class="scan-error" style="color:var(--danger-color);font-size:12px;margin-top:5px;">${scan.error}</div>` : ''}
            `;
            
            resultsContainer.prepend(resultElement);
        }

        // Update existing scan in UI
        function updateScanInUI(scanId, status, error = null) {
            const resultElement = document.getElementById(`scan-${scanId}`);
            if (resultElement) {
                const statusElement = resultElement.querySelector(".scan-status");
                statusElement.className = `scan-status status-${status}`;
                statusElement.textContent = status === "synced" ? "Synced" : 
                                          status === "error" ? "Error" : "Pending";
                
                if (error) {
                    let errorElement = resultElement.querySelector(".scan-error");
                    if (!errorElement) {
                        errorElement = document.createElement("div");
                        errorElement.className = "scan-error";
                        errorElement.style = "color:var(--danger-color);font-size:12px;margin-top:5px;";
                        resultElement.appendChild(errorElement);
                    }
                    errorElement.textContent = error;
                }
            }
        }

        // Update scan count display
        function updateScanCount() {
            scanCountElement.textContent = `${scanCount} scan${scanCount !== 1 ? 's' : ''}`;
        }

        // Update status UI
        function updateStatus(message, status) {
            statusMessage.textContent = message;
            statusIcon.className = `status-icon ${status}-icon`;
        }

        // Monitor network connection
        function monitorConnection() {
            function updateConnectionStatus() {
                if (navigator.onLine) {
                    updateStatus("Online - ready to scan", "online");
                } else {
                    updateStatus("Offline - scans will sync when back online", "offline");
                }
            }
            
            updateConnectionStatus();
            window.addEventListener("online", updateConnectionStatus);
            window.addEventListener("offline", updateConnectionStatus);
        }

        // Initialize scan sound
        function initScanSound() {
            const audioElement = document.getElementById("scan-audio");
            if (audioElement) {
                scanSound = audioElement;
            } else {
                scanSound = new Audio();
                scanSound.src = "././assets/sounds/beep-07a.mp3";
                scanSound.preload = "auto";
            }
        }

        // Play scan sound
        function playScanSound() {
            if (!scanSound) initScanSound();
            try {
                scanSound.currentTime = 0;
                scanSound.play().catch(e => console.log("Audio play blocked:", e));
            } catch (e) {
                console.log("Audio play failed:", e);
            }
        }

        // Initialize app
        async function init() {
            try {
                if (!attendanceId) {
                    throw new Error("No attendance ID specified in URL");
                }
                
                initScanSound();
                setupScanner();
                monitorConnection();
                updateStatus("Ready to scan", navigator.onLine ? "online" : "offline");
            } catch (error) {
                console.error("Initialization error:", error);
                updateStatus(`Initialization failed: ${error.message}`, "offline");
                scanButton.disabled = true;
            }
        }

        // Start the app
        window.addEventListener("DOMContentLoaded", init);
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EFMS-SCAN | Attendance QR Scanner</title>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <link rel="stylesheet" href="./php/EFMS-scanner/style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">EFMS-SCAN</div>
            <div class="tagline">Attendance QR Code Scanner</div>
        </header>
        
        <div class="scanner-container">
            <div class="video-container">
                <video id="video" playsinline></video>
                <div class="scan-frame"></div>
            </div>
            
            <div class="controls">
                <button id="scan-button" class="btn">Start Scanner</button>
                <button id="sync-button" class="btn btn-sync" style="display:none">Sync Pending Scans</button>
            </div>
        </div>
        
        <div class="status-container">
            <div class="status-title">
                <div id="status-icon" class="status-icon"></div>
                <span>System Status</span>
            </div>
            <div id="status-message" class="status-message">Initializing scanner...</div>
            <div id="database-status" class="database-status" style="font-size:12px;margin-top:5px;"></div>
        </div>
        
        <div class="results-container">
            <div class="results-title">
                <span>Scan Results</span>
                <span id="scan-count" class="badge">0 scans</span>
            </div>
            <div id="results">
                <div class="empty-results">No scans yet. Start the scanner to begin.</div>
            </div>
        </div>
        
        <footer>
            EFMS-SCAN v2.0 | &copy; 2023
        </footer>
    </div>

    <audio id="scan-audio" preload="auto" style="display:none">
        <source src="./assets/sounds/beep-07a.mp3" type="audio/mpeg">
    </audio>

    <script>
        // Configuration
        const SCAN_DELAY = 1000;
        const DB_CHECK_INTERVAL = 5000; // Check database connection every 5 seconds
        const SYNC_INTERVAL = 30000; // Attempt to sync pending scans every 30 seconds
        
        // App state
        let scanning = false;
        let stream = null;
        let scanCooldown = false;
        let scanSound = null;
        let scanCount = 0;
        let isDatabaseOnline = false;
        let pendingScans = [];
        
        // Get attendance ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const attendanceId = urlParams.get('attendance_id');
        
        // DOM elements
        const video = document.getElementById("video");
        const scanButton = document.getElementById("scan-button");
        const syncButton = document.getElementById("sync-button");
        const statusIcon = document.getElementById("status-icon");
        const statusMessage = document.getElementById("status-message");
        const databaseStatus = document.getElementById("database-status");
        const resultsContainer = document.getElementById("results");
        const scanCountElement = document.getElementById("scan-count");

        // Local storage keys
        const STORAGE_KEY = `efms_pending_scans_${attendanceId}`;

        // Check database connection
        async function checkDatabaseConnection() {
            try {
                const response = await fetch('?check_connection=1');
                const result = await response.text();
                return result.trim() === 'connected';
            } catch (error) {
                return false;
            }
        }

        // Monitor database connection
        async function monitorDatabaseConnection() {
            isDatabaseOnline = await checkDatabaseConnection();
            updateDatabaseStatus();
            
            // Check periodically
            setInterval(async () => {
                isDatabaseOnline = await checkDatabaseConnection();
                updateDatabaseStatus();
                
                // If we just came online, attempt to sync
                if (isDatabaseOnline) {
                    syncPendingScans();
                }
            }, DB_CHECK_INTERVAL);
        }

        // Update database status display
        function updateDatabaseStatus() {
            if (isDatabaseOnline) {
                databaseStatus.textContent = "Database: Online";
                databaseStatus.style.color = "var(--success-color)";
                syncButton.style.display = "none";
            } else {
                databaseStatus.textContent = "Database: Offline (scans saved locally)";
                databaseStatus.style.color = "var(--danger-color)";
                syncButton.style.display = "inline-block";
            }
        }

        // Load pending scans from local storage
        function loadPendingScans() {
            const savedScans = localStorage.getItem(STORAGE_KEY);
            if (savedScans) {
                pendingScans = JSON.parse(savedScans);
                
                // Add pending scans to UI
                pendingScans.forEach(scan => {
                    if (!document.getElementById(`scan-${scan.timestamp}`)) {
                        addResultToUI(scan);
                        scanCount++;
                    }
                });
                
                updateScanCount();
                
                // If online, attempt to sync
                if (navigator.onLine && isDatabaseOnline) {
                    syncPendingScans();
                }
            }
        }

        // Save pending scans to local storage
        function savePendingScans() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(pendingScans));
        }

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
            
            if (navigator.onLine && isDatabaseOnline) {
                await syncScan(scan);
            } else {
                // Add to pending scans and save to local storage
                pendingScans.push(scan);
                savePendingScans();
                updateScanInUI(scan.timestamp, "pending");
                updateStatus("Scan saved locally (offline)", "offline");
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
                    
                    // Remove from pending scans if it was there
                    pendingScans = pendingScans.filter(s => s.timestamp !== scan.timestamp);
                    savePendingScans();
                    
                    return true;
                } else {
                    updateScanInUI(scan.timestamp, "error", result.error || "Server rejected the scan");
                    updateStatus("Sync failed: " + (result.error || "Server error"), "offline");
                    
                    // Add to pending scans if not already there
                    if (!pendingScans.some(s => s.timestamp === scan.timestamp)) {
                        pendingScans.push(scan);
                        savePendingScans();
                    }
                    
                    return false;
                }
            } catch (error) {
                console.error("Sync error:", error);
                updateScanInUI(scan.timestamp, "error", error.message);
                updateStatus("Sync failed: " + error.message, "offline");
                
                // Add to pending scans if not already there
                if (!pendingScans.some(s => s.timestamp === scan.timestamp)) {
                    pendingScans.push(scan);
                    savePendingScans();
                }
                
                return false;
            }
        }

        // Sync all pending scans
        async function syncPendingScans() {
            if (!navigator.onLine || !isDatabaseOnline || pendingScans.length === 0) {
                return;
            }
            
            updateStatus(`Syncing ${pendingScans.length} pending scans...`, "online");
            
            // Work with a copy in case sync fails
            const scansToSync = [...pendingScans];
            let successCount = 0;
            
            for (const scan of scansToSync) {
                try {
                    const success = await syncScan(scan);
                    if (success) {
                        successCount++;
                    }
                } catch (error) {
                    console.error("Error syncing scan:", error);
                }
            }
            
            updateStatus(`Synced ${successCount} of ${scansToSync.length} pending scans`, 
                        successCount === scansToSync.length ? "online" : "offline");
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
                                        if (navigator.onLine && isDatabaseOnline) {
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
                                    updateStatus("Ready to scan", navigator.onLine ? "online" : "offline");
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
            
            // Setup sync button
            syncButton.addEventListener("click", () => {
                if (pendingScans.length > 0) {
                    syncPendingScans();
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
                    if (isDatabaseOnline) {
                        syncPendingScans();
                    }
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
                await monitorDatabaseConnection();
                loadPendingScans();
                updateStatus("Ready to scan", navigator.onLine ? "online" : "offline");
                
                // Set up periodic sync
                setInterval(() => {
                    if (navigator.onLine && isDatabaseOnline && pendingScans.length > 0) {
                        syncPendingScans();
                    }
                }, SYNC_INTERVAL);
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EFMS-SCAN | Attendance QR Scanner</title>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <style>
        :root {
            --tomato-primary: #FF6347;
            --tomato-secondary: #E74C3C;
            --tomato-light: #FFA07A;
            --tomato-dark: #C0392B;
            --white: #FFFFFF;
            --light-gray: #F5F5F5;
            --dark-gray: #333333;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            line-height: 1.6;
        }
        
        .container {
            max-width: 100%;
            width: 100%;
            padding: 20px;
            margin: 0 auto;
        }
        
        header {
            background-color: var(--tomato-primary);
            color: var(--white);
            padding: 15px 0;
            text-align: center;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .tagline {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .scanner-container {
            background-color: var(--white);
            border-radius: 0 0 8px 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .video-container {
            position: relative;
            width: 100%;
            max-width: 400px;
            margin: 0 auto 20px;
            border: 3px solid var(--tomato-light);
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 1;
        }
        
        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        .scan-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70%;
            height: 70%;
            border: 3px solid var(--tomato-primary);
            border-radius: 8px;
            box-shadow: 0 0 0 1000px rgba(0, 0, 0, 0.5);
        }
        
        .controls {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .btn {
            background-color: var(--tomato-primary);
            color: var(--white);
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .btn:hover {
            background-color: var(--tomato-secondary);
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-stop {
            background-color: var(--tomato-dark);
        }
        
        .btn-disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
        
        .status-container {
            background-color: var(--white);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid var(--tomato-primary);
        }
        
        .status-title {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: var(--tomato-dark);
            font-weight: bold;
        }
        
        .status-icon {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .online-icon {
            background-color: var(--success-color);
        }
        
        .offline-icon {
            background-color: var(--danger-color);
        }
        
        .status-message {
            font-size: 14px;
        }
        
        .results-container {
            background-color: var(--white);
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .results-title {
            color: var(--tomato-dark);
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: bold;
            border-bottom: 2px solid var(--tomato-light);
            padding-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        #results {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 5px;
        }
        
        .scan-result {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            background-color: var(--light-gray);
            border-left: 3px solid var(--tomato-primary);
        }
        
        .scan-data {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .student-id {
            font-weight: bold;
            word-break: break-all;
        }
        
        .scan-status {
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 10px;
            color: white;
            font-weight: bold;
        }
        
        .status-pending {
            background-color: var(--warning-color);
        }
        
        .status-synced {
            background-color: var(--success-color);
        }
        
        .status-error {
            background-color: var(--danger-color);
        }
        
        .scan-time {
            color: #666;
            font-size: 12px;
        }
        
        .empty-results {
            color: #666;
            text-align: center;
            padding: 20px;
            font-style: italic;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            background-color: var(--tomato-light);
            color: var(--dark-gray);
        }
        
        footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 12px;
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            
            .video-container {
                max-width: 300px;
            }
            
            .controls {
                max-width: 300px;
            }
        }
    </style>
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
            </div>
        </div>
        
        <div class="status-container">
            <div class="status-title">
                <div id="status-icon" class="status-icon"></div>
                <span>System Status</span>
            </div>
            <div id="status-message" class="status-message">Initializing scanner...</div>
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
        <source src="../../assets/sounds/beep-07a.mp3" type="audio/mpeg">
    </audio>

    <script>
        // Configuration
        const DB_NAME = "EFMS_SCAN_DB";
        const STORE_NAME = "scans";
        const SYNC_INTERVAL = 30000;
        const SCAN_DELAY = 1000;
        const MAX_SCAN_HISTORY = 20;
        
        // App state
        let db;
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

        // Initialize IndexedDB
        async function initDB() {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open(DB_NAME, 2); // Version 2 for schema updates
                
                request.onupgradeneeded = (event) => {
                    const db = event.target.result;
                    if (!db.objectStoreNames.contains(STORE_NAME)) {
                        const store = db.createObjectStore(STORE_NAME, { keyPath: "id", autoIncrement: true });
                        store.createIndex("synced", "synced", { unique: false });
                        store.createIndex("timestamp", "timestamp", { unique: false });
                        store.createIndex("attendanceId", "attendanceId", { unique: false });
                    }
                };
                
                request.onsuccess = (event) => {
                    db = event.target.result;
                    updateStatus("Database initialized", "online");
                    resolve(db);
                };
                
                request.onerror = (event) => {
                    updateStatus("Database error", "offline");
                    reject(event.target.error);
                };
            });
        }

        // Extract student ID from scanned data
        function extractStudentId(scanData) {
            // Try to parse as JSON
            try {
                const jsonData = JSON.parse(scanData);
                if (jsonData.id_student) {
                    return jsonData.id_student;
                }
            } catch (e) {
                // If not JSON, check if it matches student ID pattern (adjust regex as needed)
                const studentIdPattern = /^[A-Za-z0-9\-_]{5,20}$/;
                if (studentIdPattern.test(scanData.trim())) {
                    return scanData.trim();
                }
            }
            return null;
        }

        // Save scan to local database
        async function saveScan(scanData) {
            if (!db) await initDB();
            
            const idStudent = extractStudentId(scanData);
            if (!idStudent) {
                updateStatus("Invalid student ID format", "offline");
                return Promise.reject("Invalid student ID");
            }
            
            return new Promise((resolve, reject) => {
                const transaction = db.transaction(STORE_NAME, "readwrite");
                const store = transaction.objectStore(STORE_NAME);
                
                const scan = {
                    studentId: idStudent,
                    rawData: scanData,
                    attendanceId: attendanceId,
                    timestamp: Date.now(),
                    synced: false,
                    status: "pending"
                };
                
                const request = store.add(scan);
                
                request.onsuccess = () => {
                    scanCount++;
                    updateScanCount();
                    addResultToUI(scan);
                    if (navigator.onLine) {
                        syncScans();
                    }
                    resolve(scan);
                };
                
                request.onerror = (event) => {
                    console.error("Error saving scan:", event.target.error);
                    updateStatus("Scan save failed", "offline");
                    reject(event.target.error);
                };
            });
        }

        // Sync scans with server
        async function syncScans() {
            if (!db) await initDB();
            if (!navigator.onLine) {
                updateStatus("Offline - scans will sync when back online", "offline");
                return;
            }

            updateStatus("Syncing scans with server...", "online");
            
            const transaction = db.transaction(STORE_NAME, "readwrite");
            const store = transaction.objectStore(STORE_NAME);
            const index = store.index("synced");
            const request = index.openCursor(IDBKeyRange.only(false));

            request.onsuccess = async (event) => {
                const cursor = event.target.result;
                if (cursor) {
                    const scan = cursor.value;
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
                            await cursor.update({ 
                                ...scan, 
                                synced: true,
                                status: "synced",
                                syncTime: Date.now()
                            });
                            updateScanInUI(scan.id, "synced");
                        } else {
                            await cursor.update({ 
                                ...scan, 
                                status: "error",
                                error: result.error || "Server rejected the scan"
                            });
                            updateScanInUI(scan.id, "error", result.error);
                            console.error("Sync failed:", result.error);
                        }
                        cursor.continue();
                    } catch (error) {
                        console.error("Sync error:", error);
                        await cursor.update({ 
                            ...scan, 
                            status: "error",
                            error: error.message
                        });
                        updateScanInUI(scan.id, "error", error.message);
                        cursor.continue();
                    }
                } else {
                    updateStatus("Sync complete", "online");
                }
            };
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
                                
                                const idStudent = extractStudentId(code.data);
                                if (idStudent) {
                                    updateStatus(`Student detected: ${idStudent}`, "online");
                                    playScanSound();
                                    saveScan(code.data)
                                        .then(() => {
                                            if (navigator.onLine) {
                                                updateStatus(`Saved & syncing: ${idStudent}`, "online");
                                            } else {
                                                updateStatus(`Saved (offline): ${idStudent}`, "offline");
                                            }
                                        })
                                        .catch(e => {
                                            updateStatus("Scan failed", "offline");
                                            console.error("Scan error:", e);
                                        });
                                } else {
                                    updateStatus("Invalid student ID", "offline");
                                }
                                
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
            resultElement.id = `scan-${scan.id}`;
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

        // Load previous scans from database
        async function loadPreviousScans() {
            if (!db) await initDB();
            
            const transaction = db.transaction(STORE_NAME, "readonly");
            const store = transaction.objectStore(STORE_NAME);
            const request = store.openCursor(null, "prev");
            
            request.onsuccess = (event) => {
                const cursor = event.target.result;
                if (cursor) {
                    if (scanCount < MAX_SCAN_HISTORY) {
                        addResultToUI(cursor.value);
                        scanCount++;
                        updateScanCount();
                    }
                    cursor.continue();
                } else if (scanCount === 0) {
                    resultsContainer.innerHTML = '<div class="empty-results">No scans yet. Start the scanner to begin.</div>';
                }
            };
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
                    syncScans();
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
                scanSound.src = "../../assets/sounds/beep-07a.mp3";
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
                await initDB();
                setupScanner();
                monitorConnection();
                await loadPreviousScans();
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
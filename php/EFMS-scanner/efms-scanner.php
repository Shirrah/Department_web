<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EFMS-SCAN | Offline QR Scanner</title>
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
            max-width: 500px;
            margin: 0 auto 20px;
            border: 3px solid var(--tomato-light);
            border-radius: 8px;
            overflow: hidden;
        }
        
        #video {
            width: 100%;
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
            max-width: 500px;
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
            background-color: #2ECC71;
        }
        
        .offline-icon {
            background-color: #E74C3C;
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
        }
        
        .scan-result {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            background-color: var(--light-gray);
            border-left: 3px solid var(--tomato-primary);
            display: flex;
            justify-content: space-between;
        }
        
        .scan-data {
            font-weight: bold;
            word-break: break-all;
        }
        
        .scan-time {
            color: #666;
            font-size: 12px;
            white-space: nowrap;
            margin-left: 10px;
        }
        
        .empty-results {
            color: #666;
            text-align: center;
            padding: 20px;
            font-style: italic;
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
            
            .scan-result {
                flex-direction: column;
            }
            
            .scan-time {
                margin-left: 0;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">EFMS-SCAN</div>
            <div class="tagline">Offline QR Code Scanner with Cloud Sync</div>
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
            <div id="status-message" class="status-message">Initializing EFMS-SCAN...</div>
        </div>
        
        <div class="results-container">
            <div class="results-title">Scan Results</div>
            <div id="results">
                <div class="empty-results">No scans yet. Start the scanner to begin.</div>
            </div>
        </div>
        
        <footer>
            EFMS-SCAN v1.0 | &copy; 2023 Tomato Technologies
        </footer>
    </div>

    <script>
        // Database configuration
        const DB_NAME = "EFMS_SCAN_DB";
        const STORE_NAME = "scans";
        const SYNC_INTERVAL = 30000; // 30 seconds
        const SCAN_DELAY = 1000; // 1 second delay after successful scan
        
        // App state
        let db;
        let scanning = false;
        let stream = null;
        let scanCooldown = false;
        
        // DOM elements
        const video = document.getElementById("video");
        const scanButton = document.getElementById("scan-button");
        const statusIcon = document.getElementById("status-icon");
        const statusMessage = document.getElementById("status-message");
        const resultsContainer = document.getElementById("results");
        
        // Initialize IndexedDB
        async function initDB() {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open(DB_NAME, 1);
                
                request.onupgradeneeded = (event) => {
                    const db = event.target.result;
                    if (!db.objectStoreNames.contains(STORE_NAME)) {
                        const store = db.createObjectStore(STORE_NAME, { keyPath: "id", autoIncrement: true });
                        store.createIndex("synced", "synced", { unique: false });
                        store.createIndex("timestamp", "timestamp", { unique: false });
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
        
        // Save scan to local database
        async function saveScan(data) {
            if (!db) await initDB();
            
            return new Promise((resolve, reject) => {
                const transaction = db.transaction(STORE_NAME, "readwrite");
                const store = transaction.objectStore(STORE_NAME);
                
                const scan = {
                    data: data,
                    timestamp: Date.now(),
                    synced: false
                };
                
                const request = store.add(scan);
                
                request.onsuccess = () => {
                    addResultToUI(scan);
                    if (navigator.onLine) {
                        syncScans();
                    }
                    resolve();
                };
                
                request.onerror = (event) => {
                    console.error("Error saving scan:", event.target.error);
                    reject(event.target.error);
                };
            });
        }
        
        // Sync scans with server when online
        async function syncScans() {
            if (!db) await initDB();
            if (!navigator.onLine) return;
            
            const transaction = db.transaction(STORE_NAME, "readwrite");
            const store = transaction.objectStore(STORE_NAME);
            const index = store.index("synced");
            const request = index.openCursor(IDBKeyRange.only(false));
            
            request.onsuccess = (event) => {
                const cursor = event.target.result;
                if (cursor) {
                    const scan = cursor.value;
                    
                    // In a real app, you would send this to your server
                    // Example: await fetch('/api/scans', { method: 'POST', body: JSON.stringify(scan) });
                    
                    // Mock server sync with delay
                    setTimeout(() => {
                        scan.synced = true;
                        cursor.update(scan);
                        updateStatus(`Synced: ${scan.data.substring(0, 30)}${scan.data.length > 30 ? '...' : ''}`, "online");
                    }, 500);
                    
                    cursor.continue();
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
                    
                    // Create canvas for QR detection
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
    // Enter cooldown period
    scanCooldown = true;
    scanButton.classList.add("btn-disabled");
    
    // Process the scan
    updateStatus(`QR Code detected`, "online");
    playBeep();  // This is where the beep is triggered
    saveScan(code.data);
    
    // Set timeout to exit cooldown
    setTimeout(() => {
        scanCooldown = false;
        scanButton.classList.remove("btn-disabled");
        updateStatus("Ready to scan again", "online");
    }, SCAN_DELAY);
}
                        }
                        
                        requestAnimationFrame(scanFrame);
                    }
                    
                    scanFrame();
                } catch (err) {
                    console.error("Error starting scanner:", err);
                    updateStatus("Camera access denied", "offline");
                    scanButton.textContent = "Start Scanner";
                    scanButton.classList.remove("btn-stop");
                    scanning = false;
                }
            });
        }
        
        // Stop the scanner
        function stopScanner() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            scanButton.textContent = "Start Scanner";
            scanButton.classList.remove("btn-stop", "btn-disabled");
            scanning = false;
            scanCooldown = false;
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
            resultElement.innerHTML = `
                <div class="scan-data">${scan.data.substring(0, 50)}${scan.data.length > 50 ? '...' : ''}</div>
                <div class="scan-time">${new Date(scan.timestamp).toLocaleString()}</div>
            `;
            
            resultsContainer.prepend(resultElement);
        }
        
        // Load previous scans from database
        async function loadPreviousScans() {
            if (!db) await initDB();
            
            const transaction = db.transaction(STORE_NAME, "readonly");
            const store = transaction.objectStore(STORE_NAME);
            const request = store.openCursor(null, "prev");
            let count = 0;
            
            request.onsuccess = (event) => {
                const cursor = event.target.result;
                if (cursor) {
                    if (count < 10) { // Only show last 10 scans
                        addResultToUI(cursor.value);
                        count++;
                    }
                    cursor.continue();
                } else if (count === 0) {
                    // No scans found
                    resultsContainer.innerHTML = '<div class="empty-results">No scans yet. Start the scanner to begin.</div>';
                }
            };
        }
        
        // Update status UI
        function updateStatus(message, status) {
            statusMessage.textContent = message;
            
            if (status === "online") {
                statusIcon.className = "status-icon online-icon";
            } else {
                statusIcon.className = "status-icon offline-icon";
            }
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
            
            // Initial check
            updateConnectionStatus();
            
            // Event listeners
            window.addEventListener("online", updateConnectionStatus);
            window.addEventListener("offline", updateConnectionStatus);
            
            // Ping system
            setInterval(async () => {
                try {
                    await fetch("https://www.google.com/favicon.ico", { 
                        method: "HEAD",
                        cache: "no-cache",
                        mode: "no-cors"
                    });
                    if (!navigator.onLine) {
                        updateConnectionStatus();
                    }
                } catch (e) {
                    if (navigator.onLine) {
                        updateConnectionStatus();
                    }
                }
            }, SYNC_INTERVAL);
        }
        
        // Improved beep function with preloaded sound
        let beepSound = null;
        
        function initBeep() {
    // Create audio context only when needed
    if (typeof AudioContext !== 'undefined' || typeof webkitAudioContext !== 'undefined') {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        const ctx = new AudioContext();
        
        return {
            play: function() {
                // Create oscillator and gain node for each beep to ensure clean start/stop
                const oscillator = ctx.createOscillator();
                const gainNode = ctx.createGain();
                
                oscillator.type = "sine";
                oscillator.frequency.value = 800;
                oscillator.connect(gainNode);
                gainNode.connect(ctx.destination);
                
                // Configure the beep to last 1 second
                const now = ctx.currentTime;
                gainNode.gain.setValueAtTime(0, now);
                gainNode.gain.linearRampToValueAtTime(1, now + 0.01); // quick fade in
                gainNode.gain.setValueAtTime(1, now + 0.01);
                gainNode.gain.linearRampToValueAtTime(0, now + 1); // fade out over 1s
                
                oscillator.start(now);
                oscillator.stop(now + 1); // stop after 1 second
            }
        };
    }
    
    return {
        play: function() {
            // Fallback for browsers without Web Audio API
            console.log("Beep!");
        }
    };
}
        
        function playBeep() {
            if (!beepSound) {
                beepSound = initBeep();
            }
            beepSound.play();
        }
        
        // Initialize app
        async function init() {
            try {
                await initDB();
                setupScanner();
                monitorConnection();
                loadPreviousScans();
                updateStatus("EFMS-SCAN ready", navigator.onLine ? "online" : "offline");
            } catch (error) {
                console.error("Initialization error:", error);
                updateStatus("Initialization failed", "offline");
            }
        }
        
        // Start the app
        window.addEventListener("DOMContentLoaded", init);
    </script>
</body>
</html>
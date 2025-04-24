<!DOCTYPE html>
<html>
<head>
    <title>Offline QR Scanner</title>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <style>
        #scanner-container { position: relative; width: 100%; max-width: 500px; }
        #video { width: 100%; }
        #scan-button { margin: 10px; padding: 10px; }
        #status { margin: 10px; padding: 10px; background: #f0f0f0; }
        .offline { background: #ffcccc; }
        .online { background: #ccffcc; }
    </style>
</head>
<body>
    <div id="scanner-container">
        <video id="video" playsinline></video>
        <button id="scan-button">Start Scanner</button>
        <div id="status">Status: Loading...</div>
        <div id="results"></div>
    </div>

    <script>
        // Database for offline storage
        let db;
        const DB_NAME = "QRScannerDB";
        const STORE_NAME = "scans";
        
        // Initialize database
        function initDB() {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open(DB_NAME, 1);
                
                request.onupgradeneeded = (event) => {
                    db = event.target.result;
                    if (!db.objectStoreNames.contains(STORE_NAME)) {
                        db.createObjectStore(STORE_NAME, { keyPath: "timestamp" });
                    }
                };
                
                request.onsuccess = (event) => {
                    db = event.target.result;
                    resolve(db);
                };
                
                request.onerror = (event) => {
                    console.error("Database error:", event.target.error);
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
                    console.log("Scan saved locally");
                    resolve();
                };
                
                request.onerror = (event) => {
                    console.error("Error saving scan:", event.target.error);
                    reject(event.target.error);
                };
            });
        }
        
        // Try to sync scans with server
        async function syncScans() {
            if (!db) await initDB();
            if (!navigator.onLine) return;
            
            const transaction = db.transaction(STORE_NAME, "readwrite");
            const store = transaction.objectStore(STORE_NAME);
            const unsynced = store.index("synced").openCursor(IDBKeyRange.only(false));
            
            unsynced.onsuccess = (event) => {
                const cursor = event.target.result;
                if (cursor) {
                    const scan = cursor.value;
                    
                    // Here you would typically make a fetch() to your server
                    console.log("Syncing scan:", scan.data);
                    
                    // Mock server sync
                    setTimeout(() => {
                        // Mark as synced
                        scan.synced = true;
                        cursor.update(scan);
                        updateStatus(`Synced: ${scan.data}`);
                    }, 500);
                    
                    cursor.continue();
                }
            };
        }
        
        // Connection monitoring
        function monitorConnection() {
            const statusElement = document.getElementById("status");
            
            function updateConnectionStatus() {
                if (navigator.onLine) {
                    statusElement.textContent = "Status: Online";
                    statusElement.className = "online";
                    syncScans();
                } else {
                    statusElement.textContent = "Status: Offline (scans will sync when back online)";
                    statusElement.className = "offline";
                }
            }
            
            // Initial check
            updateConnectionStatus();
            
            // Event listeners
            window.addEventListener("online", updateConnectionStatus);
            window.addEventListener("offline", updateConnectionStatus);
            
            // Ping system - check connection periodically
            setInterval(async () => {
                try {
                    await fetch("https://www.google.com/favicon.ico", { 
                        method: "HEAD",
                        cache: "no-cache"
                    });
                    if (!navigator.onLine) {
                        // Sometimes the browser doesn't detect connection restore
                        updateConnectionStatus();
                    }
                } catch (e) {
                    if (navigator.onLine) {
                        updateConnectionStatus();
                    }
                }
            }, 30000); // Ping every 30 seconds
        }
        
        // QR Scanner
        function setupScanner() {
            const video = document.getElementById("video");
            const scanButton = document.getElementById("scan-button");
            let scanning = false;
            let stream = null;
            
            scanButton.addEventListener("click", async () => {
                if (scanning) {
                    // Stop scanning
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                    }
                    scanButton.textContent = "Start Scanner";
                    scanning = false;
                    return;
                }
                
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { facingMode: "environment" } 
                    });
                    video.srcObject = stream;
                    video.play();
                    scanButton.textContent = "Stop Scanner";
                    scanning = true;
                    
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
                            
                            if (code) {
                                updateStatus(`Scanned: ${code.data}`);
                                saveScan(code.data);
                                
                                // Optional: stop after successful scan
                                // scanning = false;
                                // scanButton.textContent = "Start Scanner";
                            }
                        }
                        
                        requestAnimationFrame(scanFrame);
                    }
                    
                    scanFrame();
                } catch (err) {
                    console.error("Error starting scanner:", err);
                    updateStatus("Error accessing camera");
                }
            });
        }
        
        function updateStatus(message) {
            const results = document.getElementById("results");
            const entry = document.createElement("div");
            entry.textContent = `${new Date().toLocaleTimeString()}: ${message}`;
            results.prepend(entry);
        }
        
        // Initialize app
        async function init() {
            await initDB();
            setupScanner();
            monitorConnection();
            updateStatus("App initialized. Click 'Start Scanner' to begin.");
        }
        
        window.addEventListener("DOMContentLoaded", init);
    </script>
</body>
</html>
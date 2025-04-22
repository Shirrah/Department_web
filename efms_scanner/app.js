// Global variables
let scannerActive = false;
let pendingScans = [];
const SCAN_TYPE = {
    BARCODE: 'barcode',
    QRCODE: 'qrcode'
};

// DOM elements
const startBtn = document.getElementById('startScanner');
const stopBtn = document.getElementById('stopScanner');
const syncBtn = document.getElementById('syncNow');
const resultDiv = document.getElementById('result');
const statusDiv = document.getElementById('connectionStatus');
const pendingCountSpan = document.getElementById('pendingCount');
const pendingListDiv = document.getElementById('pendingList');

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Load pending scans from local storage
    loadPendingScans();
    
    // Check online status
    updateOnlineStatus();
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    
    // Event listeners
    startBtn.addEventListener('click', startScanner);
    stopBtn.addEventListener('click', stopScanner);
    syncBtn.addEventListener('click', syncPendingScans);
});

// Online/offline status
function updateOnlineStatus() {
    if (navigator.onLine) {
        statusDiv.textContent = 'Online';
        statusDiv.className = 'status online';
        // Try to sync when coming online
        syncPendingScans();
    } else {
        statusDiv.textContent = 'Offline';
        statusDiv.className = 'status offline';
    }
}

// Scanner functions
function startScanner() {
    scannerActive = true;
    startBtn.disabled = true;
    stopBtn.disabled = false;
    resultDiv.textContent = 'Scanning...';
    
    // Try both QR and barcode scanning
    startQRScanner();
    startBarcodeScanner();
}

function stopScanner() {
    scannerActive = false;
    startBtn.disabled = false;
    stopBtn.disabled = true;
    Quagga.stop();
    resultDiv.textContent = 'Scanner stopped';
}

function startQRScanner() {
    const scannerElement = document.getElementById('scanner');
    scannerElement.innerHTML = '<video id="qr-video" width="100%" height="100%"></video>';
    const video = document.getElementById('qr-video');
    
    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
        .then(function(stream) {
            video.srcObject = stream;
            video.setAttribute("playsinline", true);
            video.play();
            
            function scanQR() {
                if (!scannerActive) return;
                
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                
                if (code) {
                    handleScanResult(code.data, SCAN_TYPE.QRCODE);
                } else {
                    requestAnimationFrame(scanQR);
                }
            }
            
            video.addEventListener('playing', () => {
                requestAnimationFrame(scanQR);
            });
        })
        .catch(err => {
            console.error("QR Scanner error:", err);
            resultDiv.textContent = 'Error accessing camera for QR scanner';
        });
}

function startBarcodeScanner() {
    Quagga.init({
        inputStream: {
            name: "Live",
            type: "LiveStream",
            target: document.getElementById('scanner'),
            constraints: {
                width: 480,
                height: 320,
                facingMode: "environment"
            },
        },
        decoder: {
            readers: ["ean_reader", "ean_8_reader", "code_128_reader", "code_39_reader", "code_39_vin_reader"]
        },
    }, function(err) {
        if (err) {
            console.error("Barcode Scanner error:", err);
            resultDiv.textContent = 'Error initializing barcode scanner';
            return;
        }
        Quagga.start();
    });
    
    Quagga.onDetected(function(result) {
        if (scannerActive && result.codeResult) {
            handleScanResult(result.codeResult.code, SCAN_TYPE.BARCODE);
        }
    });
}

function handleScanResult(code, type) {
    resultDiv.textContent = `Scanned ${type}: ${code}`;
    
    const scanData = {
        code: code,
        type: type,
        timestamp: new Date().toISOString(),
        synced: false
    };
    
    // Add to pending scans
    addPendingScan(scanData);
    
    // Try to sync if online
    if (navigator.onLine) {
        syncPendingScans();
    }
}

// Local storage functions
function loadPendingScans() {
    const storedScans = localStorage.getItem('pendingAttendanceScans');
    if (storedScans) {
        pendingScans = JSON.parse(storedScans);
        updatePendingUI();
    }
}

function savePendingScans() {
    localStorage.setItem('pendingAttendanceScans', JSON.stringify(pendingScans));
    updatePendingUI();
}

function addPendingScan(scanData) {
    pendingScans.push(scanData);
    savePendingScans();
}

function removePendingScan(index) {
    pendingScans.splice(index, 1);
    savePendingScans();
}

function updatePendingUI() {
    pendingCountSpan.textContent = pendingScans.length;
    
    pendingListDiv.innerHTML = '';
    pendingScans.forEach((scan, index) => {
        const scanDiv = document.createElement('div');
        scanDiv.className = 'pending-scan';
        scanDiv.innerHTML = `
            <p>${scan.type.toUpperCase()}: ${scan.code}</p>
            <small>${new Date(scan.timestamp).toLocaleString()}</small>
            <small>Status: ${scan.synced ? 'Synced' : 'Pending'}</small>
        `;
        pendingListDiv.appendChild(scanDiv);
    });
}

// Sync functions
async function syncPendingScans() {
    if (!navigator.onLine) {
        console.log('Not online, cannot sync');
        return;
    }
    
    if (pendingScans.length === 0) {
        console.log('No pending scans to sync');
        return;
    }
    
    // Filter out scans that haven't been synced
    const scansToSync = pendingScans.filter(scan => !scan.synced);
    
    if (scansToSync.length === 0) {
        console.log('All pending scans already synced');
        return;
    }
    
    try {
        const response = await fetch('sync.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ scans: scansToSync }),
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Mark scans as synced
            scansToSync.forEach(scan => scan.synced = true);
            savePendingScans();
            
            // Remove synced scans after a delay (to allow user to see the update)
            setTimeout(() => {
                pendingScans = pendingScans.filter(scan => !scan.synced);
                savePendingScans();
            }, 2000);
            
            console.log('Sync successful:', result.message);
        } else {
            console.error('Sync failed:', result.message);
        }
    } catch (error) {
        console.error('Error during sync:', error);
    }
}
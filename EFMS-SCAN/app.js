// Global variables
let scannerActive = false;
let videoStream = null;
let pendingScans = [];

// DOM elements
const startBtn = document.getElementById('startScanner');
const stopBtn = document.getElementById('stopScanner');
const syncBtn = document.getElementById('syncNow');
const resultDiv = document.getElementById('result');
const statusDiv = document.getElementById('connectionStatus');
const pendingCountSpan = document.getElementById('pendingCount');
const pendingListDiv = document.getElementById('pendingList');
const scannerElement = document.getElementById('scanner');
const scannerPlaceholder = document.getElementById('scannerPlaceholder');

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
        statusDiv.innerHTML = '<i class="fas fa-wifi"></i><span>Online</span>';
        statusDiv.className = 'status-badge online';
        // Try to sync when coming online
        syncPendingScans();
    } else {
        statusDiv.innerHTML = '<i class="fas fa-wifi-slash"></i><span>Offline</span>';
        statusDiv.className = 'status-badge offline';
    }
}

// Scanner functions
async function startScanner() {
    try {
        scannerActive = true;
        startBtn.disabled = true;
        stopBtn.disabled = false;
        resultDiv.innerHTML = '<p><i class="fas fa-spinner fa-spin"></i> Starting scanner...</p>';
        scannerPlaceholder.style.display = 'none';
        
        // Get video stream
        videoStream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: "environment",
                width: { ideal: 1280 },
                height: { ideal: 720 }
            } 
        });
        
        // Create video element
        const video = document.createElement('video');
        video.srcObject = videoStream;
        video.setAttribute('playsinline', true);
        video.style.width = '100%';
        video.style.height = '100%';
        video.style.objectFit = 'cover';
        scannerElement.appendChild(video);
        video.play();
        
        // Start scanning
        requestAnimationFrame(() => scanQRCode(video));
        
        resultDiv.innerHTML = '<p><i class="fas fa-check-circle"></i> Scanner ready</p>';
    } catch (error) {
        console.error('Scanner error:', error);
        resultDiv.innerHTML = `<p><i class="fas fa-exclamation-circle"></i> Error: ${error.message}</p>`;
        stopScanner();
    }
}

function stopScanner() {
    scannerActive = false;
    startBtn.disabled = false;
    stopBtn.disabled = true;
    
    // Stop video stream
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
    
    // Clear video element
    scannerElement.innerHTML = '<div class="scanner-overlay"></div>';
    scannerPlaceholder.style.display = 'block';
    
    resultDiv.innerHTML = '<p>Scanner stopped</p>';
}

function scanQRCode(video) {
    if (!scannerActive) return;
    
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height);
        
        if (code) {
            handleScanResult(code.data);
        } else {
            requestAnimationFrame(() => scanQRCode(video));
        }
    } else {
        requestAnimationFrame(() => scanQRCode(video));
    }
}

function handleScanResult(code) {
    // Vibrate if available
    if ('vibrate' in navigator) {
        navigator.vibrate(200);
    }
    
    resultDiv.innerHTML = `
        <p><i class="fas fa-check-circle" style="color: #4CAF50;"></i> Scanned successfully</p>
        <small>Code: ${code}</small>
    `;
    
    const scanData = {
        code: code,
        timestamp: new Date().toISOString(),
        synced: false
    };
    
    // Add to pending scans
    addPendingScan(scanData);
    
    // Try to sync if online
    if (navigator.onLine) {
        syncPendingScans();
    }
    
    // Continue scanning after a brief pause
    setTimeout(() => {
        if (scannerActive) {
            resultDiv.innerHTML = '<p>Ready to scan next QR code</p>';
        }
    }, 2000);
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
    pendingScans.unshift(scanData); // Add to beginning of array
    savePendingScans();
}

function removePendingScan(index) {
    pendingScans.splice(index, 1);
    savePendingScans();
}

function updatePendingUI() {
    pendingCountSpan.textContent = pendingScans.length;
    
    pendingListDiv.innerHTML = '';
    if (pendingScans.length === 0) {
        pendingListDiv.innerHTML = '<p style="text-align: center; color: var(--gray);">No pending scans</p>';
        return;
    }
    
    pendingScans.forEach((scan, index) => {
        const scanDiv = document.createElement('div');
        scanDiv.className = 'pending-item';
        scanDiv.innerHTML = `
            <div class="pending-info">
                <div class="pending-code">${scan.code}</div>
                <div class="pending-time">${new Date(scan.timestamp).toLocaleString()}</div>
            </div>
            <div class="sync-status ${scan.synced ? 'synced' : 'pending'}">
                ${scan.synced ? 'Synced' : 'Pending'}
            </div>
        `;
        pendingListDiv.appendChild(scanDiv);
    });
}

// Sync functions
async function syncPendingScans() {
    if (!navigator.onLine) {
        showToast('Cannot sync - you are offline', 'error');
        return;
    }
    
    if (pendingScans.length === 0) {
        showToast('No pending scans to sync', 'info');
        return;
    }
    
    // Filter out scans that haven't been synced
    const scansToSync = pendingScans.filter(scan => !scan.synced);
    
    if (scansToSync.length === 0) {
        showToast('All pending scans already synced', 'info');
        return;
    }
    
    try {
        syncBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
        syncBtn.disabled = true;
        
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
            
            showToast(`Synced ${scansToSync.length} attendance records`, 'success');
            
            // Remove synced scans after a delay
            setTimeout(() => {
                pendingScans = pendingScans.filter(scan => !scan.synced);
                savePendingScans();
            }, 2000);
        } else {
            showToast(`Sync failed: ${result.message}`, 'error');
        }
    } catch (error) {
        console.error('Error during sync:', error);
        showToast('Sync failed - please try again', 'error');
    } finally {
        syncBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Sync Now';
        syncBtn.disabled = false;
    }
}

// Toast notification
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.left = '50%';
    toast.style.transform = 'translateX(-50%)';
    toast.style.padding = '12px 24px';
    toast.style.borderRadius = '8px';
    toast.style.color = 'white';
    toast.style.zIndex = '1000';
    toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    toast.style.transition = 'all 0.3s ease';
    toast.style.opacity = '0';
    
    switch (type) {
        case 'success':
            toast.style.backgroundColor = '#4CAF50';
            break;
        case 'error':
            toast.style.backgroundColor = '#F44336';
            break;
        case 'info':
            toast.style.backgroundColor = '#2196F3';
            break;
        default:
            toast.style.backgroundColor = '#333';
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Fade in
    setTimeout(() => {
        toast.style.opacity = '1';
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}
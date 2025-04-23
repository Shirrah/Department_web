<?php
require_once './../php/db-conn.php';

// Get parameters
$semesterId = $_GET['semester'] ?? 0;
$eventId = $_GET['event'] ?? 0;
$attendanceId = $_GET['attendance'] ?? 0;

if (!$semesterId || !$eventId || !$attendanceId) {
    header("Location: index.php?error=missing_parameters");
    exit();
}

// Verify the attendance exists
$database = Database::getInstance();
$db = $database->db;
    
$query = "SELECT a.*, e.name_event, s.academic_year, s.semester_type 
      FROM attendances a
      JOIN events e ON a.id_event = e.id_event COLLATE utf8mb4_unicode_ci
      JOIN semester s ON e.semester_ID = s.semester_ID COLLATE utf8mb4_unicode_ci
      WHERE a.id_attendance = ? AND a.id_event = ? AND e.semester_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('iii', $attendanceId, $eventId, $semesterId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$attendanceData = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Attendance System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --white: #ffffff;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        h1 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .status-badge i {
            margin-right: 6px;
        }
        
        .online {
            background-color: rgba(76, 201, 240, 0.2);
            color: var(--success);
        }
        
        .offline {
            background-color: rgba(247, 37, 133, 0.2);
            color: var(--danger);
        }
        
        .scanner-card {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .scanner-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto 20px;
            position: relative;
            aspect-ratio: 1/1;
        }
        
        #scanner {
            width: 100%;
            height: 100%;
            border-radius: 8px;
            overflow: hidden;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        #scanner video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 2px solid var(--primary);
            border-radius: 8px;
            pointer-events: none;
            box-shadow: inset 0 0 0 200px rgba(0,0,0,0.3);
        }
        
        #result {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            background-color: #f8f9fa;
            font-size: 16px;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            margin: 5px;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
        }
        
        .btn-danger:hover {
            background-color: #e5177b;
        }
        
        .pending-sync {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }
        
        .pending-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .pending-count {
            background-color: var(--warning);
            color: var(--white);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .pending-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .pending-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.3s ease;
        }
        
        .pending-item:last-child {
            border-bottom: none;
        }
        
        .pending-item:hover {
            background-color: #f8f9fa;
        }
        
        .pending-info {
            flex: 1;
        }
        
        .pending-code {
            font-weight: 500;
            margin-bottom: 3px;
        }
        
        .pending-time {
            font-size: 12px;
            color: var(--gray);
        }
        
        .sync-status {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 3px;
        }
        
        .synced {
            background-color: rgba(76, 201, 240, 0.2);
            color: var(--success);
        }
        
        .pending {
            background-color: rgba(248, 150, 30, 0.2);
            color: var(--warning);
        }
        
        .scan-history {
            margin-top: 20px;
            background-color: var(--white);
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .scanned-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .scanned-info {
            flex: 1;
        }
        
        .delete-btn {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            margin-left: 10px;
            font-size: 14px;
        }
        
        .delete-btn:hover {
            color: #d1146a;
        }
        
        /* Camera error styles */
        .camera-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .scanner-help {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .scanner-help ul {
            padding-left: 20px;
            margin-top: 5px;
        }
        
        /* Manual entry styles */
        .manual-entry {
            margin-top: 20px;
            display: none;
        }
        
        /* Ensure square scanner on all devices */
        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
            
            .scanner-container {
                max-width: 300px;
            }
            
            .btn {
                padding: 8px 15px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-qrcode"></i> QR Attendance System</h1>
            <div id="connectionStatus" class="status-badge offline">
                <i class="fas fa-wifi-slash"></i>
                <span>Offline</span>
            </div>
        </header>
        
        <div class="info-card">
            <h2>Attendance Session Details</h2>
            <div class="info-item">
                <span class="info-label">Semester:</span>
                <span class="info-value"><?= htmlspecialchars($attendanceData['academic_year'] . ' - ' . $attendanceData['semester_type']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Event:</span>
                <span class="info-value"><?= htmlspecialchars($attendanceData['name_event']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Attendance Type:</span>
                <span class="info-value"><?= htmlspecialchars($attendanceData['type_attendance']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Time:</span>
                <span class="info-value">
                    <?= date('h:i A', strtotime($attendanceData['start_time'])) ?> - 
                    <?= date('h:i A', strtotime($attendanceData['end_time'])) ?>
                </span>
            </div>
        </div>
        
        <div class="scanner-card">
            <div class="scanner-container">
                <div id="scanner">
                    <div class="scanner-overlay"></div>
                    <div id="scannerPlaceholder">
                        <p>Scanner will appear here</p>
                        <!-- Error will appear here if camera fails -->
                    </div>
                </div>
            </div>
            
            <div id="result">
                <p>Scan a QR code to record attendance</p>
            </div>
            
            <div class="scanner-controls">
                <button id="startScanner" class="btn btn-primary">
                    <i class="fas fa-play"></i> Start Scanner
                </button>
                <button id="stopScanner" class="btn btn-outline" disabled>
                    <i class="fas fa-stop"></i> Stop Scanner
                </button>
            </div>
            
            <!-- Manual entry fallback -->
            <div id="manualEntry" class="manual-entry">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-keyboard"></i> Manual Entry</h5>
                        <div class="input-group mb-3">
                            <input type="text" id="manualCode" class="form-control" placeholder="Enter student code">
                            <button id="submitManual" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="pending-sync">
            <div class="pending-header">
                <h2><i class="fas fa-sync-alt"></i> Pending Sync</h2>
                <span id="pendingCount" class="pending-count">0</span>
            </div>
            
            <button id="syncNow" class="btn btn-primary" style="width: 100%; margin-bottom: 15px;">
                <i class="fas fa-cloud-upload-alt"></i> Sync Now
            </button>
            
            <div id="pendingList" class="pending-list"></div>
        </div>
        
        <div class="scan-history">
            <h2><i class="fas fa-history"></i> Recent Scans</h2>
            <div id="recentScans"></div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.min.js"></script>
    <script>
    // Global variables
    let scannerActive = false;
    let videoStream = null;
    let pendingScans = [];
    let recentScans = [];

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
    const recentScansDiv = document.getElementById('recentScans');
    const manualEntryDiv = document.getElementById('manualEntry');
    const submitManualBtn = document.getElementById('submitManual');
    const manualCodeInput = document.getElementById('manualCode');

    // Store attendance data
    const attendanceData = {
        id: <?= $attendanceId ?>,
        eventId: <?= $eventId ?>,
        semesterId: <?= $semesterId ?>
    };

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadPendingScans();
        updateOnlineStatus();
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        
        startBtn.addEventListener('click', startScanner);
        stopBtn.addEventListener('click', stopScanner);
        syncBtn.addEventListener('click', syncPendingScans);
        submitManualBtn.addEventListener('click', submitManualCode);
        
        updateRecentScansUI();
    });

    // Online/offline status
    function updateOnlineStatus() {
        if (navigator.onLine) {
            statusDiv.innerHTML = '<i class="fas fa-wifi"></i><span>Online</span>';
            statusDiv.className = 'status-badge online';
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
            manualEntryDiv.style.display = 'none';

            // Stop any existing stream
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
            }

            // Try different camera configurations
            const constraints = {
                video: {
                    facingMode: "environment",
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };

            // Try to get media with constraints
            videoStream = await navigator.mediaDevices.getUserMedia(constraints)
                .catch(async (err) => {
                    // Fallback to any camera if environment fails
                    console.log('Rear camera failed, trying any camera...');
                    return await navigator.mediaDevices.getUserMedia({
                        video: true
                    });
                });

            const video = document.createElement('video');
            video.srcObject = videoStream;
            video.setAttribute('playsinline', true);
            video.style.width = '100%';
            video.style.height = '100%';
            video.style.objectFit = 'cover';
            
            scannerElement.innerHTML = '';
            scannerElement.appendChild(video);
            
            await video.play();
            
            resultDiv.innerHTML = '<p><i class="fas fa-check-circle"></i> Scanner ready</p>';
            scanLoop(video);
            
        } catch (error) {
            console.error('Scanner error:', error);
            showCameraError(error);
            stopScanner();
        }
    }

    function showCameraError(error) {
        scannerPlaceholder.style.display = 'block';
        scannerPlaceholder.innerHTML = `
            <div class="camera-error">
                <i class="fas fa-video-slash"></i>
                <strong>Camera Error:</strong> ${error.message}
            </div>
            <div class="scanner-help">
                <p>Please ensure:</p>
                <ul>
                    <li>Your camera is connected and enabled</li>
                    <li>You've granted camera permissions</li>
                    <li>No other app is using the camera</li>
                    <li>You're using HTTPS or localhost</li>
                </ul>
                <button id="retryScanner" class="btn btn-primary mt-2">
                    <i class="fas fa-sync-alt"></i> Try Again
                </button>
            </div>
        `;
        
        document.getElementById('retryScanner').addEventListener('click', startScanner);
        manualEntryDiv.style.display = 'block';
    }

    function submitManualCode() {
        const code = manualCodeInput.value.trim();
        if (code) {
            handleScanResult(code);
            manualCodeInput.value = '';
        }
    }

    // Continuous scanning loop
    function scanLoop(video) {
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
                setTimeout(() => scanLoop(video), 1000);
            } else {
                requestAnimationFrame(() => scanLoop(video));
            }
        } else {
            requestAnimationFrame(() => scanLoop(video));
        }
    }

    function stopScanner() {
        scannerActive = false;
        startBtn.disabled = false;
        stopBtn.disabled = true;
        
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
        
        scannerElement.innerHTML = '<div class="scanner-overlay"></div>';
        scannerPlaceholder.style.display = 'block';
        resultDiv.innerHTML = '<p>Scanner stopped</p>';
    }

    function handleScanResult(code) {
        if ('vibrate' in navigator) {
            navigator.vibrate(200);
        }
        
        resultDiv.innerHTML = `
            <p><i class="fas fa-check-circle" style="color: #4CAF50;"></i> Scanned successfully</p>
            <small> Code: ${code}</small>
        `;
        
        const scanData = {
            code: code,
            attendance_id: attendanceData.id,
            timestamp: new Date().toISOString(),
            synced: false
        };
        
        addPendingScan(scanData);
        addRecentScan(scanData);
        
        if (navigator.onLine) {
            syncPendingScans();
        }
    }

// Modify the syncPendingScans function to include all attendance data
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
            body: JSON.stringify({ 
                scans: scansToSync,
                attendance_id: attendanceData.id,
                event_id: attendanceData.eventId,
                semester_id: attendanceData.semesterId
            }),
        });
        
        // Rest of your sync function remains the same
    } catch (error) {
        console.error('Error during sync:', error);
        showToast('Sync failed - please try again', 'error');
    } finally {
        syncBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Sync Now';
        syncBtn.disabled = false;
    }
}
        // Recent scans functionality
        function addRecentScan(scanData) {
            recentScans.unshift(scanData);
            if (recentScans.length > 5) {
                recentScans = recentScans.slice(0, 5);
            }
            updateRecentScansUI();
        }

        function deleteRecentScan(index) {
            recentScans.splice(index, 1);
            updateRecentScansUI();
        }

        function updateRecentScansUI() {
            recentScansDiv.innerHTML = '';
            
            if (recentScans.length === 0) {
                recentScansDiv.innerHTML = '<p style="text-align: center; color: var(--gray);">No recent scans</p>';
                return;
            }
            
            recentScans.forEach((scan, index) => {
                const scanDiv = document.createElement('div');
                scanDiv.className = 'scanned-item';
                scanDiv.innerHTML = `
                    <div class="scanned-info">
                        <div>${scan.code}</div>
                        <small>${new Date(scan.timestamp).toLocaleTimeString()}</small>
                    </div>
                    <button class="delete-btn" data-index="${index}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                `;
                recentScansDiv.appendChild(scanDiv);
            });
            
            // Add event listeners to delete buttons
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const index = parseInt(e.currentTarget.getAttribute('data-index'));
                    deleteRecentScan(index);
                });
            });
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
            pendingScans.unshift(scanData);
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

        document.getElementById('attendance').addEventListener('change', function() {
            const attendanceId = this.value;
            const scannerRedirect = document.getElementById('scannerRedirect');
            
            if (attendanceId) {
                scannerRedirect.style.display = 'block';
            } else {
                scannerRedirect.style.display = 'none';
            }
        });
        
        document.getElementById('goToScanner').addEventListener('click', function() {
            const attendanceId = document.getElementById('attendance').value;
            if (attendanceId) {
                window.location.href = `efms-scanner.php?attendance_id=${attendanceId}`;
            }
        });
    </script>
</body>
</html>
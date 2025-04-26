<?php
// If it's an AJAX request, only check connection and return result
if (isset($_GET['check_connection'])) {
    echo checkDatabaseConnection() ? 'connected' : 'offline';
    exit;
}

// Function to check online database connection
function checkDatabaseConnection() {
    $host = "p:5.181.217.145"; 
    $user = "hpo-admin";
    $pass = "Shirrah+admin1234#";
    $dbname = "dcs";

    $conn = @new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_errno) {
        return false;
    } else {
        $conn->close();
        return true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Connection Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 100px;
            background-color: #f9f9f9;
        }
        .status {
            font-size: 30px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            display: inline-block;
            margin-top: 20px;
            transition: all 0.5s ease;
        }
        .online {
            background-color: #d4edda;
            color: #155724;
        }
        .offline {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<h1>Live Database Connection Checker</h1>

<div id="connectionStatus" class="status">Checking...</div>

<script>
function checkConnection() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "?check_connection=1", true);
    xhr.onload = function() {
        var statusDiv = document.getElementById('connectionStatus');
        if (xhr.status == 200) {
            if (xhr.responseText.trim() == 'connected') {
                statusDiv.textContent = '✅ Connected to Online Database!';
                statusDiv.className = 'status online';
            } else {
                statusDiv.textContent = '❌ Offline - Cannot connect to Database.';
                statusDiv.className = 'status offline';
            }
        } else {
            statusDiv.textContent = '❌ Error checking connection.';
            statusDiv.className = 'status offline';
        }
    };
    xhr.onerror = function() {
        var statusDiv = document.getElementById('connectionStatus');
        statusDiv.textContent = '❌ Error checking connection.';
        statusDiv.className = 'status offline';
    };
    xhr.send();
}

// Check connection every 5 seconds
setInterval(checkConnection, 5000);

// Check immediately on page load
checkConnection();
</script>

</body>
</html>

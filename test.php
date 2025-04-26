<?php
// FUNCTION to check online database connection
function checkOnlineDatabaseConnection() {
    $host = "p:5.181.217.145"; 
    $user = "hpo-admin";
    $pass = "Shirrah+admin1234#";
    $dbname = "dcs";

    // Try connecting
    $conn = @new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_errno) {
        return false; // Connection failed
    } else {
        $conn->close(); // Connection success
        return true;
    }
}

// CALL the function
$isConnected = checkOnlineDatabaseConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Database Connection Status</title>
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

<h1>Online Database Status Checker</h1>

<div class="status <?php echo $isConnected ? 'online' : 'offline'; ?>">
    <?php if ($isConnected): ?>
        ✅ Connected to Online Database!
    <?php else: ?>
        ❌ Offline - Cannot connect to Online Database.
    <?php endif; ?>
</div>

</body>
</html>

<?php
$secret = 'you_are_here_you_piece_of_shit'; // Same secret as in GitHub webhook

$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate signature
$signature = 'sha256=' . hash_hmac('sha256', $json, $secret);
$github_signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (!hash_equals($signature, $github_signature)) {
    http_response_code(403);
    die('Invalid signature');
}

// Run deploy script
$output = shell_exec('/bin/bash /home/hpo-admin/htdocs/Department_web/deploy.sh 2>&1');

// Log the output
file_put_contents(__DIR__ . '/webhook.log', date('Y-m-d H:i:s') . " - Deployment Output:\n$output\n\n", FILE_APPEND);

echo "Deployment triggered.";
?>

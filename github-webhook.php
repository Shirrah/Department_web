<?php
$secret = 'you_are_here_you_piece_of_shit'; // Set this in GitHub Webhook
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate GitHub Signature
$signature = 'sha256=' . hash_hmac('sha256', $json, $secret);
$github_signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (!hash_equals($signature, $github_signature)) {
    http_response_code(403);
    die('Invalid signature');
}

// Run deployment script
$output = shell_exec('/bin/bash /home/hpo-admin/htdocs/Department_web/deploy.sh 2>&1');

// Log output for debugging
file_put_contents('webhook.log', date('Y-m-d H:i:s') . " - Deployment Output: $output\n", FILE_APPEND);

echo "Deployment triggered.";
?>

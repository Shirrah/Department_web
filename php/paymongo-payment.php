<?php

// Retrieve payment details from POST
$payment_id = $_POST['payment_id'];
$amount = $_POST['amount']; // Already in PHP centavos format
$payment_name = $_POST['payment_name'];
$remarks = $_POST['remarks'];

// Convert the amount to centavos (if necessary)
$amount_in_centavos = $amount * 100;

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.paymongo.com/v1/links",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode([
    'data' => [
        'attributes' => [
            'amount' => $amount_in_centavos, // Use dynamic amount
            'description' => $payment_name, // Use dynamic payment name as description
            'remarks' => $remarks, // Use dynamic remarks
            'payment_method_allowed' => ['card', 'gcash', 'paymaya', 'grab_pay', 'bank_transfer'], // Payment methods
            'currency' => 'PHP'
        ]
    ]
  ]),
  CURLOPT_HTTPHEADER => [
    "accept: application/json",
    "authorization: Basic c2tfdGVzdF9NU3dncWtVWUx2YW8xaEJxYmI4d0hwZ0U6", // Replace with your Base64-encoded secret key
    "content-type: application/json"
  ],
  CURLOPT_SSL_VERIFYHOST => 0, // Disable SSL host verification
  CURLOPT_SSL_VERIFYPEER => 0, // Disable SSL peer verification
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  // Assuming $response contains the API response
  $response_data = json_decode($response, true);

  if (isset($response_data['data']['attributes']['checkout_url'])) {
    $checkout_url = $response_data['data']['attributes']['checkout_url'];
    header("Location: $checkout_url");
    exit();   
    echo "Error: Unable to retrieve checkout URL.";
  }
}
?>

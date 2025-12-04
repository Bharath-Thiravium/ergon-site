<?php
// Test the outstanding API directly
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/ergon-site/src/api/outstanding.php?prefix=BKGE&limit=3');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['data'])) {
        echo "\nFirst invoice shipping address: " . ($data['data'][0]['shipping_address'] ?? 'NOT FOUND') . "\n";
    }
}
?>

<?php
$url = 'https://api.geoapify.com/v1/geocode/search?text=Paris&limit=1&apiKey=' . getenv('GEOAPIFY_API_KEY');
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
]);
$response = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
$error    = curl_error($ch);
curl_close($ch);

var_dump($status, $error, substr((string) $response, 0, 300));
<?php
require_once __DIR__ . '/../load_balancer.php';

$backendServer = getBackendServer();
$apiUrl = $backendServer . $_SERVER['REQUEST_URI'];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, getallheaders());

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// Forward response to the client
http_response_code($httpCode);
echo $response;

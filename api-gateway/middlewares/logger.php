<?php
function logRequestResponse($response, $statusCode)
{
    $logFile = __DIR__ . '/../logs/gateway.log';

    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'],
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI'],
        'status_code' => $statusCode,
        'response' => json_encode($response),
    ];

    file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND);
}

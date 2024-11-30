<?php
function checkRateLimit()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $cacheDir = realpath(__DIR__ . '/../cache');
    $rateLimitFile =
        $cacheDir . DIRECTORY_SEPARATOR . 'ratelimit_' . md5($ip) . '.json';

    // check if directory of cache exists, if not, create new directory
    if (!is_dir($cacheDir)) {
        if (!mkdir($cacheDir, 0777, true) && !is_dir($cacheDir)) {
            error_log("Failed to create cache directory: $cacheDir");
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error']);
            exit();
        }
    }

    $currentTime = time();
    $rateLimit = [
        'requests' => 0,
        'start_time' => $currentTime,
    ];

    // load rate limit data if exists
    if (file_exists($rateLimitFile)) {
        $rateLimit = json_decode(file_get_contents($rateLimitFile), true);
    }

    // reset request count if time window has passed
    $timeWindow = 60;
    $requestLimit = 5; // 5 requests per minute
    if ($currentTime - $rateLimit['start_time'] > $timeWindow) {
        $rateLimit['requests'] = 0;
        $rateLimit['start_time'] = $currentTime;
    }

    // increment request count
    $rateLimit['requests'] += 1;
    if (file_put_contents($rateLimitFile, json_encode($rateLimit)) === false) {
        error_log("Failed to write to $rateLimitFile");
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error']);
        exit();
    }

    // block if rate limit exceeded
    if ($rateLimit['requests'] > $requestLimit) {
        http_response_code(429);
        echo json_encode(['error' => 'Too many requests']);
        exit();
    }
}

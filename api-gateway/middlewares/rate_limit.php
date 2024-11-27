<?php
function checkRateLimit()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $rateLimitFile = __DIR__ . "/../cache/ratelimit_$ip.json";

    $currentTime = time();
    $rateLimit = [
        'requests' => 0,
        'start_time' => $currentTime,
    ];

    // load existing rate limit data
    if (file_exists($rateLimitFile)) {
        $rateLimit = json_decode(file_get_contents($rateLimitFile), true);
    }

    // reset if time passed (max of 1 minute, to be adjust depending on the request sa user)
    $timeWindow = 60;
    $requestLimit = 5; // 5 requests per minute
    if ($currentTime - $rateLimit['start_time'] > $timeWindow) {
        $rateLimit['requests'] = 0;
        $rateLimit['start_time'] = $currentTime;
    }

    // increment request count and save data
    $rateLimit['requests'] += 1;
    file_put_contents($rateLimitFile, json_encode($rateLimit));

    // block if rate limit exceeded
    if ($rateLimit['requests'] > $requestLimit) {
        http_response_code(429);
        echo json_encode(['error' => 'Too many requests']);
        exit();
    }
}

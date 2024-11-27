<?php
function checkCache()
{
    $cacheKey = md5(
        $_SERVER['REQUEST_URI'] .
            $_SERVER['REQUEST_METHOD'] .
            file_get_contents('php://input')
    );
    $cacheFile = __DIR__ . "/../cache/$cacheKey.json";

    if (file_exists($cacheFile) && time() - filemtime($cacheFile) < 300) {
        // cache is valid (e.g., 5 minutes expiration)
        echo file_get_contents($cacheFile);
        exit();
    }

    // start output buffer to initialize caching
    ob_start();

    // save cache after process
    register_shutdown_function(function () use ($cacheFile) {
        $response = ob_get_contents();
        file_put_contents($cacheFile, $response);
    });
}

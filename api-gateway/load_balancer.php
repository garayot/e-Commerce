<?php
function getBackendServer()
{
    // backend servers
    $servers = ['http://localhost:8000'];

    // round-robin load balance
    $cacheFile = __DIR__ . '/cache/load_balancer_state.json';
    $state = ['last_used' => -1];

    if (file_exists($cacheFile)) {
        $state = json_decode(file_get_contents($cacheFile), true);
    }

    $state['last_used'] = ($state['last_used'] + 1) % count($servers);
    file_put_contents($cacheFile, json_encode($state));

    return $servers[$state['last_used']];
}

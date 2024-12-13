<?php

require_once __DIR__ . '/../utils/db.php';

use Database\Database;

function authenticate()
{
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: Missing token']);
        exit();
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    if (!isValidToken($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: Invalid token']);
        exit();
    }
}

// validate token using regex
function isValidToken($token)
{
    // regex pattern

    if (!empty($token)) {
        return true;
    } else {
        return false;
    }
}

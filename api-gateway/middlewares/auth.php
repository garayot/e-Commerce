<?php
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

//need to double check logic if there is authToken in db
function isValidToken($token)
{
    $secretKey = 'secret';
    return $token === 'valid_example_token';
}

<?php

require_once __DIR__ . '/../UserAuth/database/db.php';

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

//need to double check logic if there is session_token in db
function isValidToken($token)
{
    $db = new Database\Database();
    $conn = $db->getConnection();
    $sql = "SELECT * FROM sessiontokens WHERE session_token = '$token'";
    $result = $conn->query($sql);
    $db->close();
    return $result->num_rows > 0;
}

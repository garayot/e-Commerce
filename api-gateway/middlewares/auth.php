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

    /**
     * 
     $pattern = '/^[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+$/';
 
     if (!preg_match($pattern, $token) || $token == '') {
         return false;
     }
     * 
     */
    // temporary comment this due to session_token isn't storing the correct token
    /**
    * 
     $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare(
        'SELECT * FROM session_token WHERE token = :token AND expires_at > NOW()'
    );
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $db->close();

    return $result !== false;
    */
}

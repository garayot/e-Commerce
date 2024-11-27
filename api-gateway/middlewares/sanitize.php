<?php
function sanitizeInputs()
{
    foreach ($_REQUEST as $key => $value) {
        //regex for invalid characters to avoid injection
        if (preg_match('/[^a-zA-Z0-9_@.\- ]/', $value)) {
            http_response_code(400);
            echo json_encode(['error' => "Invalid input in field: $key"]);
            exit();
        }
    }
}

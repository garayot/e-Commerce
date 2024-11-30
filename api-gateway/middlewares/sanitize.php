<?php
function sanitizeInputs()
{
    foreach ($_REQUEST as $key => $value) {
        // Check if the value is an array and sanitize recursively
        if (is_array($value)) {
            array_walk_recursive($value, function (&$item, $key) {
                if (preg_match('/[^a-zA-Z0-9_@.\- ]/', $item)) {
                    http_response_code(400);
                    echo json_encode([
                        'error' => "Invalid input in field. This might be an SQL Attack: $key",
                    ]);
                    exit();
                }
            });
        } else {
            // Regex for invalid characters to avoid injection
            if (preg_match('/[^a-zA-Z0-9_@.\- ]/', $value)) {
                http_response_code(400);
                echo json_encode([
                    'error' => "Invalid input in field This might be an SQL Attack: $key",
                ]);
                exit();
            }
        }
    }
}

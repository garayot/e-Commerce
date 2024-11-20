<?php

require_once __DIR__ . '../../../UserAuth/database/db.php';
require_once __DIR__ . '../../../UserAuth/utils/response.php';
require '../api/Productroutes.php';

use Database\Database;

class API
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function handleRequest()
    {
        $router = new Router($this->db);

        // Extract action from GET parameters
        $action = $_GET['action'] ?? null;

        // Check if the action parameter is set
        if (!$action) {
            jsonResponse(['error' => 'Action not specified']);
            return;
        }

        // Determine request method and handle accordingly
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                jsonResponse($router->route($action, $data));
                break;

            case 'GET':
                jsonResponse($router->route($action, $_GET));
                break;

            case 'DELETE':
                jsonResponse($router->route($action, $_GET));
                break;

            case 'PUT':
                $data = json_decode(file_get_contents('php://input'), true);
                jsonResponse($router->route($action, array_merge($_GET, $data)));
                break;

            default:
                jsonResponse(['error' => 'Invalid request method']);
                break;
        }
    }
}

// Instantiate and handle the API request
$api = new API();
$api->handleRequest();
?>

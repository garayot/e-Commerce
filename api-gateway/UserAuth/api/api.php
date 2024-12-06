<?php

require '../database/db.php';
require './routes.php';
require '../utils/response.php';

use database\Database;

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

        // Check the request method
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($_GET['action'])) {
                $action = $_GET['action'];
                jsonResponse($router->route($action, $data));
            } else {
                jsonResponse(['error' => 'Action not specified']);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_GET['action'])) {
                $action = $_GET['action'];
                jsonResponse($router->route($action, $_GET));
            } else {
                jsonResponse(['error' => 'Action not specified']);
            }
        } else {
            jsonResponse(['error' => 'Invalid request method']);
        }
    }
}

$api = new API();
$api->handleRequest();

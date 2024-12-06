<?php

namespace Database;

use PDO;
use PDOException;

class Database
{
    private $servername = '127.0.0.1';
    private $port = '3306'; // MySQL default port is 3306, adjust if necessary
    private $username = 'root';
    private $password = '';
    private $dbname = 'Ecommerce';
    public $conn;

    public function __construct()
    {
        try {
            $startTime = microtime(true); // Start time for logging
            $this->conn = new PDO(
                "mysql:host=$this->servername;port=$this->port;dbname=$this->dbname",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
            $endTime = microtime(true); // End time for logging
            $executionTime = $endTime - $startTime; // Calculate execution time
            error_log("Database connection took $executionTime seconds."); // Log execution time
        } catch (PDOException $e) {
            error_log('Connection failed: ' . $e->getMessage());
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function close()
    {
        $this->conn = null;
    }
}

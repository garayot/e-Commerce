<?php

namespace Database;

//use mysqli;
// postgre
use PDO;
use PDOException;

class Database
{
    private $servername = '127.0.0.1:5433';
    private $username = 'root';
    private $password = '';
    private $dbname = 'ecommerce';
    public $conn;

    public function __construct()
    {
        try {
            $conn = new PDO(
                "mysql:host=$this->servername;dbname=$this->dbname",
                $this->username,
                $this->password
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo 'Connected successfully';
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function close()
    {
        $this->conn->close();
    }
}

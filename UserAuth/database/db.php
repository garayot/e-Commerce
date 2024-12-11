<?php

namespace Database;

use mysqli;

class Database
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "ecommerce";
    public $conn;

    public function __construct()
    {
        // Establish the database connection
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection()
    {
        // Return the connection object
        return $this->conn;
    }

    public function close()
    {
        // Close the connection
        $this->conn->close();
    }
}
?>

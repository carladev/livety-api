<?php

namespace App\Models;
use \PDO;

class DB
{
    public function connect()
    {
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        $host = $_ENV['DB_HOST'];

        $conn_str = "mysql:host=$host;dbname=$dbname";
        $conn = new PDO($conn_str, $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }
}
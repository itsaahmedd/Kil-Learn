<?php

class DatabaseCredentials
{

    private string $host;
    private string $username;
    private string $password;
    private string $database;

    public function __construct(string $host, string $username, string $password, string $database)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }

    public function connect(): mysqli
    {
        $connection = mysqli_connect($this->host, $this->username, $this->password, $this->database);
        if (!$connection) {
            die("Failed to connect to database: " . mysqli_connect_error());
        }
        return $connection;
    }

}
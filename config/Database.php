<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Database
{
    private static $instance = null;
    protected $localhost = "localhost";
    protected $username = "root";
    protected $password = "";
    protected $database = "php_api_jwt";

    protected $mysqli = "";
    private array $result = [];
    private $connection = null;


    private function __construct()
    {
        try {
            $this->mysqli = new mysqli($this->localhost, $this->username, $this->password, $this->database);
        } catch (Exception $e) {
            die("Error! Could not connect to Database!");
        }
    }

    public function connect()
    {
        if ($this->mysqli && $this->mysqli->connect_error) {
            array_push(
                $this->result,
                $this->mysqli->connect_error,
            );

            $this->mysqli->close();

            return false;
        }

        return $this->mysqli;
    }

    public function name()
    {
        return $this->database;
    }

    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

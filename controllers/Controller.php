<?php

include '../config/Database.php';

class Controller extends Database
{
    protected $mysqli;
    protected $database;
    protected $data;
    protected array $result = [];

    public function __construct()
    {
        $db = Database::instance();
        $this->mysqli = $db->connect();

        $db_name = $db->name();
        $this->database = $db_name;
    }

    public function mysqlHeader($query, $callParams = [])
    {
        $sql = $query;

        $stmt = $this->mysqli->prepare($sql);

        if (!$stmt) {
            echo 'Error :' . $this->mysqli->error;
            die('Error Stmt');
        }

        $reservedQuery = strtolower($query);

        if (preg_match('/insert|select|update|delete/i', $reservedQuery) && count($callParams) > 0) {
            $stmt->bind_param($callParams['types'], ...$callParams['values']);
        } else {
            $stmt->bind_param($callParams['types'], $callParams['values']);
        }


        $stmt->execute();
        $this->data = $stmt->get_result(); //num_rows();
        $stmt->close();
    }

    // Check if Table Exists in Db
    public function tableExists(string $table)
    {
        $sql = "SHOW TABLES FROM $this->database LIKE '{$table}'";

        $tableInDb = $this->mysqli->query($sql);

        if ($tableInDb && $tableInDb->num_rows === 1) {
            array_push($this->result, ['table_exists' => true]);
            return true;
        }

        array_push($this->result, ['table_exists' => false]);
        return false;
    }

    public function selectTable(string $table): bool
    {
        if (!$this->tableExists($table)) {
            return false;
        }

        return true;
    }

    public function getResult(): array
    {
        $value = $this->result;

        $this->result = [];

        return $value;
    }

    public function productExists(int $productId, $product_data = null)
    {

        $sql = 'SELECT * FROM products WHERE id=?';
        $this->mysqlHeader($sql, ['types' => 'i', 'values' => [$productId]]);

        if ($this->data->num_rows === 0) {
            http_response_code(500);
            return false;
        }

        if ($product_data !== null) {
            $product_data = $this->data->fetch_assoc();

            return $product_data;
        }

        return true;
    }

    public function userExists(string|int $uid, string $email, $user_data = null)
    {

        $sql = null;
        if (gettype($uid) === 'integer') {
            $sql = 'SELECT * FROM users WHERE id=? OR email=?';
            $this->mysqlHeader($sql, ['types' => 'is', 'values' => [$uid, $email]]);
        } else {
            $sql = 'SELECT * FROM users WHERE username=? OR email=?';
            $this->mysqlHeader($sql, ['types' => 'ss', 'values' => [$uid, $email]]);
        }

        if ($this->data->num_rows === 0) {
            $this->result[] = ['user_exists' => false];

            http_response_code(500);
            return false;
        }

        $this->result[] = ['user_exists' => true];

        if ($user_data !== null) {
            $user_data = $this->data->fetch_assoc();

            return $user_data;
        }

        return true;
    }
}

<?php

include '../controllers/Controller.php';

class CRUD extends Controller
{

    protected array $result = [];

    public function insert($table, $params = [])
    {
        if ($this->selectTable($table)) {
            $table_column = implode(', ', array_keys($params));
            $table_values = array_values($params);
            $definedParams = isset($params) && $params;


            if (!$definedParams) {
                die('Error :' . $this->mysqli->error);
            }

            extract($params);

            $uid = $params['name'] ?? $params['user_id'];

            // if ($this->userExists($uid, $email ?? '')) {
            //     echo 'params' . "\n";
            // }

            $values = [];
            $params_values = array_values($params);
            $values_type = [];
            for ($i = 0; $i < count($params); $i++) {
                $values[] = '?';
                $values_type[] = gettype($params_values[$i])[0];
            }


            $sql = "INSERT INTO $table($table_column) values (" . implode(', ', array_values($values)) . ")";

            $stmt = [
                'types' => implode('', array_values($values_type)),
                'values' => $table_values
            ];
            $this->mysqlHeader($sql, $stmt);

            $this->result[] = ['insert' => 1];

            return true;
        } else {
            echo "Table does not exists";
            $this->result[] = ['insert' => 0, 'message' => 'error'];
            return false;
        }
    }

    // get data
    public function select($table, $row = "*", $join = null, $where = null, $order = null, $limit = null)
    {
        if ($this->tableExists($table)) {
            $sql = "SELECT $row FROM $table";
            if ($join !== null) {
                $sql .= " JOIN $join";
            }
            if ($where !== null) {
                $sql .= " WHERE $where";
            }
            if ($order !== null) {
                $sql .= " ORDER $order";
            }
            if ($limit !== null) {
                $sql .= " LIMIT $limit";
            }

            $query = $this->mysqli->query($sql);
            if (!$query || $query->num_rows === 0) {
                return false;
            }

            $this->result[] = ['select' => 1, 'result' => $query->fetch_all(MYSQLI_ASSOC)];
            return true;
        }

        $this->result[] = ['select' => 0, 'result' => 0];
        return false;
    }

    public function update($table, array|string $params, $where = null)
    {
        if ($this->tableExists($table)) {
            if (gettype($params) === 'array') {
                $arg = [];
                foreach ($params as $key => $val) {
                    $arg[] = "$key = '{$val}'";
                }
                $sql = "UPDATE $table SET " . implode(', ', $arg);
            } else {
                $sql = "UPDATE $table SET " . $params . " ";
            }

            if ($where !== null) {
                $sql .= "WHERE $where";
            }

            // $stmt = [
            //     'types' => 'ssss',
            //     'values' => $table_values
            // ];
            // $this->mysqlHeader($sql, $stmt);

            $query = $this->mysqli->query($sql);
            if (!$query) {
                return false;
            }

            $this->result[] = ['update' => 1];
            return true;
        }

        $this->result[] = ['update' => 0];
        return false;
    }

    public function delete($table, $where = null)
    {
        if ($this->tableExists($table)) {
            $sql = "DELETE FROM $table";
            if ($where !== null) {
                $sql .= " WHERE $where";
            }

            if (!$this->mysqli->query($sql)) {
                $this->result[] = false;
                return false;
            }

            $this->result[] = ['delete' => 1];
            return true;
        }

        $this->result[] = ['delete' => 0];
        return false;
    }
}

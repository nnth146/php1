<?php

class PdoDB
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "php1";
    private $conn;
    function __construct()
    {
        try {
            $this->conn = new PDO("mysql:host=$this->servername;dbname=$this->dbname", $this->username, $this->password);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            //Connection fail
            var_dump("Database connection fail");
        }
    }
    function __destruct()
    {
        $this->conn = null;
    }
    public function getLastInsertId()
    {
        return $this->conn->lastInsertId();
    }
    public function query($sql, $fetch = false)
    {
        try {
            $stmt = $this->conn->prepare($sql);

            $stmt->execute();

            if ($fetch) {
                $stmt->setFetchMode(PDO::FETCH_ASSOC);

                return $stmt->fetchAll();
            }
            return true;
        } catch (PDOException $ex) {
            return false;
        }
    }
    protected function mapValuesToStringValues($values)
    {
        return array_map(function ($element) {
            return "'$element'";
        }, $values);
    }
    /**
     * Insert record[columns][values] into [table]
     *
     * @param string $table
     * @param array $columns
     * @param array $values
     * @return string|boolean
     */
    public function insert($table, $columns, $values)
    {
        try {
            if (is_array($columns)) {
                $columns = implode(",", $columns);
            }

            $values = $this->mapValuesToStringValues($values);

            $values = implode(",", $values);

            $sql = "INSERT INTO $table($columns) VALUES ($values)";

            $this->conn->exec($sql);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    public function insertMulti($table, $columns, $arrValues)
    {
        try {
            if (is_array($columns)) {
                $columns = implode(",", $columns);
            }

            $length = count($arrValues);

            for ($i = 0; $i < $length; $i++) {
                $arrValues[$i] = $this->mapValuesToStringValues($arrValues[$i]);
                $arrValues[$i] = "(" . implode(",", $arrValues[$i]) . ")";
            }

            $values = implode(",", $arrValues);

            if(empty($values)) {
                return false;
            }

            $sql = "INSERT INTO $table($columns) VALUES $values";

            $this->conn->exec($sql);

            return true;
        } catch (PDOException $e) {
            var_dump($e->getMessage());
            return false;
        }
    }
    /**
     * Update record[column => value] in [table]
     * updates = ["column" => "value"]
     *
     * @param string $table
     * @param array $updates
     * @return string|boolean
     */
    public function update($table, $updates)
    {
        try {
            $updates = array_map(function ($k, $v) {
                return "$k='$v'";
            }, array_keys($updates), array_values($updates));

            $columns_values = implode(",", $updates);

            $sql = "UPDATE $table SET $columns_values";

            $this->conn->exec($sql);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    public function updateWhere($table, $updates, $where)
    {
        try {
            $updates = array_map(function ($k, $v) {
                return "$k='$v'";
            }, array_keys($updates), array_values($updates));

            $columns_values = implode(",", $updates);

            $sql = "UPDATE $table SET $columns_values WHERE $where";

            $this->conn->exec($sql);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    /**
     * Delete [record] from [table] where [id]
     *
     * @param string $table
     * @param int|string $id
     * @return string|boolean
     */
    public function delete($table, $id)
    {
        try {
            $sql = "DELETE FROM $table WHERE id = $id";

            $this->conn->exec($sql);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    public function deleteWhere($table, $where)
    {
        try {
            $sql = "DELETE FROM $table WHERE $where";

            $this->conn->exec($sql);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    public function select($table, $columns)
    {
        try {
            if (is_array($columns)) {
                $columns = implode(",", $columns);
            }

            $sql = "SELECT $columns FROM $table";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    public function selectWhere($table, $columns, $where)
    {
        try {
            if (is_array($columns)) {
                $columns = implode(",", $columns);
            }

            $sql = "SELECT $columns FROM $table WHERE $where";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
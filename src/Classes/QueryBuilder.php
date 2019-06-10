<?php

namespace Classes;
use \PDO;

class QueryBuilder
{
    private $db;

    function __construct(array $kwargs)
    {
        if ($kwargs["db"] instanceof PDO) {
            $this->db = $kwargs["db"];
        }
    }

    public function __set($name, $value)
    {
        echo "This attribute $name is private or couldn't be initialized with value $value\n";
    }

    public function __get($name)
    {
        echo "This attribute $name is private or doesn't exist\n";
    }

    public function setDb(PDO $db)
    {
        if ($db instanceof PDO) {
            $this->db = $db;
        }
    }

    public function getDb()
    {
        return $this->db;
    }

    public function createDb($dbName)
    {
        $sql = "CREATE DATABASE $dbName";
        $statement = $this->db->prepare($sql);
//        $statement->bindParam(":dbName", $dbName);
        return $statement->execute();
    }

    // first parameter MUST be id with type AUTO_INCREMENT
    public function createTable($tableName, array $colsAndTypes, $colForeignKey = null, $referenceTable = null)
    {
        $cols = array_keys($colsAndTypes);
        $bodySql = "";
        foreach ($colsAndTypes as $col => $type) {
            $bodySql .= $col . " " . $type . ",\n";
        }
        $sql = "CREATE TABLE $tableName (\n$bodySql" . "PRIMARY KEY ($cols[0])\n";
        if (!is_null($colForeignKey) && !is_null($referenceTable)) {
            $sql .= ",FOREIGN KEY ($colForeignKey) REFERENCES $referenceTable(id)\n)";
        } else
            $sql .= ")";
        $statement = $this->db->prepare($sql);
//        $statement->bindParam(":tableName", $tableName);
        return $statement->execute();
    }

    public function selectAll($table)
    {
        $sql = "SELECT * FROM $table";
        $statement = $this->db->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getColsFromTable($table, $withId = false, $withNull = false)
    {
        $cols = [];
        $sql = "DESCRIBE $table";
        $statement = $this->db->prepare($sql);
        $statement->execute();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $item) {
            if ($withNull) {
                $cols[] = $item["Field"];
            } else {
                $isNull = $item["Null"];
                if ($isNull === "NO") {
                    $cols[] = $item["Field"];
                }
            }
        }
        return ($withId) ? $cols: array_slice($cols, 1);
    }

    public function insertDataIntoTable($table, array $data, $withId = false, $withNull = false)
    {
        $colNames = $this->getColsFromTable($table, $withId, $withNull);
        $colStr = implode(", ", $colNames);
        $dataStr = "";
        foreach ($data as $val) {
            $dataStr .= (is_numeric($val)) ? $val . ", " : "'" . $val . "', ";
        }
        $dataStr = rtrim($dataStr, ", ");
        $sql = "INSERT INTO $table ($colStr) VALUES ($dataStr)";
        $statement = $this->db->prepare($sql);
        return $statement->execute();
    }

    //set data
    public function updateDataById($table, $idCol, $id, array $colsAndData)
    {
        if (!is_array($id)) {
            $id = [$id];
        }
//        else
//            return new Exception("id is not iterable variable or not a numeric\n");
        $colsAndDataStr = "";
        foreach ($colsAndData as $col => $val) {
            $colsAndDataStr .= (is_numeric($val)) ? $col . "=" . $val . ", " : $col . "=" . "'" . $val . "'" . ", ";
        }
        $colsAndDataStr = rtrim($colsAndDataStr, ", ");
        $idStr = "";
        foreach ($id as $item) {
            $idStr .= (is_numeric($item)) ? $item : "'" . $item . "'";
        }
        $sql = "UPDATE $table SET $colsAndDataStr WHERE $idCol in ($idStr)";
        $statement = $this->db->prepare($sql);
        return $statement->execute();
    }

    //select data with filter
    public function filterDataByCol($table, $col, $filter)
    {
        if (!is_array($filter) && (is_numeric($filter) || is_string($filter))) {
            $filter = [$filter];
        }
//        else
//            return new Exception("filter is not iterable variable or not a numeric or not a number\n");
        $isNumsArray = is_numeric($filter[0]);
        if ($isNumsArray) {
            $filterStr = implode(", ", $filter);
        }
        else {
            $filterStr = "";
            foreach ($filter as $item) {
                $filterStr .= "'" . $item . "', ";
            }
            $filterStr = rtrim($filterStr, ", ");
        }
        $sql = "SELECT * FROM $table WHERE $col in ($filterStr)";
        $statement = $this->db->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filterDataBetween($table, $col, $valFrom = null, $valTo = null)
    {
        if (is_null($valFrom) && is_null($valTo)) {
            return $this->selectAll($table);
        }
        else if (is_numeric($valFrom) && is_null($valTo)) {
            $sql = "SELECT * FROM $table WHERE $col >= $valFrom";
        }
        else if (is_numeric($valTo) && is_null($valFrom)) {
            $sql = "SELECT * FROM $table WHERE $col <= $valTo";
        }
        else if (is_numeric($valFrom) && is_numeric($valTo) && $valTo > $valFrom) {
            $sql = "SELECT * FROM $table WHERE $col BETWEEN $valFrom AND $valTo";
        }
        else
            return false;
        $statement = $this->db->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllLogins()
    {
        $logins = [];
        $sql = "SELECT login FROM users";
        $statement = $this->db->prepare($sql);
        $statement->execute();

        $arr = $statement->fetchAll(2);
        if (isset($arr)) {
            foreach ($arr as $login) {
                $logins[] = $login["login"];
            }
        }
        return $logins;
    }

    public function deleteRowByCond($table, array $condColIdAndId)
    {
        $strSql = "";
        foreach ($condColIdAndId as $idCol => $id) {
            if (is_numeric($id)) {
                $strSql .= $idCol . "=" . $id . " AND ";
            } else {
                $strSql .= $idCol . "=" . "'" . $id . "'" . " AND ";
            }
        }
        $strSql = rtrim($strSql, " AND ");
        $sql = "DELETE FROM $table WHERE $strSql";
        $statement = $this->db->prepare($sql);
        return $statement->execute();
    }

    public function getDataWithLimits($table, $start, $offset = null)
    {
        $sql = "SELECT * FROM $table LIMIT $start";
        if ($offset) {
            $sql .= ", $offset";
        }
        $statement = $this->db->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    static function printRedirect($redirect_page)
    {
        $str_redir = "'" . $redirect_page . "'";
        echo '<script>' .
            "window.location.href = " . $str_redir. ";
          </script>";
    }

    static function printAlertRedirect($message, $redirect_page)
    {
        $str_mes = "'" . $message . "'";
        $str_redir = "'" . $redirect_page . "'";
        echo "<script>
            alert(" . $str_mes . ");" . "\n" .
            "window.location.href = " . $str_redir. ";
          </script>";
    }

    static function printAlert($message)
    {
        $str_mes = "'" . $message . "'";
        echo "<script>
            alert(" . $str_mes . ");
          </script>";
    }

}
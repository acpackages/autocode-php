<?php

namespace AcSql\Daos;

class AcSqliteDao {
    private $pdo;
    private $databasePath;
    private $logger;

    public function __construct($databasePath, $logger) {
        $this->databasePath = $databasePath;
        $this->logger = $logger;
    }

    private function getConnection() {
        if (!$this->pdo) {
            $this->pdo = new PDO("sqlite:" . $this->databasePath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->pdo;
    }

    public function checkDatabaseExist() {
        $result = ['success' => false, 'message' => 'Database file does not exist'];
        if (file_exists($this->databasePath)) {
            $result = ['success' => true, 'message' => 'Database file exists'];
        }
        return $result;
    }

    public function createDatabase() {
        $this->getConnection();
        return ['success' => true, 'message' => 'Database created (if it did not exist)'];
    }

    public function checkTableExist($table) {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = ?");
        $stmt->execute([$table]);
        return ['success' => $stmt->fetch() ? true : false, 'message' => $stmt->fetch() ? "Table exists" : "Table does not exist"];
    }

    public function deleteRows($table, $condition = "", $parameters = []) {
        $pdo = $this->getConnection();
        $query = "DELETE FROM $table" . ($condition ? " WHERE $condition" : "");
        $stmt = $pdo->prepare($query);
        $stmt->execute($parameters);
        return ['success' => true, 'affectedRows' => $stmt->rowCount()];
    }

    public function getDatabaseTables() {
        $pdo = $this->getConnection();
        $stmt = $pdo->query("SELECT name as table_name FROM sqlite_master WHERE type='table'");
        return ['success' => true, 'tables' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function getTableDefinition($table) {
        $pdo = $this->getConnection();
        $stmt = $pdo->query("PRAGMA table_info($table)");
        return ['success' => true, 'columns' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function insertRows($table, $values) {
        $pdo = $this->getConnection();
        $columns = implode(", ", array_keys($values));
        $placeholders = implode(", ", array_fill(0, count($values), "?"));
        $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
        $stmt->execute(array_values($values));
        return ['success' => true, 'lastInsertedId' => $pdo->lastInsertId()];
    }

    public function selectStatement($query, $parameters = []) {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare($query);
        $stmt->execute($parameters);
        return ['success' => true, 'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function sqlStatement($query, $parameters = []) {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare($query);
        $stmt->execute($parameters);
        return ['success' => true, 'affectedRows' => $stmt->rowCount()];
    }

    public function updateRows($table, $values, $condition = "", $parameters = []) {
        $pdo = $this->getConnection();
        $setFields = implode(", ", array_map(fn($key) => "$key = ?", array_keys($values)));
        $stmt = $pdo->prepare("UPDATE $table SET $setFields" . ($condition ? " WHERE $condition" : ""));
        $stmt->execute(array_merge(array_values($values), $parameters));
        return ['success' => true, 'affectedRows' => $stmt->rowCount()];
    }
}

?>
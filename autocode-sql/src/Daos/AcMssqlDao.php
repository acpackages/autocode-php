<?php

namespace AcSql\Daos;

class AcMssqlDao extends AcBaseSqlDao {
    private ?PDO $pool = null;

    public function checkDatabaseExist(): AcResult {
        $result = new AcResult();
        try {
            $query = "SELECT database_id FROM sys.databases WHERE name = ?";
            $db = $this->getConnectionObject();
            if (!$db) {
                return $result->setFailure(['message' => 'Database connection error']);
            }
            $stmt = $db->prepare($query);
            $stmt->execute([$this->sqlConnection['database']]);
            $exists = $stmt->fetch();
            if($exists){
                $result->setSuccess(true, message:'Database exists');
            }
            else{
                $result->setSuccess(false,message:'Database does not exist');
            }
        } catch (PDOException $ex) {
            $result->setException($ex);
        }        
        return $result;
    }

    public function checkTableExist(string $table): AcResult {
        $result = new AcResult();
        try {
            $query = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ?";
            $db = $this->getConnectionObject();
            if (!$db) {
                return $result->setFailure(message:'Database connection error');
            }
            $stmt = $db->prepare($query);
            $stmt->execute([$table]);
            if($stmt->fetch()){
                $result->setSuccess(true, message:'Table exists');
            }
            else{
                $result->setSuccess(false,message:'Table does not exist');
            }                
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function createDatabase(): AcResult {
        $result = new AcResult();
        try {
            $query = "IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = ?) CREATE DATABASE [{$this->sqlConnection['database']}]";
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($query);
            $stmt->execute([$this->sqlConnection['database']]);
            $result->setSuccess(true,message:'Database created');
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function deleteRows(string $table, string $condition = "", array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $query = "DELETE FROM {$table} " . ($condition ? "WHERE {$condition}" : "");
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($query);
            foreach ($parameters as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $result->affectedRowsCount = $stmt->rowCount();
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function getConnectionObject(): ?PDO {
        try {
            if (!$this->pool) {
                $dsn = "sqlsrv:Server={$this->sqlConnection['hostname']},{$this->sqlConnection['port']};Database={$this->sqlConnection['database']}";
                $this->pool = new PDO($dsn, $this->sqlConnection['username'], $this->sqlConnection['password']);
                $this->pool->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            return $this->pool;
        } catch (PDOException $ex) {
            error_log("Database Connection Error: " . $ex->getMessage());
            return null;
        }
    }

    public function getDatabaseTables(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
            $db = $this->getConnectionObject();
            $stmt = $db->query($query);
            $result->rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function getTableDefinition(string $table): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $connection = $this->getConnection();
            $stmt = $connection->prepare("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :table");
            $stmt->execute(['table' => $table]);
            $result->rows = $stmt->fetchAll();
            $result->setSuccess();
        } catch (PDOException $e) {
            $result->setException($e);
        }
        return $result;
    }

    public function insertRows(string $table, array $values, string $primaryKeyColumn = ""): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $columns = implode(", ", array_keys($values));
            $placeholders = implode(", ", array_map(fn($col) => ":$col", array_keys($values)));
            $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            if ($primaryKeyColumn) {
                $query .= " OUTPUT INSERTED.{$primaryKeyColumn} AS lastInsertedId";
            }
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($query);
            foreach ($values as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $result->lastInsertedId = $db->lastInsertId();
            return $result->setSuccess();
        } catch (PDOException $ex) {
            return $result->setException($ex);
        }
    }

    public function getRows(string $statement, ?string $condition = "", ?array $parameters = [],?string $mode = AcEnumSelectMode::LIST, ?array $formatColumns = [], ?int $startIndex = -1, ?int $rowCount): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $connection = $this->getConnection();
            $stmt = $connection->prepare($statement);
            $stmt->execute($parameters);
            $result->rows = $firstRowOnly ? $stmt->fetch() : $stmt->fetchAll();
            $result->setSuccess();
        } catch (PDOException $e) {
            $result->setException($e);
        }
        return $result;
    }

    public function executeStatement(string $statement, array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $connection = $this->getConnection();
            $stmt = $connection->prepare($statement);
            $stmt->execute($parameters);
            $result->setSuccess();
        } catch (PDOException $e) {
            $result->setException($e);
        }
        return $result;
    }

    public function sqlBatchStatement(array $statements, array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            foreach ($statements as $statement) {
                $stmt = $connection->prepare($statement);
                $stmt->execute($parameters);
            }
            $connection->commit();
            $result->setSuccess();
        } catch (PDOException $e) {
            $connection->rollBack();
            $result->setException($e);
        }
        return $result;
    }

    public function updateRows(string $table, array $values, string $condition = "", array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $setClause = implode(", ", array_map(fn($col) => "$col = :$col", array_keys($values)));
            $query = "UPDATE {$table} SET {$setClause} " . ($condition ? "WHERE {$condition}" : "");
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($query);
            foreach (array_merge($values, $parameters) as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $result->affectedRowsCount = $stmt->rowCount();
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }
}

?>
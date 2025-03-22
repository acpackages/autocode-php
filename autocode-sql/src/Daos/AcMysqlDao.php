<?php

namespace AcSql\Daos;
require_once __DIR__ .'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../Enums/AcEnumRowOperation.php';
require_once __DIR__.'./../Enums/AcEnumSelectMode.php';
require_once __DIR__.'./../Enums/AcEnumTableFieldFormat.php';
require_once __DIR__ .'./../Models/AcSqlDaoResult.php';
require_once 'AcBaseSqlDao.php';

use PDO;
use PDOException;
use Autocode\AcResult;
use AcSql\Daos\AcBaseSqlDao;
use AcSql\Enums\AcEnumRowOperation;
use AcSql\Enums\AcEnumSelectMode;
use AcSql\Enums\AcEnumTableFieldFormat;
use AcSql\Models\AcSqlDaoResult;

class AcMysqlDao extends AcBaseSqlDao {
    private ?PDO $pool = null;

    public function checkDatabaseExist(): AcResult {
        $result = new AcResult();
        try {
            $statement = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
            $db = $this->getConnectionObject(false);
            if (!$db) {
                $result->setFailure(message:'Database connection error');
            }
            else{
                $stmt = $db->prepare($statement);
                $stmt->execute([$this->sqlConnection->database]);
                $exists = $stmt->fetch();
                $result->setSuccess((bool) $exists, message: $exists ? 'Database exists' : 'Database does not exist');
            }            
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function checkTableExist(string $table): AcResult {
        $result = new AcResult();
        try {
            $statement = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?";
            $db = $this->getConnectionObject();
            if (!$db) {
                return $result->setFailure(message:'Database connection error');
            }
            $stmt = $db->prepare($statement);
            $stmt->execute([$this->sqlConnection->database, $table]);
            $result->setSuccess((bool) $stmt->fetch(), message: $stmt->fetch() ? 'Table exists' : 'Table does not exist');
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function createDatabase(): AcResult {
        $result = new AcResult();
        try {
            $statement = "CREATE DATABASE IF NOT EXISTS `{$this->sqlConnection->database}`";
            $db = $this->getConnectionObject(false);
            $stmt = $db->prepare($statement);
            $stmt->execute();
            $result->setSuccess(true, message: 'Database created');
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function deleteRows(string $table, string $condition = "", array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "DELETE FROM {$table} " . ($condition ? "WHERE {$condition}" : "");
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
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

    public function getConnectionObject(bool $includeDatabase = true): ?PDO {
        $result = null;
        try {
            if (!$this->pool || !$includeDatabase) {
                if($includeDatabase){
                    $dsn = "mysql:host={$this->sqlConnection->hostname};dbname={$this->sqlConnection->database};port={$this->sqlConnection->port}";
                    $this->pool = new PDO($dsn, $this->sqlConnection->username, $this->sqlConnection->password);
                    $this->pool->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);                    
                    $result = $this->pool;
                }
                else{
                    $dsn = "mysql:host={$this->sqlConnection->hostname};port={$this->sqlConnection->port}";
                    $pool = new PDO($dsn, $this->sqlConnection->username, $this->sqlConnection->password);
                    $pool->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $result = $pool;
                }
            }
            else if($this->pool){
                $result = $this->pool;
            }
        } catch (PDOException $ex) {
            print_r($ex);
            error_log("Database Connection Error: " . $ex->getMessage());
        }
        return $result;
    }

    public function getDatabaseTables(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?";
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute([$this->sqlConnection->database]);
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
            $db = $this->getConnectionObject();
            $stmt = $db->prepare("DESCRIBE `{$table}`");
            $stmt->execute();
            $result->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result->setSuccess();
        } catch (PDOException $e) {
            $result->setException($e);
        }
        return $result;
    }

    public function insertRows(string $table, array $values, ?string $primaryKeyColumn = ""): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $columns = implode(", ", array_keys($values));
            $placeholders = implode(", ", array_map(fn($col) => ":$col", array_keys($values)));
            $statement = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            foreach ($values as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute();
            $result->lastInsertedId = $db->lastInsertId();
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }
    
    public function selectStatement(string $statement, ?string $mode = AcEnumSelectMode::LIST, ?string $condition = "", ?array $parameters = [], ?array $formatColumns = [], ?bool $firstRowOnly = false): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $db = $this->getConnectionObject();
            if($condition!=""){
                $statement .= " WHERE $condition";
            }
            $stmt = $db->prepare($statement);
            $stmt->execute($parameters);
            $result->rows = $firstRowOnly ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }    

    public function sqlStatement(string $statement, ?string $operation = AcEnumRowOperation::UNKNOWN, ?array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
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
            $db = $this->getConnectionObject();
            $db->beginTransaction();
            foreach ($statements as $statement) {
                $stmt = $db->prepare($statement);
                $stmt->execute();
            }
            $db->commit();
            $result->setSuccess();
        } catch (PDOException $e) {
            $db->rollBack();
            $result->setException($e);
        }
        return $result;
    }

    public function updateRows(string $table, array $values, string $condition = "", array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "";
            $updateFieldIndex = -1;
            $setValues = [];     
            $parameterValues = $parameters;       
            foreach($values as $key => $value){
                $updateFieldIndex++;
                $parameterKey = ':updateField'.$updateFieldIndex;
                while(in_array($parameterKey,array_keys($parameterValues))){
                    $updateFieldIndex++;
                    $parameterKey = ':updateField'.$updateFieldIndex;
                }
                $parameterValues[$parameterKey] =  $value;
                $setValues[] = "$key = $parameterKey";
            }
            $setClause = implode(", ", $setValues);
            $statement = "UPDATE {$table} SET {$setClause} " . ($condition ? "WHERE {$condition}" : "");
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            foreach ($parameterValues as $key => $value) {
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

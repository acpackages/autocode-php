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
            $statement = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = @databaseName";
            $parameterValues = [];
            $this->setSqlStatementParameters($statement,parameters:["@databaseName"=> $this->sqlConnection->database],values:$parameterValues);
            $db = $this->getConnectionObject(false);
            if (!$db) {
                $result->setFailure(message:'Database connection error');
            }
            else{
                $stmt = $db->prepare($statement);
                $stmt->execute($parameterValues);
                $exists = $stmt->fetch();
                $result->setSuccess((bool) $exists, message: $exists ? 'Database exists' : 'Database does not exist');
            }            
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function checkTableExist(string $tableName): AcResult {
        $result = new AcResult();
        try {
            $db = $this->getConnectionObject();
            if (!$db) {
                $result->setFailure(message:'Database connection error');
            }
            else{                
                $statement = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @databaseName AND TABLE_NAME = @tableName";
                $parameterValues = [];
                $this->setSqlStatementParameters($statement,parameters:["@databaseName"=> $this->sqlConnection->database,'@tableName'=>$tableName],values:$parameterValues);
                $stmt = $db->prepare($statement);
                $stmt->execute([$this->sqlConnection->database, $tableName]);
                $exists = $stmt->fetch();
                $result->setSuccess((bool) $exists, message: $exists ? 'Table exists' : 'Table does not exist');
            }            
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function createDatabase(): AcResult {
        $result = new AcResult();
        try {
            $db = $this->getConnectionObject(false);            
            $statement = "CREATE DATABASE IF NOT EXISTS `{$this->sqlConnection->database}`";
            $stmt = $db->prepare($statement);
            $stmt->execute();
            $result->setSuccess(true, message: 'Database created');
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function deleteRows(string $tableName, string $condition = "", array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult(operation:AcEnumRowOperation::DELETE);
        try {
            $statement = "DELETE FROM {$tableName} " . ($condition ? "WHERE {$condition}" : "");
            $db = $this->getConnectionObject();
            $parameterValues = [];
            $this->setSqlStatementParameters($statement,parameters:$parameters,values:$parameterValues);
            $stmt = $db->prepare($statement);
            $stmt->execute($parameterValues);
            $result->affectedRowsCount = $stmt->rowCount();
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function executeMultipleSqlStatements(array $statements, array $parameters = []): AcSqlDaoResult {
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

    public function executeStatement(string $statement, ?string $operation = AcEnumRowOperation::UNKNOWN, ?array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult(operation:$operation);
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

    public function getDatabaseFuntions(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT ROUTINE_NAME, DATA_TYPE, CREATED, DEFINER  FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_SCHEMA = ? AND ROUTINE_TYPE = 'FUNCTION'";
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

    public function getDatabaseStoredProcedures(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT ROUTINE_NAME, CREATED, DEFINER  FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_SCHEMA = ? AND ROUTINE_TYPE = 'PROCEDURE'";
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

    public function getDatabaseTriggers(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_STATEMENT, ACTION_TIMING FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA  = ?";
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

    public function getDatabaseViews(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = ?";
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

    public function getRows(?string $statement, ?string $condition = "", ?array $parameters = [],?string $mode = AcEnumSelectMode::LIST, ?array $formatColumns = [], ?int $startIndex = -1, ?int $rowCount = -1): AcSqlDaoResult {
        $result = new AcSqlDaoResult(operation:AcEnumRowOperation::SELECT);
        try {
            $db = $this->getConnectionObject();
            if($condition!=""){
                $statement .= " WHERE $condition";
            }
            $parameterValues = [];
            $this->setSqlStatementParameters($statement,parameters:$parameters,values:$parameterValues);
            $stmt = $db->prepare($statement);
            $stmt->execute($parameterValues);
            $result->rows = $mode == AcEnumSelectMode::FIRST ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }    

    public function getTableColumns(string $tableName): AcSqlDaoResult {
        $result = new AcSqlDaoResult(operation:AcEnumRowOperation::SELECT);
        try {
            $db = $this->getConnectionObject();
            $stmt = $db->prepare("DESCRIBE `{$tableName}`");
            $stmt->execute();
            $result->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result->setSuccess();
        } catch (PDOException $e) {
            $result->setException($e);
        }
        return $result;
    }

    public function getViewColumns(string $viewName): AcSqlDaoResult {
        $result = new AcSqlDaoResult(operation:AcEnumRowOperation::SELECT);
        try {
            $db = $this->getConnectionObject();
            $stmt = $db->prepare("DESCRIBE `{$viewName}`");
            $stmt->execute();
            $result->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result->setSuccess();
        } catch (PDOException $e) {
            $result->setException($e);
        }
        return $result;
    }

    public function insertRows(string $table, array $values): AcSqlDaoResult {
        $result = new AcSqlDaoResult(operation:AcEnumRowOperation::INSERT);
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
    
    public function updateRows(string $table, array $values, string $condition = "", array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult(operation:AcEnumRowOperation::UPDATE);
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

<?php

namespace AcSql\Daos;

require_once __DIR__ . '/../../../autocode-data-dictionary/vendor/autoload.php';
require_once __DIR__ . '/../../../autocode/vendor/autoload.php';
require_once __DIR__ . '/../Models/AcSqlDaoResult.php';
require_once 'AcBaseSqlDao.php';

use AcDataDictionary\Enums\AcEnumDDColumnProperty;
use AcDataDictionary\Models\AcDDFunction;
use AcDataDictionary\Models\AcDDStoredProcedure;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTrigger;
use AcDataDictionary\Models\AcDDView;
use PDO;
use PDOException;
use Autocode\Models\AcResult;
use AcSql\Daos\AcBaseSqlDao;
use AcDataDictionary\Enums\AcEnumDDRowOperation;
use AcDataDictionary\Enums\AcEnumDDSelectMode;
use AcDataDictionary\Enums\AcEnumDDColumnFormat;
use AcSql\Models\AcSqlDaoResult;

class AcMssqlDao extends AcBaseSqlDao
{
    private ?PDO $pool = null;

    public function checkDatabaseExist(): AcResult {
        $result = new AcResult();
        try {
            $statement = "SELECT name FROM sys.databases WHERE name = ?";
            $db = $this->getConnectionObject(false);
            if (!$db) {
                $result->setFailure(message: 'Database connection error');
            } else {
                $stmt = $db->prepare($statement);
                $stmt->execute([$this->sqlConnection->database]);
                $exists = $stmt->fetch();
                $result->setSuccess((bool)$exists, message: $exists ? 'Database exists' : 'Database does not exist');
            }
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function checkTableExist(string $tableName): AcResult {
        $result = new AcResult();
        try {
            $statement = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_CATALOG = ? AND TABLE_NAME = ?";
            $db = $this->getConnectionObject();
            if (!$db) {
                $result->setFailure(message: 'Database connection error');
            } else {
                $stmt = $db->prepare($statement);
                $stmt->execute([$this->sqlConnection->database, $tableName]);
                $exists = $stmt->fetch();
                $result->setSuccess((bool)$exists, message: $exists ? 'Table exists' : 'Table does not exist');
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
            $statement = "IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = ?) BEGIN CREATE DATABASE [$this->sqlConnection->database] END";
            $stmt = $db->prepare($statement);
            $stmt->execute([$this->sqlConnection->database]);
            $result->setSuccess(true, message: 'Database created');
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function deleteRows(string $tableName, string $condition = "", array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::DELETE);
        try {
            $statement = "DELETE FROM [$tableName] " . ($condition ? "WHERE $condition" : "");
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute($parameters);
            $result->affectedRowsCount = $stmt->rowCount();
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function executeMultipleSqlStatements(array $statements, array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        $db = $this->getConnectionObject();
        try {
            $db->beginTransaction();
            foreach ($statements as $statement) {
                $stmt = $db->prepare($statement);
                $stmt->execute();
            }
            $db->commit();
            $result->setSuccess();
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $result->setException($e);
        }
        return $result;
    }

    public function executeStatement(string $statement, ?string $operation = AcEnumDDRowOperation::UNKNOWN, ?array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult(operation: $operation);
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
                if ($includeDatabase) {
                    $dsn = "sqlsrv:Server={$this->sqlConnection->hostname}," . (int)$this->sqlConnection->port . ";Database={$this->sqlConnection->database}";
                    $this->pool = new PDO($dsn, $this->sqlConnection->username, $this->sqlConnection->password);
                    $this->pool->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $result = $this->pool;
                } else {
                    $dsn = "sqlsrv:Server={$this->sqlConnection->hostname}," . (int)$this->sqlConnection->port;
                    $pool = new PDO($dsn, $this->sqlConnection->username, $this->sqlConnection->password);
                    $pool->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $result = $pool;
                }
            } else {
                $result = $this->pool;
            }
        } catch (PDOException $ex) {
            error_log("Database Connection Error: " . $ex->getMessage());
        }
        return $result;
    }

    public function getDatabaseFunctions(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT ROUTINE_NAME FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_CATALOG = ? AND ROUTINE_TYPE = 'FUNCTION'";
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute([$this->sqlConnection->database]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $result->rows[] = [
                    AcDDFunction::KEY_FUNCTION_NAME => $row["ROUTINE_NAME"]
                ];
            }
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function getDatabaseStoredProcedures(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT ROUTINE_NAME FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_CATALOG = ? AND ROUTINE_TYPE = 'PROCEDURE'";
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute([$this->sqlConnection->database]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $result->rows[] = [
                    AcDDStoredProcedure::KEY_STORED_PROCEDURE_NAME => $row["ROUTINE_NAME"]
                ];
            }
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function getDatabaseTables(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_CATALOG = ? AND TABLE_TYPE = 'BASE TABLE'";
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute([$this->sqlConnection->database]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $result->rows[] = [
                    AcDDTable::KEY_TABLE_NAME => $row["TABLE_NAME"]
                ];
            }
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function getDatabaseTriggers(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT name AS TRIGGER_NAME FROM sys.triggers WHERE parent_class_desc = 'OBJECT_OR_COLUMN'";
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $result->rows[] = [
                    AcDDTrigger::KEY_TRIGGER_NAME => $row["TRIGGER_NAME"]
                ];
            }
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function getDatabaseViews(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT TABLE_NAME AS VIEW_NAME FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_CATALOG = ?";
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute([$this->sqlConnection->database]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $result->rows[] = [
                    AcDDView::KEY_VIEW_NAME => $row["VIEW_NAME"]
                ];
            }
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function getRows(string $tableName, ?string $condition = "", ?array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT * FROM {$tableName} " . ($condition ? "WHERE {$condition}" : "");
            $db = $this->getConnectionObject();
            $parameterValues = [];
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues, passedParameters: $parameters);
            $statement = $setParametersResult['statement'];
            $parameterValues = $setParametersResult['statementParameters'];
            $stmt = $db->prepare($statement);
            $stmt->execute($parameterValues);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $result->rows[] = $row;
            }
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }
    
}

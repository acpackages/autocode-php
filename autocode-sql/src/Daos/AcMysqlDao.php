<?php

namespace AcSql\Daos;
require_once __DIR__ . './../../../autocode-data-dictionary/vendor/autoload.php';
require_once __DIR__ . './../../../autocode/vendor/autoload.php';
require_once __DIR__ . './../Enums/AcEnumRowOperation.php';
require_once __DIR__ . './../Enums/AcEnumSelectMode.php';
require_once __DIR__ . './../Enums/AcEnumTableFieldFormat.php';
require_once __DIR__ . './../Models/AcSqlDaoResult.php';
require_once 'AcBaseSqlDao.php';

use AcDataDictionary\Enums\AcEnumDDFieldProperty;
use AcDataDictionary\Models\AcDDFunction;
use AcDataDictionary\Models\AcDDStoredProcedure;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableField;
use AcDataDictionary\Models\AcDDTrigger;
use AcDataDictionary\Models\AcDDView;
use AcDataDictionary\Models\AcDDViewField;
use PDO;
use PDOException;
use Autocode\AcResult;
use AcSql\Daos\AcBaseSqlDao;
use AcSql\Enums\AcEnumRowOperation;
use AcSql\Enums\AcEnumSelectMode;
use AcSql\Enums\AcEnumTableFieldFormat;
use AcSql\Models\AcSqlDaoResult;

class AcMysqlDao extends AcBaseSqlDao
{
    private ?PDO $pool = null;

    public function checkDatabaseExist(): AcResult
    {
        $result = new AcResult();
        try {
            $statement = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = @databaseName";
            $parameterValues = [];
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues,passedParameters: ["@databaseName" => $this->sqlConnection->database]);
            $statement = $setParametersResult['statement'];
            $parameterValues = $setParametersResult['statementParameters'];
            $db = $this->getConnectionObject(false);
            if (!$db) {
                $result->setFailure(message: 'Database connection error');
            } else {
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

    public function checkTableExist(string $tableName): AcResult
    {
        $result = new AcResult();
        try {
            $db = $this->getConnectionObject();
            if (!$db) {
                $result->setFailure(message: 'Database connection error');
            } else {
                $statement = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @databaseName AND TABLE_NAME = @tableName";
                $parameterValues = [];
                $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters:$parameterValues, passedParameters:["@databaseName" => $this->sqlConnection->database, '@tableName' => $tableName]);
                $statement = $setParametersResult['statement'];
                $parameterValues = $setParametersResult['statementParameters'];
                $stmt = $db->prepare($statement);
                $stmt->execute($parameterValues);
                $exists = $stmt->fetch();
                $result->setSuccess((bool) $exists, message: $exists ? 'Table exists' : 'Table does not exist');
            }
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function createDatabase(): AcResult
    {
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

    public function deleteRows(string $tableName, string $condition = "", array $parameters = []): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::DELETE);
        try {
            $statement = "DELETE FROM {$tableName} " . ($condition ? "WHERE {$condition}" : "");
            $db = $this->getConnectionObject();
            $parameterValues = [];
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters:$parameterValues, passedParameters: $parameters );
            $statement = $setParametersResult['statement'];
            $parameterValues = $setParametersResult['statementParameters'];
            $stmt = $db->prepare($statement);
            $stmt->execute($parameterValues);
            $result->affectedRowsCount = $stmt->rowCount();
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function executeMultipleSqlStatements(array $statements, array $parameters = []): AcSqlDaoResult
    {
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

    public function executeStatement(string $statement, ?string $operation = AcEnumRowOperation::UNKNOWN, ?array $parameters = []): AcSqlDaoResult
    {
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

    public function getConnectionObject(bool $includeDatabase = true): ?PDO
    {
        $result = null;
        try {
            if (!$this->pool || !$includeDatabase) {
                if ($includeDatabase) {
                    $dsn = "mysql:host={$this->sqlConnection->hostname};dbname={$this->sqlConnection->database};port={$this->sqlConnection->port}";
                    $this->pool = new PDO($dsn, $this->sqlConnection->username, $this->sqlConnection->password);
                    $this->pool->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $result = $this->pool;
                } else {
                    $dsn = "mysql:host={$this->sqlConnection->hostname};port={$this->sqlConnection->port}";
                    $pool = new PDO($dsn, $this->sqlConnection->username, $this->sqlConnection->password);
                    $pool->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $result = $pool;
                }
            } else if ($this->pool) {
                $result = $this->pool;
            }
        } catch (PDOException $ex) {
            print_r($ex);
            error_log("Database Connection Error: " . $ex->getMessage());
        }
        return $result;
    }

    public function getDatabaseFuntions(): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT ROUTINE_NAME, DATA_TYPE, CREATED, DEFINER  FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_SCHEMA = ? AND ROUTINE_TYPE = 'FUNCTION'";
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

    public function getDatabaseStoredProcedures(): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT ROUTINE_NAME, CREATED, DEFINER  FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_SCHEMA = ? AND ROUTINE_TYPE = 'PROCEDURE'";
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

    public function getDatabaseTables(): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE='BASE TABLE'";
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

    public function getDatabaseTriggers(): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_STATEMENT, ACTION_TIMING FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA  = ?";
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute([$this->sqlConnection->database]);
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

    public function getDatabaseViews(): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult();
        try {
            $statement = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = ?";
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute([$this->sqlConnection->database]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $result->rows[] = [
                    AcDDView::KEY_VIEW_NAME => $row["TABLE_NAME"]
                ];
            }
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function getRows(?string $statement, ?string $condition = "", ?array $parameters = [], ?string $mode = AcEnumSelectMode::LIST , ?array $formatColumns = [], ?int $startIndex = -1, ?int $rowCount = -1): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::SELECT);
        try {
            $db = $this->getConnectionObject();
            if ($condition != "") {
                $statement .= " WHERE $condition";
            }
            $parameterValues = [];
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues, passedParameters: $parameters);
            $statement = $setParametersResult['statement'];
            $parameterValues = $setParametersResult['statementParameters'];
            $stmt = $db->prepare($statement);
            $stmt->execute($parameterValues);
            $result->rows = $mode == AcEnumSelectMode::FIRST ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function getTableColumns(string $tableName): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::SELECT);
        try {
            $db = $this->getConnectionObject();
            $stmt = $db->prepare("DESCRIBE `{$tableName}`");
            $stmt->execute();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $properties = [];
                if ($row["Null"] != "YES") {
                    $properties[AcEnumDDFieldProperty::NOT_NULL] = false;
                }
                if ($row["Key"] == "PRI") {
                    $properties[AcEnumDDFieldProperty::PRIMARY_KEY] = true;
                }
                if ($row["Default"] != null) {
                    $properties[AcEnumDDFieldProperty::DEFAULT_VALUE] = $row["Default"];
                }
                $result->rows[] = [
                    AcDDTableField::KEY_FIELD_NAME => $row["Field"],
                    AcDDTableField::KEY_FIELD_TYPE => $row["Type"],
                    AcDDTableField::KEY_FIELD_PROPERTIES => $properties
                ];
            }
            $result->setSuccess();
        } catch (PDOException $e) {
            $result->setException($e);
        }
        return $result;
    }

    public function getViewColumns(string $viewName): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::SELECT);
        try {
            $db = $this->getConnectionObject();
            $stmt = $db->prepare("DESCRIBE `{$viewName}`");
            $stmt->execute();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $properties = [];
                if ($row["Null"] != "YES") {
                    $properties[AcEnumDDFieldProperty::NOT_NULL] = false;
                }
                if ($row["Key"] == "PRI") {
                    $properties[AcEnumDDFieldProperty::PRIMARY_KEY] = true;
                }
                if ($row["Default"] != null) {
                    $properties[AcEnumDDFieldProperty::DEFAULT_VALUE] = $row["Default"];
                }
                $result->rows[] = [
                    AcDDViewField::KEY_FIELD_NAME => $row["Field"],
                    AcDDViewField::KEY_FIELD_TYPE => $row["Type"],
                    AcDDViewField::KEY_FIELD_PROPERTIES => $properties
                ];
            }
            $result->setSuccess();
        } catch (PDOException $e) {
            $result->setException($e);
        }
        return $result;
    }

    public function insertRow(string $tableName, array $row): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::INSERT);
        try {
            $columns = [];
            $values = [];
            $index = -1;
            foreach ($row as $key => $value) {
                $index++;
                $columns[] = $key;
                $values[] = "@$index";
                $parameters["@$index"] = $value;
            }
            $statement = "INSERT INTO {$tableName} (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ");";
            $parameterValues = [];
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues,passedParameters: $parameters,);
            $statement = $setParametersResult['statement'];
            $parameterValues = $setParametersResult['statementParameters'];
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute($parameterValues);
            $result->lastInsertedId = $db->lastInsertId();
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function insertRows(string $tableName, array $rows): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::INSERT);
        try {
            $statements = [];
            $parameters = [];
            $index = -1;
            foreach ($rows as $row) {
                $columns = [];
                $values = [];
                foreach ($row as $key => $value) {
                    $index++;
                    $columns[] = $key;
                    $values[] = "@$index";
                    $parameters["@$index"] = $value;
                }
                $statement = "INSERT INTO {$tableName} (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ");";
                $statements[] = $statement;
            }
            $statement = implode("", $statements);
            $parameterValues = [];
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues,passedParameters: $parameters);
            $statement = $setParametersResult['statement'];
            $parameterValues = $setParametersResult['statementParameters'];
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute($parameterValues);
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function updateRow(string $tableName, array $row, string $condition = "", array $parameters = []): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::UPDATE);
        try {
            $setValues = [];
            $index = -1;
            foreach ($row as $key => $value) {
                $index++;
                while (in_array("@$index", array_keys($parameters))) {
                    $index++;
                }
                $parameters["@$index"] = $value;
                $setValues[] = "$key = @$index";
            }
            $setClause = implode(", ", $setValues);
            $statement = "UPDATE {$tableName} SET {$setClause} " . ($condition ? "WHERE {$condition}" : "");
            $parameterValues = [];
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues,passedParameters: $parameters);
            $statement = $setParametersResult['statement'];
            $parameterValues = $setParametersResult['statementParameters'];
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute($parameterValues);
            $result->affectedRowsCount = $stmt->rowCount();
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function updateRows(string $tableName, array $rows, string $condition = "", array $parameters = []): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::UPDATE);
        try {
            $statements = [];
            $index = -1;
            foreach ($rows as $row) {
                $setValues = [];
                foreach ($row as $key => $value) {
                    $index++;
                    while (in_array("@$index", array_keys($parameters))) {
                        $index++;
                    }
                    $parameters["@$index"] = $value;
                    $setValues[] = "$key = @$index";
                }
                $setClause = implode(", ", $setValues);
                $statement = "UPDATE {$tableName} SET {$setClause} " . ($condition ? "WHERE {$condition}" : "");
                $statements[] = $statement;
            }
            $parameterValues = [];
            $statement = implode("", $statements);
            $parameterValues = [];
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues,passedParameters: $parameters);
            $statement = $setParametersResult['statement'];
            $parameterValues = $setParametersResult['statementParameters'];
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute($parameterValues);
            $result->affectedRowsCount = $stmt->rowCount();
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }
}

?>
<?php

namespace AcSql\Daos;
require_once __DIR__ . './../../../autocode-data-dictionary/vendor/autoload.php';
require_once __DIR__ . './../../../autocode/vendor/autoload.php';
require_once __DIR__ . './../Models/AcSqlDaoResult.php';
require_once 'AcBaseSqlDao.php';

use AcDataDictionary\Enums\AcEnumDDColumnProperty;
use AcDataDictionary\Models\AcDDFunction;
use AcDataDictionary\Models\AcDDStoredProcedure;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableColumn;
use AcDataDictionary\Models\AcDDTrigger;
use AcDataDictionary\Models\AcDDView;
use AcDataDictionary\Models\AcDDViewColumn;
use AcExtensions\AcExtensionMethods;
use Autocode\AcEncryption;
use PDO;
use PDOException;
use Autocode\Models\AcResult;
use AcSql\Daos\AcBaseSqlDao;
use AcDataDictionary\Enums\AcEnumDDRowOperation;
use AcDataDictionary\Enums\AcEnumDDSelectMode;
use AcDataDictionary\Enums\AcEnumDDColumnFormat;
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
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues, passedParameters: ["@databaseName" => $this->sqlConnection->database]);
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
                $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues, passedParameters: ["@databaseName" => $this->sqlConnection->database, '@tableName' => $tableName]);
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
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::DELETE);
        try {
            $statement = "DELETE FROM {$tableName} " . ($condition ? "WHERE {$condition}" : "");
            $db = $this->getConnectionObject();
            $parameterValues = [];
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues, passedParameters: $parameters);
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

    public function executeStatement(string $statement, ?string $operation = AcEnumDDRowOperation::UNKNOWN, ?array $parameters = []): AcSqlDaoResult
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

    public function formatRow(array $row, array $columnFormats = []): array
    {
        foreach ($columnFormats as $key => $formats) {
            if (AcExtensionMethods::arrayContainsKey(key: $key, array: $row)) {
                if (in_array(AcEnumDDColumnFormat::ENCRYPT, $formats)) {
                    $row[$key] = AcEncryption::decrypt($row[$key]);
                }
                if (in_array(AcEnumDDColumnFormat::JSON, $formats)) {
                    if ($row[$key] != null && $row != "") {
                        $row[$key] = json_decode($row[$key]);
                    }
                }
                if (in_array(AcEnumDDColumnFormat::HIDE_COLUMN, $formats)) {
                    unset($row[$key]);
                }
            }
        }
        return $row;
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

    public function getDatabaseFunctions(): AcSqlDaoResult
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

    public function getRows(?string $statement, ?string $condition = "", ?array $parameters = [], ?string $mode = AcEnumDDSelectMode::LIST , ?array $columnFormats = []): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::SELECT);
        try {
            $db = $this->getConnectionObject();
            if ($condition != "") {
                $statement .= " WHERE $condition";
            }
            $parameterValues = [];
            if ($mode == AcEnumDDSelectMode::COUNT) {
                $statement = "SELECT COUNT(*) AS records_count FROM ($statement) AS records_list";
            }
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues, passedParameters: $parameters);
            $statement = $setParametersResult['statement'];
            $parameterValues = $setParametersResult['statementParameters'];
            $stmt = $db->prepare($statement);
            $stmt->execute($parameterValues);
            $result->rows = [];
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($mode == AcEnumDDSelectMode::LIST) {                
                foreach ($rows as $row) {
                    $result->rows[] = $this->formatRow(row: $row, columnFormats: $columnFormats);
                }
            } else if ($mode == AcEnumDDSelectMode::COUNT) { 
                if(sizeof($rows)>0){
                    $countRow = $rows[0];
                    $result->totalRows = $countRow['records_count'];
                }
            }
            $result->setSuccess();
        } catch (PDOException $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function getTableColumns(string $tableName): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::SELECT);
        try {
            $db = $this->getConnectionObject();
            $stmt = $db->prepare("DESCRIBE `{$tableName}`");
            $stmt->execute();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $properties = [];
                if ($row["Null"] != "YES") {
                    $properties[AcEnumDDColumnProperty::NOT_NULL] = false;
                }
                if ($row["Key"] == "PRI") {
                    $properties[AcEnumDDColumnProperty::PRIMARY_KEY] = true;
                }
                if ($row["Default"] != null) {
                    $properties[AcEnumDDColumnProperty::DEFAULT_VALUE] = $row["Default"];
                }
                $result->rows[] = [
                    AcDDTableColumn::KEY_COLUMN_NAME => $row["Field"],
                    AcDDTableColumn::KEY_COLUMN_TYPE => $row["Type"],
                    AcDDTableColumn::KEY_COLUMN_PROPERTIES => $properties
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
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::SELECT);
        try {
            $db = $this->getConnectionObject();
            $stmt = $db->prepare("DESCRIBE `{$viewName}`");
            $stmt->execute();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $properties = [];
                if ($row["Null"] != "YES") {
                    $properties[AcEnumDDColumnProperty::NOT_NULL] = false;
                }
                if ($row["Key"] == "PRI") {
                    $properties[AcEnumDDColumnProperty::PRIMARY_KEY] = true;
                }
                if ($row["Default"] != null) {
                    $properties[AcEnumDDColumnProperty::DEFAULT_VALUE] = $row["Default"];
                }
                $result->rows[] = [
                    AcDDViewColumn::KEY_COLUMN_NAME => $row["Field"],
                    AcDDViewColumn::KEY_COLUMN_TYPE => $row["Type"],
                    AcDDViewColumn::KEY_COLUMN_PROPERTIES => $properties
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
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::INSERT);
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
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues, passedParameters: $parameters, );
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
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::INSERT);
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
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues, passedParameters: $parameters);
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
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::UPDATE);
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
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues, passedParameters: $parameters);
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

    public function updateRows(string $tableName, array $rowsWithConditions): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::UPDATE);
        try {
            $statements = [];
            $index = -1;
            $parameters = [];
            foreach ($rowsWithConditions as $rowWithCondition) {
                if (isset($rowWithCondition['row']) && isset($rowWithCondition['condition'])) {
                    $setValues = [];
                    foreach ($rowWithCondition['row'] as $key => $value) {
                        $index++;
                        while (in_array("@$index", array_keys($parameters))) {
                            $index++;
                        }
                        $parameters["@$index"] = $value;
                        $setValues[] = "$key = @$index";
                    }
                    $setClause = implode(", ", $setValues);
                    $condition = $rowWithCondition["condition"];
                    if (isset($rowWithCondition["parameters"])) {
                        $rowConditionParameters = $rowWithCondition["parameters"];
                        foreach ($rowConditionParameters as $key => $value) {
                            $conditionParameterKey = $key;
                            if (isset($parameters[$conditionParameterKey])) {
                                while (in_array("@$index", array_keys($parameters))) {
                                    $index++;
                                }
                                $conditionParameterKey = "@$index";
                                $condition = str_replace($key, $conditionParameterKey, $condition);
                            }
                            $parameters[$conditionParameterKey] = $value;
                        }
                    }
                    $statement = "UPDATE {$tableName} SET {$setClause} " . ($condition ? "WHERE {$condition}" : "") . ";";
                    $statements[] = $statement;
                }
            }
            $parameterValues = [];
            $statement = implode("", $statements);
            $parameterValues = [];
            $setParametersResult = $this->setSqlStatementParameters($statement, statementParameters: $parameterValues, passedParameters: $parameters);
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
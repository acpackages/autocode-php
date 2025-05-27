<?php

namespace AcSql\Database;

require_once __DIR__ . './../../../autocode/vendor/autoload.php';
require_once __DIR__ . './../../../autocode-data-dictionary/vendor/autoload.php';

use AcDataDictionary\Enums\AcEnumDDColumnFormat;
use AcDataDictionary\Enums\AcEnumDDColumnType;
use AcDataDictionary\Enums\AcEnumDDRowEvent;
use AcDataDictionary\Enums\AcEnumDDRowOperation;
use AcDataDictionary\Models\AcDDSelectStatement;
use AcDataDictionary\Enums\AcEnumDDSelectMode;
use AcSql\Models\AcSqlDaoResult;
use Autocode\AcEncryption;
use Autocode\AcLogger;
use Autocode\Models\AcResult;
use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Models\AcDDTable;
use Autocode\Enums\AcEnumSqlDatabaseType;
use AcSql\Database\acSqlDbTableColumn;
use AcSql\Database\AcSqlDbRowEvent;
use Autocode\Autocode;
use DateTime;
use Exception;

class AcSqlDbTable extends AcSqlDbBase
{
    public string $tableName = "";
    public AcDDTable $acDDTable;

    public function __construct(string $tableName, string $dataDictionaryName = "default")
    {
        parent::__construct(dataDictionaryName: $dataDictionaryName);
        $this->tableName = $tableName;
        $this->acDDTable = AcDataDictionary::getTable(tableName: $tableName, dataDictionaryName: $dataDictionaryName);
    }

    public static function getDropTableStatement(string $tableName, ?string $databaseType = AcEnumSqlDatabaseType::UNKNOWN): string {
        $result = "DROP TABLE IF EXISTS $tableName;";
        return $result;
    }

    public function cascadeDeleteRows(array $rows): AcResult
    {
        $result = new AcResult();
        try {
            $continueOperation = true;
            $this->logger->log("Checking cascade delete for table {$this->tableName}");
            $tableRelationships = AcDataDictionary::getTableRelationships(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
            $primaryKeyColumn = $this->acDDTable->getPrimaryKeyColumnName();
            $this->logger->log("Table relationships : ", $tableRelationships);
            foreach ($rows as $row) {
                $this->logger->log("Checking cascade delete for table row :", $row);
                if ($continueOperation) {
                    foreach ($tableRelationships as $acRelationship) {
                        if ($continueOperation) {
                            $deleteTableName = "";
                            $deleteColumnName = "";
                            $deleteColumnValue = null;
                            $this->logger->log("Checking cascade delete for relationship : ", $acRelationship);
                            if ($acRelationship->sourceTable == $this->tableName && $acRelationship->cascadeDeleteDestination == true) {
                                $deleteTableName = $acRelationship->destinationTable;
                                $deleteColumnName = $acRelationship->destinationColumn;
                                if (isset($row[$acRelationship->sourceColumn])) {
                                    $deleteColumnValue = $row[$acRelationship->sourceColumn];
                                }
                            }
                            if ($acRelationship->destinationTable == $this->tableName && $acRelationship->cascadeDeleteSource == true) {
                                $deleteTableName = $acRelationship->sourceTable;
                                $deleteColumnName = $acRelationship->sourceColumn;
                                if (isset($row[$acRelationship->destinationColumn])) {
                                    $deleteColumnValue = $row[$acRelationship->destinationColumn];
                                }
                            }
                            $this->logger->log("Performing cascade delete with related table $deleteTableName and column $deleteColumnName with value $deleteColumnValue");
                            if (!empty($deleteTableName) && !empty($deleteColumnName)) {
                                if (Autocode::validPrimaryKey($deleteColumnValue)) {
                                    $this->logger->log("Deleting related rows for primary key value : $deleteColumnValue");
                                    $deleteCondition = "$deleteColumnName = :deleteColumnValue";
                                    $deleteAcTable = new AcSqlDbTable($deleteTableName, $this->dataDictionaryName);
                                    $deleteResult = $deleteAcTable->deleteRows(condition: $deleteCondition, parameters: [":deleteColumnValue" => $deleteColumnValue]);
                                    if ($deleteResult->isSuccess()) {
                                        $this->logger->log("Cascade delete successful for $deleteTableName");
                                    } else {
                                        $result->setFromResult(result: $deleteResult, message: "Error in cascade delete: " . $deleteResult->message, logger: $this->logger);
                                        $continueOperation = false;
                                    }
                                } else {
                                    $this->logger->log("No value for cascade delete records");
                                }
                            } else {
                                $this->logger->log("No table & column for cascade delete records");
                            }
                        }
                    }
                }
                if ($continueOperation) {
                    $result->setSuccess();
                }
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function checkAndSetAutoNumberValues(array $row): AcResult
    {
        $result = new AcResult();
        try {
            $continueOperation = true;
            $autoNumberColumns = [];
            $checkColumns = [];
            foreach ($this->acDDTable->tableColumns as $tableColumn) {
                $setAutoNumber = true;
                if ($tableColumn->isAutoNumber()) {
                    if (isset($row[$tableColumn->columnName]) && !empty($row[$tableColumn->columnName])) {
                        $setAutoNumber = false;
                    }
                    if ($setAutoNumber) {
                        $autoNumberColumns[$tableColumn->columnName] = [
                            "prefix" => $tableColumn->getAutoNumberPrefix(),
                            "length" => $tableColumn->getAutoNumberLength(),
                            "prefix_length" => $tableColumn->getAutoNumberPrefixLength()
                        ];
                    }
                }
                if ($tableColumn->checkInAutoNumber() || $tableColumn->checkInModify()) {
                    $checkColumns[] = $tableColumn->columnName;
                }
            }
            if (!empty($autoNumberColumns)) {
                $getRowss = [];
                $selectColumnsList = array_keys($autoNumberColumns);
                $checkCondition = "";
                $checkConditionValues = [];
                if (!empty($checkColumns)) {
                    foreach ($checkColumns as $checkColumn) {
                        $checkCondition .= " AND $checkColumn = @checkColumn$checkColumn";
                        if (isset($row[$checkColumn])) {
                            $checkConditionValues["@checkColumn$checkColumn"] = $row[$checkColumn];
                        }
                    }
                }
                foreach ($selectColumnsList as $name) {
                    $columnGetRows = "";
                    if ($this->databaseType === AcEnumSqlDatabaseType::MYSQL) {
                        $columnGetRows = "SELECT CONCAT('{\"$name\":',IF(MAX(CAST(SUBSTRING($name, " . ($autoNumberColumns[$name]["prefix_length"] + 1) . ") AS UNSIGNED)) IS NULL,0,MAX(CAST(SUBSTRING($name, " . ($autoNumberColumns[$name]["prefix_length"] + 1) . ") AS UNSIGNED))),'}') AS max_json FROM {$this->tableName} WHERE $name LIKE '{$autoNumberColumns[$name]["prefix"]}%' $checkCondition";
                    }
                    if (!empty($columnGetRows)) {
                        $getRowss[] = $columnGetRows;
                    }
                }
                if (!empty($getRowss)) {
                    $getRows = implode(" UNION ", $getRowss);
                    $selectResponse = $this->dao->getRows(statement: $getRows, parameters: $checkConditionValues);
                    if ($selectResponse->isSuccess()) {
                        $rows = $selectResponse->rows;
                        foreach ($rows as $row) {
                            $maxJson = json_decode($row["max_json"], true);
                            $name = array_key_first($maxJson);
                            $lastRecordId = (int) $maxJson[$name];
                            $lastRecordId++;
                            $autoNumberValue = $autoNumberColumns[$name]["prefix"] . $this->updateValueLengthWithChars((string) $lastRecordId, "0", $autoNumberColumns[$name]["length"]);
                            $row[$name] = $autoNumberValue;
                        }
                    } else {
                        $continueOperation = false;
                        $result->setFromResult($result);
                    }
                }
            }
            if ($continueOperation) {
                $result->setSuccess($row);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function checkUniqueValues(array $row): AcResult
    {
        $result = new AcResult();
        try {
            $parameters = [];
            $conditions = [];
            $modifyConditions = [];
            $uniqueConditions = [];
            $uniqueColumns = [];
            $primaryKeyColumnName = $this->acDDTable->getPrimaryKeyColumnName();
            if (!empty($primaryKeyColumnName)) {
                if (isset($row[$primaryKeyColumnName]) && Autocode::validPrimaryKey($row[$primaryKeyColumnName])) {
                    $conditions[] = "$primaryKeyColumnName != @primaryKeyValue";
                    $parameters["@primaryKeyValue"] = $row[$primaryKeyColumnName];
                }
            }
            foreach ($this->acDDTable->tableColumns as $tableColumn) {
                $value = $row[$tableColumn->columnName] ?? null;
                if ($tableColumn->checkInModify()) {
                    $modifyConditions[] = "$tableColumn->columnName = @modify_$tableColumn->columnName";
                    $parameters["@modify_$tableColumn->columnName"] = $value;
                }
                if ($tableColumn->isUniqueKey()) {
                    $uniqueConditions[] = "$tableColumn->columnName = @unique_$tableColumn->columnName";
                    $parameters["@unique_$tableColumn->columnName"] = $value;
                    $uniqueColumns[] = $tableColumn->columnName;
                }
            }
            if (!empty($uniqueConditions)) {
                if (!empty($modifyConditions)) {
                    $conditions = array_merge($conditions, $modifyConditions);
                }
                $conditions[] = "(" . implode(" OR ", $uniqueConditions) . ")";
                if (!empty($conditions)) {
                    $this->logger->log("Searching for Unique Records getting Repeated");
                    $selectResponse = $this->selectRows(condition: implode(" AND ", $conditions), parameters: $parameters, fetchMode: "COUNT");
                    if ($selectResponse->isSuccess()) {
                        $rowsCount = $selectResponse->rowsCount();
                        if ($rowsCount > 0) {
                            $result->setFailure(value: ["unique_columns" => $uniqueColumns], message: "Unique key violated");
                        } else {
                            $result->setSuccess();
                        }
                    } else {
                        $result->setFromResult($selectResponse);
                    }
                } else {
                    $result->setSuccess();
                }
            } else {
                $this->logger->log("No unique conditions found");
                $result->setSuccess();
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function deleteRows(?string $condition = "", ?string $primaryKeyValue = "", ?array $parameters = [], ?bool $executeAfterEvent = true, ?bool $executeBeforeEvent = true): AcSqlDaoResult
    {
        $this->logger->log("Deleting row with condition : $condition & primaryKeyValue $primaryKeyValue");
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::DELETE);
        try {
            $continueOperation = true;
            $primaryKeyColumnName = $this->acDDTable->getPrimaryKeyColumnName();
            if (empty($condition)) {
                if (!empty($primaryKeyValue) && !empty($primaryKeyColumnName)) {
                    $condition = "$primaryKeyColumnName = :primaryKeyValue";
                    $parameters[":primaryKeyValue"] = $primaryKeyValue;
                } else {
                    $continueOperation = false;
                    $result->setFailure(message: 'Primary key column of column value is missing');
                }
            } else {
                $condition = " $primaryKeyColumnName  IN (SELECT $primaryKeyColumnName FROM $this->tableName WHERE $condition)";
            }
            if ($continueOperation) {
                if ($executeBeforeEvent) {
                    $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                    $rowEvent->condition = $condition;
                    $rowEvent->parameters = $parameters;
                    $rowEvent->eventType = AcEnumDDRowEvent::BEFORE_DELETE;
                    $eventResult = $rowEvent->execute();
                    if ($eventResult->isSuccess()) {
                        $condition = $rowEvent->condition;
                        $parameters = $rowEvent->parameters;
                    } else {
                        $continueOperation = false;
                        $result->setFromResult($eventResult, message: "Aborted from before delete row events");
                    }
                }
            }
            if ($continueOperation) {
                $this->logger->log(["", "", "Performing delete operation on table $this->tableName with condition : $condition and parameters : ", $parameters, "", ""]);
                $getResult = $this->getRows(condition: $condition, parameters: $parameters);
                if ($getResult->isSuccess()) {
                    $result->rows = $getResult->rows;
                    $setNullResult = $this->setValuesNullBeforeDelete(condition: $condition, parameters: $parameters);
                    if ($setNullResult->isFailure()) {
                        $this->logger->error('Error setting null before delete', $setNullResult);
                        $continueOperation = false;
                        $result->setFromResult($setNullResult);
                    }
                    if ($continueOperation) {
                        $cascadeDeleteResult = $this->cascadeDeleteRows($result->rows);
                        if ($cascadeDeleteResult->isFailure()) {
                            $this->logger->error('Error cascade deleting row', $cascadeDeleteResult);
                            $continueOperation = false;
                            $result->setFromResult($setNullResult, logger: $this->logger);
                        } else {
                            $this->logger->log('Cascade delete result', $cascadeDeleteResult);
                        }
                    }
                    if ($continueOperation) {
                        $deleteResult = $this->dao->deleteRows(tableName: $this->tableName, condition: $condition, parameters: $parameters);
                        if ($deleteResult->isSuccess()) {
                            $result->affectedRowsCount = $deleteResult->affectedRowsCount;
                            $result->setSuccess(message: "$result->affectedRowsCount row(s) deleted successfully");
                        } else {
                            $result->setFromResult($deleteResult);
                            if (strpos($deleteResult->message, "foreign key") !== false) {
                                $result->message = "Cannot delete row! Foreign key constraint is preventing form deleting rows!";
                            }
                        }
                    }
                } else {
                    $result->setFromResult($getResult, logger: $this->logger);
                }
            }
            if ($continueOperation && $executeAfterEvent) {
                $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                $rowEvent->eventType = AcEnumDDRowEvent::AFTER_DELETE;
                $rowEvent->condition = $condition;
                $rowEvent->parameters = $parameters;
                $rowEvent->result = $result;
                $eventResult = $rowEvent->execute();
                if ($eventResult->isSuccess()) {
                    $result = $rowEvent->result;
                } else {
                    $result->setFromResult($eventResult);
                }
            }
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function formatValues($row, $insertMode = false): AcResult
    {
        $result = new AcResult();
        $continueOperation = true;
        $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
        $rowEvent->row = $row;
        $rowEvent->eventType = AcEnumDDRowEvent::BEFORE_FORMAT;
        $eventResult = $rowEvent->execute();
        if ($eventResult->isSuccess()) {
            $row = $rowEvent->row;
        } else {
            $result->setFromResult(result: $eventResult);
            $continueOperation = false;
        }
        if ($continueOperation) {
            foreach ($this->acDDTable->tableColumns as $column) {
                if (isset($row[$column->columnName]) || $insertMode) {
                    $setColumnValue = isset($row[$column->columnName]);
                    $formats = $column->getColumnFormats();
                    $type = $column->columnType;
                    $value = $row[$column->columnName] ?? "";
                    if ($value === "" && $column->getDefaultValue() != null && $insertMode) {
                        $value = $column->getDefaultValue();
                        $setColumnValue = true;
                    }
                    if ($setColumnValue) {
                        if (in_array($type, [AcEnumDDColumnType::DATE, AcEnumDDColumnType::DATETIME, AcEnumDDColumnType::STRING])) {
                            $value = trim(strval($value));
                            if ($type === AcEnumDDColumnType::STRING) {
                                if (in_array(AcEnumDDColumnFormat::LOWERCASE, $formats)) {
                                    $value = strtolower($value);
                                }
                                if (in_array(AcEnumDDColumnFormat::UPPERCASE, $formats)) {
                                    $value = strtoupper($value);
                                }
                                if (in_array(AcEnumDDColumnFormat::ENCRYPT, $formats)) {
                                    $value = AcEncryption::encrypt(plainText: $value);
                                }
                            } elseif (in_array($type, [AcEnumDDColumnType::DATE, AcEnumDDColumnType::DATETIME]) && !empty($value)) {
                                try {
                                    $dateTimeValue = new DateTime($value);
                                    $format = ($type === AcEnumDDColumnType::DATETIME) ? 'Y-m-d H:i:s' : 'Y-m-d';
                                    $value = $dateTimeValue->format($format);
                                } catch (Exception $ex) {
                                    $this->logger->log("Error while setting dateTimeValue for $column->columnName in table $this->tableName with value: $value");
                                }
                            }
                        } elseif (in_array($type, [AcEnumDDColumnType::JSON, AcEnumDDColumnType::MEDIA_JSON])) {
                            $value = is_string($value) ? $value : json_encode($value);
                        } elseif ($type === AcEnumDDColumnType::PASSWORD) {
                            $value = AcEncryption::encrypt(plainText: $value);
                        }
                        $row[$column->columnName] = $value;
                    }
                }
            }
        }
        if ($continueOperation) {
            $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
            $rowEvent->row = $row;
            $rowEvent->eventType = AcEnumDDRowEvent::AFTER_FORMAT;
            $eventResult = $rowEvent->execute();
            if ($eventResult->isSuccess()) {
                $row = $rowEvent->row;
            } else {
                $result->setFromResult(result: $eventResult);
                $continueOperation = false;
            }
        }
        if ($continueOperation) {
            $result->setSuccess($row);
        }
        return $result;
    }

    public function getCreateTableStatement(): string
    {
        $tableColumns = $this->acDDTable->tableColumns;
        $columnDefinitions = [];
        foreach ($tableColumns as $columnName => $columnDetails) {
            $acSqlDbTableColumn = new acSqlDbTableColumn(tableName: $this->tableName, columnName: $columnName, dataDictionaryName: $this->dataDictionaryName);
            $columnDefinition = $acSqlDbTableColumn->getColumnDefinitionForStatement();
            if ($columnDefinition != "") {
                $columnDefinitions[] = $columnDefinition;
            }
        }
        $result = "CREATE TABLE IF NOT EXISTS $this->tableName (" . implode(",", $columnDefinitions) . ");";
        return $result;
    }

    public function getColumnFormats(?bool $getPasswordColumns = false) {
        $result = [];
        foreach($this->acDDTable->tableColumns as $acDDTableColumn){
            $columnFormats = [];
            if($acDDTableColumn->columnType == AcEnumDDColumnType::JSON || $acDDTableColumn->columnType == AcEnumDDColumnType::MEDIA_JSON){
                $columnFormats[] = AcEnumDDColumnFormat::JSON;
            }
            else if($acDDTableColumn->columnType == AcEnumDDColumnType::DATE){
                $columnFormats[] = AcEnumDDColumnFormat::DATE;
            }
            else if($acDDTableColumn->columnType == AcEnumDDColumnType::PASSWORD && !$getPasswordColumns){
                $columnFormats[] = AcEnumDDColumnFormat::HIDE_COLUMN;
            }
            else if($acDDTableColumn->columnType == AcEnumDDColumnType::ENCRYPTED){
                $columnFormats[] = AcEnumDDColumnFormat::ENCRYPT;
            }
            if(!empty($columnFormats)){
                $result[$acDDTableColumn->columnName] = $columnFormats;
            }
        }
        return $result;
    }

    public function getSelectStatement(?array $includeColumns = [], ?array $excludeColumns = []): string
    {
        $result = "SELECT * FROM $this->tableName";
        $columns = [];
        if (empty($includeColumns) && empty($excludeColumns)) {
            $columns = ["*"];
        } else {
            if (empty($includeColumns)) {
                $columns = $includeColumns;
            } else if (empty($excludeColumns)) {
                $columns = $excludeColumns;
            }
        }
        $result = "SELECT " . implode(",", $columns) . " FROM " . $this->tableName;
        return $result;
    }

    public function getDistinctColumnValues(string $columnName = "", ?string $condition = "", ?string $orderBy = "", ?string $mode = AcEnumDDSelectMode::LIST , ?int $pageNumber = -1, ?int $pageSize = -1, ?array $parameters = []): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::SELECT);
        try {
            if (empty($orderBy)) {
                $orderBy = $columnName;
            }
            $selectStatement = $this->getSelectStatement();
            $selectStatement = "SELECT DISTINCT $columnName FROM ($selectStatement) AS recordsList";
            if (!empty($condition)) {
                $condition .= " AND $columnName IS NOT NULL AND $columnName != ''";
            } else {
                $condition = " $columnName IS NOT NULL AND $columnName != ''";
            }
            $this->logger->log(["", "", "Executing getDistinctColumnValues select statement"]);
            $sqlStatement = AcDDSelectStatement::generateSqlStatement(selectStatement:$selectStatement,condition:$condition,orderBy:$orderBy,pageNumber:$pageNumber,pageSize:$pageSize,databaseType:$this->databaseType);
            $result = $this->dao->getRows(statement: $sqlStatement,parameters: $parameters);
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function getColumnDefinitionForStatement(string $columnName): string
    {
        $result = "";
        $acDDTableColumn = $this->acDDTable->tableColumns[$columnName];
        $columnType = $acDDTableColumn->columnType;
        $defaultValue = $acDDTableColumn->getDefaultValue();
        $size = $acDDTableColumn->getSize();
        $isAutoIncrementSet = false;
        $isPrimaryKeySet = false;
        if ($this->databaseType == AcEnumSqlDatabaseType::MYSQL) {
            $columnType = "TEXT";
            switch ($columnType) {
                case AcEnumDDColumnType::AUTO_INCREMENT:
                    $columnType = 'INT AUTO_INCREMENT PRIMARY KEY';
                    $isAutoIncrementSet = true;
                    $isPrimaryKeySet = true;
                    break;
                case AcEnumDDColumnType::BLOB:
                    $columnType = "LONGBLOB";
                    if ($size > 0) {
                        if ($size <= 255) {
                            $columnType = "TINYBLOB";
                        }
                        if ($size <= 65535) {
                            $columnType = "BLOB";
                        } else if ($size <= 16777215) {
                            $columnType = "MEDIUMBLOB";
                        }
                    }
                    break;
                case AcEnumDDColumnType::DATE:
                    $columnType = 'DATE';
                    break;
                case AcEnumDDColumnType::DATETIME:
                    $columnType = 'DATETIME';
                    break;
                case AcEnumDDColumnType::DOUBLE:
                    $columnType = 'DOUBLE';
                    break;
                case AcEnumDDColumnType::UUID:
                    $columnType = 'CHAR(36)';
                    break;
                case AcEnumDDColumnType::INTEGER:
                    $columnType = 'INT';
                    if ($size > 0) {
                        if ($size <= 255) {
                            $columnType = "TINYINT";
                        } else if ($size <= 65535) {
                            $columnType = "SMALLINT";
                        } else if ($size <= 16777215) {
                            $columnType = "MEDIUMINT";
                        } else if ($size <= 18446744073709551615) {
                            $columnType = "BIGINT";
                        }
                    }
                    break;
                case AcEnumDDColumnType::JSON:
                    $columnType = 'LONGTEXT';
                    break;
                case AcEnumDDColumnType::STRING:
                    if ($size == 0) {
                        $size = 255;
                    }
                    $columnType = "VARCHAR($size)";
                    break;
                case AcEnumDDColumnType::TEXT:
                    $columnType = 'LONGTEXT';
                    if ($size > 0) {
                        if ($size <= 255) {
                            $columnType = "TINYTEXT";
                        }
                        if ($size <= 65535) {
                            $columnType = "TEXT";
                        } else if ($size <= 16777215) {
                            $columnType = "MEDIUMTEXT";
                        }
                    }
                    break;
                case AcEnumDDColumnType::TIME:
                    $columnType = 'TIME';
                    break;
                case AcEnumDDColumnType::TIMESTAMP:
                    $columnType = 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
                    break;
            }
            $result = "$columnName $columnType";
            if ($acDDTableColumn->isAutoIncrement() && !$isAutoIncrementSet) {
                $result .= " AUTO_INCREMENT";
            }
            if ($acDDTableColumn->isPrimaryKey() && !$isPrimaryKeySet) {
                $result .= " PRIMARY KEY";
            }
            if ($acDDTableColumn->isUniqueKey()) {
                $result .= " UNIQUE";
            }
            if ($acDDTableColumn->isNotNull()) {
                $result .= " NOT NULL";
            }
            if ($defaultValue != null) {
                // $result.=" DEFAULT $defaultValue";
            }
        } else if ($this->databaseType == AcEnumSqlDatabaseType::SQLITE) {
            $columnType = "TEXT";
            switch ($columnType) {
                case AcEnumDDColumnType::AUTO_INCREMENT:
                    $columnType = 'INTEGER PRIMARY KEY AUTOINCREMENT';
                    $isAutoIncrementSet = true;
                    $isPrimaryKeySet = true;
                    break;
                case AcEnumDDColumnType::DOUBLE:
                    $columnType = 'REAL';
                    break;
                case AcEnumDDColumnType::BLOB:
                    $columnType = 'BLOB';
                    break;
                case AcEnumDDColumnType::INTEGER:
                    $columnType = 'INTEGER';
                    break;
            }
            $result = "$columnName $columnType";
            if ($acDDTableColumn->isAutoIncrement() && !$isAutoIncrementSet) {
                $result .= " AUTOINCREMENT";
            }
            if ($acDDTableColumn->isPrimaryKey() && !$isPrimaryKeySet) {
                $result .= " PRIMARY KEY";
            }
            if ($acDDTableColumn->isUniqueKey()) {
                $result .= " UNIQUE ";
            }
            if ($acDDTableColumn->isNotNull()) {
                $result .= " NOT NULL";
            }
            if ($defaultValue != null) {
                // $result.=" DEFAULT $defaultValue";
            }
        }
        return $result;
    }

    public function getRows(?string $selectStatement = "",?string $condition = "", ?string $orderBy = "", ?string $mode = AcEnumDDSelectMode::LIST , ?int $pageNumber = -1, ?int $pageSize = -1, ?array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::SELECT);
        try {
            if($selectStatement == ""){
                $selectStatement = $this->getSelectStatement();
            }
            $sqlStatement = AcDDSelectStatement::generateSqlStatement(selectStatement:$selectStatement,condition:$condition,orderBy:$orderBy,pageNumber:$pageNumber,pageSize:$pageSize,databaseType:$this->databaseType);
            $result = $this->dao->getRows(statement: $sqlStatement, parameters: $parameters, mode: $mode,columnFormats:$this->getColumnFormats());
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function getRowsFromAcDDStatement(AcDDSelectStatement $acDDSelectStatement): AcSqlDaoResult {
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::SELECT);
        try {
            $sqlStatement = $acDDSelectStatement->getSqlStatement();
            $sqlParameters = $acDDSelectStatement->parameters;
            $result = $this->dao->getRows(statement: $sqlStatement, parameters: $sqlParameters,columnFormats:$this->getColumnFormats());
            $countSqlStatement = $acDDSelectStatement->getSqlStatement(skipLimit:true);
            $countResult = $this->dao->getRows(statement: $countSqlStatement, parameters: $sqlParameters,mode:AcEnumDDSelectMode::COUNT);
            if($countResult->isSuccess()){
                $result->totalRows = $countResult->totalRows;
            }
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function insertRow(array $row, ?AcResult $validateResult = null, bool $executeAfterEvent = true, bool $executeBeforeEvent = true): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::INSERT);
        try {
            $this->logger->log("Inserting row with data : ", $row);
            $continueOperation = true;
            if ($validateResult == null) {
                $validateResult = $this->validateValues(row: $row, isInsert: true);
            }
            $this->logger->log("Validation result : ", $validateResult);
            if ($validateResult->isSuccess()) {
                foreach ($this->acDDTable->tableColumns as $column) {
                    if (($column->columnType == AcEnumDDColumnType::UUID || ($column->columnType == AcEnumDDColumnType::STRING && $column->isPrimaryKey())) && !isset($row[$column->columnName])) {
                        $row[$column->columnName] = Autocode::guid();
                    }
                }
                $primaryKeyColumn = $this->acDDTable->getPrimaryKeyColumnName();
                $primaryKeyValue = null;
                if (isset($row[$primaryKeyColumn])) {
                    $primaryKeyValue = $row[$primaryKeyColumn];
                }
                if (!empty($row)) {
                    if ($continueOperation) {
                        if ($executeBeforeEvent) {
                            $this->logger->log("Executing before insert event");
                            $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                            $rowEvent->row = $row;
                            $rowEvent->eventType = AcEnumDDRowEvent::BEFORE_INSERT;
                            $eventResult = $rowEvent->execute();
                            $this->logger->log("Before insert result", $eventResult);
                            if ($eventResult->isSuccess()) {
                                $row = $rowEvent->row;
                            } else {
                                $continueOperation = false;
                                $result->setFromResult($eventResult, message: "Aborted from before insert row events");
                            }
                        }
                    }
                    if ($continueOperation) {
                        $this->logger->log("Inserting data : ", $row);
                        $insertResult = $this->dao->insertRow(tableName: $this->tableName, row: $row);
                        if ($insertResult->isSuccess()) {
                            $this->logger->log($insertResult);
                            $result->setSuccess(message: "Row inserted successfully");
                            $result->primaryKeyColumn = $primaryKeyColumn;
                            $result->primaryKeyValue = $primaryKeyValue;
                            if (!empty($primaryKeyColumn)) {
                                if (!Autocode::validPrimaryKey($primaryKeyValue) && Autocode::validPrimaryKey($insertResult->lastInsertedId)) {
                                    $primaryKeyValue = $insertResult->lastInsertedId;
                                }
                            }
                            $result->lastInsertedId = $primaryKeyValue;
                            $this->logger->log(message: "Getting inserted row from database");
                            $condition = "$primaryKeyColumn = :primaryKeyValue";
                            $parameters = [":primaryKeyValue" => $primaryKeyValue];
                            $this->logger->log("Select condition", $condition, $parameters);
                            $selectResult = $this->getRows(condition: $condition, parameters: $parameters);
                            if ($selectResult->isSuccess()) {
                                if ($selectResult->hasRows()) {
                                    $result->rows = $selectResult->rows;
                                }
                            } else {
                                $result->message = 'Error getting inserted row : ' . $selectResult->message;
                            }
                            if ($continueOperation && $executeAfterEvent) {
                                $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                                $rowEvent->eventType = AcEnumDDRowEvent::AFTER_INSERT;
                                $rowEvent->result = $result;
                                $eventResult = $rowEvent->execute();
                                if ($eventResult->isSuccess()) {
                                    $result = $rowEvent->result;
                                } else {
                                    $result->setFromResult($eventResult);
                                }
                            }
                        } else {
                            $result->setFromResult($insertResult);
                        }
                    }
                } else {
                    $result->message = 'No values for new row';
                }
            } else {
                $result = $validateResult;
            }
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function insertRows(array $rows, bool $executeAfterEvent = true, bool $executeBeforeEvent = true): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::INSERT);
        try {
            $this->logger->log("Inserting rows : ", $rows);
            $continueOperation = true;
            $rowsToInsert = [];
            $primaryKeyValues = [];
            $primaryKeyColumn = $this->acDDTable->getPrimaryKeyColumnName();
            foreach ($rows as $row) {
                if ($continueOperation) {
                    $validateResult = $this->validateValues(row: $row, isInsert: true);
                    if ($validateResult->isSuccess()) {
                        foreach ($this->acDDTable->tableColumns as $column) {
                            if (($column->columnType == AcEnumDDColumnType::UUID || ($column->columnType == AcEnumDDColumnType::STRING && $column->isPrimaryKey())) && !isset($row[$column->columnName])) {
                                $row[$column->columnName] = Autocode::guid();
                            }
                        }
                        if (isset($row[$primaryKeyColumn])) {
                            $primaryKeyValues[] = $row[$primaryKeyColumn];
                        }
                        if (!empty($row)) {
                            if ($continueOperation) {
                                if ($executeBeforeEvent) {
                                    $this->logger->log("Executing before insert event");
                                    $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                                    $rowEvent->row = $row;
                                    $rowEvent->eventType = AcEnumDDRowEvent::BEFORE_INSERT;
                                    $eventResult = $rowEvent->execute();
                                    $this->logger->log("Before insert result", $eventResult);
                                    if ($eventResult->isSuccess()) {
                                        $row = $rowEvent->row;
                                    } else {
                                        $continueOperation = false;
                                        $result->setFromResult($eventResult, message: "Aborted from before insert row events");
                                    }
                                }
                            }
                            if ($continueOperation) {
                                $rowsToInsert[] = $row;
                            }
                        } else {
                            $result->message = 'No values for new row';
                        }
                    } else {
                        $result = $validateResult;
                    }
                }

            }
            if ($continueOperation) {
                $this->logger->log("Inserting " . sizeof($rows) . " rows");
                $insertResult = $this->dao->insertRows(tableName: $this->tableName, rows: $rowsToInsert);
                if ($insertResult->isSuccess()) {
                    $this->logger->log($insertResult);
                    $result->lastInsertedIds = $primaryKeyValues;
                    $this->logger->log(message: "Getting inserted row from database");
                    $condition = "$primaryKeyColumn IN (:primaryKeyValue)";
                    $parameters = [":primaryKeyValue" => $primaryKeyValues];
                    $this->logger->log("Select condition", $condition, $parameters);
                    $selectResult = $this->getRows(condition: $condition, parameters: $parameters);
                    if ($selectResult->isSuccess()) {
                        if ($selectResult->hasRows()) {
                            $result->rows = $selectResult->rows;
                        }
                    } else {
                        $result->message = 'Error getting inserted rows : ' . $selectResult->message;
                    }
                    if ($continueOperation && $executeAfterEvent) {
                        foreach ($result->rows as $row) {
                            $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                            $rowEvent->eventType = AcEnumDDRowEvent::AFTER_INSERT;
                            $rowEvent->result = $result;
                            $rowEvent->row = $row;
                            $eventResult = $rowEvent->execute();
                            if ($eventResult->isSuccess()) {
                            } else {
                                $continueOperation = false;
                            }
                        }
                    }
                } else {
                    $continueOperation = false;
                    $result->setFromResult($insertResult);
                }
            }
            if ($continueOperation) {
                $result->setSuccess(message: "Rows inserted successfully");
            }
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function saveRow(array $row, bool $executeAfterEvent = true, bool $executeBeforeEvent = true): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::UNKNOWN);
        try {
            $continueOperation = true;
            $operation = AcEnumDDRowOperation::UNKNOWN;
            $primaryKeyColumn = $this->acDDTable->getPrimaryKeyColumnName();
            $primaryKeyValue = null;
            if (isset($row[$primaryKeyColumn])) {
                $primaryKeyValue = $row[$primaryKeyColumn];
            }
            $condition = "";
            $conditionParameters = [];
            if (Autocode::validPrimaryKey($primaryKeyValue)) {
                $this->logger->log("Found primary key value so primary key value will be used");
                $condition = $primaryKeyColumn . " = @primaryKeyValue";
                $conditionParameters["@primaryKeyValue"] = $primaryKeyValue;
            } else {
                $checkInSaveColumns = [];
                foreach ($this->acDDTable->tableColumns as $column) {
                    if ($column->checkInSave()) {
                        $checkInSaveColumns[$column->columnName] = null;
                        if (isset($row[$column->columnName])) {
                            $checkInSaveColumns[$column->columnName] = $row[$column->columnName];
                        }
                    }
                }
                $this->logger->log("Not found primary key value so checking for columns while saving");
                if (!empty($checkInSaveColumns)) {
                    $checkConditions = [];
                    $checkParameters = [];
                    foreach ($checkInSaveColumns as $key => $value) {
                        $checkConditions[] = "$key = :$key";
                        $conditionParameters[":$key"] = $value;
                    }
                    $condition = implode(" AND ", $checkConditions);
                } else {
                    $continueOperation = false;
                    $result->setFailure(message: "No values to check in save", logger: $this->logger);
                }
            }
            if ($condition != "") {
                $getResult = $this->getRows(condition: $condition, parameters: $conditionParameters);
                if ($getResult->isSuccess()) {
                    if ($getResult->hasRows()) {
                        $existingRecord = $getResult->rows[0];
                        if (isset($existingRecord[$primaryKeyColumn])) {
                            $primaryKeyValue = $existingRecord[$primaryKeyColumn];
                            $row[$primaryKeyColumn] = $primaryKeyValue;
                            $operation = AcEnumDDRowOperation::UPDATE;
                        } else {
                            $continueOperation = false;
                            $result->message = "Row does not have primary key value";
                        }
                    } else {
                        $operation = AcEnumDDRowOperation::INSERT;
                    }
                } else {
                    $continueOperation = false;
                    $result->setFromResult($getResult);
                }
            } else {
                $operation = AcEnumDDRowOperation::INSERT;
            }
            if ($operation != AcEnumDDRowOperation::INSERT && $operation != AcEnumDDRowOperation::UPDATE) {
                $result->message = "Invalid Operation";
                $continueOperation = false;
            }
            if ($continueOperation) {
                $this->logger->log("Executing operation $operation in save.");
                if ($executeBeforeEvent) {
                    $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                    $rowEvent->row = $row;
                    $rowEvent->eventType = AcEnumDDRowEvent::BEFORE_SAVE;
                    $eventResult = $rowEvent->execute();
                    if ($eventResult->isSuccess()) {
                        $row = $rowEvent->row;
                    } else {
                        $continueOperation = false;
                        $result->setFromResult($eventResult, message: "Aborted from before update row events", logger: $this->logger);
                    }
                }
                if ($operation == AcEnumDDRowOperation::INSERT) {
                    $result = $this->insertRow(row: $row);
                } else if ($operation == AcEnumDDRowOperation::UPDATE) {
                    $result = $this->updateRow(row: $row, );
                } else {
                    $continueOperation = false;
                }
                if ($continueOperation && $executeAfterEvent) {
                    $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                    $rowEvent->eventType = AcEnumDDRowEvent::AFTER_SAVE;
                    $rowEvent->result = $result;
                    $eventResult = $rowEvent->execute();
                    if ($eventResult->isSuccess()) {
                        $result = $rowEvent->result;
                    } else {
                        $result->setFromResult($eventResult);
                    }
                }
            }
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function saveRows(array $rows, bool $executeAfterEvent = true, bool $executeBeforeEvent = true): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::UNKNOWN);
        try {
            $continueOperation = true;
            $primaryKeyColumn = $this->acDDTable->getPrimaryKeyColumnName();
            $rowsToInsert = [];
            $rowsToUpdate = [];
            foreach ($rows as $row) {
                if ($continueOperation) {
                    $primaryKeyValue = null;
                    if (isset($row[$primaryKeyColumn])) {
                        $primaryKeyValue = $row[$primaryKeyColumn];
                    }
                    $condition = "";
                    $conditionParameters = [];
                    if (Autocode::validPrimaryKey($primaryKeyValue)) {
                        $this->logger->log("Found primary key value so primary key value will be used");
                        $condition = $primaryKeyColumn . " = @primaryKeyValue";
                        $conditionParameters["@primaryKeyValue"] = $primaryKeyValue;
                    } else {
                        $checkInSaveColumns = [];
                        foreach ($this->acDDTable->tableColumns as $column) {
                            if ($column->checkInSave()) {
                                $checkInSaveColumns[$column->columnName] = null;
                                if (isset($row[$column->columnName])) {
                                    $checkInSaveColumns[$column->columnName] = $row[$column->columnName];
                                }
                            }
                        }
                        $this->logger->log("Not found primary key value so checking for columns while saving");
                        if (!empty($checkInSaveColumns)) {
                            $checkConditions = [];
                            $checkParameters = [];
                            foreach ($checkInSaveColumns as $key => $value) {
                                $checkConditions[] = "$key = :$key";
                                $conditionParameters[":$key"] = $value;
                            }
                            $condition = implode(" AND ", $checkConditions);
                        } else {
                            $continueOperation = false;
                            $result->setFailure(message: "No values to check in save", logger: $this->logger);
                        }
                    }
                    if ($condition != "") {
                        $getResult = $this->getRows(condition: $condition, parameters: $conditionParameters);
                        if ($getResult->isSuccess()) {
                            if ($getResult->hasRows()) {
                                $existingRecord = $getResult->rows[0];
                                if (isset($existingRecord[$primaryKeyColumn])) {
                                    $primaryKeyValue = $existingRecord[$primaryKeyColumn];
                                    $row[$primaryKeyColumn] = $primaryKeyValue;
                                    $rowsToUpdate[] = $row;
                                } else {
                                    $continueOperation = false;
                                    $result->message = "Row does not have primary key value";
                                }
                            } else {
                                $rowsToInsert[] = $row;
                            }
                        } else {
                            $continueOperation = false;
                            $result->setFromResult($getResult);
                        }
                    } else {
                        $rowsToInsert[] = $row;
                    }
                }
            }
            if ($continueOperation) {
                if ($executeBeforeEvent) {
                    foreach ($rowsToInsert as $row) {
                        if ($continueOperation) {
                            $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                            $rowEvent->row = $row;
                            $rowEvent->eventType = AcEnumDDRowEvent::BEFORE_SAVE;
                            $eventResult = $rowEvent->execute();
                            if ($eventResult->isSuccess()) {
                                $row = $rowEvent->row;
                            } else {
                                $continueOperation = false;
                                $result->setFromResult(result: $eventResult, message: "Aborted from before save row events", logger: $this->logger);
                            }
                        }
                    }
                    foreach ($rowsToUpdate as $row) {
                        if ($continueOperation) {
                            $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                            $rowEvent->row = $row;
                            $rowEvent->eventType = AcEnumDDRowEvent::BEFORE_SAVE;
                            $eventResult = $rowEvent->execute();
                            if ($eventResult->isSuccess()) {
                                $row = $rowEvent->row;
                            } else {
                                $continueOperation = false;
                                $result->setFromResult(result: $eventResult, message: "Aborted from before save row events", logger: $this->logger);
                            }
                        }
                    }
                }
                if ($continueOperation) {
                    $insertResult = $this->insertRows(rows: $rowsToInsert);
                    if ($insertResult->isFailure()) {
                        $continueOperation = false;
                        $result->setFromResult(result: $insertResult);
                    }
                    if ($continueOperation) {
                        $updateResult = $this->updateRows(rows: $rowsToUpdate);
                        if ($updateResult->isFailure()) {
                            $continueOperation = false;
                            $result->setFromResult(result: $updateResult);
                        }
                        if ($continueOperation) {
                            $result->setSuccess(true, message: "Rows updated successfully");
                            $result->rows = [...$insertResult->rows, ...$updateResult->rows];
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function setValuesNullBeforeDelete(string $condition, ?array $parameters = []): AcResult
    {
        $result = new AcResult();
        try {
            $continueOperation = true;
            $this->logger->log("Checking cascade delete for table {$this->tableName}");
            $tableRelationships = AcDataDictionary::getTableRelationships(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
            foreach ($tableRelationships as $acRelationship) {
                if ($continueOperation) {
                    if ($acRelationship->destinationTable == $this->tableName) {
                        if (isset($row[$acRelationship->destinationColumn])) {
                            $column = $this->acDDTable->getColumn($acRelationship->destinationColumn);
                            if ($column != null) {
                                if ($column->isSetValuesNullBeforeDelete()) {
                                    $setNullStatement = "UPDATE $acRelationship->sourceTable SET $acRelationship->sourceColumn = NULL WHERE $acRelationship->sourceColumn IN (SELECT $acRelationship->destinationColumn FROM {$this->tableName} WHERE $condition)";
                                    $this->logger->log(["Executing set null statement", $setNullStatement]);
                                    $setNullResult = $this->dao->sqlStatement(statement: $setNullStatement, parameters: $parameters);
                                    if ($setNullResult->isSuccess()) {
                                        $this->logger->success($setNullResult->toJson());
                                    } else {
                                        $continueOperation = false;
                                        $result->setFromResult($setNullResult);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($continueOperation) {
                $result->setSuccess();
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function updateRow(array $row, ?string $condition = "", ?array $parameters = [], ?AcResult $validateResult = null, bool $executeAfterEvent = true, bool $executeBeforeEvent = true): AcSqlDaoResult
    {
        $this->logger->log("Updating row with data : ", $row);
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::UPDATE);
        try {
            $continueOperation = true;
            if ($validateResult == null) {
                $validateResult = $this->validateValues(row: $row, isInsert: false);
            }
            if ($validateResult->isSuccess() && $continueOperation) {
                $this->logger->log("Validation result : ", $validateResult);
                $primaryKeyColumn = $this->acDDTable->getPrimaryKeyColumnName();
                $primaryKeyValue = null;
                if (isset($row[$primaryKeyColumn])) {
                    $primaryKeyValue = $row[$primaryKeyColumn];
                }
                $formatResult = $this->formatValues($row);
                if ($formatResult->isSuccess()) {
                    $row = $formatResult->value;
                } else {
                    $continueOperation = false;
                }
                $this->logger->log("Formatted data : ", $row);
                if (empty($condition) && Autocode::validPrimaryKey($primaryKeyValue)) {
                    $condition = "$primaryKeyColumn = :primaryKeyValue";
                    $parameters = [":primaryKeyValue" => $primaryKeyValue];
                }
                $this->logger->log("Update condition : $condition", $parameters);
                if (!empty($row)) {
                    if ($continueOperation) {
                        if ($executeBeforeEvent) {
                            $this->logger->log("Executing before update event");
                            $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                            $rowEvent->row = $row;
                            $rowEvent->eventType = AcEnumDDRowEvent::BEFORE_UPDATE;
                            $eventResult = $rowEvent->execute();
                            if ($eventResult->isSuccess()) {
                                $this->logger->log("Before event result", $eventResult);
                                $row = $rowEvent->row;
                            } else {
                                $this->logger->error("Before event result", $eventResult);
                                $continueOperation = false;
                                $result->setFromResult($eventResult, message: "Aborted from before update row events");
                            }
                        } else {
                            $this->logger->log("Skipping before update event");
                        }
                    }
                    if ($continueOperation) {
                        $updateResult = $this->dao->updateRow(tableName: $this->tableName, row: $row, condition: $condition, parameters: $parameters);
                        if ($updateResult->isSuccess()) {
                            $result->setSuccess(message: "Row updated successfully", logger: $this->logger);
                            $result->primaryKeyColumn = $primaryKeyColumn;
                            $result->primaryKeyValue = $primaryKeyValue;
                            $selectResult = $this->getRows(condition: $condition, parameters: $parameters);
                            if ($selectResult->isSuccess()) {
                                $result->rows = $selectResult->rows;
                            } else {
                                $this->logger->error('Error getting updated row : ' . $selectResult->message, $selectResult);
                                $result->message = 'Error getting updated row : ' . $selectResult->message;
                            }
                            if ($continueOperation && $executeAfterEvent) {
                                $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                                $rowEvent->eventType = AcEnumDDRowEvent::AFTER_UPDATE;
                                $rowEvent->result = $result;
                                $eventResult = $rowEvent->execute();
                                if ($eventResult->isSuccess()) {
                                    $this->logger->log("After event result", $eventResult);
                                    $result = $rowEvent->result;
                                } else {
                                    $this->logger->error("After event result", $eventResult);
                                    $result->setFromResult($eventResult);
                                }
                            }
                        } else {
                            $result->setFromResult($updateResult, logger: $this->logger);
                        }
                    }
                } else {
                    $this->logger->log("No data to update");
                    $result->message = 'No values to update row';
                }
            } else {
                $this->logger->error("Validation result : ", $validateResult);
                $result = $validateResult;
            }
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function updateRows(array $rows, bool $executeAfterEvent = true, bool $executeBeforeEvent = true): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumDDRowOperation::UPDATE);
        try {
            $continueOperation = true;
            $rowsWithConditions = [];
            $primaryKeyValues = [];
            $index = -1;
            foreach ($rows as $row) {
                $index++;
                if ($continueOperation) {
                    $this->logger->log("Updating row with data : ", $row);
                    $validateResult = $this->validateValues(row: $row, isInsert: false);
                    if ($validateResult->isSuccess() && $continueOperation) {
                        $this->logger->log("Validation result : ", $validateResult);
                        $primaryKeyColumn = $this->acDDTable->getPrimaryKeyColumnName();
                        $primaryKeyValue = null;
                        if (isset($row[$primaryKeyColumn])) {
                            $primaryKeyValue = $row[$primaryKeyColumn];
                        }
                        $formatResult = $this->formatValues($row);
                        if ($formatResult->isSuccess()) {
                            $row = $formatResult->value;
                        } else {
                            $continueOperation = false;
                        }
                        $this->logger->log("Formatted data : ", $row);
                        if (!empty($row) && Autocode::validPrimaryKey($primaryKeyValue)) {
                            $condition = "$primaryKeyColumn = :primaryKeyValue$index";
                            $parameters = [":primaryKeyValue$index" => $primaryKeyValue];
                            $primaryKeyValues[] = $primaryKeyValue;
                            $rowsWithConditions[] = ["row" => $row, "condition" => $condition, "parameters" => $parameters];
                        }
                    } else {
                        $this->logger->error("Validation result : ", $validateResult);
                        $result = $validateResult;
                        $continueOperation = false;
                    }
                }
            }
            if ($continueOperation) {
                if (count($rowsWithConditions) > 0) {
                    if ($executeBeforeEvent) {
                        if ($continueOperation) {
                            foreach ($rowsWithConditions as $rowDetails) {
                                $this->logger->log("Executing before update event");
                                $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                                $rowEvent->row = $rowDetails["row"];
                                $rowEvent->condition = $rowDetails["condition"];
                                $rowEvent->parameters = $rowDetails["parameters"];
                                $rowEvent->eventType = AcEnumDDRowEvent::BEFORE_UPDATE;
                                $eventResult = $rowEvent->execute();
                                if ($eventResult->isSuccess()) {
                                    $this->logger->log("Before event result", $eventResult);
                                    $row = $rowEvent->row;
                                } else {
                                    $this->logger->error("Before event result", $eventResult);
                                    $continueOperation = false;
                                    $result->setFromResult($eventResult, message: "Aborted from before update row events");
                                }
                            }
                        }
                    } else {
                        $this->logger->log("Skipping before update event");
                    }
                    if ($continueOperation) {
                        $updateResult = $this->dao->updateRows(tableName: $this->tableName, rowsWithConditions:$rowsWithConditions);
                        if ($updateResult->isSuccess()) {
                            $result->setSuccess(message: "Rows updated successfully", logger: $this->logger);
                            $selectResult = $this->getRows(condition: "$primaryKeyColumn IN (@primaryKeyValues)", parameters: ["@primaryKeyValues" => $primaryKeyValues]);
                            if ($selectResult->isSuccess()) {
                                $result->rows = $selectResult->rows;
                            } else {
                                $continueOperation = false;
                                $this->logger->error('Error getting updated row : ' . $selectResult->message, $selectResult);
                                $result->message = 'Error getting updated row : ' . $selectResult->message;
                            }
                            if ($continueOperation && $executeAfterEvent) {
                                $rowEvent = new AcSqlDbRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                                $rowEvent->eventType = AcEnumDDRowEvent::AFTER_UPDATE;
                                $rowEvent->result = $result;
                                $eventResult = $rowEvent->execute();
                                if ($eventResult->isSuccess()) {
                                    $this->logger->log("After event result", $eventResult);
                                    $result = $rowEvent->result;
                                } else {
                                    $this->logger->error("After event result", $eventResult);
                                    $continueOperation = false;
                                    $result->setFromResult($eventResult);
                                }
                            }
                        } else {
                            $result->setFromResult($updateResult, logger: $this->logger);
                        }
                    }

                } else {
                    $result->message = "Nothing to update";
                }

            }
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function updateValueLengthWithChars(string $value, string $char, int $length): string
    {
        $result = $value;
        if ($length > 0) {
            $currentLength = strlen($value);
            if ($currentLength < $length) {
                $result = str_repeat($char, $length - $currentLength) . $value;
            }
        }
        return $result;
    }

    public function validateValues(array $row, ?bool $isInsert = false): AcResult {
        $result = new AcResult();
        try {
            $continueOperation = true;
            foreach ($this->acDDTable->tableColumns as $column) {
                $value = null;
                if (isset($row[$column->columnName])) {
                    $value = $row[$column->columnName];
                }
                if ($continueOperation) {
                    if ($column->isRequired()) {
                        $validRequired = true;
                        if (!isset($row[$column->columnName]) && $isInsert) {
                            $validRequired = false;
                        } else if (trim((string) $row[$column->columnName]) === "" || $row[$column->columnName] == null) {
                            $validRequired = false;
                        }
                        if (!$validRequired) {
                            $continueOperation = false;
                            $result->setFailure(message: "Required column value is missing");
                        }
                    }
                }
                if ($continueOperation) {
                    if ($column->columnType == AcEnumDDColumnType::INTEGER || $column->columnType == AcEnumDDColumnType::DOUBLE) {
                        if (!is_numeric($value)) {
                            $result->setFailure(message: "Invalid numeric value for column : $column->columnName");
                            break;
                        }
                    } else if ($column->columnType == AcEnumDDColumnType::DATE || $column->columnType == AcEnumDDColumnType::DATETIME || $column->columnType == AcEnumDDColumnType::TIME) {
                        if (!empty($value) && $value !== "NOW") {
                            try {
                                new DateTime($value);
                            } catch (Exception $ex) {
                                $result->setFailure(message: "Invalid datetime value for column : $column->columnName");
                                break;
                            }
                        }
                    }
                }
            }
            if ($continueOperation) {
                $checkResponse = $this->checkUniqueValues($row);
                if ($checkResponse->isFailure()) {
                    $continueOperation = false;
                    $result->setFromResult($checkResponse);
                }
            }
            if ($continueOperation) {
                $result->setSuccess();
            }
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }
}

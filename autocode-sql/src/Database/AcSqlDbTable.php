<?php

namespace AcSql\Database;

require_once '../../autocode/vendor/autoload.php';
require_once '../../autocode-data-dictionary/vendor/autoload.php';

use AcDataDictionary\Enums\AcEnumDDFieldFormat;
use AcDataDictionary\Enums\AcEnumDDTableRowEvent;
use AcSql\Enums\AcEnumFieldType;
use AcSql\Enums\AcEnumRowOperation;
use AcSql\Enums\AcEnumSelectMode;
use AcSql\Models\AcSqlDaoResult;
use Autocode\AcLogger;
use Autocode\AcResult;
use AcDataDictionary\AcDataDictionary;
use AcDataDictionary\Enums\AcEnumDDFieldType;
use AcDataDictionary\Models\AcDDTable;
use AcSql\Enums\AcEnumSqlDatabaseType;
use AcSql\Database\AcSqlDbTableRowEvent;
use Autocode\Autocode;
use DateTime;
use Exception;

class AcSqlDbTable extends AcSqlDbBase {
    public string $tableName = "";
    public AcDDTable $acDDTable;

    public function __construct(string $tableName, string $dataDictionaryName = "default"){
        parent::__construct(dataDictionaryName: "default");
        $this->tableName = $tableName;
        $this->acDDTable = AcDataDictionary::getTable(tableName: $tableName, dataDictionaryName: $dataDictionaryName);
    }

    public function cascadeDeleteRows(array $rows): AcResult
    {
        $result = new AcResult();
        try {
            $continueOperation = true;
            $this->logger->log("Checking cascade delete for table {$this->tableName}");
            $tableRelationships = AcDataDictionary::getTableRelationships(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
            $primaryKeyField = $this->acDDTable->getPrimaryKeyFieldName();
            $this->logger->log("Table relationships : ",$tableRelationships);
            foreach ($rows as $row) {
                $this->logger->log("Checking cascade delete for table row :",$row);
                if ($continueOperation) {
                    foreach ($tableRelationships as $acRelationship) {
                        if ($continueOperation) {
                            $deleteTableName = "";
                            $deleteFieldName = "";
                            $deleteFieldValue = null;
                            $this->logger->log("Checking cascade delete for relationship : ",$acRelationship);
                            if ($acRelationship->sourceTable == $this->tableName && $acRelationship->cascadeDeleteDestination == true) {
                                $deleteTableName = $acRelationship->destinationTable;
                                $deleteFieldName = $acRelationship->destinationField;
                                if (isset($row[$acRelationship->sourceField])) {
                                    $deleteFieldValue = $row[$acRelationship->sourceField];
                                }
                            }
                            if ($acRelationship->destinationTable == $this->tableName && $acRelationship->cascadeDeleteSource == true) {
                                $deleteTableName = $acRelationship->sourceTable;
                                $deleteFieldName = $acRelationship->sourceField;
                                if (isset($row[$acRelationship->destinationField])) {
                                    $deleteFieldValue = $row[$acRelationship->destinationField];
                                }
                            }
                            $this->logger->log("Performing cascade delete with related table $deleteTableName and field $deleteFieldName with value $deleteFieldValue");
                            if (!empty($deleteTableName) && !empty($deleteFieldName)) {
                                if(Autocode::validPrimaryKey($deleteFieldValue)) {
                                    $this->logger->log("Deleting related rows for primary key value : $deleteFieldValue");
                                    $deleteCondition = "$deleteFieldName = :deleteFieldValue";
                                    $deleteAcTable = new AcSqlDbTable($deleteTableName, $this->dataDictionaryName);
                                    $deleteResult = $deleteAcTable->deleteRows(condition: $deleteCondition, parameters: [":deleteFieldValue" => $deleteFieldValue]);
                                    if ($deleteResult->isSuccess()) {
                                        $this->logger->log("Cascade delete successful for $deleteTableName");
                                    } else {
                                        $result->setFromResult(result: $deleteResult, message: "Error in cascade delete: " . $deleteResult->message, logger: $this->logger);
                                        $continueOperation = false;
                                    }
                                }
                                else{
                                    $this->logger->log("No value for cascade delete records");
                                }
                            }
                            else{
                                $this->logger->log("No table & field for cascade delete records");
                            }
                        }
                    }
                }
                if($continueOperation){
                    $result->setSuccess();
                }
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function checkAndSetAutoNumberValues(array $data): AcResult
    {
        $result = new AcResult();
        try {
            $continueOperation = true;
            $autoNumberFields = [];
            $checkFields = [];
            foreach ($this->acDDTable->tableFields as $tableField) {
                $setAutoNumber = true;
                if ($tableField->isAutoNumber()) {
                    if (isset($data[$tableField->fieldName]) && !empty($data[$tableField->fieldName])) {
                        $setAutoNumber = false;
                    }
                    if ($setAutoNumber) {
                        $autoNumberFields[$tableField->fieldName] = [
                            "prefix" => $tableField->getAutoNumberPrefix(),
                            "length" => $tableField->getAutoNumberLength(),
                            "prefix_length" => $tableField->getAutoNumberPrefixLength()
                        ];
                    }
                }
                if ($tableField->checkInAutoNumber() || $tableField->checkInModify()) {
                    $checkFields[] = $tableField->fieldName;
                }
            }
            if (!empty($autoNumberFields)) {
                $getRowss = [];
                $selectFieldsList = array_keys($autoNumberFields);
                $checkCondition = "";
                $checkConditionValues = [];
                if (!empty($checkFields)) {
                    foreach ($checkFields as $checkField) {
                        $checkCondition .= " AND $checkField = @checkField$checkField";
                        if (isset($data[$checkField])) {
                            $checkConditionValues["@checkField$checkField"] = $data[$checkField];
                        }
                    }
                }
                foreach ($selectFieldsList as $name) {
                    $fieldgetRows = "";
                    if ($this->databaseType === AcEnumSqlDatabaseType::MYSQL) {
                        $fieldgetRows = "SELECT CONCAT('{\"$name\":',IF(MAX(CAST(SUBSTRING($name, " . ($autoNumberFields[$name]["prefix_length"] + 1) . ") AS UNSIGNED)) IS NULL,0,MAX(CAST(SUBSTRING($name, " . ($autoNumberFields[$name]["prefix_length"] + 1) . ") AS UNSIGNED))),'}') AS max_json FROM {$this->tableName} WHERE $name LIKE '{$autoNumberFields[$name]["prefix"]}%' $checkCondition";
                    }
                    if (!empty($fieldgetRows)) {
                        $getRowss[] = $fieldgetRows;
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
                            $autoNumberValue = $autoNumberFields[$name]["prefix"] . $this->updateValueLengthWithChars((string) $lastRecordId, "0", $autoNumberFields[$name]["length"]);
                            $data[$name] = $autoNumberValue;
                        }
                    } else {
                        $continueOperation = false;
                        $result->setFromResult($result);
                    }
                }
            }
            if ($continueOperation) {
                $result->setSuccess($data);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function checkUniqueValues(array $data): AcResult
    {
        $result = new AcResult();
        try {
            $parameters = [];
            $conditions = [];
            $modifyConditions = [];
            $uniqueConditions = [];
            $uniqueFields = [];
            $primaryKeyFieldName = $this->acDDTable->getPrimaryKeyFieldName();
            if (!empty($primaryKeyFieldName)) {
                if (isset($data[$primaryKeyFieldName]) && Autocode::validPrimaryKey($data[$primaryKeyFieldName])) {
                    $conditions[] = "$primaryKeyFieldName != @primaryKeyValue";
                    $parameters["@primaryKeyValue"] = $data[$primaryKeyFieldName];
                }
            }
            foreach ($this->acDDTable->tableFields as $tableField) {
                $value = $data[$tableField->fieldName] ?? null;
                if ($tableField->checkInModify()) {
                    $modifyConditions[] = "$tableField->fieldName = @modify_$tableField->fieldName";
                    $parameters["@modify_$tableField->fieldName"] = $value;
                }
                if ($tableField->isUniqueKey()) {
                    $uniqueConditions[] = "$tableField->fieldName = @unique_$tableField->fieldName";
                    $parameters["@unique_$tableField->fieldName"] = $value;
                    $uniqueFields[] = $tableField->fieldName;
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
                            $result->setFailure(value: ["unique_fields" => $uniqueFields], message: "Unique key violated");
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
        $this->logger->log("Deleting record with condition : $condition & primaryKeyValue $primaryKeyValue");
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::DELETE);
        try {
            $continueOperation = true;
            $primaryKeyFieldName = $this->acDDTable->getPrimaryKeyFieldName();
            if (empty($condition)) {
                if (!empty($primaryKeyValue) && !empty($primaryKeyFieldName)) {
                    $condition = "$primaryKeyFieldName = :primaryKeyValue";
                    $parameters[":primaryKeyValue"] = $primaryKeyValue;
                } else {
                    $continueOperation = false;
                    $result->setFailure(message: 'Primary key field of field value is missing');
                }
            } else {
                $condition = " $primaryKeyFieldName  IN (SELECT $primaryKeyFieldName FROM $this->tableName WHERE $condition)";
            }
            if ($continueOperation) {
                if ($executeBeforeEvent) {
                    $rowEvent = new AcSqlDbTableRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                    $rowEvent->condition = $condition;
                    $rowEvent->parameters = $parameters;
                    $rowEvent->eventType = AcEnumDDTableRowEvent::BEFORE_DELETE;
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
                    $setNullResult = $this->setValuesNullBeforeDelete(condition:$condition,parameters: $parameters);
                    if ($setNullResult->isFailure()) {
                        $this->logger->error('Error setting null before delete',$setNullResult);
                        $continueOperation = false;
                        $result->setFromResult($setNullResult);
                    }
                    if ($continueOperation) {
                        $cascadeDeleteResult = $this->cascadeDeleteRows($result->rows);
                        if ($cascadeDeleteResult->isFailure()) {
                            $this->logger->error('Error cascade deleting record',$cascadeDeleteResult);
                            $continueOperation = false;
                            $result->setFromResult($setNullResult,logger:$this->logger);
                        }
                        else{
                            $this->logger->log('Cascade delete result',$cascadeDeleteResult);
                        }
                    }
                    if ($continueOperation) {
                        $deleteResult = $this->dao->deleteRows(tableName:$this->tableName,condition: $condition, parameters: $parameters);
                        if ($deleteResult->isSuccess()) {
                            $result->affectedRowsCount = $deleteResult->affectedRowsCount;
                            $result->setSuccess(message: "$result->affectedRowsCount row(s) deleted successfully");
                        } else {
                            $result->setFromResult($deleteResult);
                            if (strpos($deleteResult->message, "foreign key") !== false) {
                                $result->message = "Cannot delete record! Foreign key constraint is preventing form deleting rows!";
                            }
                        }
                    }
                } else {
                    $result->setFromResult($getResult,logger:$this->logger);
                }
            }
            if ($continueOperation && $executeAfterEvent) {
                $rowEvent = new AcSqlDbTableRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                $rowEvent->eventType = AcEnumDDTableRowEvent::AFTER_DELETE;
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

    public function formatValues($data, $insertMode = false): AcResult
    {
        $result = new AcResult();
        $continueOperation = true;
        $rowEvent = new AcSqlDbTableRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
        $rowEvent->data = $data;
        $rowEvent->eventType = AcEnumDDTableRowEvent::BEFORE_FORMAT;
        $eventResult = $rowEvent->execute();
        if ($eventResult->isSuccess()) {
            $data = $rowEvent->data;
        } else {
            $result->setFromResult(result: $eventResult);
            $continueOperation = false;
        }
        if ($continueOperation) {
            foreach ($this->acDDTable->tableFields as $field) {
                if (isset($data[$field->fieldName]) || $insertMode) {
                    $setFieldValue = isset($data[$field->fieldName]);
                    $formats = $field->getFieldFormats();
                    $type = $field->fieldType;
                    $value = $data[$field->fieldName] ?? "";
                    if ($value === "" && $field->getDefaultValue() != null && $insertMode) {
                        $value = $field->getDefaultValue();
                        $setFieldValue = true;
                    }
                    if ($setFieldValue) {
                        if (in_array($type, [AcEnumDDFieldType::DATE, AcEnumDDFieldType::DATETIME, AcEnumDDFieldType::STRING])) {
                            $value = trim(strval($value));
                            if ($type === AcEnumDDFieldType::STRING) {
                                if (in_array(AcEnumDDFieldFormat::LOWERCASE, $formats)) {
                                    $value = strtolower($value);
                                }
                                if (in_array(AcEnumDDFieldFormat::UPPERCASE, $formats)) {
                                    $value = strtoupper($value);
                                }
                                if (in_array(AcEnumDDFieldFormat::ENCRYPT, $formats)) {
                                    // $value = EncryptionHelper::encrypt($value);
                                }
                            } elseif (in_array($type, [AcEnumDDFieldType::DATE, AcEnumDDFieldType::DATETIME]) && !empty($value)) {
                                try {
                                    $dateTimeValue = new DateTime($value);
                                    $format = ($type === AcEnumDDFieldType::DATETIME) ? 'Y-m-d H:i:s' : 'Y-m-d';
                                    $value = $dateTimeValue->format($format);
                                } catch (Exception $ex) {
                                    $this->logger->log("Error while setting dateTimeValue for $field->fieldName in table $this->tableName with value: $value");
                                }
                            }
                        } elseif (in_array($type, [AcEnumDDFieldType::JSON, AcEnumDDFieldType::MEDIA_JSON])) {
                            $value = is_string($value) ? $value : json_encode($value);
                        } elseif ($type === AcEnumDDFieldType::PASSWORD) {
                            // $value = EncryptionHelper::encrypt($value);
                        }
                        $data[$field->fieldName] = $value;
                    }
                }
            }
        }
        if ($continueOperation) {
            $rowEvent = new AcSqlDbTableRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
            $rowEvent->data = $data;
            $rowEvent->eventType = AcEnumDDTableRowEvent::AFTER_FORMAT;
            $eventResult = $rowEvent->execute();
            if ($eventResult->isSuccess()) {
                $data = $rowEvent->data;
            } else {
                $result->setFromResult(result: $eventResult);
                $continueOperation = false;
            }
        }
        if ($continueOperation) {
            $result->setSuccess($data);
        }
        return $result;
    }

    public function getCreateTableStatement(): string{
        $tableFields = $this->acDDTable->tableFields;
        $columnDefinitions = [];
        foreach ($tableFields as $fieldName => $fieldDetails) {
            $columnDefinition = $this->getFieldDefinitionForStatement($fieldName);
            if ($columnDefinition != "") {
                $columnDefinitions[] = $columnDefinition;
            }
        }
        $result = "CREATE TABLE IF NOT EXISTS $this->tableName (" . implode(",", $columnDefinitions) . ");";
        return $result;
    }

    public function getSelectStatement(?array $includeFields = [], ?array $excludeFields = []): string{
        $result = "SELECT * FROM $this->tableName";
        $fields = [];
        if (empty($includeFields) && empty($excludeFields)) {
            $fields = ["*"];
        } else {
            if (empty($includeFields)) {
                $fields = $includeFields;
            } else if (empty($excludeFields)) {
                $fields = $excludeFields;
            }
        }
        $result = "SELECT " . implode(",", $fields) . " FROM " . $this->tableName;
        return $result;
    }

    public function getDistinctFieldValues(string $fieldName = "", ?string $condition = "", ?string $orderBy = "", ?string $mode = AcEnumSelectMode::LIST , ?int $startIndex = -1, ?int $rowCount = -1, ?array $parameters = []): AcSqlDaoResult{
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::SELECT);
        try {
            if (empty($orderBy)) {
                $orderBy = $fieldName;
            }
            $selectStatement = $this->getSelectStatement();
            $statement = "SELECT DISTINCT $fieldName FROM ($selectStatement) AS recordsList";
            if (!empty($condition)) {
                $condition .= " AND $fieldName IS NOT NULL AND $fieldName != ''";
            }
            $this->logger->log(["", "", "Executing getDistinctFieldValues select statement"]);
            $result = $this->dao->getRows(statement: $statement, mode: $mode, startIndex: $startIndex, rowCount: $rowCount, parameters: $parameters);
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function getFieldDefinitionForStatement(string $fieldName): string{
        $result = "";
        $acDDTableField = $this->acDDTable->tableFields[$fieldName];
        $fieldType = $acDDTableField->fieldType;
        $defaultValue = $acDDTableField->getDefaultValue();
        $size = $acDDTableField->getSize();
        $isAutoIncrementSet = false;
        $isPrimaryKeySet = false;
        if ($this->databaseType == AcEnumSqlDatabaseType::MYSQL) {
            $columnType = "TEXT";
            switch ($fieldType) {
                case AcEnumDDFieldType::AUTO_INCREMENT:
                    $columnType = 'INT AUTO_INCREMENT PRIMARY KEY';
                    $isAutoIncrementSet = true;
                    $isPrimaryKeySet = true;
                    break;
                case AcEnumDDFieldType::BLOB:
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
                case AcEnumDDFieldType::DATE:
                    $columnType = 'DATE';
                    break;
                case AcEnumDDFieldType::DATETIME:
                    $columnType = 'DATETIME';
                    break;
                case AcEnumDDFieldType::DOUBLE:
                    $columnType = 'DOUBLE';
                    break;
                case AcEnumDDFieldType::GUID:
                    $columnType = 'CHAR(36)';
                    break;
                case AcEnumDDFieldType::INTEGER:
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
                case AcEnumDDFieldType::JSON:
                    $columnType = 'LONGTEXT';
                    break;
                case AcEnumDDFieldType::STRING:
                    if ($size == 0) {
                        $size = 255;
                    }
                    $columnType = "VARCHAR($size)";
                    break;
                case AcEnumDDFieldType::TEXT:
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
                case AcEnumDDFieldType::TIME:
                    $columnType = 'TIME';
                    break;
                case AcEnumDDFieldType::TIMESTAMP:
                    $columnType = 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
                    break;
            }
            $result = "$fieldName $columnType";
            if ($acDDTableField->isAutoIncrement() && !$isAutoIncrementSet) {
                $result .= " AUTO_INCREMENT";
            }
            if ($acDDTableField->isPrimaryKey() && !$isPrimaryKeySet) {
                $result .= " PRIMARY KEY";
            }
            if ($acDDTableField->isUniqueKey()) {
                $result .= " UNIQUE";
            }
            if ($acDDTableField->isNotNull()) {
                $result .= " NOT NULL";
            }
            if ($defaultValue != null) {
                // $result.=" DEFAULT $defaultValue";
            }
        } else if ($this->databaseType == AcEnumSqlDatabaseType::SQLITE) {
            $columnType = "TEXT";
            switch ($fieldType) {
                case AcEnumDDFieldType::AUTO_INCREMENT:
                    $columnType = 'INTEGER PRIMARY KEY AUTOINCREMENT';
                    $isAutoIncrementSet = true;
                    $isPrimaryKeySet = true;
                    break;
                case AcEnumDDFieldType::DOUBLE:
                    $columnType = 'REAL';
                    break;
                case AcEnumDDFieldType::BLOB:
                    $columnType = 'BLOB';
                    break;
                case AcEnumDDFieldType::INTEGER:
                    $columnType = 'INTEGER';
                    break;
            }
            $result = "$fieldName $columnType";
            if ($acDDTableField->isAutoIncrement() && !$isAutoIncrementSet) {
                $result .= " AUTOINCREMENT";
            }
            if ($acDDTableField->isPrimaryKey() && !$isPrimaryKeySet) {
                $result .= " PRIMARY KEY";
            }
            if ($acDDTableField->isUniqueKey()) {
                $result .= " UNIQUE ";
            }
            if ($acDDTableField->isNotNull()) {
                $result .= " NOT NULL";
            }
            if ($defaultValue != null) {
                // $result.=" DEFAULT $defaultValue";
            }
        }
        return $result;
    }

    public function getRows(?string $condition = "", ?string $orderBy = "", ?string $mode = AcEnumSelectMode::LIST , ?int $startIndex = -1, ?int $rowCount = -1, ?array $parameters = []): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::SELECT);
        try {
            $selectStatement = $this->getSelectStatement();
            if (!empty($condition)) {
                $selectStatement .= " WHERE $condition";
            }
            if (!empty($orderBy)) {
                $selectStatement .= " ORDER BY $orderBy";
            }
            $result = $this->dao->getRows(statement: $selectStatement, parameters: $parameters, mode: $mode, startIndex: $startIndex, rowCount: $rowCount);
        } catch (Exception $ex) {
            $result->setException(exception: $ex, logger: $this->logger, logException: true);
        }
        return $result;
    }

    public function insertRow(array $data, ?AcResult $validateResult = null, bool $executeAfterEvent = true, bool $executeBeforeEvent = true): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::INSERT);
        try {
            $this->logger->log("Inserting record with data : ",$data);
            $continueOperation = true;
            if ($validateResult == null) {
                $validateResult = $this->validateValues(data: $data, isInsert: true);
            }
            $this->logger->log("Validation result : ",$validateResult);           
            if ($validateResult->isSuccess()) {
                foreach ($this->acDDTable->tableFields as $field) {
                    if (($field->fieldType == AcEnumFieldType::GUID || ($field->fieldType == AcEnumFieldType::STRING && $field->isPrimaryKey())) && !isset($data[$field->fieldName])) {
                        $data[$field->fieldName] = Autocode::guid();
                    }
                }
                $primaryKeyField = $this->acDDTable->getPrimaryKeyFieldName();
                $primaryKeyValue = null;
                if (isset($data[$primaryKeyField])) {
                    $primaryKeyValue = $data[$primaryKeyField];
                }
                if (!empty($data)) {
                    if ($continueOperation) {
                        if ($executeBeforeEvent) {
                            $this->logger->log("Executing before insert event");
                            $rowEvent = new AcSqlDbTableRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                            $rowEvent->data = $data;
                            $rowEvent->eventType = AcEnumDDTableRowEvent::BEFORE_INSERT;
                            $eventResult = $rowEvent->execute();
                            $this->logger->log("Before insert result",$eventResult);
                            if ($eventResult->isSuccess()) {
                                $data = $rowEvent->data;
                            } else {
                                $continueOperation = false;
                                $result->setFromResult($eventResult, message: "Aborted from before insert row events");
                            }
                        }
                    }
                    if ($continueOperation) {
                        $this->logger->log("Inserting data : ",$data);
                        $insertResult = $this->dao->insertRows(table: $this->tableName, values: $data);
                        if ($insertResult->isSuccess()) {
                            $this->logger->log($insertResult);
                            $result->setSuccess(message: "Row inserted successfully");
                            $result->primaryKeyField = $primaryKeyField;
                            $result->primaryKeyValue = $primaryKeyValue;
                            if (!empty($primaryKeyField)) {
                                if (!Autocode::validPrimaryKey($primaryKeyValue) && Autocode::validPrimaryKey($insertResult->lastInsertedId)) {
                                    $primaryKeyValue = $insertResult->lastInsertedId;
                                }
                            }
                            $result->lastInsertedId = $primaryKeyValue;
                            $this->logger->log(message: "Getting inserted record from database");
                            $condition = "$primaryKeyField = :primaryKeyValue";
                            $parameters = [":primaryKeyValue" => $primaryKeyValue];
                            $this->logger->log( "Select condition",$condition,$parameters);
                            $selectResult = $this->getRows(condition: $condition, parameters: $parameters);
                            if ($selectResult->isSuccess()) {
                                if ($selectResult->hasRows()) {
                                    $result->rows = $selectResult->rows;
                                }
                            } else {
                                $result->message = 'Error getting inserted row : ' . $selectResult->message;
                            }
                            if ($continueOperation && $executeAfterEvent) {
                                $rowEvent = new AcSqlDbTableRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                                $rowEvent->eventType = AcEnumDDTableRowEvent::AFTER_INSERT;
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

    public function saveRow(array $data, bool $executeAfterEvent = true, bool $executeBeforeEvent = true): AcSqlDaoResult
    {
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::UNKNOWN);
        try {
            $continueOperation = true;
            $primaryKeyField = $this->acDDTable->getPrimaryKeyFieldName();
            $primaryKeyValue = null;
            if (isset($data[$primaryKeyField])) {
                $primaryKeyValue = $data[$primaryKeyField];
            }
            $validateResult = $this->validateValues($data);
            $checkInSaveFields = [];

            if ($validateResult->isSuccess()) {
                foreach ($this->acDDTable->tableFields as $field) {
                    if ($field->checkInSave()) {
                        $checkInSaveFields[$field->fieldName] = null;
                        if (isset($data[$field->fieldName])) {
                            $checkInSaveFields[$field->fieldName] = $data[$field->fieldName];
                        }
                    }
                }
                if (!Autocode::validPrimaryKey($primaryKeyValue)) {
                    $this->logger->log("Not found primary key value so checking for unique values");
                    if (!empty($checkInSaveFields)) {
                        $checkConditions = [];
                        $checkParameters = [];
                        foreach ($checkInSaveFields as $key => $value) {
                            $checkConditions[] = "$key = :$key";
                            $checkParameters[":$key"] = $value;
                        }
                        $this->logger->log(["Checking for values in save record", $checkConditions, $checkParameters]);
                        $getResult = $this->getRows(condition: implode(" AND ", $checkConditions), parameters: $checkParameters);
                        if ($getResult->isSuccess()) {
                            if ($getResult->hasRows()) {
                                $checkRecord = $getResult->rows[0];
                                if (isset($checkRecord[$primaryKeyField])) {
                                    $primaryKeyValue = $checkRecord[$primaryKeyField];
                                    $data[$primaryKeyField] = $primaryKeyValue;
                                }
                            }
                        } else {
                            $continueOperation = false;
                            $result->setFromResult($getResult);
                        }
                    } else {
                        $continueOperation = false;
                        $result->setFailure(message: "No values to check in save", logger: $this->logger);
                    }
                }
                if ($continueOperation) {
                    $this->logger->log("Primary Key value for Save $primaryKeyField: $primaryKeyValue");
                    if ($executeBeforeEvent) {
                        $rowEvent = new AcSqlDbTableRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                        $rowEvent->data = $data;
                        $rowEvent->eventType = AcEnumDDTableRowEvent::BEFORE_SAVE;
                        $eventResult = $rowEvent->execute();
                        if ($eventResult->isSuccess()) {
                            $data = $rowEvent->data;
                        } else {
                            $continueOperation = false;
                            $result->setFromResult($eventResult, message: "Aborted from before update row events", logger: $this->logger);
                        }
                    }
                    if (Autocode::validPrimaryKey($primaryKeyValue)) {
                        $result = $this->updateRow($data, validateResult: $validateResult);
                        if ($result->isSuccess()) {
                            if ($result->rowsCount() <= 0) {
                                $selectResponse = $this->getRows(condition: "$primaryKeyField = :PrimaryKeyValue", parameters: ["@:rimaryKeyValue" => $primaryKeyValue], mode: AcEnumSelectMode::COUNT);
                                if ($selectResponse->isSuccess() && $selectResponse->rowsCount() > 0) {
                                    $result = $this->insertRow(data: $data, validateResult: $validateResult);
                                }
                            }
                        }
                    } else {
                        $result = $this->insertRow(data: $data, validateResult: $validateResult);
                    }
                    if ($continueOperation && $executeAfterEvent) {
                        $rowEvent = new AcSqlDbTableRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                        $rowEvent->eventType = AcEnumDDTableRowEvent::AFTER_SAVE;
                        $rowEvent->result = $result;
                        $eventResult = $rowEvent->execute();
                        if ($eventResult->isSuccess()) {
                            $result = $rowEvent->result;
                        } else {
                            $result->setFromResult($eventResult);
                        }
                    }
                }
            } else {
                $result = $validateResult;
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
                        if (isset($row[$acRelationship->destinationField])) {
                            $field = $this->acDDTable->getField($acRelationship->destinationField);
                            if ($field != null) {
                                if ($field->isSetValuesNullBeforeDelete()) {
                                    $setNullStatement = "UPDATE $acRelationship->sourceTable SET $acRelationship->sourceField = NULL WHERE $acRelationship->sourceField IN (SELECT $acRelationship->destinationField FROM {$this->tableName} WHERE $condition)";
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

    public function updateRow(array $data, ?string $condition = "", ?array $parameters = [], ?AcResult $validateResult = null, bool $executeAfterEvent = true, bool $executeBeforeEvent = true): AcSqlDaoResult
    {
        $this->logger->log("Updating record with data : ",$data);
        $result = new AcSqlDaoResult(operation: AcEnumRowOperation::UPDATE);
        try {
            $continueOperation = true;
            if ($validateResult == null) {
                $validateResult = $this->validateValues(data: $data, isInsert: false);
            }
            if ($validateResult->isSuccess() && $continueOperation) {                
                $this->logger->log( "Validation result : ",$validateResult);
                $primaryKeyField = $this->acDDTable->getPrimaryKeyFieldName();
                $primaryKeyValue = null;
                if (isset($data[$primaryKeyField])) {
                    $primaryKeyValue = $data[$primaryKeyField];
                }
                $formatResult = $this->formatValues($data);
                if($formatResult->isSuccess()){
                    $data = $formatResult->value;
                }
                else{
                    $continueOperation = false;
                }
                $this->logger->log("Formatted data : ",$data);
                if (empty($condition) && Autocode::validPrimaryKey($primaryKeyValue)) {
                    $condition = "$primaryKeyField = :primaryKeyValue";
                    $parameters = [":primaryKeyValue" => $primaryKeyValue];
                }
                $this->logger->log( "Update condition : $condition",$parameters);
                if (!empty($data)) {
                    if ($continueOperation) {
                        if ($executeBeforeEvent) {
                            $this->logger->log( "Executing before update event");
                            $rowEvent = new AcSqlDbTableRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                            $rowEvent->data = $data;
                            $rowEvent->eventType = AcEnumDDTableRowEvent::BEFORE_UPDATE;
                            $eventResult = $rowEvent->execute();
                            if ($eventResult->isSuccess()) {
                                $this->logger->log( "Before event result",$eventResult);
                                $data = $rowEvent->data;
                            } else {
                                $this->logger->error( "Before event result",$eventResult);
                                $continueOperation = false;
                                $result->setFromResult($eventResult, message: "Aborted from before update row events");
                            }
                        }
                        else{
                            $this->logger->log( "Skipping before update event");
                        }
                    }
                    if ($continueOperation) {
                        $updateResult = $this->dao->updateRows(table: $this->tableName, values: $data, condition: $condition, parameters: $parameters);
                        if ($updateResult->isSuccess()) {
                            $result->setSuccess(message: "Row updated successfully",logger:$this->logger);
                            $result->primaryKeyField = $primaryKeyField;
                            $result->primaryKeyValue = $primaryKeyValue;
                            $selectResult = $this->getRows(condition: $condition, parameters: $parameters);
                            if ($selectResult->isSuccess()) {
                                if ($selectResult->hasRows()) {
                                    $result->rows = $selectResult->rows;
                                }
                            } else {
                                $this->logger->error('Error getting updated row : ' . $selectResult->message,$selectResult);
                                $result->message = 'Error getting updated row : ' . $selectResult->message;
                            }
                            if ($continueOperation && $executeAfterEvent) {
                                $rowEvent = new AcSqlDbTableRowEvent(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
                                $rowEvent->eventType = AcEnumDDTableRowEvent::AFTER_UPDATE;
                                $rowEvent->result = $result;
                                $eventResult = $rowEvent->execute();
                                if ($eventResult->isSuccess()) {
                                    $this->logger->log( "After event result",$eventResult);
                                    $result = $rowEvent->result;
                                } else {
                                    $this->logger->error( "After event result",$eventResult);
                                    $result->setFromResult($eventResult);
                                }
                            }
                        } else {
                            $result->setFromResult($updateResult,logger:$this->logger);
                        }
                    }
                } else {
                    $this->logger->log( "No data to update");
                    $result->message = 'No values to update row';
                }
            } else {
                $this->logger->error( "Validation result : ",$validateResult);
                $result = $validateResult;
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

    public function validateValues(array $data, ?bool $isInsert = false): AcResult
    {
        $result = new AcResult();
        try {
            $continueOperation = true;
            foreach ($this->acDDTable->tableFields as $field) {
                $value = null;
                if (isset($data[$field->fieldName])) {
                    $value = $data[$field->fieldName];
                }
                if ($continueOperation) {
                    if ($field->isRequired()) {
                        $validRequired = true;
                        if (!isset($data[$field->fieldName]) && $isInsert) {
                            $validRequired = false;
                        } else if (trim((string) $data[$field->fieldName]) === "" || $data[$field->fieldName] == null) {
                            $validRequired = false;
                        }
                        if (!$validRequired) {
                            $continueOperation = false;
                            $result->setFailure(message: "Required field value is missing");
                        }
                    }
                }
                if ($continueOperation) {
                    if ($field->fieldType == AcEnumFieldType::INTEGER || $field->fieldType == AcEnumFieldType::DOUBLE) {
                        if (!is_numeric($value)) {
                            $result->setFailure(message: "Invalid numeric value for field : $field->fieldName");
                            break;
                        }
                    } else if ($field->fieldType == AcEnumFieldType::DATE || $field->fieldType == AcEnumFieldType::DATETIME || $field->fieldType == AcEnumFieldType::TIME) {
                        if (!empty($value) && $value !== "NOW") {
                            try {
                                new DateTime($value);
                            } catch (Exception $ex) {
                                $result->setFailure(message: "Invalid datetime value for field : $field->fieldName");
                                break;
                            }
                        }
                    }
                }
            }
            if ($continueOperation) {
                $checkResponse = $this->checkUniqueValues($data);
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

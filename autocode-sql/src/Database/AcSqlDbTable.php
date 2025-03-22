<?php

namespace AcSql\Database;

require_once '../../autocode/vendor/autoload.php';
require_once '../../autocode-data-dictionary/vendor/autoload.php';

use Autocode\AcLogger;
use AcDataDictionary\AcDataDictionary;
use AcDataDictionary\Enums\AcEnumDDFieldFormat;
use AcDataDictionary\Enums\AcEnumDDFieldProperty;
use AcDataDictionary\Enums\AcEnumDDFieldType;
use AcDataDictionary\Enums\AcEnumDDTableProperty;
use AcDataDictionary\Enums\AcEnumDDTableRowEvent;
use AcDataDictionary\Enums\AcEnumDDTableRowOperation;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableField;
use AcDataDictionary\Models\AcDDTableFieldProperty;
use AcDataDictionary\Models\AcDDTableProperty;
use AcDataDictionary\Models\AcDDTableRowEvent;
use AcSql\Enums\AcEnumSqlDatabaseType;

class AcSqlDbTable extends AcSqlDbBase{
    public AcLogger $logger;
    public string $tableName = "";
    public string $dataDictionaryName = "default";
    public AcDDTable $acDDTable;
    public AcDataDictionary $acDataDictionary;

    public function __construct(string $tableName,string $dataDictionaryName = "default") {
        parent::__construct(dataDictionaryName:"default");
        $this->tableName = $tableName;
        $this->acDDTable = AcDataDictionary::getTable(tableName:$tableName,dataDictionaryName:$dataDictionaryName);
        $this->acDataDictionary = AcDataDictionary::getInstance();
    }

    public function getCreateTableStatement():string {
        $tableFields = $this->acDDTable->tableFields;
        $columnDefinitions = [];
        foreach($tableFields as $fieldName => $fieldDetails){
            $columnDefinition = $this->getFieldDefinitionForStatement($fieldName);
            if($columnDefinition!=""){
                $columnDefinitions[] = $columnDefinition;
            }
        }
        $result = "CREATE TABLE IF NOT EXISTS $this->tableName (". implode(",",$columnDefinitions).");";
        return $result;
    }

    public function getFieldDefinitionForStatement(string $fieldName):string{
        $result = "";
        $acDDTableField = $this->acDDTable->tableFields[$fieldName];
        $fieldType = $acDDTableField->fieldType;
        $defaultValue = $acDDTableField->getDefaultValue();
        $size = $acDDTableField->getSize();
        $isAutoIncrementSet = false;
        $isPrimaryKeySet = false;
        if($this->databaseType == AcEnumSqlDatabaseType::MYSQL){
            $columnType = "TEXT";
            switch($fieldType){
                case AcEnumDDFieldType::AUTO_INCREMENT:
                    $columnType = 'INT AUTO_INCREMENT PRIMARY KEY';
                    $isAutoIncrementSet = true;
                    $isPrimaryKeySet = true;
                    break;
                case AcEnumDDFieldType::BLOB:
                    $columnType = "LONGBLOB";
                    if($size>0){
                        if($size <= 255 ){
                            $columnType = "TINYBLOB";
                        }
                        if($size <= 65535 ){
                            $columnType = "BLOB";
                        }
                        else if($size <= 16777215 ){
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
                    if($size>0){
                        if($size <= 255 ){
                            $columnType = "TINYINT";
                        }
                        else if($size <= 65535  ){
                            $columnType = "SMALLINT";
                        }
                        else if($size <= 16777215 ){
                            $columnType = "MEDIUMINT";
                        }
                        else if($size <= 18446744073709551615 ){
                            $columnType = "BIGINT";
                        }
                    }
                    break;
                case AcEnumDDFieldType::JSON:
                    $columnType = 'LONGTEXT';
                    break;
                case AcEnumDDFieldType::STRING:
                    if($size == 0){
                        $size = 255;
                    }
                    $columnType = "VARCHAR($size)";
                    break;
                case AcEnumDDFieldType::TEXT:
                    $columnType = 'LONGTEXT';
                    if($size>0){
                        if($size <= 255 ){
                            $columnType = "TINYTEXT";
                        }
                        if($size <= 65535 ){
                            $columnType = "TEXT";
                        }
                        else if($size <= 16777215 ){
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
            if($acDDTableField->isAutoIncrement() && !$isAutoIncrementSet){
                $result.=" AUTO_INCREMENT";
            }
            if($acDDTableField->isPrimaryKey() && !$isPrimaryKeySet){
                $result.=" PRIMARY KEY";
            }
            if($acDDTableField->isUniqueKey()){
                $result.=" UNIQUE";
            }
            if($acDDTableField->isNotNull()){
                $result.=" NOT NULL";
            }            
            if($defaultValue != null){
                // $result.=" DEFAULT $defaultValue";
            }
        }
        else if($this->databaseType == AcEnumSqlDatabaseType::SQLITE){
            $columnType = "TEXT";
            switch($fieldType){
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
            if($acDDTableField->isAutoIncrement() && !$isAutoIncrementSet){
                $result.=" AUTOINCREMENT";
            }
            if($acDDTableField->isPrimaryKey() && !$isPrimaryKeySet){
                $result.=" PRIMARY KEY";
            }
            if($acDDTableField->isUniqueKey()){
                $result.=" UNIQUE ";
            }
            if($acDDTableField->isNotNull()){
                $result.=" NOT NULL";
            }
            if($defaultValue != null){
                // $result.=" DEFAULT $defaultValue";
            }
        }        
        return $result;
    }
}

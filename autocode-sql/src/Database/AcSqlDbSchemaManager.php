<?php

namespace AcSql\Database;

require_once __DIR__ . './../../../autocode/vendor/autoload.php';
require_once __DIR__ . './../../../autocode-data-dictionary/vendor/autoload.php';
require_once __DIR__ . './../../../autocode-extensions/vendor/autoload.php';

use Autocode\AcLogger;
use Autocode\AcResult;
use AcExtensions\AcExtensionMethods;
use AcDataDictionary\AcDataDictionary;
use AcDataDictionary\Enums\AcEnumDDFieldType;
use AcDataDictionary\Enums\AcEnumDDFieldProperties;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableField;

class AcSqlDbSchemaManager extends AcSqlDbBase{

    static $acSchemaDataDictionary = [
        AcDataDictionary::KEY_VERSION => 1,
        AcDataDictionary::KEY_TABLES => [
            "_ac_schema_details" => [
                AcDDTable::KEY_TABLE_NAME => "_ac_schema_details",
			    AcDDTable::KEY_TABLE_FIELDS => [
                    "ac_schema_detail_id" => [
                        AcDDTableField::KEY_FIELD_NAME => "ac_schema_detail_id",
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::AUTO_INCREMENT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    "ac_schema_detail_key" => [
                        AcDDTableField::KEY_FIELD_NAME => "ac_schema_detail_key",
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::STRING,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    "ac_schema_detail_string_value" => [
                        AcDDTableField::KEY_FIELD_NAME => "ac_schema_detail_string_value",
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::TEXT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    "ac_schema_detail_numeric_value" => [
                        AcDDTableField::KEY_FIELD_NAME => "ac_schema_detail_numeric_value",
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::DOUBLE,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ]
                ]
            ],
            "_ac_schema_logs" => [
                AcDDTable::KEY_TABLE_NAME => "_ac_schema_logs",
			    AcDDTable::KEY_TABLE_FIELDS => [
                    "ac_schema_log_id" => [
                        AcDDTableField::KEY_FIELD_NAME => "ac_schema_log_id",
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::AUTO_INCREMENT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    "ac_schema_operation" => [
                        AcDDTableField::KEY_FIELD_NAME => "ac_schema_operation",
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::STRING,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    "ac_schema_entity_name" => [
                        AcDDTableField::KEY_FIELD_NAME => "ac_schema_entity_name",
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::TEXT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    "ac_schema_entity_value" => [
                        AcDDTableField::KEY_FIELD_NAME => "ac_schema_entity_value",
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::TEXT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    "ac_schema_operation_statement" => [
                        AcDDTableField::KEY_FIELD_NAME => "ac_schema_operation_statement",
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::TEXT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    "ac_schema_operation_result" => [
                        AcDDTableField::KEY_FIELD_NAME => "ac_schema_operation_result",
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::TEXT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    "ac_schema_operation_timestamp" => [
                        AcDDTableField::KEY_FIELD_NAME => "ac_schema_operation_timestamp",
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::TIMESTAMP,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ]
                ]
            ]
        ]
    ];

    function initDatabase():AcResult{
        $result = new AcResult();
        $checkResult = $this->dao->checkDatabaseExist();        
        if($checkResult->isSuccess()){                
            $schemaResult = $this->initSchemaDataDictionary();
            if($schemaResult->isSuccess()){
                $continueOperation = true;
                if(!$checkResult->value) {
                    $createDbResult = $this->dao->createDatabase();
                    if($createDbResult->isSuccess()){
                        $createSchemaResult = $this->createSchema();
                        if($createSchemaResult->isSuccess()){
                            $result->setSuccess(message:'Schema created successfully');
                        }
                        else{
                            $continueOperation = false;
                            $result->setFromResult($createSchemaResult,message:"Error creating database schema from data dictionary");
                        }
                    }
                    else{
                        $continueOperation = false;
                        $result->setFromResult($createDbResult,message:"Error creating database");
                    }                
                }
                else{
                    $updateSchemaResult = $this->updateSchema();
                    if($updateSchemaResult->isSuccess()){
                        $result->setSuccess(message:'Schema updated successfully');
                    }
                    else{
                        $continueOperation = false;
                        $result->setFromResult($createSchemaResult,message:"Error creating database schema from data dictionary");
                    }
                }    
            }
            else{
                $result->setFromResult($schemaResult,message:"Error initializing schema data dictionary");
            } 
        }
        else{
            $result->setFromResult($checkResult,message:"Error checking if database exist");
        }       
        return $result;
    }

    function initSchemaDataDictionary():AcResult{
        $result = new AcResult();
        try{
            if(!AcExtensionMethods::arrayContainsKey("_ac_schema",AcDataDictionary::$dataDictionaries)){
                AcDataDictionary::registerDataDictionary(AcSqlDbSchemaManager::$acSchemaDataDictionary,"_ac_schema");
                $acSchemaManager = new AcSqlDbSchemaManager();
                $acSchemaManager->dataDictionaryName = "_ac_schema";
                $initSchemaResult = $acSchemaManager->initDatabase();
                if($initSchemaResult->isSuccess()){
                    $result->setSuccess();
                }
                else{
                    $result->setFromResult($initSchemaResult,message:"Error setting schema entities in database");
                }
            }
            else{
                $result->setSuccess(message:'Scheama already initialized');
            }
        }
        catch(Exception $ex ){
            $result->setException($ex);
        }        
        return $result;
    }

    function createSchema(){
        $result = new AcResult();
        $createTableResult = $this->createDatabaseTables();
        if($createTableResult->isSuccess()){
            $result->setSuccess();
        }
        else{
            $result->setFromResult($createTableResult,message:'Error creating schema database tables');
        }
        return $result;
    }

    function createDatabaseTables():AcResult{
        $result = new AcResult();
        $acDDTables = AcDataDictionary::getTables(dataDictionaryName:$this->dataDictionaryName);
        $continueOperation = true;
        foreach ($acDDTables as $acDDTable) {
            if($continueOperation){
                $acSqlDbTable = new AcSqlDbTable(tableName:$acDDTable->tableName,dataDictionaryName:$this->dataDictionaryName);
                $createTableStatement = $acSqlDbTable->getCreateTableStatement();
                $createTableResult = $this->dao->sqlStatement($createTableStatement);
                if($createTableResult->isSuccess()){                    
                }
                else{
                    $continueOperation = false;
                    $result->setFromResult($createTableResult);
                }
            }
        }
        if($continueOperation){
            $result->setSuccess();
        }
        return $result;
    }

    function updateSchema(){
        $result = new AcResult();
        $getTablesResult = $this->dao->getDatabaseTables();
        print_r($getTablesResult);
        $result->setSuccess();
        return $result;
    }
}

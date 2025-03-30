<?php

namespace AcSql\Database;

require_once __DIR__ . './../../../autocode/vendor/autoload.php';
require_once __DIR__ . './../../../autocode-data-dictionary/vendor/autoload.php';
require_once __DIR__ . './../../../autocode-extensions/vendor/autoload.php';

use AcSql\Enums\AcEnumSqlDatabaseType;
use Autocode\AcResult;
use AcExtensions\AcExtensionMethods;
use AcDataDictionary\AcDataDictionary;
use AcDataDictionary\Enums\AcEnumDDFieldType;
use AcDataDictionary\Models\AcDDFunction;
use AcDataDictionary\Models\AcDDStoredProcedure;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableField;
use AcDataDictionary\Models\AcDDTrigger;
use AcDataDictionary\Models\AcDDView;

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
                        $result->setFromResult($updateSchemaResult,message:"Error creating database schema from data dictionary");
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

    function createDatabaseFunctions():AcResult{
        $result = new AcResult();
        $acDDFunctions = AcDataDictionary::getFunctions(dataDictionaryName:$this->dataDictionaryName);
        $continueOperation = true;
        foreach ($acDDFunctions as $acDDFunction) {
            if($continueOperation){
                $acSqlDbTable = new AcSqlDbFunction(functionName:$acDDFunction->functionName,dataDictionaryName:$this->dataDictionaryName);
                $dropStatement = $acSqlDbTable->getDropFunctionStatement();
                $dropResult = $this->dao->executeStatement($dropStatement);
                if($dropResult->isSuccess()){                    
                }
                else{
                    $continueOperation = false;
                    $result->setFromResult($dropResult);
                }
                $createStatement = $acSqlDbTable->getCreateFunctionStatement();
                $createResult = $this->dao->executeStatement($createStatement);
                if($createResult->isSuccess()){                    
                }
                else{
                    $continueOperation = false;
                    $result->setFromResult($createResult);
                }
            }
        }
        if($continueOperation){
            $result->setSuccess();
        }
        return $result;
    }

    function createDatabaseRelationships():AcResult{
        $result = new AcResult();
        try{
            $continueOperation = true;
            if($this->databaseType == AcEnumSqlDatabaseType::MYSQL){
                $setCheckResult = $this->dao->executeStatement("SET FOREIGN_KEY_CHECKS = 0;");
                if($setCheckResult->isFailure()){
                    $continueOperation = false;
                    $result->setFromResult($setCheckResult,logger: $this->logger);
                }
            }
            if($continueOperation){
                $getDropRelationshipsStatements = "SELECT CONCAT('ALTER TABLE `', table_name, '` DROP FOREIGN KEY `', constraint_name, '`;') AS drop_query FROM information_schema.table_constraints WHERE constraint_type = 'FOREIGN KEY' AND table_schema = '".$this->sqlConnection->database."'";
                $getResult = $this->dao->getRows($getDropRelationshipsStatements);
                if($getResult->isSuccess()){
                    $rows = $getResult->rows;
                    foreach ($rows as $row) {
                        if($continueOperation){
                            $dropResponse = $this->dao->executeStatement($row['drop_query']);
                            if($dropResponse->isFailure()){
                                $continueOperation = false;
                                $result->setFromResult($dropResponse,logger: $this->logger);
                            }
                        }                        
                    }
                }
                else{
                    $continueOperation = false;
                    $result->setFromResult($getResult,logger: $this->logger);
                }
            }
            if($continueOperation){
                $acDDRelationships = AcDataDictionary::getRelationships(dataDictionaryName:$this->dataDictionaryName);
                foreach ($acDDRelationships as $acDDRelationship) {
                    $acSqlDbRelationship = new AcSqlDbRelationship(acDDRelationship:$acDDRelationship,dataDictionaryName: $this->dataDictionaryName);
                    # code...
                }

                $getDropRelationshipsStatements = "SELECT CONCAT('ALTER TABLE `', table_name, '` DROP FOREIGN KEY `', constraint_name, '`;') AS drop_query FROM information_schema.table_constraints WHERE constraint_type = 'FOREIGN KEY' AND table_schema = '".$this->sqlConnection->database."'";
                $getResult = $this->dao->getRows($getDropRelationshipsStatements);
                if($getResult->isSuccess()){
                    $rows = $getResult->rows;
                    foreach ($rows as $row) {
                        if($continueOperation){
                            $dropResponse = $this->dao->executeStatement($row['drop_query']);
                            if($dropResponse->isFailure()){
                                $continueOperation = false;
                                $result->setFromResult($dropResponse,logger: $this->logger);
                            }
                        }                        
                    }
                }
                else{
                    $continueOperation = false;
                    $result->setFromResult($getResult,logger: $this->logger);
                }
            }
        }
        catch(Exception $ex ){
            $result->setException($ex);
        }        
        return $result;
    }

    function createDatabaseStoredProcedures():AcResult{
        $result = new AcResult();
        try{
        }
        catch(Exception $ex ){
            $result->setException($ex);
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
                $createTableResult = $this->dao->executeStatement($createTableStatement);
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

    function createDatabaseTriggers():AcResult{
        $result = new AcResult();
        try{
        }
        catch(Exception $ex ){
            $result->setException($ex);
        }        
        return $result;
    }

    function createDatabaseViews():AcResult{
        $result = new AcResult();
        try{
        }
        catch(Exception $ex ){
            $result->setException($ex);
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

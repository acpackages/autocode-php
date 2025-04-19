<?php

namespace AcSql\Database;

require_once __DIR__ . './../../../autocode/vendor/autoload.php';
require_once __DIR__ . './../../../autocode-data-dictionary/vendor/autoload.php';
require_once __DIR__ . './../../../autocode-extensions/vendor/autoload.php';
require_once 'AcSchemaDataDictionary.php';

use AcSql\Models\AcSqlDaoResult;
use Autocode\Models\AcResult;
use AcExtensions\AcExtensionMethods;
use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableField;
use AcDataDictionary\Models\AcDDView;
use AcSql\Database\AcSchemaManagerTables;
use AcSql\Database\SchemaDetails;
use AcSql\Database\TblSchemaDetails;
use AcSql\Database\TblSchemaLogs;
use AcSql\Database\AcSMDataDictionary;
use AcSql\Enums\AcEnumSqlDatabaseType;
use AcSql\Enums\AcEnumSqlDatabaseEntity;
use Exception;

class AcSqlDbSchemaManager extends AcSqlDbBase { 

    public $acSqlDDTableSchemaDetails;
    public $acSqlDDTableSchemaLogs;

    function checkSchemaUpdateAvailableFromVersion():AcResult{
        $result = new AcResult();
        try {
            $continueOperation = true;
            $updateAvailable = false;
            if($this->dataDictionaryName != AcSMDataDictionary::DATA_DICTIONARY_NAME){
                $this->logger->log("Checking if database data dictionary version is same as current data dictionary version...");
                $getRowsResult = $this->acSqlDDTableSchemaDetails->getRows(condition:TblSchemaDetails::AC_SCHEMA_DETAIL_KEY." = @key",parameters:[
                    "@key"=>SchemaDetails::KEY_DATA_DICTIONARY_VERSION
                ]);
                if($getRowsResult->isSuccess()){                    
                    if($getRowsResult->hasRows()){
                        $row = $getRowsResult->rows[0];
                        $databaseVersion = $row[TblSchemaDetails::AC_SCHEMA_DETAIL_NUMERIC_VALUE];
                        if($this->acDataDictionary->version == $databaseVersion){
                            $this->logger->log("Database data dictionary and current data dictionary version is same");
                        }
                        else if($this->acDataDictionary->version < $databaseVersion){
                            $this->logger->log("Database data dictionary is greater than current data dictionary version");
                        }
                        else{
                            $this->logger->log("Database data dictionary is less than current data dictionary version");
                            $updateAvailable = true;
                        }
                    }
                    else{
                        $this->logger->log("No version detail row found in details table");
                        $updateAvailable = true;
                    }                    
                }
                else{
                    $continueOperation = false;
                    $result->setFromResult($getRowsResult,logger: $this->logger);
                }
            }
            if($continueOperation){
                $result->setSuccess($updateAvailable,logger:$this->logger);
            }
            
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger);
        }
        return $result;
    }

    function createDatabaseFunctions(): AcResult
    {
        $result = new AcResult();
        try {
            $this->logger->log(args: "Creating functions in database...");
            $acDDFunctions = AcDataDictionary::getFunctions(dataDictionaryName: $this->dataDictionaryName);
            $continueOperation = true;
            foreach ($acDDFunctions as $acDDFunction) {
                if ($continueOperation) {
                    $this->logger->log(args: "Creating function ".$acDDFunction->functionName);
                    $acSqlDbFunction = new AcSqlDbFunction(functionName: $acDDFunction->functionName, dataDictionaryName: $this->dataDictionaryName);
                    $dropStatement = AcSqlDbFunction::getDropFunctionStatement(functionName: $acDDFunction->functionName,databaseType:$this->databaseType);
                    $this->logger->log("Executing drop function statement...",$dropStatement);
                    $dropResult = $this->dao->executeStatement($dropStatement);
                    if ($dropResult->isSuccess()) {
                        $this->logger->log(args: "Drop statement executed successfully");
                    } else {
                        $continueOperation = false;
                        $result->setFromResult($dropResult,'Error executing drop statement',logger:$this->logger);
                    }
                    $this->saveSchemaLogEntry([
                        TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::FUNCTION,
                        TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $acDDFunction->functionName,
                        TblSchemaLogs::AC_SCHEMA_OPERATION => 'drop',
                        TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $dropResult->status,
                        TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $dropStatement,
                        TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                    ]);
                    if ($continueOperation) {
                        $createStatement = $acSqlDbFunction->getCreateFunctionStatement();
                        $this->logger->log(args: "Creating statement : ".$createStatement);
                        $createResult = $this->dao->executeStatement($createStatement);
                        if ($createResult->isSuccess()) {
                            $this->logger->log(args: "Function created successfully");
                        } else {
                            $continueOperation = false;
                            $result->setFromResult($createResult,logger:$this->logger);
                        }
                        $this->saveSchemaLogEntry([
                            TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::FUNCTION,
                            TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $acDDFunction->functionName,
                            TblSchemaLogs::AC_SCHEMA_OPERATION => 'create',
                            TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $createResult->status,
                            TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $createStatement,
                            TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                        ]);
                    }
                }
            }
            if ($continueOperation) {
                $result->setSuccess(message: 'Functions created successfully',logger:$this->logger);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger);
        }
        return $result;
    }

    function createDatabaseRelationships(): AcResult
    {
        $result = new AcResult();
        try {
            $continueOperation = true;
            $this->logger->log(args: "Creating database relationships...");
            if ($this->databaseType == AcEnumSqlDatabaseType::MYSQL) {
                $disableCheckStatement = "SET FOREIGN_KEY_CHECKS = 0;";
                $this->logger->log("Executing disable check statement...",$disableCheckStatement);
                $setCheckResult = $this->dao->executeStatement($disableCheckStatement);
                if ($setCheckResult->isFailure()) {
                    $continueOperation = false;
                    $result->setFromResult($setCheckResult, logger: $this->logger);
                }
                else{
                    $this->logger->log("Disabled foreign key checks.");
                }
            }
            if ($continueOperation) {
                $this->logger->log(args: "Getting and dropping existing relationships...");
                $getDropRelationshipsStatements = "SELECT CONCAT('ALTER TABLE `', table_name, '` DROP FOREIGN KEY `', constraint_name, '`;') AS drop_query,constraint_name FROM information_schema.table_constraints WHERE constraint_type = 'FOREIGN KEY' AND table_schema = '" . $this->sqlConnection->database . "'";
                $this->logger->log("Get relationships statement : ",$getDropRelationshipsStatements);
                $getResult = $this->dao->getRows($getDropRelationshipsStatements);
                if ($getResult->isSuccess()) {
                    $rows = $getResult->rows;
                    foreach ($rows as $row) {
                        if ($continueOperation) {
                            $dropRelationshipStatement = $row['drop_query'];
                            $this->logger->log("Executing drop relationship statement : ".$dropRelationshipStatement);
                            $dropResponse = $this->dao->executeStatement($dropRelationshipStatement);
                            if ($dropResponse->isFailure()) {
                                $continueOperation = false;
                                $result->setFromResult($dropResponse, logger: $this->logger);
                            }
                            else{
                                $this->logger->log("Executed drop relation statement successfully.");
                            }
                            $this->saveSchemaLogEntry([
                                TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::RELATIONSHIP,
                                TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $row['constraint_name'],
                                TblSchemaLogs::AC_SCHEMA_OPERATION => 'drop',
                                TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $dropResponse->status,
                                TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $dropRelationshipStatement,
                                TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                            ]);
                        }
                    }
                } else {
                    $continueOperation = false;
                    $result->setFromResult($getResult, logger: $this->logger);
                }
            }
            if ($continueOperation) {
                $acDDRelationships = AcDataDictionary::getRelationships(dataDictionaryName: $this->dataDictionaryName);
                foreach ($acDDRelationships as $acDDRelationship) {
                    if ($continueOperation) {
                        $this->logger->log("Creating relationship for",$acDDRelationships);
                        $acSqlDbRelationship = new AcSqlDbRelationship(acDDRelationship: $acDDRelationship, dataDictionaryName: $this->dataDictionaryName);
                        $createRelationshipStatement = $acSqlDbRelationship->getCreateReleationshipStatement();
                        $this->logger->log("Create statement",$createRelationshipStatement);
                        $createResult = $this->dao->executeStatement($createRelationshipStatement);
                        if ($createResult->isFailure()) {
                            $continueOperation = false;
                            $result->setFromResult($createResult, logger: $this->logger);
                        }
                        else{
                            $this->logger->log("Relationship created successfully");
                        }
                        $this->saveSchemaLogEntry([
                            TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::RELATIONSHIP,
                            TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $acDDRelationship->sourceTable.".".$acDDRelationship->sourceField.">".$acDDRelationship->destinationTable.".".$acDDRelationship->destinationField,
                            TblSchemaLogs::AC_SCHEMA_OPERATION => 'create',
                            TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $createResult->status,
                            TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $createRelationshipStatement,
                            TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                        ]);
                    }
                }
            }
            if ($continueOperation) {
                $result->setSuccess(message: 'Relationships created successfully',logger:$this->logger);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger);
        }
        return $result;
    }

    function createDatabaseStoredProcedures(): AcResult
    {
        $result = new AcResult();
        try {
            $this->logger->log("Creating stored procedures...");
            $acDDStoredProcedures = AcDataDictionary::getStoredProcedures(dataDictionaryName: $this->dataDictionaryName);
            $continueOperation = true;
            foreach ($acDDStoredProcedures as $acDDStoredProcedure) {
                if ($continueOperation) {
                    $this->logger->log("Creating stored procedure ".$acDDStoredProcedure->storedProcedureName);
                    $acSqlDbTable = new AcSqlDbStoredProcedure(storedProcedureName: $acDDStoredProcedure->storedProcedureName, dataDictionaryName: $this->dataDictionaryName);
                    $dropStatement = AcSqlDbStoredProcedure::getDropStoredProcedureStatement(storedProcedureName: $acDDStoredProcedure->storedProcedureName,databaseType:$this->databaseType);
                    $this->logger->log("Executing drop stored procedure statement...",$dropStatement);
                    $dropResult = $this->dao->executeStatement($dropStatement);
                    if ($dropResult->isSuccess()) {
                        $this->logger->log(args: "Drop statement executed successfully");
                    } else {
                        $continueOperation = false;
                        $result->setFromResult($dropResult,logger:$this->logger);
                    }
                    $this->saveSchemaLogEntry([
                        TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::STORED_PROCEDURE,
                        TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $acDDStoredProcedure->storedProcedureName,
                        TblSchemaLogs::AC_SCHEMA_OPERATION => 'drop',
                        TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $dropResult->status,
                        TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $dropStatement,
                        TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                    ]);
                    if ($continueOperation) {
                        $createStatement = $acSqlDbTable->getCreateStoredProcedureStatement();
                        $this->logger->log("Create statement ",$createStatement);
                        $createResult = $this->dao->executeStatement($createStatement);
                        if ($createResult->isSuccess()) {
                            $this->logger->log("Stored procedure created successfully");
                        } else {
                            $continueOperation = false;
                            $result->setFromResult($createResult,logger:$this->logger);
                        }
                        $this->saveSchemaLogEntry([
                            TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::STORED_PROCEDURE,
                            TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $acDDStoredProcedure->storedProcedureName,
                            TblSchemaLogs::AC_SCHEMA_OPERATION => 'create',
                            TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $createResult->status,
                            TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $createStatement,
                            TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                        ]);
                    }
                }
            }
            if ($continueOperation) {
                $result->setSuccess(message: 'Stored procedures created successfully',logger:$this->logger);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger);
        }
        return $result;
    }

    function createDatabaseTables(): AcResult
    {
        $result = new AcResult();
        try {
            $acDDTables = AcDataDictionary::getTables(dataDictionaryName: $this->dataDictionaryName);
            $continueOperation = true;
            foreach ($acDDTables as $acDDTable) {
                if ($continueOperation) {
                    $this->logger->log("Creating tabe ".$acDDTable->tableName);
                    $acSqlDbTable = new AcSqlDbTable(tableName: $acDDTable->tableName, dataDictionaryName: $this->dataDictionaryName);
                    $createStatement = $acSqlDbTable->getCreateTableStatement();
                    $this->logger->log("Executing create table statement...",$createStatement);
                    $createResult = $this->dao->executeStatement($createStatement);
                    if ($createResult->isSuccess()) {
                        $this->logger->log("Create statement executed successfully");
                    } else {
                        $continueOperation = false;
                        $result->setFromResult($createResult,logger:$this->logger);
                    }
                    $this->saveSchemaLogEntry([
                        TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::TABLE,
                        TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $acDDTable->tableName,
                        TblSchemaLogs::AC_SCHEMA_OPERATION => 'create',
                        TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $createResult->status,
                        TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $createStatement,
                        TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                    ]);
                }
            }
            if ($continueOperation) {
                $result->setSuccess(message: 'Tables created successfully',logger:$this->logger);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger);
        }
        return $result;
    }

    function createDatabaseTriggers(): AcResult
    {
        $result = new AcResult();
        try {
            $this->logger->log("Creating triggers...");
            $acDDTriggers = AcDataDictionary::getTriggers(dataDictionaryName: $this->dataDictionaryName);
            $continueOperation = true;
            foreach ($acDDTriggers as $acDDTrigger) {
                if ($continueOperation) {
                    $this->logger->log("Creating trigger ".$acDDTrigger->triggerName);
                    $acSqlDbTrigger = new AcSqlDbTrigger(triggerName: $acDDTrigger->triggerName, dataDictionaryName: $this->dataDictionaryName);
                    $dropStatement = AcSqlDbTrigger::getDropTriggerStatement(triggerName: $acDDTrigger->triggerName,databaseType:$this->databaseType);
                    $this->logger->log("Executing drop trigger statement...",$dropStatement);
                    $dropResult = $this->dao->executeStatement($dropStatement);
                    if ($dropResult->isSuccess()) {
                        $this->logger->log(args: "Drop statement executed successfully");
                    } else {
                        $continueOperation = false;
                        $result->setFromResult($dropResult,logger:$this->logger);
                    }
                    $this->saveSchemaLogEntry([
                        TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::TRIGGER,
                        TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $acDDTrigger->triggerName,
                        TblSchemaLogs::AC_SCHEMA_OPERATION => 'drop',
                        TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $dropResult->status,
                        TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $dropStatement,
                        TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                    ]);
                    if ($continueOperation) {
                        $createStatement = $acSqlDbTrigger->getCreateTriggerStatement();
                        $this->logger->log("Create statement ",$createStatement);
                        $createResult = $this->dao->executeStatement($createStatement);
                        if ($createResult->isSuccess()) {
                            $this->logger->log("Trigger created successfully");
                        } else {
                            $continueOperation = false;
                            $result->setFromResult($createResult,logger:$this->logger);
                        }
                        $this->saveSchemaLogEntry([
                            TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::TRIGGER,
                            TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $acDDTrigger->triggerName,
                            TblSchemaLogs::AC_SCHEMA_OPERATION => 'create',
                            TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $createResult->status,
                            TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $createStatement,
                            TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                        ]);
                    }
                }
            }
            if ($continueOperation) {
                $result->setSuccess(message: 'Triggers created successfully',logger:$this->logger);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger);
        }
        return $result;
    }

    function createDatabaseViews(): AcResult
    {
        $result = new AcResult();
        try {
            $this->logger->log("Creating views...");
            $acDDViews = AcDataDictionary::getViews(dataDictionaryName: $this->dataDictionaryName);
            $continueOperation = true;
            $errorViews = [];
            foreach ($acDDViews as $acDDView) {
                if ($continueOperation) {
                    $this->logger->log("Creating view ".$acDDView->viewName);
                    $acSqlDbView = new AcSqlDbView(viewName: $acDDView->viewName, dataDictionaryName: $this->dataDictionaryName);
                    $dropStatement = AcSqlDbView::getDropViewStatement(viewName: $acDDView->viewName,databaseType:$this->databaseType);
                    $this->logger->log("Executing drop view statement...",$dropStatement);
                    $dropResult = $this->dao->executeStatement($dropStatement);
                    if ($dropResult->isSuccess()) {
                        $this->logger->log(args: "Drop statement executed successfully");
                    } else {
                        $continueOperation = false;
                        $result->setFromResult($dropResult,logger:$this->logger);
                    }
                    $this->saveSchemaLogEntry([
                        TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::VIEW,
                        TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $acDDView->viewName,
                        TblSchemaLogs::AC_SCHEMA_OPERATION => 'drop',
                        TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $dropResult->status,
                        TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $dropStatement,
                        TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                    ]);
                    if ($continueOperation) {
                        $createStatement = $acSqlDbView->getCreateViewStatement();
                        $this->logger->log("Create statement ",$createStatement);
                        $createResult = $this->dao->executeStatement($createStatement);
                        if ($createResult->isSuccess()) {
                            $this->logger->log("View created successfully");
                        } else {
                            $this->logger->error("Error creating view");
                            $errorViews[] = $acDDView;
                        }
                        $this->saveSchemaLogEntry([
                            TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::VIEW,
                            TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $acDDView->viewName,
                            TblSchemaLogs::AC_SCHEMA_OPERATION => 'create',
                            TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $createResult->status,
                            TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $createStatement,
                            TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                        ]);
                    }
                }
            }
            if (sizeof($errorViews) > 0) {
                $this->logger->log("Retrying creating ".sizeof($errorViews)." views with errors");
                $retryCount = 0;
                $retryViews = [];
                while (sizeof($errorViews) > 0 && $retryCount < 10) {
                    $retryCount++;
                    $this->logger->info(sizeof($errorViews)." views with errors will be retried in iteration ".$retryCount);
                    foreach ($errorViews as $acDDView) {
                        $acSqlDbView = new AcSqlDbView(viewName: $acDDView->viewName, dataDictionaryName: $this->dataDictionaryName);
                        $createStatement = $acSqlDbView->getCreateViewStatement();
                        $this->logger->log("Retrying creating view for ".$acDDView->viewName,$createStatement);
                        $createResult = $this->dao->executeStatement($createStatement);
                        if ($createResult->isSuccess()) {
                            $this->logger->log("View created successfully");
                        } else {
                            $this->logger->error("Error creating view");
                            $retryViews[] = $acDDView;
                        }
                        $this->saveSchemaLogEntry([
                            TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::VIEW,
                            TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $acDDView->viewName,
                            TblSchemaLogs::AC_SCHEMA_OPERATION => 'create',
                            TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $createResult->status,
                            TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $createStatement,
                            TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                        ]);
                    }
                    $this->logger->info("After iteration $retryCount, ".sizeof($retryViews)." still has errors");
                    $errorViews = $retryViews;
                    $retryViews = [];
                    $this->logger->log("Will try executing ".sizeof($errorViews)." in next iteration");
                }
                $this->logger->log("After retrying creating error views, there are ".sizeof($errorViews)." with errors");
                if (sizeof($errorViews) > 0) {
                    $errorViewsList = [];
                    foreach ($errorViews as $acDDView) {
                        $acSqlDbView = new AcSqlDbView(viewName: $acDDView->viewName, dataDictionaryName: $this->dataDictionaryName);
                        $createStatement = $acSqlDbView->getCreateViewStatement();
                        $errorViewDetails = [AcDDView::KEY_VIEW_NAME => $acDDView->viewName,"create_statement"=>$createStatement];
                        $this->logger->error("Error in view",$errorViewDetails);
                        $errorViewsList[] = $errorViewDetails;
                    }
                    $result->setFailure($errorViewsList,message:'Error creating views',logger:$this->logger);
                    $continueOperation = false;
                }
            }
            if ($continueOperation) {
                $result->setSuccess(message: 'Views created successfully',logger:$this->logger);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger);
        }
        return $result;
    }

    function createSchema(): AcResult
    {
        $result = new AcResult();
        try {
            $this->logger->log(args: "Creating schema in database...");
            $continueOperation = true;
            $this->logger->log(args: "Creating tables in database...");
            $createTablesResult = $this->createDatabaseTables();
            if ($createTablesResult->isSuccess()) {
                $this->logger->log(args: "Tables created successfully");
            } else {
                $continueOperation = false;
                $result->setFromResult($createTablesResult, message: 'Error creating schema database tables',logger:$this->logger);
            }
            if ($continueOperation) {
                $createViewsResult = $this->createDatabaseViews();
                if ($createViewsResult->isSuccess()) {
                    $this->logger->log(args: "Views created successfully");
                } else {
                    $continueOperation = false;
                    $result->setFromResult($createViewsResult, message: 'Error creating schema database views',logger:$this->logger);
                }
            }
            if ($continueOperation) {
                $createTriggersResult = $this->createDatabaseTriggers();
                if ($createTriggersResult->isSuccess()) {
                    $this->logger->log(args: "Triggers created successfully");
                } else {
                    $continueOperation = false;
                    $result->setFromResult($createTriggersResult, message: 'Error creating schema database triggers',logger:$this->logger);
                }
            }
            if ($continueOperation) {
                $createStoredProceduresResult = $this->createDatabaseStoredProcedures();
                if ($createStoredProceduresResult->isSuccess()) {
                    $this->logger->log(args: "Stored procedures created successfully");
                } else {
                    $continueOperation = false;
                    $result->setFromResult($createStoredProceduresResult, message: 'Error creating schema database stored procedures',logger:$this->logger);
                }
            }
            if ($continueOperation) {
                $createFuntionsResult = $this->createDatabaseFunctions();
                if ($createFuntionsResult->isSuccess()) {
                    $this->logger->log(args: "Functions created successfully");
                } else {
                    $continueOperation = false;
                    $result->setFromResult($createFuntionsResult, message: 'Error creating schema database functions',logger:$this->logger);
                }
            }
            if ($continueOperation) {
                $result->setSuccess(message: 'Schema created successfully',logger:$this->logger);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger);
        }
        return $result;
    }

    function getDatabaseSchemaDifference():AcResult{
        $result = new AcResult();
        try{
            $continueOperation = true;
            $differenceResult = [];
            $getTablesResult = $this->dao->getDatabaseTables();
            if($getTablesResult->isSuccess()){
                $currentDataDictionaryTables = $this->acDataDictionary->getTableNames();
                $foundTables = [];
                $modifiedTables=[];
                $missingInDataDictionaryTables = [];
                foreach($getTablesResult->rows as $tableRow){
                    if(!in_array($tableRow[AcDDTable::KEY_TABLE_NAME], [AcSchemaManagerTables::SCHEMA_DETAILS,AcSchemaManagerTables::SCHEMA_LOGS])){
                        if(in_array($tableRow[AcDDTable::KEY_TABLE_NAME], $currentDataDictionaryTables)){
                            $tableDifferenceResult = [];
                            $foundTables[] = $tableRow[AcDDTable::KEY_TABLE_NAME];
                            $getTableColumnsResult = $this->dao->getTableColumns(tableName:$tableRow[AcDDTable::KEY_TABLE_NAME]);
                            if($getTableColumnsResult->isSuccess()){                            
                                $currentDataDictionaryFields = $this->acDataDictionary->getTableFieldNames($tableRow[AcDDTable::KEY_TABLE_NAME]);
                                $foundFields = [];
                                $missingInDataDictionaryFields = [];
                                foreach($getTableColumnsResult->rows as $fieldRow){
                                    if(in_array($fieldRow[AcDDTableField::KEY_FIELD_NAME], $currentDataDictionaryFields)){
                                        $foundFields[] = $fieldRow[AcDDTableField::KEY_FIELD_NAME];
                                    }
                                    else{
                                        $missingInDataDictionaryFields[] = $fieldRow[AcDDTableField::KEY_FIELD_NAME];
                                    }
                                }
                                $tableDifferenceResult["missing_fields_in_database"] = array_diff($currentDataDictionaryFields, $foundFields);
                                $tableDifferenceResult["missing_fields_in_data_dictionary"] = $missingInDataDictionaryFields;
                            }
                            else{
                                $continueOperation = false;
                                $result->setFromResult($getTableColumnsResult, message: 'Error getting columns for table '.$tableRow[AcDDTable::KEY_TABLE_NAME],logger:$this->logger);
                            }
                            if(sizeof($tableDifferenceResult['missing_fields_in_database']) > 0 || sizeof($tableDifferenceResult["missing_fields_in_data_dictionary"]) > 0){
                                $modifiedTables[] = [
                                    AcDDTable::KEY_TABLE_NAME => $tableRow[AcDDTable::KEY_TABLE_NAME],"difference_details" => $tableDifferenceResult
                                ];
                            }
                        }
                        else{
                            $missingInDataDictionaryTables[] = $tableRow[AcDDTable::KEY_TABLE_NAME];
                        } 
                    }                                       
                }
                $differenceResult["missing_tables_in_database"] = array_diff($currentDataDictionaryTables, $foundTables);
                $differenceResult["missing_tables_in_data_dictionary"] = $missingInDataDictionaryTables;
                $differenceResult["modified_tables_in_data_dictionary"] = $modifiedTables;
                if($continueOperation){
                    $result->setSuccess();
                }
                $result->value = $differenceResult;
            }
            else{
                $continueOperation = false;
                $result->setFromResult($getTablesResult, message: 'Error getting current database tables',logger:$this->logger);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger);
        }
        return $result;
    }

    function initDatabase(): AcResult
    {
        $result = new AcResult();
        try {
            $this->logger->log("Initializing database...");
            $checkResult = $this->dao->checkDatabaseExist();
            if ($checkResult->isSuccess()) {
                $schemaResult = $this->initSchemaDataDictionary();
                if ($schemaResult->isSuccess()) {
                    $continueOperation = true;
                    $updateDataDictionaryVerion = false;
                    if (!$checkResult->value) {
                        $this->logger->log("Creating database...");
                        $createDbResult = $this->dao->createDatabase();
                        if ($createDbResult->isSuccess()) {
                            $this->logger->log(args: "Database created successfully");
                            $createSchemaResult = $this->createSchema();
                            if ($createSchemaResult->isSuccess()) {
                                $updateDataDictionaryVerion = true;
                                $result->setSuccess(message: 'Schema created successfully',logger:$this->logger);
                                $this->saveSchemaDetail(data:[
                                    TblSchemaDetails::AC_SCHEMA_DETAIL_KEY => SchemaDetails::KEY_CREATED_ON,
                                    TblSchemaDetails::AC_SCHEMA_DETAIL_STRING_VALUE => date("Y-m-d H:i:s")
                                ]);
                            } else {
                                $continueOperation = false;
                                $result->setFromResult($createSchemaResult, message: "Error creating database schema from data dictionary",logger:$this->logger);
                            }
                        } else {
                            $continueOperation = false;
                            $result->setFromResult($createDbResult, message: "Error creating database",logger:$this->logger);
                        }
                    } else {
                        $checkUpdateResult = $this->checkSchemaUpdateAvailableFromVersion();
                        if($checkUpdateResult->isSuccess()) {
                            if($checkUpdateResult->value == true){
                                $updateSchemaResult = $this->updateSchema();
                                if ($updateSchemaResult->isSuccess()) {
                                    $updateDataDictionaryVerion = true;
                                    $result->setSuccess(message: 'Schema updated successfully',logger:$this->logger);
                                    $this->saveSchemaDetail(data:[
                                        TblSchemaDetails::AC_SCHEMA_DETAIL_KEY => SchemaDetails::KEY_LAST_UPDATED_ON,
                                        TblSchemaDetails::AC_SCHEMA_DETAIL_STRING_VALUE => date("Y-m-d H:i:s")
                                    ]);
                                } else {
                                    $continueOperation = false;
                                    $result->setFromResult($updateSchemaResult, message: "Error updating database schema from data dictionary",logger:$this->logger);
                                }
                            }
                            else{
                                $result->setSuccess(message: 'Schema is latest. No changes required',logger:$this->logger);
                            }
                        }
                        else{
                            $continueOperation = false;
                            $result->setFromResult($checkUpdateResult, message: "Error updating database schema from data dictionary",logger:$this->logger);
                        }
                        
                    }
                    if($updateDataDictionaryVerion){
                        $this->saveSchemaDetail(data:[
                            TblSchemaDetails::AC_SCHEMA_DETAIL_KEY => SchemaDetails::KEY_DATA_DICTIONARY_VERSION,
                            TblSchemaDetails::AC_SCHEMA_DETAIL_NUMERIC_VALUE => $this->acDataDictionary->version
                        ]);
                    }
                } else {
                    $result->setFromResult($schemaResult, message: "Error initializing schema data dictionary",logger:$this->logger);
                }
            } else {
                $result->setFromResult($checkResult, message: "Error checking if database exist",logger:$this->logger);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger);
        }
        return $result;
    }

    function initSchemaDataDictionary(): AcResult
    {
        $result = new AcResult();
        try {            
            if (!AcExtensionMethods::arrayContainsKey(AcSMDataDictionary::DATA_DICTIONARY_NAME, AcDataDictionary::$dataDictionaries)) {
                $this->logger->log(args: "Registering schema data dictionary...");
                AcDataDictionary::registerDataDictionary(AcSMDataDictionary::DATA_DICTIONARY, AcSMDataDictionary::DATA_DICTIONARY_NAME);
                $this->acSqlDDTableSchemaDetails = new AcSqlDbTable(tableName:AcSchemaManagerTables::SCHEMA_DETAILS,dataDictionaryName:AcSMDataDictionary::DATA_DICTIONARY_NAME);
                $this->acSqlDDTableSchemaLogs = new AcSqlDbTable(tableName:AcSchemaManagerTables::SCHEMA_LOGS,dataDictionaryName:AcSMDataDictionary::DATA_DICTIONARY_NAME);
                $acSchemaManager = new AcSqlDbSchemaManager();
                $acSchemaManager->dataDictionaryName = "_ac_schema";
                $initSchemaResult = $acSchemaManager->initDatabase();
                if ($initSchemaResult->isSuccess()) {
                    $result->setSuccess(message: 'Schema data dictionary initialized successfully',logger: $this->logger);
                } else {
                    $result->setFromResult($initSchemaResult, message: "Error setting schema entities in database",logger:$this->logger);
                }
            } else {
                $result->setSuccess(message: 'Scheama data dictionary already initialized',logger:$this->logger);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger);
        }
        return $result;
    }
    
    function saveSchemaLogEntry($row):AcSqlDaoResult{
        $result = new AcSqlDaoResult();
        if ($this->acSqlDDTableSchemaLogs !== null) {
            $result = $this->acSqlDDTableSchemaLogs->insertRow($row);
        }
        return $result;
    }

    function saveSchemaDetail(mixed $data):AcSqlDaoResult{
        $result = new AcSqlDaoResult();
        if ($this->acSqlDDTableSchemaDetails !== null) {
            $result = $this->acSqlDDTableSchemaDetails->saveRow($data);
        }
        return $result;
    }    

    function updateDatabaseDifferences():AcResult{
        $result = new AcResult();
        try{
            $continueOperation = true;
            $differenceResult = $this->getDatabaseSchemaDifference();
            $dropFieldStatements = [];
            $dropTableStatements = [];
            if($differenceResult->isSuccess()){
                $differences = $differenceResult->value;
                if(sizeof($differences["missing_tables_in_database"])>0){
                    foreach ($differences["missing_tables_in_database"] as $tableName) {
                        if($continueOperation){
                            $this->logger->log("Creating table ".$tableName);
                            $acSqlDbTable = new AcSqlDbTable(tableName: $tableName, dataDictionaryName: $this->dataDictionaryName);
                            $createStatement = $acSqlDbTable->getCreateTableStatement();
                            $this->logger->log("Executing create table statement...",$createStatement);
                            $createResult = $this->dao->executeStatement($createStatement);
                            if ($createResult->isSuccess()) {
                                $this->logger->log("Create statement executed successfully");
                            } else {
                                $continueOperation = false;
                                $result->setFromResult($createResult,logger:$this->logger);
                            }
                            $this->saveSchemaLogEntry([
                                TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::TABLE,
                                TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $tableName,
                                TblSchemaLogs::AC_SCHEMA_OPERATION => 'create',
                                TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $createResult->status,
                                TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $createStatement,
                                TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                            ]);
                        }
                    }
                }
                if($continueOperation){
                    if(sizeof($differences["modified_tables_in_data_dictionary"])>0){
                        foreach ($differences["modified_tables_in_data_dictionary"] as $modificationDetails) {
                            if($continueOperation){
                                $tableName = $modificationDetails[AcDDTable::KEY_TABLE_NAME];
                                $tableDifferenceDetails = $modificationDetails["difference_details"];
                                if(sizeof($tableDifferenceDetails["missing_fields_in_database"])>0){
                                    foreach ($tableDifferenceDetails["missing_fields_in_database"] as $fieldName) {
                                        if($continueOperation){
                                            $this->logger->log("Adding table field ".$fieldName);
                                            $acSqlDbTableField = new AcSqlDbTableField(tableName: $tableName,fieldName:$fieldName, dataDictionaryName: $this->dataDictionaryName);
                                            $addStatement = $acSqlDbTableField->getAddFieldStatement();
                                            $this->logger->log("Executing add table field statement...",$addStatement);
                                            $createResult = $this->dao->executeStatement($addStatement);
                                            if ($createResult->isSuccess()) {
                                                $this->logger->log("Add statement executed successfully");
                                            } else {
                                                $continueOperation = false;
                                                $result->setFromResult($createResult,logger:$this->logger);
                                            }
                                            $this->saveSchemaLogEntry([
                                                TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => AcEnumSqlDatabaseEntity::TABLE,
                                                TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => $tableName,
                                                TblSchemaLogs::AC_SCHEMA_OPERATION => 'modify',
                                                TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => $createResult->status,
                                                TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => $addStatement,
                                                TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => date("Y-m-d H:i:s")
                                            ]);
                                        }
                                    }
                                }
                                if(sizeof($tableDifferenceDetails["missing_fields_in_data_dictionary"])>0){
                                    foreach ($tableDifferenceDetails["missing_fields_in_data_dictionary"] as $fieldName) {
                                        if($continueOperation){
                                            $acSqlDbTable = new AcSqlDbTable(tableName: $tableName, dataDictionaryName: $this->dataDictionaryName);
                                            $dropFieldStatements[] = AcSqlDbTableField::getDropFieldStatement(tableName:$tableName,fieldName: $fieldName,databaseType:$this->databaseType);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if($continueOperation){
                    if(sizeof($differences["missing_tables_in_data_dictionary"])>0){
                        foreach ($differences["missing_tables_in_data_dictionary"] as $tableName) {
                            $dropTableStatements[] = AcSqlDbTable::getDropTableStatement(tableName:$tableName,databaseType:$this->databaseType);
                        }
                    }
                }
                if($continueOperation){
                    $result->setSuccess();
                    if(sizeof($dropFieldStatements)>0 || sizeof($dropTableStatements)>0){
                        $this->logger->log("There are fields and tables that are not defined in data dictionary. Here are drop statement for dropping them.");
                        foreach ($dropFieldStatements as $dropFieldStatement){
                            $this->logger->error($dropFieldStatement);
                        }
                        foreach ($dropTableStatements as $dropTableStatement){
                            $this->logger->error($dropTableStatement);
                        }
                    }
                }
            }
            else{
                $result->setFromResult($differenceResult);
            }
        } catch (Exception $ex) {
            $result->setException($ex, logger: $this->logger);
        }
        return $result;
    }

    function updateSchema(): AcResult
    {
        $result = new AcResult();
        $continueOperation = true;
        $updateDifferenceResult = $this->updateDatabaseDifferences();
        if($updateDifferenceResult->isSuccess()){
        }
        else{
            $continueOperation = false;
            $result->setFromResult($updateDifferenceResult, message: 'Error updating differences',logger:$this->logger);
        }
        if ($continueOperation) {
            $createViewsResult = $this->createDatabaseViews();
            if ($createViewsResult->isSuccess()) {
            } else {
                $continueOperation = false;
                $result->setFromResult($createViewsResult, message: 'Error updating schema database views',logger:$this->logger);
            }
        }
        if ($continueOperation) {
            $createTriggersResult = $this->createDatabaseTriggers();
            if ($createTriggersResult->isSuccess()) {
            } else {
                $continueOperation = false;
                $result->setFromResult($createTriggersResult, message: 'Error updating schema database triggers',logger:$this->logger);
            }
        }
        if ($continueOperation) {
            $createStoredProceduresResult = $this->createDatabaseStoredProcedures();
            if ($createStoredProceduresResult->isSuccess()) {
            } else {
                $continueOperation = false;
                $result->setFromResult($createStoredProceduresResult, message: 'Error updating schema database stored procedures',logger:$this->logger);
            }
        }
        if ($continueOperation) {
            $createFuntionsResult = $this->createDatabaseFunctions();
            if ($createFuntionsResult->isSuccess()) {
            } else {
                $continueOperation = false;
                $result->setFromResult($createFuntionsResult, message: 'Error updating schema database functions',logger:$this->logger);
            }
        }
        if ($continueOperation) {
            $result->setSuccess(message: 'Schema updated successfully',logger:$this->logger);
        }
        return $result;
    }
}

<?php
namespace AcDataDictionary\Models;

require __DIR__ . './../../../autocode-extensions/vendor/autoload.php';
require_once __DIR__ . './../Enums/AcEnumDDFieldRelationType.php';
require_once 'AcDDFunction.php';
require_once 'AcDDRelationship.php';
require_once 'AcDDStoredProcedure.php';
require_once 'AcDDTable.php';
require_once 'AcDDTableField.php';
require_once 'AcDDTrigger.php';
require_once 'AcDDView.php';
use AcExtensions\AcExtensionMethods;
use AcDataDictionary\Enums\AcEnumDDFieldRelationType;
use AcDataDictionary\Models\AcDDFunction;
use AcDataDictionary\Models\AcDDRelationship;
use AcDataDictionary\Models\AcDDStoredProcedure;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableField;
use AcDataDictionary\Models\AcDDTrigger;
use AcDataDictionary\Models\AcDDView;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;

class AcDataDictionary {
    public const KEY_DATA_DICTIONARIES = "data_dictionaries";
    public const KEY_FUNCTIONS = "functions";
    public const KEY_RELATIONSHIPS = "relationships";
    public const KEY_STORED_PROCEDURES = "stored_procedures";
    public const KEY_TABLES = "tables";
    public const KEY_TRIGGERS = "triggers";
    public const KEY_VERSION = "version";
    public const KEY_VIEWS = "views";
    
    #[AcBindJsonProperty(key: AcDataDictionary::KEY_DATA_DICTIONARIES)]
    public static array $dataDictionaries = [];

    public array $functions = [];

    public array $relationships = [];

    #[AcBindJsonProperty(key: AcDataDictionary::KEY_STORED_PROCEDURES)]
    public array $storedProcedures = [];

    public array $tables = [];

    #[AcBindJsonProperty(key: AcDataDictionary::KEY_TRIGGERS)]
    public array $triggers = [];

    #[AcBindJsonProperty(key: AcDataDictionary::KEY_VERSION)]
    public int $version = 0;

    #[AcBindJsonProperty(key: AcDataDictionary::KEY_VIEWS)]
    public array $views = [];

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public static function fromJsonString(string $jsonString): self {
        $instance = new self();
        $jsonData = AcExtensionMethods::stringParseJsonToArray($jsonString);
        $instance->fromJson($jsonData);
        return $instance;
    }

    public static function getFunctions(string $dataDictionaryName = "default"): array {
        $result = [];
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->functions)) {
            foreach ($acDataDictionary->functions as $functionName => $functionData) {
                $result[$functionName] = AcDDFunction::instanceFromJson($functionData);
            }
        }
        return $result;
    }

    public static function getFunction(string $functionName,string $dataDictionaryName = "default"): ?AcDDFunction {
        $result = null;
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->functions)) {
            if(AcExtensionMethods::arrayContainsKey($functionName,$acDataDictionary->functions)){
                $functionData = $acDataDictionary->functions[$functionName];
                $result = AcDDFunction::instanceFromJson($functionData);
            }
        }
        return $result;
    }

    public static function getInstance(string $dataDictionaryName = "default"): self {
        $result = new self();
        if (isset(self::$dataDictionaries[$dataDictionaryName])) {
            $result->fromJson(self::$dataDictionaries[$dataDictionaryName]);
        }
        return $result;
    }

    public static function getRelationships(string $dataDictionaryName = "default"): array {
        $result = [];
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->relationships)) {
            foreach ($acDataDictionary->relationships as $destinationTableName => $destinationTableDetails) {
                foreach ($destinationTableDetails as $destinationFieldName => $destinationFieldDetails) {
                    foreach ($destinationFieldDetails as $sourceTableName => $sourceTableDetails) {
                        foreach ($sourceTableDetails as $sourceFieldName => $relationshipDetails) {
                            $result[] = AcDDRelationship::instanceFromJson($relationshipDetails);
                        }
                    }
                }
            }
        }
        return $result;
    }

    public static function getStoredProcedures(string $dataDictionaryName = "default"): array {
        $result = [];
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->storedProcedures)) {
            foreach ($acDataDictionary->storedProcedures as $storedProcedureName => $storedProcedureData) {
                $result[$storedProcedureName] = AcDDStoredProcedure::instanceFromJson($storedProcedureData);
            }
        }
        return $result;
    }

    public static function getStoredProcedure(string $storedProcedureName,string $dataDictionaryName = "default"): ?AcDDStoredProcedure {
        $result = null;
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->storedProcedures)) {
            if(AcExtensionMethods::arrayContainsKey($storedProcedureName,$acDataDictionary->storedProcedures)){
                $storedProcedureData = $acDataDictionary->storedProcedures[$storedProcedureName];
                $result = AcDDStoredProcedure::instanceFromJson($storedProcedureData);
            }
        }
        return $result;
    }

    public static function getTable(string $tableName,string $dataDictionaryName = "default"): ?AcDDTable {
        $result = null;
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->tables)) {
            if(AcExtensionMethods::arrayContainsKey($tableName,$acDataDictionary->tables)){
                $tableData = $acDataDictionary->tables[$tableName];
                $result = AcDDTable::instanceFromJson($tableData);
            }
            else{
                echo "Not found";
            }
        }
        else{
            echo "Tables is empty";
        }
        return $result;
    }

    public static function getTableField(string $tableName,string $fieldName,?string $dataDictionaryName = "default"): ?AcDDTableField {
        $result = null;
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->tables)) {
            if(AcExtensionMethods::arrayContainsKey($tableName,$acDataDictionary->tables)){
                $tableData = $acDataDictionary->tables[$tableName];
                $acDDTable = AcDDTable::instanceFromJson($tableData);
                $result = $acDDTable->getField($fieldName);
            }
            else{
                echo "Not found";
            }
        }
        else{
            echo "Tables is empty";
        }
        return $result;
    }

    public static function getTableFieldRelationships(string $tableName,$fieldName,?string $relationType = AcEnumDDFieldRelationType::ANY,string $dataDictionaryName = "default"): array {
        $result = null;
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->relationships)) {
            foreach ($acDataDictionary->relationships as $destinationTableName => $destinationTableDetails) {
                foreach ($destinationTableDetails as $destinationFieldName => $destinationFieldDetails) {
                    foreach ($destinationFieldDetails as $sourceTableName => $sourceTableDetails) {
                        foreach ($sourceTableDetails as $sourceFieldName => $relationshipDetails) {
                            $includeRelation = false;
                            if($relationType == AcEnumDDFieldRelationType::ANY){
                                if(($tableName == $relationshipDetails[AcDDRelationship::KEY_DESTINATION_TABLE] && $fieldName == $relationshipDetails[AcDDRelationship::KEY_DESTINATION_FIELD]) || ($tableName == $relationshipDetails[AcDDRelationship::KEY_SOURCE_TABLE] && $fieldName == $relationshipDetails[AcDDRelationship::KEY_SOURCE_FIELD])){
                                    $includeRelation = true;
                                    $result[] = AcDDRelationship::instanceFromJson($relationshipDetails);
                                } 
                            }
                            else if($relationType == AcEnumDDFieldRelationType::SOURCE){
                                if($tableName == $relationshipDetails[AcDDRelationship::KEY_SOURCE_TABLE] && $fieldName == $relationshipDetails[AcDDRelationship::KEY_SOURCE_FIELD]){
                                    $includeRelation = true;
                                }
                            }
                            else if($relationType == AcEnumDDFieldRelationType::DESTINATION){
                                if($tableName == $relationshipDetails[AcDDRelationship::KEY_DESTINATION_TABLE] && $fieldName == $relationshipDetails[AcDDRelationship::KEY_DESTINATION_FIELD]){
                                    $includeRelation = true;
                                }
                            }
                            if($includeRelation){
                                $result[] = AcDDRelationship::instanceFromJson($relationshipDetails);
                            } 
                        }
                    }
                }
            }
        }
        return $result;
    }

    public static function getTableRelationships(string $tableName,?string $relationType = AcEnumDDFieldRelationType::ANY,string $dataDictionaryName = "default"): array {
        $result = [];
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->relationships)) {
            foreach ($acDataDictionary->relationships as $destinationTableName => $destinationTableDetails) {
                foreach ($destinationTableDetails as $destinationFieldName => $destinationFieldDetails) {
                    foreach ($destinationFieldDetails as $sourceTableName => $sourceTableDetails) {
                        foreach ($sourceTableDetails as $sourceFieldName => $relationshipDetails) {
                            $includeRelation = false;
                            if($relationType == AcEnumDDFieldRelationType::ANY){
                                if($tableName == $relationshipDetails[AcDDRelationship::KEY_DESTINATION_TABLE] || $tableName == $relationshipDetails[AcDDRelationship::KEY_SOURCE_TABLE]){
                                    $includeRelation = true;
                                } 
                            }
                            else if($relationType == AcEnumDDFieldRelationType::SOURCE){
                                if($tableName == $relationshipDetails[AcDDRelationship::KEY_SOURCE_TABLE]){
                                    $includeRelation = true;
                                }
                            }
                            else if($relationType == AcEnumDDFieldRelationType::DESTINATION){
                                if($tableName == $relationshipDetails[AcDDRelationship::KEY_DESTINATION_TABLE]){
                                    $includeRelation = true;
                                }
                            }
                            if($includeRelation){
                                $result[] = AcDDRelationship::instanceFromJson($relationshipDetails);
                            } 
                        }
                    }
                }
            }
        }
        return $result;
    }

    public static function getTables(string $dataDictionaryName = "default"): array {
        $result = [];
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->tables)) {
            foreach ($acDataDictionary->tables as $tableName => $tableData) {
                $result[$tableName] = AcDDTable::instanceFromJson($tableData);
            }
        }
        return $result;
    }   

    public static function getTriggers(string $dataDictionaryName = "default"): array {
        $result = [];
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->triggers)) {
            foreach ($acDataDictionary->triggers as $triggerName => $triggerData) {
                $result[$triggerName] = AcDDTrigger::instanceFromJson($triggerData);
            }
        }
        return $result;
    }

    public static function getTrigger(string $triggerName,string $dataDictionaryName = "default"): ?AcDDTrigger {
        $result = null;
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->triggers)) {
            if(AcExtensionMethods::arrayContainsKey($triggerName,$acDataDictionary->triggers)){
                $triggerData = $acDataDictionary->triggers[$triggerName];
                $result = AcDDTrigger::instanceFromJson($triggerData);
            }
        }
        return $result;
    }

    public static function getViews(string $dataDictionaryName = "default"): array {
        $result = [];
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->views)) {
            foreach ($acDataDictionary->views as $viewName => $viewData) {
                $result[$viewName] = AcDDView::instanceFromJson($viewData);
            }
        }
        return $result;
    }

    public static function getView(string $viewName,string $dataDictionaryName = "default"): ?AcDDView {
        $result = null;
        $acDataDictionary = self::getInstance($dataDictionaryName);
        if (!empty($acDataDictionary->views)) {
            if(AcExtensionMethods::arrayContainsKey($viewName,$acDataDictionary->views)){
                $viewData = $acDataDictionary->views[$viewName];
                $result = AcDDView::instanceFromJson($viewData);
            }
        }
        return $result;
    }

    public static function registerDataDictionary(array $dataDictionaryJson, string $dataDictionaryName = "default"): void {
        self::$dataDictionaries[$dataDictionaryName] = $dataDictionaryJson;
    }

    public static function registerDataDictionaryJsonString(string $jsonString, string $dataDictionaryName = "default"): void {
        $jsonData = AcExtensionMethods::stringParseJsonToArray($jsonString);
        self::registerDataDictionary($jsonData,$dataDictionaryName);
    }

    public function fromJson(array $jsonData): static {
        AcUtilsJson::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }


    public function getTableNames(): array {
        return array_keys($this->tables);
    }

    public function getTablesList(): array {
        return array_values($this->tables);
    }

    public function getTableFieldNames(string $tableName): array {
        return isset($this->tables[$tableName][AcDDTable::KEY_TABLE_FIELDS])
            ? array_keys($this->tables[$tableName][AcDDTable::KEY_TABLE_FIELDS])
            : [];
    }

    public function getTableFieldsList(string $tableName): array {
        return isset($this->tables[$tableName][AcDDTable::KEY_TABLE_FIELDS])
            ? array_values($this->tables[$tableName][AcDDTable::KEY_TABLE_FIELDS])
            : [];
    }

    public function getTableRelationshipsList(string $tableName, bool $asDestination = true): array {
        $result = [];
        foreach ($this->relationships as $destinationTableName => $destinationTableDetails) {
            foreach ($destinationTableDetails as $destinationFieldName => $destinationFieldDetails) {
                foreach ($destinationFieldDetails as $sourceTableName => $sourceTableDetails) {
                    foreach ($sourceTableDetails as $sourceFieldName => $relationshipDetails) {
                        $checkField = $asDestination ? AcDDRelationship::KEY_DESTINATION_TABLE : AcDDRelationship::KEY_SOURCE_TABLE;
                        if ($relationshipDetails[$checkField] === $tableName) {
                            $result[] = $relationshipDetails;
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function getTableTriggersList(string $tableName): array {
        return array_filter(array_values($this->triggers), function ($triggerDetails) use ($tableName) {
            return $triggerDetails[AcDDTrigger::KEY_TRIGGER_TABLE_NAME] === $tableName;
        });
    }

    public function toJson(): array {
        return AcUtilsJson::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

}

?>
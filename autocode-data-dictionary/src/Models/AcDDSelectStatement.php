<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableField.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDSelectStatement {

    const KEY_EXCLUDE_FIELDS = "exclude_fields";
    const KEY_INCLUDE_FIELDS = "include_fields";
    const KEY_CONDITIONS = "filters";
    const KEY_TABLE_NAME = "table_name";

    public AcJsonBindConfig $acJsonBindConfig;
    public string $dataDictionaryName = "";
    public array $excludeFields = [];
    public array $includeFields = [];
    public string $tableName = "";
    private array $groupStack = [];
    public string $sqlStatement = "";
    public string $sqlCondition = "";
    public array $sqlParameters = [];

    private AcDDConditionGroup $conditionGroup;

    public static function fromJson(array $jsonData): AcDDSelectStatement {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public function __construct(string $tableName, string $dataDictionaryName = "default") {
        $this->acJsonBindConfig = AcJsonBindConfig::fromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_EXCLUDE_FIELDS => "excludeFields",
                self::KEY_INCLUDE_FIELDS => "includeFields",
                self::KEY_TABLE_NAME => "tableName"
            ]        
        ]);
        $this->tableName = $tableName;
        $this->dataDictionaryName = $dataDictionaryName;
        $this->conditionGroup = new AcDDConditionGroup();
        $this->groupStack[] = $this->conditionGroup;
    }

    public function addCondition(string $field,string $operator,mixed $value):AcDDSelectStatement{
        $this->groupStack[count($this->groupStack)-1]->addCondition(field: $field, operator: $operator, value: $value);
        return $this;
    }

    public function startGroup(string $operator = 'AND'): self {
        $group = new AcDDConditionGroup();
        $group->operator = $operator;
        $this->groupStack[] = &$group;
        return $this;
    }

    public function closeGroup(): self {
        if (count($this->groupStack) > 1) {
            array_pop($this->groupStack);
        }
        return $this;
    }

    public function setValuesFromJson(array $jsonData = []): void {
        AcUtilsJson::bindInstancePropertiesFromJson(instance: $this, data: $jsonData);
    }

    public function toJson(): array {
        return AcUtilsJson::createJsonArrayFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function getSqlStatement(): string {
        $acDDTable = AcDataDictionary::getTable(tableName: $this->tableName,dataDictionaryName: $this->dataDictionaryName);
        $fields = [];
        if(empty($this->includeFields)&&empty($this->excludeFields)){
            $fields[] = "*";
        }
        else if(!empty($this->includeFields)){
            $fields = $this->includeFields;
        }
        else if(!empty($this->excludeFields)){
            foreach ($acDDTable->getFieldNames() as $field) {
                if(!in_array($field, $this->excludeFields)){
                    $fields[] = $field;
                }
            }
        }
        $fieldsList = implode(separator: ",", array: $fields);
        $this->sqlStatement = "SELECT $fieldsList FROM ".$this->tableName;
        $this->sqlCondition = "";
        $this->sqlParameters = [];
        $this->setSqlConditionGroup(acDDConditionGroup: $this->conditionGroup,includeParenthesis:false);
        if($this->sqlCondition != ""){
            $this->sqlStatement .= " WHERE ".$this->sqlCondition;
        }
        return $this->sqlStatement;
    }

    private function setSqlCondition(AcDDCondition $acDDCondition): void{
        $parameterName = "@parameter".count($this->sqlParameters);
        $this->sqlCondition .= $acDDCondition->field ." ".$acDDCondition->operator." ".$parameterName;
    }

    private function setSqlConditionGroup(AcDDConditionGroup $acDDConditionGroup,bool $includeParenthesis = true): void{
        $index = -1;
        foreach ($acDDConditionGroup->conditions as $acDDCondition) {
            $index++;
            if($index>0){
                $this->sqlCondition .= " ".$acDDCondition->operator." ";
            }
            if($acDDCondition instanceof AcDDConditionGroup){
                if($includeParenthesis){
                    $this->sqlCondition .= "(";
                }
                $this->setSqlConditionGroup(acDDConditionGroup: $acDDCondition);
                if($includeParenthesis){
                    $this->sqlCondition .= ")";
                }
            }
            else if($acDDCondition instanceof AcDDCondition){
                $this->setSqlCondition(acDDCondition: $acDDCondition);
            }            
        }
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}

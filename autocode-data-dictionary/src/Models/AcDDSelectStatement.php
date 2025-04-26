<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableField.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use AcDataDictionary\Enums\AcEnumDDConditionOperator;
use AcDataDictionary\Enums\AcEnumDDFieldType;
use AcDataDictionary\Enums\AcEnumDDLogicalOperator;
use AcExtensions\AcExtensionMethods;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Enums\AcEnumSqlDatabaseType;
use Autocode\Utils\AcUtilsJson;

class AcDDSelectStatement
{
    const KEY_CONDITION = "condition";
    const KEY_CONDITION_GROUP = "condition_group";
    const KEY_DATABASE_TYPE = "database_type";
    const KEY_DATA_DICTIONARY_NAME = "data_dictionary_name";
    const KEY_EXCLUDE_FIELDS = "exclude_fields";
    const KEY_INCLUDE_FIELDS = "include_fields";
    const KEY_ORDER_BY = "order_by";
    const KEY_PAGE_NUMBER = "page_number";
    const KEY_PAGE_SIZE = "page_size";
    const KEY_PARAMETERS = "parameters";
    const KEY_SELECT_STATEMENT = "select_statement";
    const KEY_SQL_STATEMENT = "sql_statement";
    const KEY_TABLE_NAME = "table_name";

    public string $condition = "";

    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_CONDITION_GROUP)]
    public AcDDConditionGroup $conditionGroup;

    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_DATABASE_TYPE)]
    public string $databaseType = "";
    
    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_DATA_DICTIONARY_NAME)]
    public string $dataDictionaryName = "";

    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_EXCLUDE_FIELDS)]
    public array $excludeFields = [];

    private array $groupStack = [];

    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_INCLUDE_FIELDS)]
    public array $includeFields = [];

    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_ORDER_BY)]
    public string $orderBy = "";

    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_PAGE_NUMBER)]
    public int $pageNumber = 0;

    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_PAGE_SIZE)]
    public int $pageSize = 0;
    
    public array $parameters = [];

    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_SELECT_STATEMENT)]
    public string $selectStatement = "";

    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_SQL_STATEMENT)]
    public string $sqlStatement = "";

    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_TABLE_NAME)]
    public string $tableName = "";

    public static function generateSqlStatement(?string $selectStatement = "", ?string $condition = "", ?string $orderBy = "", ?int $pageNumber = 0, ?int $pageSize = 0, ?string $databaseType = AcEnumSqlDatabaseType::UNKNOWN): string {
        $sqlStatement = $selectStatement;
        if ($condition != "") {
            $sqlStatement .= ' WHERE ' . $condition;
        }
        if ($orderBy != "") {
            $sqlStatement .= " ORDER BY " . $orderBy;
        }
        if ($pageSize > 0 && $pageNumber > 0) {
            $sqlStatement .= " LIMIT " . (($pageNumber - 1) * $pageSize) . "," . $pageSize;
        }
        return $sqlStatement;
    }

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function __construct(?string $tableName = "", ?string $dataDictionaryName = "default"){
        $this->tableName = $tableName;
        $this->dataDictionaryName = $dataDictionaryName;
        $this->conditionGroup = new AcDDConditionGroup();
        $this->conditionGroup->operator = AcEnumDDLogicalOperator::AND;
        $this->groupStack[] = &$this->conditionGroup;
    }

    public function addCondition(string $fieldName, string $operator, mixed $value): static {
        $this->groupStack[count($this->groupStack) - 1]->addCondition(fieldName: $fieldName, operator: $operator, value: $value);
        return $this;
    }

    public function addConditionGroup(array $conditions, ?string $operator = AcEnumDDLogicalOperator::AND): static {
        $this->groupStack[count($this->groupStack) - 1]->addConditionGroup(conditions: $conditions, operator: $operator);
        return $this;
    }

    public function endGroup(): static {
        if (count($this->groupStack) > 1) {
            $this->groupStack[count($this->groupStack) - 2]->conditions[] = array_pop($this->groupStack);
        }
        return $this;
    }

    public function fromJson(array $jsonData = []): static {
        AcUtilsJson::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function getSqlStatement(?bool $skipCondition = false,?bool $skipSelectStatement = false): string {
        if(!$skipSelectStatement){
            $acDDTable = AcDataDictionary::getTable(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
            $fields = [];
            if (empty($this->includeFields) && empty($this->excludeFields)) {
                $fields[] = "*";
            } else if (!empty($this->includeFields)) {
                $fields = $this->includeFields;
            } else if (!empty($this->excludeFields)) {
                foreach ($acDDTable->getFieldNames() as $fieldName) {
                    if (!in_array($fieldName, $this->excludeFields)) {
                        $fields[] = $fieldName;
                    }
                }
            }
            $fieldsList = implode(separator: ",", array: $fields);
            $this->selectStatement = "SELECT $fieldsList FROM " . $this->tableName;
        }        
        if(!$skipCondition){
            $this->condition = "";
            $this->parameters = [];
            $this->setSqlConditionGroup(acDDConditionGroup: $this->conditionGroup, includeParenthesis: false);
        }
        $this->sqlStatement = self::generateSqlStatement(selectStatement: $this->selectStatement, condition: $this->condition, orderBy: $this->orderBy, pageNumber: $this->pageNumber, pageSize: $this->pageSize, databaseType: $this->databaseType);
        return $this->sqlStatement;
    }

    public function setConditionsFromFilters(array $filters): AcDDSelectStatement {
        if(AcExtensionMethods::arrayContainsKey(key: AcDDConditionGroup::KEY_CONDITIONS, array: $filters)){
            $operator = AcEnumDDLogicalOperator::AND;
            if(AcExtensionMethods::arrayContainsKey(key: AcDDConditionGroup::KEY_OPERATOR, array: $filters)){
                $operator = $filters[AcDDConditionGroup::KEY_OPERATOR];
            }
            $this->addConditionGroup(conditions:$filters[AcDDConditionGroup::KEY_CONDITIONS],operator:$operator);
        }
        return $this;
    }

    private function setSqlCondition(AcDDCondition $acDDCondition): static {
        if ($acDDCondition->operator == AcEnumDDConditionOperator::BETWEEN) {
            if (is_array($acDDCondition->value) && count($acDDCondition->value) == 2) {
                $parameterName = "@parameter" . count($this->parameters);
                $this->parameters[$parameterName] = $acDDCondition->value[0];
                $this->condition .= $acDDCondition->fieldName . " BETWEEN " . $parameterName;
                $parameterName = "@parameter" . count($this->parameters);
                $this->parameters[$parameterName] = $acDDCondition->value[0];
                $this->condition .= " AND " . $parameterName;
            }
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::EQUAL_TO) {
            $parameterName = "@parameter" . count($this->parameters);
            $this->parameters[$parameterName] = $acDDCondition->value;
            $this->condition .= $acDDCondition->fieldName . " = " . $parameterName;
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::GREATER_THAN) {
            $parameterName = "@parameter" . count($this->parameters);
            $this->parameters[$parameterName] = $acDDCondition->value;
            $this->condition .= $acDDCondition->fieldName . " > " . $parameterName;
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::GREATER_THAN_EQUAL_TO) {
            $parameterName = "@parameter" . count($this->parameters);
            $this->parameters[$parameterName] = $acDDCondition->value;
            $this->condition .= $acDDCondition->fieldName . " >= " . $parameterName;
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::IN) {
            $parameterName = "@parameter" . count($this->parameters);
            if (is_string($acDDCondition->value)) {
                $this->parameters[$parameterName] = explode(",", $acDDCondition->value);
            } else {
                $this->parameters[$parameterName] = $acDDCondition->value;
            }
            $this->condition .= $acDDCondition->fieldName . " IN (" . $parameterName . ")";
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::IS_EMPTY) {
            $this->condition .= $acDDCondition->fieldName . " = ''";
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::IS_NOT_NULL) {
            $this->condition .= $acDDCondition->fieldName . " IS NOT NULL";
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::IS_NULL) {
            $this->condition .= $acDDCondition->fieldName . " IS NULL";
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::LESS_THAN) {
            $parameterName = "@parameter" . count($this->parameters);
            $this->parameters[$parameterName] = $acDDCondition->value;
            $this->condition .= $acDDCondition->fieldName . " < " . $parameterName;
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::LESS_THAN_EQUAL_TO) {
            $parameterName = "@parameter" . count($this->parameters);
            $this->parameters[$parameterName] = $acDDCondition->value;
            $this->condition .= $acDDCondition->fieldName . " <= " . $parameterName;
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::LIKE) {
            $this->setSqlLikeStringCondition(acDDCondition: $acDDCondition);
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::NOT_EQUAL_TO) {
            $parameterName = "@parameter" . count($this->parameters);
            $this->parameters[$parameterName] = $acDDCondition->value;
            $this->condition .= $acDDCondition->fieldName . " != " . $parameterName;
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::NOT_IN) {
            $parameterName = "@parameter" . count($this->parameters);
            if (is_string($acDDCondition->value)) {
                $this->parameters[$parameterName] = explode(",", $acDDCondition->value);
            } else {
                $this->parameters[$parameterName] = $acDDCondition->value;
            }
            $this->condition .= $acDDCondition->fieldName . " NOT IN (" . $parameterName . ")";
        }
        return $this;
    }

    private function setSqlConditionGroup(AcDDConditionGroup $acDDConditionGroup, bool $includeParenthesis = true): static {
        $index = -1;
        foreach ($acDDConditionGroup->conditions as $acDDCondition) {
            $index++;
            if ($index > 0) {
                $this->condition .= " " . $acDDConditionGroup->operator . " ";
            }
            if ($acDDCondition instanceof AcDDConditionGroup) {
                if ($includeParenthesis) {
                    $this->condition .= "(";
                }
                $this->setSqlConditionGroup(acDDConditionGroup: $acDDCondition);
                if ($includeParenthesis) {
                    $this->condition .= ")";
                }
            } else if ($acDDCondition instanceof AcDDCondition) {
                $this->setSqlCondition(acDDCondition: $acDDCondition);
            }
        }
        return $this;
    }

    private function setSqlLikeStringCondition(AcDDCondition $acDDCondition): static {
        $acDDTableField = AcDataDictionary::getTableField(tableName: $this->tableName, fieldName: $acDDCondition->fieldName, dataDictionaryName: $this->dataDictionaryName);
        $fieldCheck = 'LOWER(' . $acDDCondition->fieldName . ')';
        $likeValue = strtolower($acDDCondition->value);
        $jsonField = "value";
        if ($acDDTableField->fieldType == AcEnumDDFieldType::JSON || $acDDTableField->fieldType == AcEnumDDFieldType::MEDIA_JSON) {
            $parameter1 = "@parameter" . count($this->parameters);
            $this->parameters[$parameter1] = "%\"$jsonField\":\"$likeValue%\"%";
            $parameter2 = "@parameter" . count($this->parameters);
            $this->parameters[$parameter2] = "%\"$jsonField\":\"%$likeValue%\"%";
            $parameter3 = "@parameter" . count($this->parameters);
            $this->parameters[$parameter3] = "%\"$jsonField\":\"%$likeValue\"%";
            $this->condition .= '(' . $fieldCheck . ' LIKE ' . $parameter1 . ' OR  ' . $fieldCheck . ' LIKE ' . $parameter2 . ' OR ' . $fieldCheck . ' LIKE ' . $parameter3 . ')';
        } else {
            $parameter1 = "@parameter" . count($this->parameters);
            $this->parameters[$parameter1] = $likeValue . "%";
            $parameter2 = "@parameter" . count($this->parameters);
            $this->parameters[$parameter2] = "%" . $likeValue . "%";
            $this->condition .= '(' . $fieldCheck . ' LIKE ' . $parameter1 . ' OR ' . $fieldCheck . ' LIKE ' . $parameter2 . ')';
        }
        return $this;
    }

    public function startGroup(string $operator = 'AND'): static {
        $group = new AcDDConditionGroup();
        $group->operator = $operator;
        $this->groupStack[] = &$group;
        return $this;
    }

    public function toJson(): array {
        return AcUtilsJson::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

}

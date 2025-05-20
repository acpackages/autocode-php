<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableColumn.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use AcDataDictionary\Enums\AcEnumDDConditionOperator;
use AcDataDictionary\Enums\AcEnumDDColumnType;
use AcDataDictionary\Enums\AcEnumDDLogicalOperator;
use AcExtensions\AcExtensionMethods;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Enums\AcEnumSqlDatabaseType;
use Autocode\Utils\AcJsonUtils;

class AcDDSelectStatement
{
    const KEY_CONDITION = "condition";
    const KEY_CONDITION_GROUP = "condition_group";
    const KEY_DATABASE_TYPE = "database_type";
    const KEY_DATA_DICTIONARY_NAME = "data_dictionary_name";
    const KEY_EXCLUDE_COLUMNS = "exclude_columns";
    const KEY_INCLUDE_COLUMNS = "include_columns";
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

    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_EXCLUDE_COLUMNS)]
    public array $excludeColumns = [];

    private array $groupStack = [];

    #[AcBindJsonProperty(key: AcDDSelectStatement::KEY_INCLUDE_COLUMNS)]
    public array $includeColumns = [];

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

    public function addCondition(string $columnName, string $operator, mixed $value): static {
        $this->groupStack[count($this->groupStack) - 1]->addCondition(columnName: $columnName, operator: $operator, value: $value);
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
        AcJsonUtils::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function getSqlStatement(?bool $skipCondition = false,?bool $skipSelectStatement = false): string {
        if(!$skipSelectStatement){
            $acDDTable = AcDataDictionary::getTable(tableName: $this->tableName, dataDictionaryName: $this->dataDictionaryName);
            $columns = [];
            if (empty($this->includeColumns) && empty($this->excludeColumns)) {
                $columns[] = "*";
            } else if (!empty($this->includeColumns)) {
                $columns = $this->includeColumns;
            } else if (!empty($this->excludeColumns)) {
                foreach ($acDDTable->getColumnNames() as $columnName) {
                    if (!in_array($columnName, $this->excludeColumns)) {
                        $columns[] = $columnName;
                    }
                }
            }
            $columnsList = implode(separator: ",", array: $columns);
            $this->selectStatement = "SELECT $columnsList FROM " . $this->tableName;
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
                $this->condition .= $acDDCondition->columnName . " BETWEEN " . $parameterName;
                $parameterName = "@parameter" . count($this->parameters);
                $this->parameters[$parameterName] = $acDDCondition->value[0];
                $this->condition .= " AND " . $parameterName;
            }
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::EQUAL_TO) {
            $parameterName = "@parameter" . count($this->parameters);
            $this->parameters[$parameterName] = $acDDCondition->value;
            $this->condition .= $acDDCondition->columnName . " = " . $parameterName;
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::GREATER_THAN) {
            $parameterName = "@parameter" . count($this->parameters);
            $this->parameters[$parameterName] = $acDDCondition->value;
            $this->condition .= $acDDCondition->columnName . " > " . $parameterName;
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::GREATER_THAN_EQUAL_TO) {
            $parameterName = "@parameter" . count($this->parameters);
            $this->parameters[$parameterName] = $acDDCondition->value;
            $this->condition .= $acDDCondition->columnName . " >= " . $parameterName;
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::IN) {
            $parameterName = "@parameter" . count($this->parameters);
            if (is_string($acDDCondition->value)) {
                $this->parameters[$parameterName] = explode(",", $acDDCondition->value);
            } else {
                $this->parameters[$parameterName] = $acDDCondition->value;
            }
            $this->condition .= $acDDCondition->columnName . " IN (" . $parameterName . ")";
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::IS_EMPTY) {
            $this->condition .= $acDDCondition->columnName . " = ''";
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::IS_NOT_NULL) {
            $this->condition .= $acDDCondition->columnName . " IS NOT NULL";
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::IS_NULL) {
            $this->condition .= $acDDCondition->columnName . " IS NULL";
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::LESS_THAN) {
            $parameterName = "@parameter" . count($this->parameters);
            $this->parameters[$parameterName] = $acDDCondition->value;
            $this->condition .= $acDDCondition->columnName . " < " . $parameterName;
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::LESS_THAN_EQUAL_TO) {
            $parameterName = "@parameter" . count($this->parameters);
            $this->parameters[$parameterName] = $acDDCondition->value;
            $this->condition .= $acDDCondition->columnName . " <= " . $parameterName;
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::LIKE) {
            $this->setSqlLikeStringCondition(acDDCondition: $acDDCondition);
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::NOT_EQUAL_TO) {
            $parameterName = "@parameter" . count($this->parameters);
            $this->parameters[$parameterName] = $acDDCondition->value;
            $this->condition .= $acDDCondition->columnName . " != " . $parameterName;
        } else if ($acDDCondition->operator == AcEnumDDConditionOperator::NOT_IN) {
            $parameterName = "@parameter" . count($this->parameters);
            if (is_string($acDDCondition->value)) {
                $this->parameters[$parameterName] = explode(",", $acDDCondition->value);
            } else {
                $this->parameters[$parameterName] = $acDDCondition->value;
            }
            $this->condition .= $acDDCondition->columnName . " NOT IN (" . $parameterName . ")";
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
        $acDDTableColumn = AcDataDictionary::getTableColumn(tableName: $this->tableName, columnName: $acDDCondition->columnName, dataDictionaryName: $this->dataDictionaryName);
        $columnCheck = 'LOWER(' . $acDDCondition->columnName . ')';
        $likeValue = strtolower($acDDCondition->value);
        $jsonColumn = "value";
        if ($acDDTableColumn->columnType == AcEnumDDColumnType::JSON ) {
            $parameter1 = "@parameter" . count($this->parameters);
            $this->parameters[$parameter1] = "%\"$jsonColumn\":\"$likeValue%\"%";
            $parameter2 = "@parameter" . count($this->parameters);
            $this->parameters[$parameter2] = "%\"$jsonColumn\":\"%$likeValue%\"%";
            $parameter3 = "@parameter" . count($this->parameters);
            $this->parameters[$parameter3] = "%\"$jsonColumn\":\"%$likeValue\"%";
            $this->condition .= '(' . $columnCheck . ' LIKE ' . $parameter1 . ' OR  ' . $columnCheck . ' LIKE ' . $parameter2 . ' OR ' . $columnCheck . ' LIKE ' . $parameter3 . ')';
        } else {
            $parameter1 = "@parameter" . count($this->parameters);
            $this->parameters[$parameter1] = $likeValue . "%";
            $parameter2 = "@parameter" . count($this->parameters);
            $this->parameters[$parameter2] = "%" . $likeValue . "%";
            $this->condition .= '(' . $columnCheck . ' LIKE ' . $parameter1 . ' OR ' . $columnCheck . ' LIKE ' . $parameter2 . ')';
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
        return AcJsonUtils::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

}

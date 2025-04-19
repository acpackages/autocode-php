<?php

namespace AcSql\Database;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../../../autocode-data-dictionary/vendor/autoload.php';

use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Enums\AcEnumDDFieldType;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableField;
use AcSql\Enums\AcEnumSqlDatabaseType;

use Autocode\Autocode;
use DateTime;
use Exception;

class AcSqlDbTableField extends AcSqlDbBase {
    public string $fieldName = "";
    public string $tableName = "";
    public AcDDTable $acDDTable;
    public AcDDTableField $acDDTableField;

    public function __construct(string $tableName,string $fieldName, string $dataDictionaryName = "default"){
        parent::__construct(dataDictionaryName: $dataDictionaryName);
        $this->tableName = $tableName;
        $this->fieldName = $fieldName;
        $this->acDDTable = AcDataDictionary::getTable(tableName: $tableName, dataDictionaryName: $dataDictionaryName);
        $this->acDDTableField =  AcDataDictionary::getTableField(tableName: $tableName,fieldName:$fieldName, dataDictionaryName: $dataDictionaryName);
    }

    public static function getDropFieldStatement(string $tableName,string $fieldName,?string $databaseType = AcEnumSqlDatabaseType::UNKNOWN): string{
        $result = "ALTER TABLE ".$tableName." DROP COLUMN ".$fieldName.";";
        return $result;
    }

    public function getAddFieldStatement():string{
        $result = "";
        if ($this->databaseType == AcEnumSqlDatabaseType::MYSQL) {
            $result = "ALTER TABLE ".$this->tableName." ADD COLUMN ".$this->getFieldDefinitionForStatement();
        }
        return $result;
    }

    public function getFieldDefinitionForStatement(): string{
        $result = "";
        $fieldType = $this->acDDTableField->fieldType;
        $defaultValue = $this->acDDTableField->getDefaultValue();
        $size = $this->acDDTableField->getSize();
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
            $result = "$this->fieldName $columnType";
            if ($this->acDDTableField->isAutoIncrement() && !$isAutoIncrementSet) {
                $result .= " AUTO_INCREMENT";
            }
            if ($this->acDDTableField->isPrimaryKey() && !$isPrimaryKeySet) {
                $result .= " PRIMARY KEY";
            }
            if ($this->acDDTableField->isUniqueKey()) {
                $result .= " UNIQUE";
            }
            if ($this->acDDTableField->isNotNull()) {
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
            $result = "$this->fieldName $columnType";
            if ($this->acDDTableField->isAutoIncrement() && !$isAutoIncrementSet) {
                $result .= " AUTOINCREMENT";
            }
            if ($this->acDDTableField->isPrimaryKey() && !$isPrimaryKeySet) {
                $result .= " PRIMARY KEY";
            }
            if ($this->acDDTableField->isUniqueKey()) {
                $result .= " UNIQUE ";
            }
            if ($this->acDDTableField->isNotNull()) {
                $result .= " NOT NULL";
            }
            if ($defaultValue != null) {
                // $result.=" DEFAULT $defaultValue";
            }
        }
        return $result;
    }

    
}

<?php

namespace AcSql\Database;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../../../autocode-data-dictionary/vendor/autoload.php';

use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Enums\AcEnumDDColumnType;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableColumn;
use Autocode\Enums\AcEnumSqlDatabaseType;

use Autocode\Autocode;
use DateTime;
use Exception;

class AcSqlDbTableColumn extends AcSqlDbBase {
    public string $columnName = "";
    public string $tableName = "";
    public AcDDTable $acDDTable;
    public AcDDTableColumn $acDDTableColumn;

    public function __construct(string $tableName,string $columnName, string $dataDictionaryName = "default"){
        parent::__construct(dataDictionaryName: $dataDictionaryName);
        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->acDDTable = AcDataDictionary::getTable(tableName: $tableName, dataDictionaryName: $dataDictionaryName);
        $this->acDDTableColumn =  AcDataDictionary::getTableColumn(tableName: $tableName,columnName:$columnName, dataDictionaryName: $dataDictionaryName);
    }

    public static function getDropColumnStatement(string $tableName,string $columnName,?string $databaseType = AcEnumSqlDatabaseType::UNKNOWN): string{
        $result = "ALTER TABLE ".$tableName." DROP COLUMN ".$columnName.";";
        return $result;
    }

    public function getAddColumnStatement():string{
        $result = "";
        if ($this->databaseType == AcEnumSqlDatabaseType::MYSQL) {
            $result = "ALTER TABLE ".$this->tableName." ADD COLUMN ".$this->getColumnDefinitionForStatement();
        }
        return $result;
    }

    public function getColumnDefinitionForStatement(): string{
        $result = "";
        $columnType = $this->acDDTableColumn->columnType;
        $defaultValue = $this->acDDTableColumn->getDefaultValue();
        $size = $this->acDDTableColumn->getSize();
        $isAutoIncrementSet = false;
        $isPrimaryKeySet = false;
        if ($this->databaseType == AcEnumSqlDatabaseType::MYSQL) {
            $columnType = "TEXT";
            switch ($columnType) {
                case AcEnumDDColumnType::AUTO_INCREMENT:
                    $columnType = 'INT AUTO_INCREMENT PRIMARY KEY';
                    $isAutoIncrementSet = true;
                    $isPrimaryKeySet = true;
                    break;
                case AcEnumDDColumnType::BLOB:
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
                case AcEnumDDColumnType::DATE:
                    $columnType = 'DATE';
                    break;
                case AcEnumDDColumnType::DATETIME:
                    $columnType = 'DATETIME';
                    break;
                case AcEnumDDColumnType::DOUBLE:
                    $columnType = 'DOUBLE';
                    break;
                case AcEnumDDColumnType::UUID:
                    $columnType = 'CHAR(36)';
                    break;
                case AcEnumDDColumnType::INTEGER:
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
                case AcEnumDDColumnType::JSON:
                    $columnType = 'LONGTEXT';
                    break;
                case AcEnumDDColumnType::STRING:
                    if ($size == 0) {
                        $size = 255;
                    }
                    $columnType = "VARCHAR($size)";
                    break;
                case AcEnumDDColumnType::TEXT:
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
                case AcEnumDDColumnType::TIME:
                    $columnType = 'TIME';
                    break;
                case AcEnumDDColumnType::TIMESTAMP:
                    $columnType = 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
                    break;
            }
            $result = "$this->columnName $columnType";
            if ($this->acDDTableColumn->isAutoIncrement() && !$isAutoIncrementSet) {
                $result .= " AUTO_INCREMENT";
            }
            if ($this->acDDTableColumn->isPrimaryKey() && !$isPrimaryKeySet) {
                $result .= " PRIMARY KEY";
            }
            if ($this->acDDTableColumn->isUniqueKey()) {
                $result .= " UNIQUE";
            }
            if ($this->acDDTableColumn->isNotNull()) {
                $result .= " NOT NULL";
            }
            if ($defaultValue != null) {
                // $result.=" DEFAULT $defaultValue";
            }
        } else if ($this->databaseType == AcEnumSqlDatabaseType::SQLITE) {
            $columnType = "TEXT";
            switch ($columnType) {
                case AcEnumDDColumnType::AUTO_INCREMENT:
                    $columnType = 'INTEGER PRIMARY KEY AUTOINCREMENT';
                    $isAutoIncrementSet = true;
                    $isPrimaryKeySet = true;
                    break;
                case AcEnumDDColumnType::DOUBLE:
                    $columnType = 'REAL';
                    break;
                case AcEnumDDColumnType::BLOB:
                    $columnType = 'BLOB';
                    break;
                case AcEnumDDColumnType::INTEGER:
                    $columnType = 'INTEGER';
                    break;
            }
            $result = "$this->columnName $columnType";
            if ($this->acDDTableColumn->isAutoIncrement() && !$isAutoIncrementSet) {
                $result .= " AUTOINCREMENT";
            }
            if ($this->acDDTableColumn->isPrimaryKey() && !$isPrimaryKeySet) {
                $result .= " PRIMARY KEY";
            }
            if ($this->acDDTableColumn->isUniqueKey()) {
                $result .= " UNIQUE ";
            }
            if ($this->acDDTableColumn->isNotNull()) {
                $result .= " NOT NULL";
            }
            if ($defaultValue != null) {
                // $result.=" DEFAULT $defaultValue";
            }
        }
        return $result;
    }
    
}

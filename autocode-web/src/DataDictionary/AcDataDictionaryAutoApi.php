<?php
namespace AcWeb\DataDictionary;
require_once __DIR__ . './../../../autocode/vendor/autoload.php';
require_once __DIR__ . './../../../autocode-data-dictionary/vendor/autoload.php';
require_once __DIR__ . './../../../autocode-extensions/vendor/autoload.php';
require_once __DIR__ . './../ApiDocs/Utils/AcApiDocUtils.php';
require_once __DIR__ . './AcDataDictionaryAutoDelete.php';
require_once __DIR__ . './AcDataDictionaryAutoInsert.php';
require_once __DIR__ . './AcDataDictionaryAutoSave.php';
require_once __DIR__ . './AcDataDictionaryAutoSelect.php';
require_once __DIR__ . './AcDataDictionaryAutoSelectDistinct.php';
require_once __DIR__ . './AcDataDictionaryAutoUpdate.php';

use AcDataDictionary\Models\AcDataDictionary;
use AcSql\Database\AcSqlDbTable;
use AcWeb\ApiDocs\Models\AcApiDocRoute;
use AcWeb\Models\AcWebRequest;
use AcWeb\Models\AcWebResponse;
use AcWeb\Annotaions\AcWebValueFromPath;
use AcWeb\ApiDocs\Models\AcApiDocParameter;
use AcWeb\ApiDocs\Models\AcApiDocTag;
use AcWeb\Core\AcWeb;

use ApiDocs\Utils\AcApiDocUtils;
use Autocode\Enums\AcEnumHttpMethod;
class AcDataDictionaryAutoApi {
    public AcWeb $acWeb;
    public string $dataDictionaryName = "";
    public mixed $excludeTables = [];
    public mixed $includeTables = [];
    public string $pathForDelete = "delete";
    public string $pathForInsert = "add";
    public string $pathForSave = "save";
    public string $pathForSelect = "get";
    public string $pathForSelectDistinct = "unique";
    public string $pathForUpdate = "update";
    public string $urlPrefix = "";
    public AcDataDictionary $acDataDictionary;

    public function __construct(AcWeb $acWeb, string $dataDictionaryName = "default")
    {
        $this->acWeb = $acWeb;
        $this->dataDictionaryName = $dataDictionaryName;
        $this->acDataDictionary = AcDataDictionary::getInstance(dataDictionaryName: $dataDictionaryName);
    }

    public function excludeTable(string $tableName, ?bool $delete, ?bool $insert, ?bool $save, ?bool $select, ?bool $selectDistinct, ?bool $update): static
    {
        if ($delete == null && $insert == null && $save == null && $select == null && $selectDistinct == null && $update == null) {
            $delete = true;
            $insert = true;
            $save = true;
            $select = true;
            $selectDistinct = true;
            $update = true;
        } else {
            if ($delete == null) {
                $delete = false;
            }
            if ($insert == null) {
                $insert = false;
            }
            if ($save == null) {
                $save = false;
            }
            if ($select == null) {
                $select = false;
            }
            if ($selectDistinct == null) {
                $selectDistinct = false;
            }
            if ($update == null) {
                $update = false;
            }
        }
        $this->excludeTables[$tableName] = [
            "delete" => $delete,
            "insert" => $insert,
            "save" => $save,
            "select" => $select,
            "select_distinct" => $selectDistinct,
            "update" => $update
        ];
        return $this;
    }

    public function includeTable(string $tableName, ?bool $delete = null, ?bool $insert = null, ?bool $save = null, ?bool $select = null, ?bool $selectDistinct = null, ?bool $update = null): static {
        if ($delete == null && $insert == null && $save == null && $select == null && $selectDistinct == null && $update == null) {
            $delete = true;
            $insert = true;
            $save = true;
            $select = true;
            $selectDistinct = true;
            $update = true;
        } else {
            if ($delete == null) {
                $delete = false;
            }
            if ($insert == null) {
                $insert = false;
            }
            if ($save == null) {
                $save = false;
            }
            if ($select == null) {
                $select = false;
            }
            if ($selectDistinct == null) {
                $selectDistinct = false;
            }
            if ($update == null) {
                $update = false;
            }
        }
        $this->includeTables[$tableName] = [
            "delete" => $delete,
            "insert" => $insert,
            "save" => $save,
            "select" => $select,
            "select_distinct" => $selectDistinct,
            "update" => $update
        ];
        return $this;
    }

    public function generate(): static{
        foreach (AcDataDictionary::getTables(dataDictionaryName: $this->dataDictionaryName) as $acDDTable) {
            $schema = AcApiDocUtils::getApiModelRefFromAcDDTable(acDDTable: $acDDTable, acApiDoc: $this->acWeb->acApiDoc);
            $continueOperation = false;
            $delete = true;
            $insert = true;
            $save = true;
            $select = true;
            $selectDistinct = true;
            $update = true;
            if (sizeof($this->includeTables) > 0 && sizeof($this->excludeTables) > 0) {
                $continueOperation = true;
            } else {
                if (sizeof($this->includeTables) > 0) {
                    if (isset($this->includeTables[$acDDTable->tableName])) {
                        $continueOperation = true;
                        $delete = $this->includeTables[$acDDTable->tableName]["delete"];
                        $insert = $this->includeTables[$acDDTable->tableName]["insert"];
                        $save = $this->includeTables[$acDDTable->tableName]["save"];
                        $select = $this->includeTables[$acDDTable->tableName]["select"];
                        $selectDistinct = $this->includeTables[$acDDTable->tableName]["select_distinct"];
                        $update = $this->includeTables[$acDDTable->tableName]["update"];
                    }
                } else if (!isset($this->excludeTables[$acDDTable->tableName])) {
                    $continueOperation = true;
                } else if (isset($this->excludeTables[$acDDTable->tableName])) {
                    $continueOperation = true;
                    $delete = !$this->excludeTables[$acDDTable->tableName]["delete"];
                    $insert = !$this->excludeTables[$acDDTable->tableName]["insert"];
                    $save = !$this->excludeTables[$acDDTable->tableName]["save"];
                    $select = !$this->excludeTables[$acDDTable->tableName]["select"];
                    $selectDistinct = !$this->excludeTables[$acDDTable->tableName]["select_distinct"];
                    $update = !$this->excludeTables[$acDDTable->tableName]["update"];
                }
                if ($continueOperation) {
                    $apiAdded = false;
                    $primaryKeyColumnName = $acDDTable->getPrimaryKeyColumnName();
                    if ($delete) {
                        $controler = new AcDataDictionaryAutoDelete(acDDTable: $acDDTable,acDataDictionaryAutoApi: $this);
                        $apiAdded = true;
                    }
                    if ($insert) {
                        $controler = new AcDataDictionaryAutoInsert(acDDTable: $acDDTable,acDataDictionaryAutoApi: $this);
                        $apiAdded = true;
                    }
                    if ($save) {
                        $controler = new AcDataDictionaryAutoSave(acDDTable: $acDDTable,acDataDictionaryAutoApi: $this);
                        $apiAdded = true;
                    }
                    if ($select) {
                        $controler = new AcDataDictionaryAutoSelect(acDDTable: $acDDTable,acDataDictionaryAutoApi: $this);
                        $apiAdded = true;
                    }
                    if ($selectDistinct) {
                        foreach ($acDDTable->getSelectDistinctColumns() as $distinctColumn) {
                            $controler = new AcDataDictionaryAutoSelectDistinct(acDDTable: $acDDTable,acDDTableColumn:$distinctColumn,acDataDictionaryAutoApi: $this);
                            $apiAdded = true;
                        }
                    }
                    if ($update) {
                        $controler = new AcDataDictionaryAutoUpdate(acDDTable: $acDDTable,acDataDictionaryAutoApi: $this);
                        $apiAdded = true;
                    }
                    if ($apiAdded) {
                        $tag = new AcApiDocTag();
                        $tag->name = $acDDTable->tableName;
                        $tag->description = "Database operations for table ".$acDDTable->tableName;
                        $this->acWeb->acApiDoc->addTag($tag);
                    }
                }
            }
        }
        return $this;
    }

}

?>
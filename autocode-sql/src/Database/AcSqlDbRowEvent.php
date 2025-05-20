<?php

namespace AcSql\Database;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../../../autocode-data-dictionary/vendor/autoload.php';

use Autocode\AcLogger;
use Autocode\Models\AcResult;
use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Models\AcDDTable;
use Exception;

class AcSqlDbRowEvent{
    public AcLogger $logger;
    public string $tableName = "";
    public string $dataDictionaryName = "default";
    public AcDDTable $acDDTable;
    public AcDataDictionary $acDataDictionary;
    public string $condition = "";
    public mixed $row;
    public mixed $result;
    public array $parameters = [];
    public bool $abortOperation = false;

    public function __construct(string $tableName,string $dataDictionaryName = "default") {
        $this->tableName = $tableName;
        $this->acDDTable = AcDataDictionary::getTable(tableName:$tableName,dataDictionaryName:$dataDictionaryName);
        $this->acDataDictionary = AcDataDictionary::getInstance();
    }

    public function execute():AcResult {
        $result = new AcResult();
        $result->setSuccess();
        return $result;
    }    
    
}

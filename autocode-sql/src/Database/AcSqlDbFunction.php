<?php

namespace AcSql\Database;

require_once '../../autocode/vendor/autoload.php';
require_once '../../autocode-data-dictionary/vendor/autoload.php';

use Autocode\AcLogger;
use AcDataDictionary\AcDataDictionary;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableField;
use AcDataDictionary\Models\AcDDTableFieldProperty;
use AcDataDictionary\Models\AcDDTableProperty;
use AcDataDictionary\Models\AcDDTableRowEvent;

class AcSqlDbFunction {
    public AcLogger $logger;
    public string $functionName = "";
    public string $dataDictionaryName = "default";

    public function __construct(string $functionName,string $dataDictionaryName = "default") {
        $this->logger = new AcLogger();
        $this->functionName = $functionName;
        $this->dataDictionaryName = $dataDictionaryName;
    }    
}

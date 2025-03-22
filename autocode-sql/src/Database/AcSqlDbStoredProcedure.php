<?php

namespace AcSql\Database;

require_once '../../autocode/vendor/autoload.php';

use Autocode\AcLogger;

class AcSqlDbStoredProcedure {
    public AcLogger $logger;
    public string $storedProcedureName = "";
    public string $dataDictionaryName = "default";

    public function __construct(string $storedProcedureName,string $dataDictionaryName = "default") {
        $this->logger = new AcLogger();
        $this->storedProcedureName = $storedProcedureName;
        $this->dataDictionaryName = $dataDictionaryName;
    }    
}

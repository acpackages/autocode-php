<?php

namespace AcSql\Database;

require_once '../../autocode/vendor/autoload.php';

use Autocode\AcLogger;

class AcSqlDbTrigger {
    public AcLogger $logger;
    public string $triggerName = "";
    public string $dataDictionaryName = "default";

    public function __construct(string $triggerName,string $dataDictionaryName = "default") {
        $this->logger = new AcLogger();
        $this->triggerName = $triggerName;
        $this->dataDictionaryName = $dataDictionaryName;
    } 
}

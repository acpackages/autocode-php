<?php

namespace AcSql\Database;

require_once '../../autocode/vendor/autoload.php';

use Autocode\AcLogger;

class AcSqlDbView {
    public AcLogger $logger;
    public string $viewName = "";
    public string $dataDictionaryName = "default";

    public function __construct(string $viewName,string $dataDictionaryName = "default") {
        $this->logger = new AcLogger();
        $this->viewName = $viewName;
        $this->dataDictionaryName = $dataDictionaryName;
    } 
}

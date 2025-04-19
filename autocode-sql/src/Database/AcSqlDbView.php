<?php

namespace AcSql\Database;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';

use AcDataDictionary\Models\AcDDView;
use AcDataDictionary\AcDataDictionary;
use Exception;

class AcSqlDbView extends AcSqlDbBase{
    public string $viewName = "";
    public AcDDView $acDDView;

    public function __construct(string $viewName, string $dataDictionaryName = "default"){
        parent::__construct(dataDictionaryName: "default");
        $this->viewName = $viewName;
        $this->acDDView = AcDataDictionary::getView(viewName: $viewName, dataDictionaryName: $dataDictionaryName);
    }

    public static function getDropViewStatement(string $viewName,string $databaseType = AcEnumSqlDatabaseType::UNKNOWN): string{
        $result = "DROP VIEW IF EXISTS $viewName;";
        return $result;
    }

    public function getCreateViewStatement(): string{
        $result = "CREATE VIEW $this->viewName AS {$this->acDDView->viewQuery};";
        return $result;
    }

    

}

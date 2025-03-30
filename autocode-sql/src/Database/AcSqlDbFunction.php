<?php

namespace AcSql\Database;

require_once '../../autocode/vendor/autoload.php';
require_once '../../autocode-data-dictionary/vendor/autoload.php';

use AcDataDictionary\Models\AcDDFunction;
use AcDataDictionary\AcDataDictionary;
use Exception;


class AcSqlDbFunction extends AcSqlDbBase{
    public AcDDFunction $acDDFunction;
    public string $functionName = "";

    public function __construct(string $functionName,string $dataDictionaryName = "default") {
        parent::__construct(dataDictionaryName: "default");
        $this->functionName = $functionName;
        $this->acDDFunction = AcDataDictionary::getFunction(functionName: $functionName, dataDictionaryName: $dataDictionaryName);
    }    

    public function getCreateFunctionStatement(): string{
        $result = $this->acDDFunction->functionCode;
        return $result;
    }

    public function getDropFunctionStatement(): string{
        $result = "DROP FUNCTION IF EXISTS $this->functionName;";
        return $result;
    }
}

<?php

namespace AcSql\Database;

require_once '../../autocode/vendor/autoload.php';
require_once '../../autocode-data-dictionary/vendor/autoload.php';

use AcDataDictionary\Models\AcDDFunction;
use AcDataDictionary\AcDataDictionary;
use AcSql\Enums\AcEnumSqlDatabaseType;
use Exception;


class AcSqlDbFunction extends AcSqlDbBase{
    public AcDDFunction $acDDFunction;
    public string $functionName = "";

    public function __construct(string $functionName,string $dataDictionaryName = "default") {
        parent::__construct(dataDictionaryName: "default");
        $this->functionName = $functionName;
        $this->acDDFunction = AcDataDictionary::getFunction(functionName: $functionName, dataDictionaryName: $dataDictionaryName);
    }    

    public static function getDropFunctionStatement(string $functionName,string $databaseType = AcEnumSqlDatabaseType::UNKNOWN): string{
        $result = "DROP FUNCTION IF EXISTS $functionName;";
        return $result;
    }

    public function getCreateFunctionStatement(): string{
        $result = $this->acDDFunction->functionCode;
        return $result;
    }

    
}

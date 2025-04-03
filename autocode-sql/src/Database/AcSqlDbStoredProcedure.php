<?php

namespace AcSql\Database;

require_once '../../autocode/vendor/autoload.php';

use AcDataDictionary\Models\AcDDStoredProcedure;
use AcDataDictionary\AcDataDictionary;
use Exception;


class AcSqlDbStoredProcedure  extends AcSqlDbBase {    
    public AcDDStoredProcedure $acDDStoredProcedure;
    public string $storedProcedureName = "";

    public function __construct(string $storedProcedureName,string $dataDictionaryName = "default") {
        parent::__construct(dataDictionaryName: "default");
        $this->storedProcedureName = $storedProcedureName;
        $this->acDDStoredProcedure = AcDataDictionary::getStoredProcedure(storedProcedureName: $storedProcedureName, dataDictionaryName: $dataDictionaryName);
    } 

    public static function getDropStoredProcedureStatement(string $storedProcedureName,string $databaseType = AcEnumSqlDatabaseType::UNKNOWN): string{
        $result = "DROP PROCEDURE IF EXISTS $storedProcedureName;";
        return $result;
    }
    
    public function getCreateStoredProcedureStatement(): string{
        $result = $this->acDDStoredProcedure->storedProcedureCode;
        return $result;
    }

    
}

<?php

namespace AcSql\Database;

require_once '../../autocode/vendor/autoload.php';

use AcDataDictionary\Models\AcDDTrigger;
use AcDataDictionary\AcDataDictionary;
use AcSql\Enums\AcEnumSqlDatabaseType;
use Exception;


class AcSqlDbTrigger extends AcSqlDbBase {
    public AcDDTrigger $acDDTrigger;
    public string $triggerName = "";

    public function __construct(string $triggerName,string $dataDictionaryName = "default") {
        parent::__construct(dataDictionaryName: "default");
        $this->triggerName = $triggerName;
        $this->acDDTrigger = AcDataDictionary::getTrigger(triggerName: $triggerName, dataDictionaryName: $dataDictionaryName);
    } 

    public static function getDropTriggerStatement(string $triggerName,string $databaseType = AcEnumSqlDatabaseType::UNKNOWN): string{
        $result = "DROP TRIGGER IF EXISTS $triggerName;";
        return $result;
    }

    public function getCreateTriggerStatement(): string{
        $result = "";
        if ($this->databaseType == AcEnumSqlDatabaseType::MYSQL) {
            $result = "CREATE TRIGGER ".$this->triggerName." ".$this->acDDTrigger->triggerExecution." ".$this->acDDTrigger->rowOperation." ON ".$this->acDDTrigger->tableName." FOR EACH ROW BEGIN ".$this->acDDTrigger->triggerCode." END;";
        }
        else if($this->databaseType == AcEnumSqlDatabaseType::SQLITE){
            $result = "CREATE TRIGGER ".$this->triggerName." ".$this->acDDTrigger->triggerExecution." ".$this->acDDTrigger->rowOperation." ON ".$this->acDDTrigger->tableName." FOR EACH ROW BEGIN ".$this->acDDTrigger->triggerCode." END;";
        }
        return $result;
    }

    
}

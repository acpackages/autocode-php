<?php

namespace AcSql\Database;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../../../autocode-data-dictionary/vendor/autoload.php';

use AcDataDictionary\Models\AcDDFunction;
use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Models\AcDDRelationship;
use Exception;


class AcSqlDbRelationship extends AcSqlDbBase{
    public AcDDRelationship $acDDRelationship;
    public function __construct(AcDDRelationship $acDDRelationship,string $dataDictionaryName = "default") {
        parent::__construct(dataDictionaryName: "default");
        $this->acDDRelationship = $acDDRelationship;
    }    

    public function getCreateReleationshipStatement(): string{
        $result = "ALTER TABLE $this->acDDRelationship->destinationTable ADD FOREIGN KEY ($this->acDDRelationship->destinationField) REFERENCES $this->acDDRelationship->sourceTable($this->acDDRelationship->sourceField);";
        return $result;
    }
}

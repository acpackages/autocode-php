<?php
namespace AcWeb\DataDictionary;
use AcExtensions\AcExtensionMethods;
use AcWeb\Core\AcWebPath;
class AcDataDictionaryAutoInsertApiPath extends AcWebPath {
    public $tableName = "";
    public function handleRequest(){
        $this->responseHtml("Executing insert api for ".$this->tableName);
    }
}

?>
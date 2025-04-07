<?php
namespace AcWeb\DataDictionary;
use AcExtensions\AcExtensionMethods;
use AcWeb\Core\AcWebPath;
class AcDataDictionaryAutoSelectApiPath extends AcWebPath {
    public $tableName = "";
    public function handleRequest(){
        $this->responseHtml("Executing select api for ".$this->tableName);
    }
}

?>
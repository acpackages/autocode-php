<?php
namespace AcWeb\DataDictionary;
use AcExtensions\AcExtensionMethods;
use AcWeb\Core\AcWebPath;
class AcDataDictionaryAutoSaveApiPath extends AcWebPath {
    public $tableName = "";
    public function handleRequest(){
        $this->responseHtml("Executing save api for ".$this->tableName);
    }
}

?>
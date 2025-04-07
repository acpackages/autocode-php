<?php
namespace AcWeb\DataDictionary;
use AcExtensions\AcExtensionMethods;
use AcWeb\Core\AcWebPath;
class AcDataDictionaryAutoUpdateApiPath extends AcWebPath{
    public $tableName = "";
    public function handleRequest(){
        $this->responseHtml("Executing update api for ".$this->tableName);
    }
}

?>
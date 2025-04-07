<?php
namespace AcWeb\DataDictionary;
use AcExtensions\AcExtensionMethods;
use AcWeb\Core\AcWebPath;
class AcDataDictionaryAutoDeleteApiPath extends AcWebPath {
    public $tableName = "";
    
    public function handleRequest(){
        $this->responseHtml("Executing delete api for ".$this->tableName);
    }
}

?>
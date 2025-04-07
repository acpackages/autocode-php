<?php
namespace AcWeb\DataDictionary;
use AcExtensions\AcExtensionMethods;
use AcWeb\Core\AcWebPath;
class AcDataDictionaryAutoSelectDistinctApiPath extends AcWebPath {
    public $fieldName = "";
    public $tableName = "";
    public function handleRequest(){
        $this->responseHtml("Executing select distinct api for ".$this->tableName." > ".$this->fieldName);
    }

}

?>
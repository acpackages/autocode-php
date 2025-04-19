<?php
namespace AcWeb\DataDictionary;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableField;
use AcSql\Database\AcSqlDbTable;
use AcWeb\ApiDocs\Models\AcApiDocContent;
use AcWeb\ApiDocs\Models\AcApiDocRequestBody;
use AcWeb\ApiDocs\Models\AcApiDocRoute;
use AcWeb\Models\AcWebRequest;
use AcWeb\Models\AcWebResponse;
use ApiDocs\Utils\AcApiDocUtils;
class AcDataDictionaryAutoSelectDistinct{
    
    public function __construct(public AcDDTable $acDDTable,public AcDDTableField $acDDTableField,public AcDataDictionaryAutoApi &$acDataDictionaryAutoApi)
    {
        $apiUrl = $acDataDictionaryAutoApi->urlPrefix . '/' . $acDDTable->tableName . "/" . $acDataDictionaryAutoApi->pathForSelectDistinct . "-" . $acDDTableField->fieldName;
        $acDataDictionaryAutoApi->acWeb->get(url: $apiUrl, handler: $this->getHandler(), acApiDocRoute: $this->getAcApiDocRoute());
    }

    private function getAcApiDocRoute(): AcApiDocRoute{
        $acApiDocRoute = new AcApiDocRoute();
        $acApiDocRoute->addTag($this->acDDTable->tableName);
        $acApiDocRoute->summary = "Get " . $this->acDDTable->tableName."'s ".$this->acDDTableField->fieldName;
        $acApiDocRoute->description = "Auto generated data dictionary api to get distinct values from column " . $this->acDDTableField->fieldName . " in table " . $this->acDDTable->tableName;
        return $acApiDocRoute;
    }

    private function getHandler(): callable{
        $handler = function (AcWebRequest $acWebRequest): AcWebResponse {
            $acSqlDbTable = new AcSqlDbTable(tableName: $this->acDDTable->tableName);
            $getResponse = $acSqlDbTable->getDistinctFieldValues(fieldName:$this->acDDTableField->fieldName);
            return AcWebResponse::json($getResponse->toJson());
        };
        return $handler;
    }
}

?>
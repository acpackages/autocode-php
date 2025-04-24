<?php
namespace AcWeb\DataDictionary;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableField;
use AcSql\Database\AcSqlDbTable;
use AcWeb\ApiDocs\Models\AcApiDocContent;
use AcWeb\ApiDocs\Models\AcApiDocParameter;
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
        $queryParameter = new AcApiDocParameter();
        $queryParameter->name = "query";
        $queryParameter->description = "Filter values using like condition for field ".$this->acDDTable->getPrimaryKeyFieldName();
        $queryParameter->required = false;
        $queryParameter->in = "query";
        $acApiDocRoute->addParameter(parameter: $queryParameter);
        $pageParameter = new AcApiDocParameter();
        $pageParameter->name = "page_number";
        $pageParameter->description = "Page number of rows";
        $pageParameter->required = false;
        $pageParameter->in = "query";
        $acApiDocRoute->addParameter(parameter: $pageParameter);
        $countParameter = new AcApiDocParameter();
        $countParameter->name = "rows_count";
        $countParameter->description = "Number of rows in each page";
        $countParameter->required = false;
        $countParameter->in = "query";
        $acApiDocRoute->addParameter(parameter: $countParameter);
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
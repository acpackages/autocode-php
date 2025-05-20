<?php
namespace AcWeb\DataDictionary;
use AcDataDictionary\Enums\AcEnumDDRowOperation;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableColumn;
use AcSql\Database\AcSqlDbTable;
use AcWeb\ApiDocs\Models\AcApiDocContent;
use AcWeb\ApiDocs\Models\AcApiDocParameter;
use AcWeb\ApiDocs\Models\AcApiDocRequestBody;
use AcWeb\ApiDocs\Models\AcApiDocRoute;
use AcWeb\Models\AcWebRequest;
use AcWeb\Models\AcWebResponse;
use ApiDocs\Utils\AcApiDocUtils;
class AcDataDictionaryAutoSelectDistinct{
    
    public function __construct(public AcDDTable $acDDTable,public AcDDTableColumn $acDDTableColumn,public AcDataDictionaryAutoApi &$acDataDictionaryAutoApi)
    {
        $apiUrl = $acDataDictionaryAutoApi->urlPrefix . '/' . $acDDTable->tableName . "/" . $acDataDictionaryAutoApi->pathForSelectDistinct . "-" . $acDDTableColumn->columnName;
        $acDataDictionaryAutoApi->acWeb->get(url: $apiUrl, handler: $this->getHandler(), acApiDocRoute: $this->getAcApiDocRoute());
    }

    private function getAcApiDocRoute(): AcApiDocRoute{
        $acApiDocRoute = new AcApiDocRoute();
        $acApiDocRoute->addTag($this->acDDTable->tableName);
        $acApiDocRoute->summary = "Get " . $this->acDDTable->tableName."'s ".$this->acDDTableColumn->columnName;
        $acApiDocRoute->description = "Auto generated data dictionary api to get distinct values from column " . $this->acDDTableColumn->columnName . " in table " . $this->acDDTable->tableName;
        $queryParameter = new AcApiDocParameter();
        $queryParameter->name = "query";
        $queryParameter->description = "Filter values using like condition for column ".$this->acDDTable->getPrimaryKeyColumnName();
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
        $responses = AcApiDocUtils::getApiDocRouteResponsesForOperation(operation:AcEnumDDRowOperation::SELECT, acDDTable:$this->acDDTable,acApiDoc:$this->acDataDictionaryAutoApi->acWeb->acApiDoc);
        foreach ($responses as $response) {
            $acApiDocRoute->addResponse(acApiDocResponse: $response);
        }  
        return $acApiDocRoute;
    }

    private function getHandler(): callable{
        $handler = function (AcWebRequest $acWebRequest): AcWebResponse {
            $acSqlDbTable = new AcSqlDbTable(tableName: $this->acDDTable->tableName);
            $getResponse = $acSqlDbTable->getDistinctColumnValues(columnName:$this->acDDTableColumn->columnName);
            return AcWebResponse::json($getResponse->toJson());
        };
        return $handler;
    }
}

?>
<?php
namespace AcWeb\DataDictionary;
use AcDataDictionary\Enums\AcEnumDDRowOperation;
use AcDataDictionary\Models\AcDDTable;
use AcSql\Database\AcSqlDbTable;
use AcWeb\ApiDocs\Models\AcApiDocContent;
use AcWeb\ApiDocs\Models\AcApiDocParameter;
use AcWeb\ApiDocs\Models\AcApiDocRequestBody;
use AcWeb\ApiDocs\Models\AcApiDocResponse;
use AcWeb\ApiDocs\Models\AcApiDocRoute;
use AcWeb\Models\AcWebRequest;
use AcWeb\Models\AcWebResponse;
use ApiDocs\Enums\AcEnumApiDataType;
use ApiDocs\Utils\AcApiDocUtils;
use Autocode\Enums\AcEnumHttpResponseCode;
use Autocode\Models\AcResult;
class AcDataDictionaryAutoDelete{
    
    public function __construct(public AcDDTable $acDDTable,public AcDataDictionaryAutoApi &$acDataDictionaryAutoApi){
        $apiUrl = $acDataDictionaryAutoApi->urlPrefix . '/' . $acDDTable->tableName . "/" . $acDataDictionaryAutoApi->pathForDelete."/{".$this->acDDTable->getPrimaryKeyColumnName()."}";        
        $acDataDictionaryAutoApi->acWeb->delete(url: $apiUrl, handler: $this->getHandler(), acApiDocRoute: $this->getAcApiDocRoute());
    }

    private function getAcApiDocRoute(): AcApiDocRoute{
        $acApiDocRoute = new AcApiDocRoute();
        $acApiDocRoute->addTag($this->acDDTable->tableName);
        $acApiDocRoute->summary = "Delete " . $this->acDDTable->tableName;
        $acApiDocRoute->description = "Auto generated data dictionary api to delete row in table " . $this->acDDTable->tableName;
        $parameter = new AcApiDocParameter();
        $parameter->name = $this->acDDTable->getPrimaryKeyColumnName();
        $parameter->description = $this->acDDTable->getPrimaryKeyColumnName() . " value of row to delete";
        $parameter->required = true;
        $parameter->in = "path";
        $acApiDocRoute->addParameter($parameter);
        $acApiDocResponse = new AcApiDocResponse();
        $acApiDocResponse->code = AcEnumHttpResponseCode::OK;
        $acApiDocResponse->description = "Successfull operation";
        $responses = AcApiDocUtils::getApiDocRouteResponsesForOperation(operation:AcEnumDDRowOperation::DELETE, acDDTable:$this->acDDTable,acApiDoc:$this->acDataDictionaryAutoApi->acWeb->acApiDoc);
        foreach ($responses as $response) {
            $acApiDocRoute->addResponse(acApiDocResponse: $response);
        }        
        return $acApiDocRoute;
    }

    private function getHandler(): callable{
        $handler = function (AcWebRequest $acWebRequest): AcWebResponse {
            $response = new AcResult();
            if(isset($acWebRequest->pathParameters[$this->acDDTable->getPrimaryKeyColumnName()])){
                $acSqlDbTable = new AcSqlDbTable(tableName: $this->acDDTable->tableName);
                $response = $acSqlDbTable->deleteRows(primaryKeyValue:$acWebRequest->pathParameters[$this->acDDTable->getPrimaryKeyColumnName()]);
            }
            else{
                $response->message = "parameters missing";
            }
            return AcWebResponse::json(data: $response);
        };
        return $handler;
    }
}

?>
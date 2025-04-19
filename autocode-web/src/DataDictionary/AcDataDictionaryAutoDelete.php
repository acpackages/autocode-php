<?php
namespace AcWeb\DataDictionary;
use AcDataDictionary\Models\AcDDTable;
use AcSql\Database\AcSqlDbTable;
use AcWeb\ApiDocs\Models\AcApiDocContent;
use AcWeb\ApiDocs\Models\AcApiDocParameter;
use AcWeb\ApiDocs\Models\AcApiDocRequestBody;
use AcWeb\ApiDocs\Models\AcApiDocRoute;
use AcWeb\Models\AcWebRequest;
use AcWeb\Models\AcWebResponse;
use ApiDocs\Utils\AcApiDocUtils;
use Autocode\Models\AcResult;
class AcDataDictionaryAutoDelete{
    
    public function __construct(public AcDDTable $acDDTable,public AcDataDictionaryAutoApi &$acDataDictionaryAutoApi){
        $apiUrl = $acDataDictionaryAutoApi->urlPrefix . '/' . $acDDTable->tableName . "/" . $acDataDictionaryAutoApi->pathForDelete."/{".$this->acDDTable->getPrimaryKeyFieldName()."}";        
        $acDataDictionaryAutoApi->acWeb->delete(url: $apiUrl, handler: $this->getHandler(), acApiDocRoute: $this->getAcApiDocRoute());
    }

    private function getAcApiDocRoute(): AcApiDocRoute{
        $acApiDocRoute = new AcApiDocRoute();
        $acApiDocRoute->addTag($this->acDDTable->tableName);
        $acApiDocRoute->summary = "Delete " . $this->acDDTable->tableName;
        $acApiDocRoute->description = "Auto generated data dictionary api to delete row in table " . $this->acDDTable->tableName;
        $parameter = new AcApiDocParameter();
        $parameter->name = $this->acDDTable->getPrimaryKeyFieldName();
        $parameter->description = $this->acDDTable->getPrimaryKeyFieldName() . " value of row to delete";
        $parameter->required = true;
        $parameter->in = "path";
        $acApiDocRoute->addParameter($parameter);
        return $acApiDocRoute;
    }

    private function getHandler(): callable{
        $handler = function (AcWebRequest $acWebRequest): AcWebResponse {
            $response = new AcResult();
            if(isset($acWebRequest->pathParameters[$this->acDDTable->getPrimaryKeyFieldName()])){
                $acSqlDbTable = new AcSqlDbTable(tableName: $this->acDDTable->tableName);
                $response = $acSqlDbTable->deleteRows(primaryKeyValue:$acWebRequest->pathParameters[$this->acDDTable->getPrimaryKeyFieldName()]);
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
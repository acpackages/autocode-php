<?php
namespace AcWeb\DataDictionary;
use AcDataDictionary\Models\AcDDTable;
use AcSql\Database\AcSqlDbTable;
use AcWeb\ApiDocs\Models\AcApiDocContent;
use AcWeb\ApiDocs\Models\AcApiDocRequestBody;
use AcWeb\ApiDocs\Models\AcApiDocRoute;
use AcWeb\Models\AcWebRequest;
use AcWeb\Models\AcWebResponse;
use ApiDocs\Enums\AcEnumApiDataType;
use ApiDocs\Utils\AcApiDocUtils;
use Autocode\Models\AcResult;
class AcDataDictionaryAutoSave{
    
    public function __construct(public AcDDTable $acDDTable,public AcDataDictionaryAutoApi &$acDataDictionaryAutoApi)
    {
        $apiUrl = $acDataDictionaryAutoApi->urlPrefix . '/' . $acDDTable->tableName . "/" . $acDataDictionaryAutoApi->pathForSave;        
        $acDataDictionaryAutoApi->acWeb->post(url: $apiUrl, handler: $this->getHandler(), acApiDocRoute: $this->getAcApiDocRoute());
    }

    private function getAcApiDocRoute(): AcApiDocRoute{
        $acApiDocRoute = new AcApiDocRoute();
        $acApiDocRoute->addTag($this->acDDTable->tableName);
        $acApiDocRoute->summary = "Save " . $this->acDDTable->tableName;
        $acApiDocRoute->description = "Auto generated data dictionary api to save row in table " . $this->acDDTable->tableName.". Either single row or multiple rows can be saved at a time.";
        $schema = AcApiDocUtils::getApiModelRefFromAcDDTable(acDDTable: $this->acDDTable, acApiDoc: $this->acDataDictionaryAutoApi->acWeb->acApiDoc);
        $content = new AcApiDocContent();
        $content->encoding = "application/json";
        $contentSchema = [
            "type" => AcEnumApiDataType::OBJECT,
            "properties" => [
                "row" => $schema,
                "rows" => [
                    "type" => AcEnumApiDataType::ARRAY,
                    "items" => $schema
                ]
            ]
        ];
        $content->schema = $contentSchema;
        $requestBody = new AcApiDocRequestBody();
        $requestBody->addContent(content: $content);
        $acApiDocRoute->requestBody = $requestBody;
        return $acApiDocRoute;
    }

    private function getHandler(): callable{
        $handler = function (AcWebRequest $acWebRequest): AcWebResponse {
            $acSqlDbTable = new AcSqlDbTable(tableName: $this->acDDTable->tableName);
            $response = new AcResult();
            if(isset($acWebRequest->body['row'])){
                $response = $acSqlDbTable->saveRow(row: $acWebRequest->body['row']);
            }
            else if(isset($acWebRequest->body['rows'])){
                $response = $acSqlDbTable->saveRows(rows: $acWebRequest->body['rows']);
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
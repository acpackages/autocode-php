<?php
namespace AcWeb\DataDictionary;
use AcDataDictionary\Enums\AcEnumDDConditionOperator;
use AcDataDictionary\Enums\AcEnumDDLogicalOperator;
use AcDataDictionary\Enums\AcEnumDDRowOperation;
use AcDataDictionary\Models\AcDDSelectStatement;
use AcDataDictionary\Models\AcDDTable;
use AcExtensions\AcExtensionMethods;
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
class AcDataDictionaryAutoSelect {

    public function __construct(public AcDDTable $acDDTable, public AcDataDictionaryAutoApi &$acDataDictionaryAutoApi)
    {
        $apiUrl = $acDataDictionaryAutoApi->urlPrefix . '/' . $acDDTable->tableName . "/" . $acDataDictionaryAutoApi->pathForSelect;
        $acDataDictionaryAutoApi->acWeb->get(url: $apiUrl, handler: $this->getHandler(), acApiDocRoute: $this->getAcApiDocRoute());
        $apiUrl = $acDataDictionaryAutoApi->urlPrefix . '/' . $acDDTable->tableName . "/" . $acDataDictionaryAutoApi->pathForSelect . "/{" . $this->acDDTable->getPrimaryKeyColumnName() . "}";
        $acDataDictionaryAutoApi->acWeb->get(url: $apiUrl, handler: $this->getByIdHandler(), acApiDocRoute: $this->getByIdAcApiDocRoute());
        $apiUrl = $acDataDictionaryAutoApi->urlPrefix . '/' . $acDDTable->tableName . "/" . $acDataDictionaryAutoApi->pathForSelect;
        $acDataDictionaryAutoApi->acWeb->post(url: $apiUrl, handler: $this->postHandler(), acApiDocRoute: $this->postAcApiDocRoute());
    }

    private function getAcApiDocRoute(): AcApiDocRoute
    {
        $acApiDocRoute = new AcApiDocRoute();
        $acApiDocRoute->addTag($this->acDDTable->tableName);
        $acApiDocRoute->summary = "Get " . $this->acDDTable->tableName;
        $acApiDocRoute->description = "Auto generated data dictionary api to get rows in table " . $this->acDDTable->tableName;
        $queryColumns = $this->acDDTable->getSearchQueryColumnNames();
        if (count($queryColumns) > 0) {
            $queryParameter = new AcApiDocParameter();
            $queryParameter->name = "query";
            $queryParameter->description = "Filter values using like condition for columns (" . implode(separator: ",", array: $queryColumns) . ")";
            $queryParameter->required = false;
            $queryParameter->in = "query";
            $acApiDocRoute->addParameter(parameter: $queryParameter);
        }
        $pageParameter = new AcApiDocParameter();
        $pageParameter->name = "page_number";
        $pageParameter->description = "Page number of rows";
        $pageParameter->required = false;
        $pageParameter->in = "query";
        $acApiDocRoute->addParameter(parameter: $pageParameter);
        $countParameter = new AcApiDocParameter();
        $countParameter->name = "page_size";
        $countParameter->description = "Number of rows in each page";
        $countParameter->required = false;
        $countParameter->in = "query";
        $acApiDocRoute->addParameter(parameter: $countParameter);
        $orderParameter = new AcApiDocParameter();
        $orderParameter->name = "order_by";
        $orderParameter->description = "Order by value for rows";
        $orderParameter->required = false;
        $orderParameter->in = "query";
        $acApiDocRoute->addParameter(parameter: $orderParameter);
        foreach ($this->acDDTable->tableColumns as $acDDTableColumn) {
            $requestParameter = new AcApiDocParameter();
            $requestParameter->name = $acDDTableColumn->columnName;
            $requestParameter->description = "Filter values in column " . $acDDTableColumn->columnName;
            $requestParameter->required = false;
            $requestParameter->in = "query";
            $acApiDocRoute->addParameter(parameter: $requestParameter);
        }
        $responses = AcApiDocUtils::getApiDocRouteResponsesForOperation(operation:AcEnumDDRowOperation::SELECT, acDDTable:$this->acDDTable,acApiDoc:$this->acDataDictionaryAutoApi->acWeb->acApiDoc);
        foreach ($responses as $response) {
            $acApiDocRoute->addResponse(acApiDocResponse: $response);
        }  
        return $acApiDocRoute;
    }

    private function getHandler(): callable {
        $handler = function (AcWebRequest $acWebRequest): AcWebResponse {
            $acSqlDbTable = new AcSqlDbTable(tableName: $this->acDDTable->tableName);
            $acDDSelectStatement = new AcDDSelectStatement(tableName: $this->acDDTable->tableName);
            if(AcExtensionMethods::arrayContainsKey(key: "query",array: $acWebRequest->get)){
                $queryColumns = $this->acDDTable->getSearchQueryColumnNames();
                $acDDSelectStatement->startGroup(operator:AcEnumDDLogicalOperator::OR);
                foreach($queryColumns as $columnName){
                    $acDDSelectStatement->addCondition(columnName:$columnName,operator:AcEnumDDConditionOperator::LIKE,value: $acWebRequest->get["query"]);
                }
                $acDDSelectStatement->endGroup();
            }
            foreach ($this->acDDTable->tableColumns as $acDDTableColumn){
                if(AcExtensionMethods::arrayContainsKey(key: $acDDTableColumn->columnName,array: $acWebRequest->get)){
                    $acDDSelectStatement->addCondition(columnName:$acDDTableColumn->columnName,operator:AcEnumDDConditionOperator::LIKE,value: $acWebRequest->get[$acDDTableColumn->columnName]);
                }
            }
            if(AcExtensionMethods::arrayContainsKey(key: "page_number",array: $acWebRequest->get)){
                $acDDSelectStatement->pageNumber = intval($acWebRequest->get["page_number"]);
            }
            else{
                $acDDSelectStatement->pageNumber = 1;
            }
            if(AcExtensionMethods::arrayContainsKey(key: "page_size",array: $acWebRequest->get)){
                $acDDSelectStatement->pageSize = intval($acWebRequest->get["page_size"]);
            }
            else{
                $acDDSelectStatement->pageSize = 50;
            }
            if(AcExtensionMethods::arrayContainsKey(key: "order_by",array: $acWebRequest->get)){
                $acDDSelectStatement->orderBy = $acWebRequest->get["order_by"];
            }            
            $sqlStatement = $acDDSelectStatement->getSqlStatement();
            $sqlParameters = $acDDSelectStatement->parameters;
            $getResponse = $acSqlDbTable->getRows(
                selectStatement: $sqlStatement, 
                parameters: $sqlParameters,
            );
            return AcWebResponse::json($getResponse->toJson());
        };
        return $handler;
    }

    private function getByIdAcApiDocRoute(): AcApiDocRoute{
        $acApiDocRoute = new AcApiDocRoute();
        $acApiDocRoute->addTag($this->acDDTable->tableName);
        $acApiDocRoute->summary = "Get single " . $this->acDDTable->tableName;
        $acApiDocRoute->description = "Auto generated data dictionary api to get single row matching column value " . $this->acDDTable->getPrimaryKeyColumnName() . " in table " . $this->acDDTable->tableName;
        $parameter = new AcApiDocParameter();
        $parameter->name = $this->acDDTable->getPrimaryKeyColumnName();
        $parameter->description = $this->acDDTable->getPrimaryKeyColumnName() . " value of row to get";
        $parameter->required = true;
        $parameter->in = "path";
        $acApiDocRoute->addParameter($parameter);
        $responses = AcApiDocUtils::getApiDocRouteResponsesForOperation(operation:AcEnumDDRowOperation::SELECT, acDDTable:$this->acDDTable,acApiDoc:$this->acDataDictionaryAutoApi->acWeb->acApiDoc);
        foreach ($responses as $response) {
            $acApiDocRoute->addResponse(acApiDocResponse: $response);
        }  
        return $acApiDocRoute;
    }

    private function getByIdHandler(): callable{
        $handler = function (AcWebRequest $acWebRequest): AcWebResponse {
            $acSqlDbTable = new AcSqlDbTable(tableName: $this->acDDTable->tableName);
            $acDDSelectStatement = new AcDDSelectStatement(tableName: $this->acDDTable->tableName);
            $primaryKeyValue = $acWebRequest->pathParameters[$acWebRequest->pathParameters[$this->acDDTable->getPrimaryKeyColumnName()]];
            $getResponse = $acSqlDbTable->getRows(condition:$this->acDDTable->getPrimaryKeyColumnName()." = @primaryKeyValue", parameters: [
                "@primaryKeyValue" => $primaryKeyValue 
            ]);
            return AcWebResponse::json($getResponse->toJson());
        };
        return $handler;
    }

    private function postAcApiDocRoute(): AcApiDocRoute {
        $acApiDocRoute = new AcApiDocRoute();
        $acApiDocRoute->addTag($this->acDDTable->tableName);
        $acApiDocRoute->summary = "Get " . $this->acDDTable->tableName;
        $acApiDocRoute->description = "Auto generated data dictionary api to get rows in table " . $this->acDDTable->tableName;
        $queryColumns = $this->acDDTable->getSearchQueryColumnNames();
        $properties = [
            "query" => [
                "type" => AcEnumApiDataType::STRING
            ],
            "page_number" => [
                "type" => AcEnumApiDataType::INTEGER
            ],
            "page_size" => [
                "type" => AcEnumApiDataType::INTEGER
            ],
            "order_by" => [
                "type" => AcEnumApiDataType::STRING
            ],
            "filters" => [
                "type" => AcEnumApiDataType::OBJECT
            ],
            "include_columns" => [
                "type" => AcEnumApiDataType::ARRAY,
                "items" => [
                    "type" => AcEnumApiDataType::STRING
                ]
            ],
            "exclude_columns" => [
                "type" => AcEnumApiDataType::ARRAY,
                "items" => [
                    "type" => AcEnumApiDataType::STRING
                ]
            ]
        ];
        if (count($queryColumns) == 0) {
            unset($properties["query"]);
        }
        $content = new AcApiDocContent();
        $content->encoding = "application/json";
        $contentSchema = [
            "type" => AcEnumApiDataType::OBJECT,
            "properties" => $properties
        ];
        $content->schema = $contentSchema;
        $requestBody = new AcApiDocRequestBody();
        $requestBody->addContent(content: $content);
        $acApiDocRoute->requestBody = $requestBody;
        $responses = AcApiDocUtils::getApiDocRouteResponsesForOperation(operation:AcEnumDDRowOperation::SELECT, acDDTable:$this->acDDTable,acApiDoc:$this->acDataDictionaryAutoApi->acWeb->acApiDoc);
        foreach ($responses as $response) {
            $acApiDocRoute->addResponse(acApiDocResponse: $response);
        }  
        return $acApiDocRoute;
    }

    private function postHandler(): callable
    {
        $handler = function (AcWebRequest $acWebRequest): AcWebResponse {            
            $acSqlDbTable = new AcSqlDbTable(tableName: $this->acDDTable->tableName);
            $acDDSelectStatement = new AcDDSelectStatement(tableName: $this->acDDTable->tableName);
            if(AcExtensionMethods::arrayContainsKey(key: "include_columns",array: $acWebRequest->body)){
                $acDDSelectStatement->includeColumns = $acWebRequest->body["include_columns"];
            }
            if(AcExtensionMethods::arrayContainsKey(key: "exclude_columns",array: $acWebRequest->body)){
                $acDDSelectStatement->excludeColumns = $acWebRequest->body["exclude_columns"];
            }
            if(AcExtensionMethods::arrayContainsKey(key: "query",array: $acWebRequest->body)){
                $queryColumns = $this->acDDTable->getSearchQueryColumnNames();
                $acDDSelectStatement->startGroup(operator:AcEnumDDLogicalOperator::OR);
                foreach($queryColumns as $columnName){
                    $acDDSelectStatement->addCondition(columnName:$columnName,operator:AcEnumDDConditionOperator::LIKE,value: $acWebRequest->body["query"]);
                }
                $acDDSelectStatement->endGroup();
            }
            if(AcExtensionMethods::arrayContainsKey(key: "filters",array: $acWebRequest->body)){
                $filters = $acWebRequest->body['filters'];
                $acDDSelectStatement->setConditionsFromFilters($filters);
            }
            $pageNumber = 1;
            if(AcExtensionMethods::arrayContainsKey(key: "page_number",array: $acWebRequest->body)){
                $pageNumber = intval($acWebRequest->body["page_number"]);
                if($pageNumber<=0){
                    $pageNumber = 1;
                }                
            }
            $acDDSelectStatement->pageNumber = $pageNumber;
            $pageSize = 50;
            if(AcExtensionMethods::arrayContainsKey(key: "page_size",array: $acWebRequest->body)){
                $pageSize = intval($acWebRequest->body["page_size"]);
                if($pageSize<=0){
                    $pageSize = 50;
                } 
            }
            $acDDSelectStatement->pageSize = $pageSize;
            if(AcExtensionMethods::arrayContainsKey(key: "order_by",array: $acWebRequest->body)){
                $acDDSelectStatement->orderBy = $acWebRequest->body["order_by"];
            }            
            $sqlStatement = $acDDSelectStatement->getSqlStatement();
            $sqlParameters = $acDDSelectStatement->parameters;
            $getResponse = $acSqlDbTable->getRows(
                selectStatement: $sqlStatement, 
                parameters: $sqlParameters,
            );
            $data = $getResponse->toJson();
            $data["sql_statement"] = $acDDSelectStatement->toJson();
            return AcWebResponse::json($data);
        };
        return $handler;
    }
}

?>
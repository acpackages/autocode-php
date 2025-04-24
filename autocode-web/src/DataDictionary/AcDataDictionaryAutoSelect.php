<?php
namespace AcWeb\DataDictionary;
use AcDataDictionary\Models\AcDDSelectStatement;
use AcDataDictionary\Models\AcDDTable;
use AcSql\Database\AcSqlDbTable;
use AcWeb\ApiDocs\Models\AcApiDocContent;
use AcWeb\ApiDocs\Models\AcApiDocParameter;
use AcWeb\ApiDocs\Models\AcApiDocRequestBody;
use AcWeb\ApiDocs\Models\AcApiDocRoute;
use AcWeb\Models\AcWebRequest;
use AcWeb\Models\AcWebResponse;
use ApiDocs\Enums\AcEnumApiDataType;
use ApiDocs\Utils\AcApiDocUtils;
class AcDataDictionaryAutoSelect
{

    public function __construct(public AcDDTable $acDDTable, public AcDataDictionaryAutoApi &$acDataDictionaryAutoApi)
    {
        $apiUrl = $acDataDictionaryAutoApi->urlPrefix . '/' . $acDDTable->tableName . "/" . $acDataDictionaryAutoApi->pathForSelect;
        $acDataDictionaryAutoApi->acWeb->get(url: $apiUrl, handler: $this->getHandler(), acApiDocRoute: $this->getAcApiDocRoute());
        $apiUrl = $acDataDictionaryAutoApi->urlPrefix . '/' . $acDDTable->tableName . "/" . $acDataDictionaryAutoApi->pathForSelect . "/{" . $this->acDDTable->getPrimaryKeyFieldName() . "}";
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
        $queryFields = $this->acDDTable->getSearchQueryFieldNames();
        if (count($queryFields) > 0) {
            $queryParameter = new AcApiDocParameter();
            $queryParameter->name = "query";
            $queryParameter->description = "Filter values using like condition for fields (" . implode(separator: ",", array: $queryFields) . ")";
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
        $countParameter->name = "rows_count";
        $countParameter->description = "Number of rows in each page";
        $countParameter->required = false;
        $countParameter->in = "query";
        $acApiDocRoute->addParameter(parameter: $countParameter);
        foreach ($this->acDDTable->tableFields as $acDDTableField) {
            $requestParameter = new AcApiDocParameter();
            $requestParameter->name = $acDDTableField->fieldName;
            $requestParameter->description = "Filter values in field " . $acDDTableField->fieldName;
            $requestParameter->required = false;
            $requestParameter->in = "query";
            $acApiDocRoute->addParameter(parameter: $requestParameter);
        }
        return $acApiDocRoute;
    }

    private function getHandler(): callable
    {
        $handler = function (AcWebRequest $acWebRequest): AcWebResponse {
            $acSqlDbTable = new AcSqlDbTable(tableName: $this->acDDTable->tableName);
            $acDDSelectStatement = new AcDDSelectStatement(tableName: $this->acDDTable->tableName);
            $acDDSelectStatement->includeFields = ["api_name"];
            $sqlStatement = $acDDSelectStatement->getSqlStatement();
            $sqlParameters = $acDDSelectStatement->sqlParameters;
            $getResponse = $acSqlDbTable->getRows(selectStatement: $sqlStatement, parameters: $sqlParameters);
            return AcWebResponse::json($getResponse->toJson());
        };
        return $handler;
    }

    private function getByIdAcApiDocRoute(): AcApiDocRoute
    {
        $acApiDocRoute = new AcApiDocRoute();
        $acApiDocRoute->addTag($this->acDDTable->tableName);
        $acApiDocRoute->summary = "Get single " . $this->acDDTable->tableName;
        $acApiDocRoute->description = "Auto generated data dictionary api to get single row matching field value " . $this->acDDTable->getPrimaryKeyFieldName() . " in table " . $this->acDDTable->tableName;
        $parameter = new AcApiDocParameter();
        $parameter->name = $this->acDDTable->getPrimaryKeyFieldName();
        $parameter->description = $this->acDDTable->getPrimaryKeyFieldName() . " value of row to get";
        $parameter->required = true;
        $parameter->in = "path";
        $acApiDocRoute->addParameter($parameter);
        return $acApiDocRoute;
    }

    private function getByIdHandler(): callable
    {
        $handler = function (AcWebRequest $acWebRequest): AcWebResponse {
            $acSqlDbTable = new AcSqlDbTable(tableName: $this->acDDTable->tableName);
            $acDDSelectStatement = new AcDDSelectStatement(tableName: $this->acDDTable->tableName);
            $acDDSelectStatement->includeFields = ["api_name"];
            $sqlStatement = $acDDSelectStatement->getSqlStatement();
            $sqlParameters = $acDDSelectStatement->sqlParameters;
            $getResponse = $acSqlDbTable->getRows(selectStatement: $sqlStatement, parameters: $sqlParameters);
            return AcWebResponse::json($getResponse->toJson());
        };
        return $handler;
    }

    private function postAcApiDocRoute(): AcApiDocRoute
    {
        $acApiDocRoute = new AcApiDocRoute();
        $acApiDocRoute->addTag($this->acDDTable->tableName);
        $acApiDocRoute->summary = "Get " . $this->acDDTable->tableName;
        $acApiDocRoute->description = "Auto generated data dictionary api to get rows in table " . $this->acDDTable->tableName;
        $queryFields = $this->acDDTable->getSearchQueryFieldNames();
        $properties = [
            "query" => [
                "type" => AcEnumApiDataType::STRING
            ],
            "page_number" => [
                "type" => AcEnumApiDataType::INTEGER
            ],
            "rows_count" => [
                "type" => AcEnumApiDataType::INTEGER
            ],
            "filters" => [
                "type" => AcEnumApiDataType::OBJECT
            ],
        ];
        if (count($queryFields) == 0) {
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
        return $acApiDocRoute;
    }

    private function postHandler(): callable
    {
        $handler = function (AcWebRequest $acWebRequest): AcWebResponse {
            $acSqlDbTable = new AcSqlDbTable(tableName: $this->acDDTable->tableName);
            $acDDSelectStatement = new AcDDSelectStatement(tableName: $this->acDDTable->tableName);
            $acDDSelectStatement->includeFields = ["api_name"];
            $sqlStatement = $acDDSelectStatement->getSqlStatement();
            $sqlParameters = $acDDSelectStatement->sqlParameters;
            $getResponse = $acSqlDbTable->getRows(selectStatement: $sqlStatement, parameters: $sqlParameters);
            return AcWebResponse::json($getResponse->toJson());
        };
        return $handler;
    }
}

?>
<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Models\AcJsonBindConfig;

class AcApiDocRoute {
    const KEY_TAGS = 'tags';
    const KEY_SUMMARY = 'summary';
    const KEY_DESCRIPTION = 'description';
    const KEY_OPERATION_ID = 'operationId';
    const KEY_PARAMETERS = 'parameters';
    const KEY_REQUEST_BODY = 'requestBody';
    const KEY_RESPONSES = 'responses';
    const KEY_CONSUMES = 'consumes';
    const KEY_PRODUCES = 'produces';
    const KEY_DEPRECATED = 'deprecated';
    const KEY_SECURITY = 'security';
    public AcJsonBindConfig $acJsonBindConfig;
    public array $tags = [];
    public string $summary = '';
    public string $description = '';
    public string $operationId = '';
    public array $parameters = [];
    public ?AcApiDocRequestBody $requestBody = null;
    public array $responses = [];
    public array $consumes = [];
    public array $produces = [];
    public bool $deprecated = false;
    public array $security = [];

    public static function instanceFromJson(array $jsonData): AcApiDocRoute {
        $instance = new AcApiDocRoute();
        $instance->tags = $jsonData[self::KEY_TAGS] ?? [];
        $instance->summary = $jsonData[self::KEY_SUMMARY] ?? '';
        $instance->description = $jsonData[self::KEY_DESCRIPTION] ?? '';
        $instance->operationId = $jsonData[self::KEY_OPERATION_ID] ?? '';
        if (isset($jsonData[self::KEY_PARAMETERS])) {
            foreach ($jsonData[self::KEY_PARAMETERS] as $param) {
                $instance->parameters[] = AcApiDocParameter::instanceFromJson($param);
            }
        }
        if (isset($jsonData[self::KEY_REQUEST_BODY])) {
            $instance->requestBody = AcApiDocRequestBody::instanceFromJson($jsonData[self::KEY_REQUEST_BODY]);
        }
        if (isset($jsonData[self::KEY_RESPONSES])) {
            foreach ($jsonData[self::KEY_RESPONSES] as $response) {
                $instance->responses[] = AcApiDocResponse::instanceFromJson($response);
            }
        }
        $instance->responses = $jsonData[self::KEY_RESPONSES] ?? [];
        $instance->consumes = $jsonData[self::KEY_CONSUMES] ?? [];
        $instance->produces = $jsonData[self::KEY_PRODUCES] ?? [];
        $instance->deprecated = $jsonData[self::KEY_DEPRECATED] ?? false;
        $instance->security = $jsonData[self::KEY_SECURITY] ?? [];
        return $instance;
    }

    public function addParameter(AcApiDocParameter $parameter){
        $this->parameters[] = $parameter;
    }

    public function addTag(string $tag){
        $this->tags[] = $tag;
    }

    public function toJson(): array {
        $result = [];
    
        if (!empty($this->tags)) {
            $result[self::KEY_TAGS] = $this->tags;
        }
    
        if (!empty($this->summary)) {
            $result[self::KEY_SUMMARY] = $this->summary;
        }
    
        if (!empty($this->description)) {
            $result[self::KEY_DESCRIPTION] = $this->description;
        }
    
        if (!empty($this->operationId)) {
            $result[self::KEY_OPERATION_ID] = $this->operationId;
        }
    
        if (!empty($this->parameters)) {
            $parametersJson = [];
            foreach ($this->parameters as $param) {
                $paramJson = $param->toJson();
                if (!empty($paramJson)) {
                    $parametersJson[] = $paramJson;
                }
            }
            if (!empty($parametersJson)) {
                $result[self::KEY_PARAMETERS] = $parametersJson;
            }
        }
    
        if ($this->requestBody !== null) {
            $bodyJson = $this->requestBody->toJson();
            if (!empty($bodyJson)) {
                $result[self::KEY_REQUEST_BODY] = $bodyJson;
            }
        }
    
        if (!empty($this->responses)) {
            $responsesJson = [];
            foreach ($this->responses as $response) {
                $responseJson = $response->toJson();
                if (!empty($responseJson)) {
                    $responsesJson[] = $responseJson;
                }
            }
            if (!empty($responsesJson)) {
                $result[self::KEY_RESPONSES] = $responsesJson;
            }
        }
    
        if (!empty($this->consumes)) {
            $result[self::KEY_CONSUMES] = $this->consumes;
        }
    
        if (!empty($this->produces)) {
            $result[self::KEY_PRODUCES] = $this->produces;
        }
    
        if ($this->deprecated) {
            $result[self::KEY_DEPRECATED] = true;
        }
    
        if (!empty($this->security)) {
            $result[self::KEY_SECURITY] = $this->security;
        }
    
        return $result;
    }   

    public function toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function __toString(): string {
        return $this->toString();
    }
}
?>

<?php
namespace AcWeb\ApiDocs\Model;

use AcWeb\ApiDocs\Model\AcApiDocParameter;
use AcWeb\ApiDocs\Model\AcApiDocResponse;

class AcApiDocOperation {    
    const KEY_DESCRIPTION = "description";
    const KEY_PARAMETERS = "parameters";
    const KEY_RESPONSES = "responses";    
    const KEY_SUMMARY = "summary";

    public ?string $summary = null;
    public ?string $description = null;
    public array $parameters = [];
    public array $responses = [];

    public static function fromJson(array $jsonData): AcApiDocOperation {
        $instance = new AcApiDocOperation();
        $instance->summary = $jsonData[self::KEY_SUMMARY] ?? null;
        $instance->description = $jsonData[self::KEY_DESCRIPTION] ?? null;
        if (isset($jsonData[self::KEY_PARAMETERS])) {
            foreach ($jsonData[self::KEY_PARAMETERS] as $parameter) {
                $instance->parameters[] = AcApiDocParameter::fromJson($parameter);
            }
        }
        if (isset($jsonData[self::KEY_RESPONSES])) {
            foreach ($jsonData[self::KEY_RESPONSES] as $status => $response) {
                $instance->responses[$status] = AcApiDocResponse::fromJson($response);
            }
        }
        return $instance;
    }

    public function toJson(): array {
        $json = array_filter([
            self::KEY_SUMMARY => $this->summary,
            self::KEY_DESCRIPTION => $this->description,
        ]);
        if (!empty($this->parameters)) {
            $json[self::KEY_PARAMETERS] = array_map(function ($param) {
                return $param->toJson();
            }, $this->parameters);
        }
        if (!empty($this->responses)) {
            foreach ($this->responses as $status => $response) {
                $json[self::KEY_RESPONSES][$status] = $response->toJson();
            }
        }
        return $json;
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
?>

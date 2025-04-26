<?php
namespace AcWeb\ApiDocs\Models;

use AcWeb\ApiDocs\Models\AcApiDocParameter;
use AcWeb\ApiDocs\Models\AcApiDocResponse;
use Autocode\Utils\AcUtilsJson;

class AcApiDocOperation {    
    const KEY_DESCRIPTION = "description";
    const KEY_PARAMETERS = "parameters";
    const KEY_RESPONSES = "responses";    
    const KEY_SUMMARY = "summary";
    public ?string $summary = null;
    public ?string $description = null;
    public array $parameters = [];
    public array $responses = [];

    public static function instanceFromJson(array $jsonData): static {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData): static {
        if (isset($jsonData[self::KEY_PARAMETERS])) {
            foreach ($jsonData[self::KEY_PARAMETERS] as $parameter) {
                $this->parameters[] = AcApiDocParameter::instanceFromJson($parameter);
            }
            unset($jsonData[self::KEY_PARAMETERS]);
        }
        if (isset($jsonData[self::KEY_RESPONSES])) {
            foreach ($jsonData[self::KEY_RESPONSES] as $status => $response) {
                $this->responses[$status] = AcApiDocResponse::instanceFromJson($response);
            }
            unset($jsonData[self::KEY_RESPONSES]);
        }
        AcUtilsJson::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
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

}
?>

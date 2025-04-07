<?php
namespace AcWeb\ApiDocs\Model;

class AcApiDocPathOperation {
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

    public static function fromJson(array $jsonData): AcApiDocPathOperation {
        $instance = new AcApiDocPathOperation();

        $instance->tags = $jsonData[self::KEY_TAGS] ?? [];
        $instance->summary = $jsonData[self::KEY_SUMMARY] ?? '';
        $instance->description = $jsonData[self::KEY_DESCRIPTION] ?? '';
        $instance->operationId = $jsonData[self::KEY_OPERATION_ID] ?? '';
        if (isset($jsonData[self::KEY_PARAMETERS])) {
            foreach ($jsonData[self::KEY_PARAMETERS] as $param) {
                $instance->parameters[] = AcApiDocParameter::fromJson($param);
            }
        }
        if (isset($jsonData[self::KEY_REQUEST_BODY])) {
            $instance->requestBody = AcApiDocRequestBody::fromJson($jsonData[self::KEY_REQUEST_BODY]);
        }
        if (isset($jsonData[self::KEY_RESPONSES])) {
            foreach ($jsonData[self::KEY_RESPONSES] as $response) {
                $instance->responses[] = AcApiDocResponse::fromJson($response);
            }
        }
        $instance->responses = $jsonData[self::KEY_RESPONSES] ?? [];
        $instance->consumes = $jsonData[self::KEY_CONSUMES] ?? [];
        $instance->produces = $jsonData[self::KEY_PRODUCES] ?? [];
        $instance->deprecated = $jsonData[self::KEY_DEPRECATED] ?? false;
        $instance->security = $jsonData[self::KEY_SECURITY] ?? [];

        return $instance;
    }

    public function toJson(): array {
        $parametersJson = [];
        foreach ($this->parameters as $param) {
            $parametersJson[] = $param->toJson();
        }

        return [
            self::KEY_TAGS => $this->tags,
            self::KEY_SUMMARY => $this->summary,
            self::KEY_DESCRIPTION => $this->description,
            self::KEY_OPERATION_ID => $this->operationId,
            self::KEY_PARAMETERS => $parametersJson,
            self::KEY_REQUEST_BODY => $this->requestBody?->toJson(),
            self::KEY_RESPONSES => $this->responses,
            self::KEY_CONSUMES => $this->consumes,
            self::KEY_PRODUCES => $this->produces,
            self::KEY_DEPRECATED => $this->deprecated,
            self::KEY_SECURITY => $this->security,
        ];
    }

    public function toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function __toString(): string {
        return $this->toString();
    }
}
?>

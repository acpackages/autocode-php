<?php
namespace AcWeb\ApiDocs\Models;

class AcApiDocSecurityScheme {
    const KEY_TYPE = 'type';
    const KEY_DESCRIPTION = 'description';
    const KEY_NAME = 'name';
    const KEY_IN = 'in';
    const KEY_SCHEME = 'scheme';
    const KEY_BEARER_FORMAT = 'bearerFormat';
    const KEY_FLOWS = 'flows';
    const KEY_OPENID_CONNECT_URL = 'openIdConnectUrl';

    public string $type = '';
    public string $description = '';
    public string $name = '';
    public string $in = '';
    public string $scheme = '';
    public string $bearerFormat = '';
    public array $flows = [];
    public string $openIdConnectUrl = '';

    public static function fromJson(array $jsonData): AcApiDocSecurityScheme {
        $instance = new AcApiDocSecurityScheme();

        $instance->type = $jsonData[self::KEY_TYPE] ?? '';
        $instance->description = $jsonData[self::KEY_DESCRIPTION] ?? '';
        $instance->name = $jsonData[self::KEY_NAME] ?? '';
        $instance->in = $jsonData[self::KEY_IN] ?? '';
        $instance->scheme = $jsonData[self::KEY_SCHEME] ?? '';
        $instance->bearerFormat = $jsonData[self::KEY_BEARER_FORMAT] ?? '';
        $instance->flows = $jsonData[self::KEY_FLOWS] ?? [];
        $instance->openIdConnectUrl = $jsonData[self::KEY_OPENID_CONNECT_URL] ?? '';

        return $instance;
    }

    public function toJson(): array {
        return [
            self::KEY_TYPE => $this->type,
            self::KEY_DESCRIPTION => $this->description,
            self::KEY_NAME => $this->name,
            self::KEY_IN => $this->in,
            self::KEY_SCHEME => $this->scheme,
            self::KEY_BEARER_FORMAT => $this->bearerFormat,
            self::KEY_FLOWS => $this->flows,
            self::KEY_OPENID_CONNECT_URL => $this->openIdConnectUrl,
        ];
    }

    public function toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function __toString(): string {
        return $this->toString();
    }
}
?>

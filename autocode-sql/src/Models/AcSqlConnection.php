<?php

namespace AcSql\Models;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';

use Autocode\AcLogger;
use Autocode\Utils\AcJsonUtils;


class AcSqlConnection {
    const KEY_CONNECTION_PORT = 'port';
    const KEY_CONNECTION_HOSTNAME = 'hostname';
    const KEY_CONNECTION_USERNAME = 'username';
    const KEY_CONNECTION_PASSWORD = 'password';
    const KEY_CONNECTION_DATABASE = 'database';
    const KEY_CONNECTION_OPTIONS = 'options';

    public AcLogger $logger;
    public int $port = 0;
    public string $hostname = "";
    public string $username = "";
    public string $password = "";
    public string $database = "";
    public array $options = [];


    public static function instanceFromJson(array $jsonData): static {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData = []): static {
        AcJsonUtils::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcJsonUtils::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

}

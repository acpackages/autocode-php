<?php

namespace AcSql\Models;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';

use Autocode\AcLogger;
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;


class AcSqlConnection {
    const KEY_CONNECTION_PORT = 'port';
    const KEY_CONNECTION_HOSTNAME = 'hostname';
    const KEY_CONNECTION_USERNAME = 'username';
    const KEY_CONNECTION_PASSWORD = 'password';
    const KEY_CONNECTION_DATABASE = 'database';
    const KEY_CONNECTION_OPTIONS = 'options';

    public AcJsonBindConfig $acJsonBindConfig;
    public AcLogger $logger;
    public int $port = 0;
    public string $hostname = "";
    public string $username = "";
    public string $password = "";
    public string $database = "";
    public array $options = [];

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::instanceFromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_CONNECTION_PORT => "port",
                self::KEY_CONNECTION_HOSTNAME => "hostname",
                self::KEY_CONNECTION_USERNAME => "username",
                self::KEY_CONNECTION_PASSWORD => "password",
                self::KEY_CONNECTION_DATABASE => "database",
                self::KEY_CONNECTION_OPTIONS => "options",
            ]        
        ]);
        $this->logger = new AcLogger();
    }

    public static function instanceFromJson(array $jsonData): AcSqlConnection {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData = []): static {
        AcUtilsJson::bindInstancePropertiesFromJson(instance: $this, data: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcUtilsJson::createJsonArrayFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}

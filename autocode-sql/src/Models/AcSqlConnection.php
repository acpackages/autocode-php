<?php

namespace AcSql\Models;

require_once '../../autocode/vendor/autoload.php';

use Autocode\AcLogger;

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

    public function __construct() {
        $this->logger = new AcLogger();
    }

    public static function fromJson(array $jsonData): AcSqlConnection {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public function setValuesFromJson(array $jsonData = []): void {
        if (isset($jsonData[self::KEY_CONNECTION_PORT])) {
            $this->port = (int) $jsonData[self::KEY_CONNECTION_PORT];
        }

        if (isset($jsonData[self::KEY_CONNECTION_HOSTNAME])) {
            $this->hostname = (string) $jsonData[self::KEY_CONNECTION_HOSTNAME];
        }

        if (isset($jsonData[self::KEY_CONNECTION_USERNAME])) {
            $this->username = (string) $jsonData[self::KEY_CONNECTION_USERNAME];
        }

        if (isset($jsonData[self::KEY_CONNECTION_PASSWORD])) {
            $this->password = (string) $jsonData[self::KEY_CONNECTION_PASSWORD];
        }

        if (isset($jsonData[self::KEY_CONNECTION_DATABASE])) {
            $this->database = (string) $jsonData[self::KEY_CONNECTION_DATABASE];
        }

        if (isset($jsonData[self::KEY_CONNECTION_OPTIONS])) {
            $this->options = (array) $jsonData[self::KEY_CONNECTION_OPTIONS];
        }
    }

    public function toJson(): array {
        return [
            self::KEY_CONNECTION_PORT => $this->port,
            self::KEY_CONNECTION_HOSTNAME => $this->hostname,
            self::KEY_CONNECTION_USERNAME => $this->username,
            self::KEY_CONNECTION_PASSWORD => $this->password,
            self::KEY_CONNECTION_DATABASE => $this->database,
            self::KEY_CONNECTION_OPTIONS => $this->options,
        ];
    }
}

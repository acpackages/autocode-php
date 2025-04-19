<?php

namespace Autocode\Models;

use Autocode\AcLogger;
use Autocode\Utils\AcUtilsJson;

class AcResult {
    const CODE_NOTHING_EXECUTED = 0;
    const CODE_SUCCESS = 1;
    const CODE_FAILURE = -1;
    const CODE_EXCEPTION = -2;

    const KEY_CODE = "code";
    const KEY_MESSAGE = "message";
    const KEY_OTHER_DETAILS = "other_details";
    const KEY_STATUS = "status";
    const KEY_VALUE = "value";

    public AcJsonBindConfig $acJsonBindConfig;
    public $exception = null;
    public $stackTrace = null;
    public ?AcLogger $logger = null;
    public int $code = self::CODE_NOTHING_EXECUTED;
    public $message = "Nothing executed";
    public string $status = "failure";
    public $value = null;
    public $previousResult = null;
    public array $otherDetails = [];
    public array $log = [];

    public static function fromJson(array $jsonData): AcResult {
        $instance = new AcResult();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::fromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_CODE => "code",
                self::KEY_MESSAGE => "message",
                self::KEY_OTHER_DETAILS => "otherDetails",
                self::KEY_STATUS => "status",
                self::KEY_VALUE => "value",
            ]        
        ]);
    }

    public function isException(): bool {
        return $this->status === "failure" && $this->code === self::CODE_EXCEPTION;
    }

    public function isFailure(): bool {
        return $this->status === "failure";
    }

    public function isSuccess(): bool {
        return $this->status === "success";
    }

    public function appendResultLog(AcResult $result) {
        $this->log = array_merge($this->log, $result->log);
    }

    public function prependResultLog(AcResult $result) {
        $this->log = array_merge($result->log, $this->log);
    }

    public function setFromResult(AcResult $result,?string $message = null,?AcLogger $logger = null) {
        $this->status = $result->status;
        $this->message = $result->message;
        $this->code = $result->code;
        $this->previousResult = $result;

        if ($this->isException()) {
            $this->exception = $result->exception;
            $this->message = $result->message;
        } elseif ($this->isSuccess()) {
            $this->value = $result->value;
        }
    }

    public function setSuccess(mixed $value = null,?string $message = null,?AcLogger $logger = null) {
        $this->status = "success";
        $this->code = self::CODE_SUCCESS;

        if ($value!=null) {
            $this->value = $value;
        }
        if(isset($message)) {
            $this->message = $message;
            if (isset($logger)) {
                $logger->success($this->message);
            }
            if ($this->logger) {
                $this->logger->success($this->message);
            }
        }
    }

    public function setFailure(mixed $value = null,?string $message = null,?AcLogger $logger = null) {
        $this->status = "failure";
        $this->code = self::CODE_FAILURE;

        if(isset($message)) {
            $this->message = $message;
            if (isset($logger)) {
                $logger->error($this->message);
            }
            if ($this->logger) {
                $this->logger->error($this->message);
            }
        }
    }

    public function setException($exception = null, ?string $message = null,?AcLogger $logger = null,?bool $logException = false,?string $stackTrace = null) {
        $this->code = self::CODE_EXCEPTION;
        $this->exception = $exception;
        $this->stackTrace = $stackTrace ?? null;
        $this->message = $message ?? $exception->getMessage();

        if ($logException && isset($logger)) {
            $logger->error([$exception->getMessage(), $this->stackTrace]);
        }
        if ($logException && $this->logger) {
            $this->logger->error([$exception->getMessage(), $this->stackTrace]);
        }
    }

    public function setValuesFromJson(array $jsonData) {
        AcUtilsJson::bindInstancePropertiesFromJson(instance: $this, data: $jsonData);
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
?>
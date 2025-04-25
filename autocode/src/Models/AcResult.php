<?php

namespace Autocode\Models;

use Autocode\AcLogger;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;

class AcResult {
    const CODE_NOTHING_EXECUTED = 0;
    const CODE_SUCCESS = 1;
    const CODE_FAILURE = -1;
    const CODE_EXCEPTION = -2;

    const KEY_CODE = "code";
    const KEY_EXCEPTION = "exception";
    const KEY_LOG = "log";
    const KEY_MESSAGE = "message";
    const KEY_OTHER_DETAILS = "other_details";
    const KEY_PREVIOUS_RESULT = "previous_result";
    const KEY_STACK_TRACE = "stack_trace";
    const KEY_STATUS = "status";
    const KEY_VALUE = "value";

    public ?AcLogger $logger = null;

    #[AcBindJsonProperty(key: AcHookResult::KEY_CODE)]
    public int $code = self::CODE_NOTHING_EXECUTED;

    #[AcBindJsonProperty(key: AcHookResult::KEY_EXCEPTION)]
    public $exception = null;

    #[AcBindJsonProperty(key: AcHookResult::KEY_LOG)]
    public array $log = [];

    #[AcBindJsonProperty(key: AcHookResult::KEY_MESSAGE)]
    public $message = "Nothing executed";

    #[AcBindJsonProperty(key: AcHookResult::KEY_OTHER_DETAILS)]
    public array $otherDetails = [];

    #[AcBindJsonProperty(key: AcHookResult::KEY_STACK_TRACE)]
    public $stackTrace = null;
    
    #[AcBindJsonProperty(key: AcHookResult::KEY_STATUS)]
    public string $status = "failure";

    #[AcBindJsonProperty(key: AcHookResult::KEY_VALUE)]
    public $value = null;

    public static function instanceFromJson(array $jsonData): AcResult {
        $instance = new AcResult();
        $instance->fromJson($jsonData);
        return $instance;
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

    public function appendResultLog(AcResult $result):static {
        $this->log = array_merge($this->log, $result->log);
        return $this;
    }

    public function prependResultLog(AcResult $result):static {
        $this->log = array_merge($result->log, $this->log);
        return $this;
    }

    public function setFromResult(AcResult $result,?string $message = null,?AcLogger $logger = null): static {
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
        return $this;
    }

    public function setSuccess(mixed $value = null,?string $message = null,?AcLogger $logger = null):static {
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
        return $this;
    }

    public function setFailure(mixed $value = null,?string $message = null,?AcLogger $logger = null): static {
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
        return $this;
    }

    public function setException($exception = null, ?string $message = null,?AcLogger $logger = null,?bool $logException = false,?string $stackTrace = null):static{
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
        return $this;
    }

    public function fromJson(array $jsonData): static {
        AcUtilsJson::bindInstancePropertiesFromJson(instance: $this, data: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcUtilsJson::createJsonArrayFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
?>
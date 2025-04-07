<?php
namespace AcSql\Models;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../Enums/AcEnumRowOperation.php';
use Autocode\AcLogger;
use Autocode\AcResult;
use Autocode\Enums\AcEnumHttpResponseCode;

class AcWebResponse {
    const KEY_CODE = "code";
    const KEY_STATUS = "status";
    const KEY_VALUE = "value";

    public $exception = null;
    public $stackTrace = null;
    public ?AcLogger $logger = null;
    public int $code = AcEnumHttpResponseCode::NOT_IMPLEMENTED;
    public $message = "Nothing executed";
    public string $status = "failure";
    public $value = null;
    public $previousResult = null;
    public array $otherDetails = [];
    public array $log = [];

    public static function fromJson(array $mapData): AcResult {
        $instance = new AcResult();
        $instance->setValuesFromMap($mapData);
        return $instance;
    }

    public function __construct(?string $message = "Nothing executed") {
        $this->message = $message;
    }

    public function isException(): bool {
        return $this->status === "failure" && $this->code === AcEnumHttpResponseCode::INTERNAL_SERVER_ERROR;
    }

    public function isFailure(): bool {
        return $this->status === "failure";
    }

    public function isSuccess(): bool {
        return $this->status === "success";
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
        $this->code = AcEnumHttpResponseCode::OK;

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
        $this->code = AcEnumHttpResponseCode::OK;

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
        $this->code = AcEnumHttpResponseCode::INTERNAL_SERVER_ERROR;
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

    public function setValuesFromMap(array $mapData) {
        if (isset($mapData[self::KEY_CODE])) {
            $this->code = $mapData[self::KEY_CODE];
        }
        if (isset($mapData[self::KEY_MESSAGE])) {
            $this->message = $mapData[self::KEY_MESSAGE];
        }
        if (isset($mapData[self::KEY_OTHER_DETAILS])) {
            $this->otherDetails = $mapData[self::KEY_OTHER_DETAILS];
        }
        if (isset($mapData[self::KEY_STATUS])) {
            $this->status = $mapData[self::KEY_STATUS];
        }
        if (isset($mapData[self::KEY_VALUE])) {
            $this->value = $mapData[self::KEY_VALUE];
        }
    }

    public function toJson(): array {
        return [
            self::KEY_CODE => $this->code,
            self::KEY_MESSAGE => $this->message,
            self::KEY_OTHER_DETAILS => $this->otherDetails,
            self::KEY_STATUS => $this->status,
            self::KEY_VALUE => $this->value,
        ];
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}

?>
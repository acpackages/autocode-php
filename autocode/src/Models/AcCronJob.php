<?php

namespace Autocode\Models;

use Autocode\Annotaions\AcBindJsonProperty;
use DateTime;

class AcCronJob {
    const KEY_CALLBACK = "callback";
    const KEY_DURATION = "duration";
    const KEY_EXECUTION = "execution";
    const KEY_ID = "id";
    const KEY_LAST_EXECUTION_TIME = "last_execution_time";

    public string $id;
    public string $execution;
    public array $duration;

    #[AcBindJsonProperty(key: AcHookResult::KEY_CALLBACK,skipInToJson:true)]
    public mixed $callback = null;

    #[AcBindJsonProperty(key: AcHookResult::KEY_LAST_EXECUTION_TIME)]
    public ?DateTime $lastExecutionTime = null;

    public function __construct(string $id, string $execution, array $duration, callable $callback) {
        $this->id = $id;
        $this->execution = $execution;
        $this->duration = $duration;
        $this->callback = $callback;
    }
}

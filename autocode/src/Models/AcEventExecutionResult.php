<?php

namespace Autocode\Models;

use Autocode\Annotaions\AcBindJsonProperty;

require_once 'AcResult.php';

class AcEventExecutionResult extends AcResult {
    const KEY_CONTINUE_OPERATION = "continue_operation";
    const KEY_HAS_RESULTS = "has_results";
    const KEY_RESULTS = "results";

    #[AcBindJsonProperty(key: AcHookExecutionResult::KEY_CONTINUE_OPERATION)]
    public bool $continueOperation = true;   

    #[AcBindJsonProperty(key: AcHookExecutionResult::KEY_HAS_RESULTS)]
    public bool $hasResults = false;

    public array $results = [];
}

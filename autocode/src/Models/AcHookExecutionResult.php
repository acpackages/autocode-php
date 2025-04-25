<?php

namespace Autocode\Models;

use Autocode\Annotaions\AcBindJsonProperty;

require_once 'AcResult.php';

class AcHookExecutionResult extends AcResult {
    const KEY_CONTINUE = "continue";
    const KEY_HAS_RESULTS = "has_results";
    const KEY_RESULTS = "results";

    #[AcBindJsonProperty(key: AcHookExecutionResult::KEY_CONTINUE)]
    public bool $continue = true;   

    #[AcBindJsonProperty(key: AcHookExecutionResult::KEY_HAS_RESULTS)]
    public bool $hasResults = false;

    #[AcBindJsonProperty(key: AcHookExecutionResult::KEY_RESULTS)]
    public array $results = [];
}

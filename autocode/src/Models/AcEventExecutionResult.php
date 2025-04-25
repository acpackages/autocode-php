<?php

namespace Autocode\Models;

use Autocode\Annotaions\AcBindJsonProperty;

require_once 'AcResult.php';

class AcEventExecutionResult extends AcResult {
    
    const KEY_CONTINUE = "continue";
    const KEY_HAS_RESULTS = "has_results";
    const KEY_RESULTS = "results";

    #[AcBindJsonProperty(key: AcEventExecutionResult::KEY_CONTINUE)]
    public bool $continue = true;   

    #[AcBindJsonProperty(key: AcEventExecutionResult::KEY_HAS_RESULTS)]
    public bool $hasResults = false;

    #[AcBindJsonProperty(key: AcEventExecutionResult::KEY_RESULTS)]
    public array $results = [];
}

<?php

namespace Autocode\Models;

require_once 'AcResult.php';

class AcEventExecutionResult extends AcResult {
    
    const KEY_CONTINUE = "continue";
    const KEY_HAS_RESULTS = "has_results";
    public bool $hasResults = false;
    public bool $continue = true;
    public array $results = [];

    public function __construct() {
        parent::__construct();
        $this->acJsonBindConfig->propertyBindings["continue"]="continue";
        $this->acJsonBindConfig->propertyBindings["has_results"]="hasResults";
    }
}

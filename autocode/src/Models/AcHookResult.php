<?php

namespace Autocode\Models;

require_once 'AcResult.php';

class AcHookResult extends AcResult {
    public bool $continue = true;
    public array $changes = [];

    public function __construct() {
        $this->acJsonBindConfig->propertyBindings["continue"]="continue";
    }
}

<?php

namespace Autocode\Models;

use Autocode\Annotaions\AcBindJsonProperty;

require_once 'AcResult.php';

class AcHookResult extends AcResult {

    const KEY_CONTINUE = "continue";
    const KEY_CHANGES = "changes";

    public bool $continue = true;

    public array $changes = [];

}

<?php

namespace Autocode\Models;

use Autocode\Annotaions\AcBindJsonProperty;

require_once 'AcResult.php';

class AcHookResult extends AcResult {

    const KEY_CONTINUE_OPERATION = "continue_operation";
    const KEY_CHANGES = "changes";

    #[AcBindJsonProperty(key: AcHookResult::KEY_CONTINUE_OPERATION)]
    public bool $continueOperation = true;

    public array $changes = [];

}

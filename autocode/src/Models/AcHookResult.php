<?php

namespace Autocode\Models;

use Autocode\Annotaions\AcBindJsonProperty;

require_once 'AcResult.php';

class AcHookResult extends AcResult {

    const KEY_CONTINUE = "continue";
    const KEY_CHANGES = "changes";

    #[AcBindJsonProperty(key: AcHookResult::KEY_CONTINUE)]
    public bool $continue = true;

    #[AcBindJsonProperty(key: AcHookResult::KEY_CHANGES)]
    public array $changes = [];

}

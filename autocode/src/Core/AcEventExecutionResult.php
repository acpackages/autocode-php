<?php

namespace Autocode;

require_once 'AcResult.php';

class AcEventExecutionResult extends AcResult {
    public bool $hasResults = false;
    public array $results = [];

    public function toJson(): array {
        $result = parent::toJson();
        $result['has_results'] = $this->hasResults;
        return $result;
    }
}

<?php

namespace Autocode;

require_once '../../autocode-extensions/vendor/autoload.php';
use AcExtensions\AcExtensionMethods;

require_once 'AcEventExecutionResult.php';
require_once 'Autocode.php';

class AcEvents {
    private array $events = [];

    public function execute(string $key, ...$args): AcEventExecutionResult {
        $result = new AcEventExecutionResult();

        try {
            $functionResults = [];

            if (isset($this->events[$key])) {
                $functionsToExecute = $this->events[$key];

                foreach ($functionsToExecute as $functionId => $fun) {
                    $functionResult = null;

                    if (!empty($args)) {
                        $functionResult = call_user_func_array($fun, $args);
                    } else {
                        $functionResult = call_user_func($fun);
                    }

                    if ($functionResult !== null && isset($functionResult->status) && $functionResult->status === "success") {
                        $functionResults[$functionId] = $functionResult;
                    }
                }
            }

            if (!empty($functionResults)) {
                $result->hasResults = true;
                $result->results = $functionResults;
            }

            $result->setSuccess();
        } catch (Exception $ex) {
            $result->setException($ex);
        }

        return $result;
    }

    public function subscribe(string $eventName, callable $fun): string {
        if (!array_key_exists($eventName, $this->events)) {
            $this->events[$eventName] = [];
        }
        $subscriptionId = Autocode::uniqueId();
        $this->events[$eventName][$subscriptionId] = $fun;
        return $subscriptionId;
    }

    public function unsubscribe(string $subscriptionId): bool {
        foreach ($this->events as $eventName => $eventFunctions) {
            if (array_key_exists($subscriptionId, $eventFunctions)) {
                $this->events[$eventName] = AcExtensionMethods.arrayRemove($eventFunctions,$eventFunctions[$subscriptionId]);
            }
        }
    }
}
?>
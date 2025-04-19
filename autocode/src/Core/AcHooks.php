<?php

namespace Autocode;
require_once __DIR__.'./../../../autocode-extensions/vendor/autoload.php';
require_once __DIR__.'./../Models/AcHookExecutionResult.php';
require_once 'Autocode.php';
use AcExtensions\AcExtensionMethods;
use Autocode\Models\AcHookExecutionResult;
use Exception;

class AcHooks {
    private static array $hooks = [];

    public static function execute(string $hookName, &...$args): AcHookExecutionResult {
        $result = new AcHookExecutionResult();
        try {
            $functionResults = [];
            $continueOperation = true;
            if (isset(self::$hooks[$hookName])) {
                $functionsToExecute =self::$hooks[$hookName];
                foreach ($functionsToExecute as $functionId => $fun) {
                    if($continueOperation){
                        $functionResult = null;
                        if (!empty($args)) {
                            $functionResult = call_user_func_array($fun, $args);
                        } else {
                            $functionResult = call_user_func($fun);
                        }
                        if ($functionResult != null) {
                            $functionResults[$functionId] = $functionResult;
                            if($functionResult->isFailure()){
                                $continueOperation = false;
                            }
                            if($functionResult->continue != true) {
                                $result->continue = false;
                            }                        
                        }
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

    public static function subscribe(string $hookName, callable $fun): string {
        if (!array_key_exists($hookName, self::$hooks)) {
            self::$hooks[$hookName] = [];
        }
        $subscriptionId = Autocode::uniqueId();
        self::$hooks[$hookName][$subscriptionId] = $fun;
        return $subscriptionId;
    }

    public function unsubscribe(string $subscriptionId):void {
        foreach ($this->hooks as $hookName => $eventFunctions) {
            if (array_key_exists($subscriptionId, $eventFunctions)) {
                $this->hooks[$hookName] = AcExtensionMethods::arrayRemove($eventFunctions,$eventFunctions[$subscriptionId]);
            }
        }
    }
}
?>
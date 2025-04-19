<?php
require_once __DIR__ ."./../../../autocode/vendor/autoload.php";
use Autocode\Models\AcJsonBindConfig;
class Account{
    public AcJsonBindConfig $acJsonBindConfig;
    public string $accountName = "";
    public string $accountTarget = "";

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::fromJson([
            "property_bindings" => [
                "account_name" => "accountName",
                "account_target" => "accountTarget"
            ]        
        ]);
    }
}

?>
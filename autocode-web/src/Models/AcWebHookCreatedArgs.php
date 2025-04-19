<?php
namespace AcWeb\Models;

use AcWeb\ApiDocs\Models\AcApiDocPath;
use AcWeb\ApiDocs\Models\AcApiDocRoute;
use AcWeb\Core\AcWeb;
use Autocode\Enums\AcEnumHttpMethod;


class AcWebHookCreatedArgs {
    public AcWeb $acWeb;
    public function __construct(AcWeb $acWeb) {
        $this->acWeb = $acWeb;
    }    

}

?>
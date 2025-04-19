<?php

use AcWeb\ApiDocs\Models\AcApiDocContent;
use AcWeb\ApiDocs\Models\AcApiDocRequestBody;
require_once(dirname(__FILE__) ."./../models/Account.php");
use AcWeb\ApiDocs\Models\AcApiDocParameter;
use AcWeb\Core\AcWebPath;
class AccountSaveRequest extends AcWebPath
{

    public function __construct()
    {
        parent::__construct();
        $parameter = new AcApiDocParameter();
        $parameter->name = "body";
        $parameter->description = "Test api for account save operation";
        $parameter->required = true;
        $parameter->in = "body";
        $parameter->schema = [
            '$ref' => "#/definitions/accounts"
        ];
        $requestBody = new AcApiDocRequestBody();
        $requestBody->description = "Test api for account save operation";
        $requestContent = new AcApiDocContent();
        $requestContent->encoding = "application/json";
        $requestContent->schema = [
            '$ref' => "#/definitions/accounts"
        ];
        $requestBody->addContent( $requestContent);
        $this->acApiDocRoute->requestBody = $requestBody;
    }
    function handleRequest()
    {
        
        $account = $this->postInstance(new Account());
        $this->responseJson($account);
    }
}
?>
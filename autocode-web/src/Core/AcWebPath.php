<?php
namespace AcWeb\Core;

require_once __DIR__ ."./../../../autocode/vendor/autoload.php";
require_once __DIR__ ."./../ApiDocs/Models/AcApiDocPath.php";

use AcExtensions\AcExtensionMethods;
use AcWeb\ApiDocs\Model\AcApiDocPath;
use Autocode\AcLogger;
class AcWebPath {
    const KEY_COOKIES = 'cookies';
    const KEY_DELETE = 'delete';
    const KEY_FILES = 'files';
    const KEY_GET = 'get';
    const KEY_POST = 'post';
    const KEY_PUT = 'put';
    const KEY_SESSION = 'session';
    const KEY_URL = 'url';
    public array $cookies=[];   
    public array $delete=[]; 
    public array $files=[];
    public array $get=[];
    public array $headers=[];
    public array $post=[];    
    public array $put=[];
    public array $session=[];

    public AcApiDocPath $acApiDocPath;
    public AcLogger $logger;
    public $acWebResponse = null;
    public string $url;
    
    public function __construct() {
        $this->acApiDocPath = new AcApiDocPath();
    }

    public function initialize(string $url) {    
        $this->url = $url;
        $this->logger = new AcLogger();
    }

    public function handleRequest(){}

    public function responseJson($result){

    }

    public function responseHtml($result){
        echo $result;
    }

    public function redirect($targetUrl){}

    public function setValuesFromJson(array $jsonData = []): void {
        if (isset($jsonData[self::KEY_CONNECTION_PORT])) {
            $this->port = (int) $jsonData[self::KEY_CONNECTION_PORT];
        }

        if (isset($jsonData[self::KEY_CONNECTION_HOSTNAME])) {
            $this->hostname = (string) $jsonData[self::KEY_CONNECTION_HOSTNAME];
        }

        if (isset($jsonData[self::KEY_CONNECTION_USERNAME])) {
            $this->username = (string) $jsonData[self::KEY_CONNECTION_USERNAME];
        }

        if (isset($jsonData[self::KEY_CONNECTION_PASSWORD])) {
            $this->password = (string) $jsonData[self::KEY_CONNECTION_PASSWORD];
        }

        if (isset($jsonData[self::KEY_CONNECTION_DATABASE])) {
            $this->database = (string) $jsonData[self::KEY_CONNECTION_DATABASE];
        }

        if (isset($jsonData[self::KEY_CONNECTION_OPTIONS])) {
            $this->options = (array) $jsonData[self::KEY_CONNECTION_OPTIONS];
        }
    }

    public function toJson(): array {
        return [
            self::KEY_CONNECTION_PORT => $this->port,
            self::KEY_CONNECTION_HOSTNAME => $this->hostname,
            self::KEY_CONNECTION_USERNAME => $this->username,
            self::KEY_CONNECTION_PASSWORD => $this->password,
            self::KEY_CONNECTION_DATABASE => $this->database,
            self::KEY_CONNECTION_OPTIONS => $this->options,
        ];
    }
}

?>
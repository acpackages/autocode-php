<?php
namespace AcWeb\Core;

require_once __DIR__ ."./../../../autocode/vendor/autoload.php";
require_once __DIR__ ."./../ApiDocs/Models/AcApiDocPath.php";

use AcExtensions\AcExtensionMethods;
use AcWeb\ApiDocs\Models\AcApiDocPath;
use AcWeb\ApiDocs\Models\AcApiDocRoute;
use Autocode\AcLogger;
use Autocode\Utils\AcUtilsFile;
use Autocode\Utils\AcUtilsJson;
class AcWebPath {
    const KEY_COOKIES = 'cookies';
    const KEY_DELETE = 'delete';
    const KEY_FILES = 'files';
    const KEY_GET = 'get';
    const KEY_POST = 'post';
    const KEY_PUT = 'put';
    const KEY_SESSION = 'session';
    const KEY_URL = 'url';
    public array $cookie=[];    
    public array $files=[];
    public array $get=[];
    public array $pathParameter=[];
    public array $headers=[];
    public array $post=[];    
    public array $session=[];

    public AcApiDocRoute $acApiDocRoute;
    public AcLogger $logger;
    public $acWebResponse = null;
    public string $url;
    
    public function __construct() {
        $this->acApiDocRoute = new AcApiDocRoute();
    }

    public function initialize(string $url) {    
        $this->url = $url;
        $this->acApiDocRoute->url = $this->url;
        $this->logger = new AcLogger();
        $this->get = $_GET;
        $this->post = $_POST;
        $this->session = $_SESSION;
        $this->cookie = $_COOKIE;
    }

    public function handleRequest(){}

    public function post(string $key=""):object{        
        $data = json_decode(file_get_contents('php://input'), true);
        return $data[$key];
    }

    public function postInstance(object $instance,?string $key=""):object{
        $data = json_decode(file_get_contents('php://input'), true);
        if($key != ""){
            if(isset($data[$key])){
                $data = $data[$key];
            }
        }
        if($data !=null){
            $instance = AcUtilsJson::bindInstancePropertiesFromJson(instance: $instance, data: $data);
        }
        return $instance;
    }

    public function responseJson($result){
        $jsonArray = $result;
        if(is_object($result)){
            $jsonArray = AcUtilsJson::instanceToJson(instance: $result);
        }        
        header("Content-Type: application/json");
        echo json_encode($jsonArray);
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
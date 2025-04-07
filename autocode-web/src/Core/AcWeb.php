<?php
namespace AcWeb\Core;
require_once __DIR__.'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../../../autocode-extensions/vendor/autoload.php';
use AcExtensions\AcArrayExtensions;
use AcExtensions\AcExtensionMethods;
use AcWeb\Models\AcWebPathHandlers;
use Autocode\Enums\AcEnumHttpMethod;
class AcWeb {
    public array $paths = [];
    
    function delete(string $url,callable $createFuntion,?array $createParameters = []){
        $this->addPath(method: AcEnumHttpMethod::DELETE, url: $url, createFunction: $createFuntion,createParameters:$createParameters);
    }

    function get(string $url,callable $createFuntion,?array $createParameters = []){
        $this->addPath(method: AcEnumHttpMethod::GET, url: $url, createFunction: $createFuntion,createParameters:$createParameters);
    }

    function post(string $url,callable $createFuntion,?array $createParameters = []){
        $this->addPath(method: AcEnumHttpMethod::POST, url: $url, createFunction: $createFuntion,createParameters:$createParameters);
    }

    function put(string $url,callable $createFuntion,?array $createParameters = []){
        $this->addPath(method: AcEnumHttpMethod::PUT, url: $url, createFunction: $createFuntion,createParameters:$createParameters);
    }

    function request(string $url,callable $createFuntion,?array $createParameters = []){
        $this->addPath(method: 'request', url: $url, createFunction: $createFuntion,createParameters:$createParameters);
    }

    function addPath(string $method,string $url,callable $createFunction,?array $createParameters = []) {
        if(!AcExtensionMethods::arrayContainsKey($url, $this->paths)){
            $this->paths[$url] = new AcWebPathHandlers();
        }
        if($method == AcEnumHttpMethod::DELETE){
            $this->paths[$url]->delete[] = ["function"=>$createFunction,"parameters"=>$createParameters];
        }
        else if($method == AcEnumHttpMethod::GET){
            $this->paths[$url]->get[] = ["function"=>$createFunction,"parameters"=>$createParameters];
        }
        else if($method == AcEnumHttpMethod::POST){
            $this->paths[$url]->post[] = ["function"=>$createFunction,"parameters"=>$createParameters];
        }
        else if($method == AcEnumHttpMethod::PUT){
            $this->paths[$url]->put[] = ["function"=>$createFunction,"parameters"=>$createParameters];
        }
        else if($method == 'request'){
            $this->paths[$url]->request[] = ["function"=>$createFunction,"parameters"=>$createParameters];
        }
    }

    function serve(string $urlPrefix = ""){
        $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
        );
        if(str_starts_with($uri, $urlPrefix)){
            $uri = substr($uri, strlen($urlPrefix));
        }
        if(AcExtensionMethods::arrayContainsKey($uri, $this->paths)){
            $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
            $pathHandlers = $this->paths[$uri];
            $pathHandler = $pathHandlers->getPathHandler($uri,$requestMethod);
            if($pathHandler!=null){
                $pathHandler->handleRequest();
                $webResponse = $pathHandler->acWebResponse;
            }
            else{
                echo "Path method not found";
            }
        }
        else{
            echo "Path not found";
        }
    }
}

?>
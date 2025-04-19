<?php
namespace AcWeb\Core;
require_once __DIR__ . './../../../autocode/vendor/autoload.php';
require_once __DIR__ . './../../../autocode-extensions/vendor/autoload.php';
use AcExtensions\AcArrayExtensions;
use AcExtensions\AcExtensionMethods;
use AcSwaggerJsonWebPath;
use AcWeb\ApiDocs\Models\AcApiDocServer;
use AcWeb\Enums\AcEnumWebHook;
use AcWeb\ApiDocs\Core\AcApiDocs;
use AcWeb\ApiDocs\Models\AcApiDoc;
use AcWeb\ApiDocs\Models\AcApiDocPath;
use AcWeb\Models\AcWebHookCreatedArgs;
use AcWeb\Models\AcWebPathHandlers;
use Autocode\AcHooks;
use Autocode\Enums\AcEnumHttpMethod;
use Autocode\Utils\AcUtilsFile;
class AcWebOriginal
{
    private array $hostUrls = [];
    private array $paths = [];
    private array $staticFiles = [];

    public AcApiDoc $acApiDoc;

    public function __construct(array $paths = [])
    {
        $this->acApiDoc = new AcApiDoc();
        $hookArgs = new AcWebHookCreatedArgs(acWeb: $this);
        AcHooks::execute(AcEnumWebHook::AC_WEB_CREATED, $this);
        $this->staticFiles(__DIR__ . "./../ApiDocs/Swagger/SwaggerUI", "swagger");
        $this->get(url: "/swagger/swagger.json", createFuntion: function ($params): AcSwaggerJsonWebPath {
            $acWeb = $params["acWeb"];
            $pathHandler = new AcSwaggerJsonWebPath();
            $pathHandler->acWeb = $acWeb;
            return AcWebRes;
        }, createParameters: ["acWeb" => $this]);
    }

    function addHostUrl(string $url){
        $server = new AcApiDocServer();
        $server->url = $url;
        $this->acApiDoc->addServer( $server);
    }

    function addPath(string $method, string $url, callable $createFunction, ?array $createParameters = [])
    {
        if (!AcExtensionMethods::arrayContainsKey($url, $this->paths)) {
            $this->paths[$url] = new AcWebPathHandlers(url: $url);
        }
        if ($method == AcEnumHttpMethod::DELETE) {
            $this->paths[$url]->delete[] = ["function" => $createFunction, "parameters" => $createParameters];
        } else if ($method == AcEnumHttpMethod::GET) {
            $this->paths[$url]->get[] = ["function" => $createFunction, "parameters" => $createParameters];
        } else if ($method == AcEnumHttpMethod::POST) {
            $this->paths[$url]->post[] = ["function" => $createFunction, "parameters" => $createParameters];
        } else if ($method == AcEnumHttpMethod::PUT) {
            $this->paths[$url]->put[] = ["function" => $createFunction, "parameters" => $createParameters];
        } else if ($method == 'request') {
            $this->paths[$url]->request[] = ["function" => $createFunction, "parameters" => $createParameters];
        }
    }

    function delete(string $url, callable $createFuntion, ?array $createParameters = [])
    {
        $this->addPath(method: AcEnumHttpMethod::DELETE, url: $url, createFunction: $createFuntion, createParameters: $createParameters);
    }

    function get(string $url, callable $createFuntion, ?array $createParameters = [])
    {
        $this->addPath(method: AcEnumHttpMethod::GET, url: $url, createFunction: $createFuntion, createParameters: $createParameters);
    }

    function getApiDoc()
    {
        $this->acApiDoc->paths = [];
        $urls = array_keys($this->paths);
        sort($urls);
        foreach ($urls as $url) {
            $pathHandler = $this->paths[$url];
            $apiDocForPath = $pathHandler->getApiDocForPath();
            $this->acApiDoc->addPath($apiDocForPath);
        }
        return $this->acApiDoc;
    }

    private function isValidStaticPath(string $uri): bool
    {
        foreach ($this->staticFiles as $staticConfig) {
            $prefix = $staticConfig["prefix"];
            $baseDir = $staticConfig["directory"];
            if (str_starts_with($uri, $prefix)) {
                $relativePath = substr($uri, strlen($prefix));
                $filePath = realpath($baseDir . DIRECTORY_SEPARATOR . ltrim($relativePath, '/'));
                if ($filePath && is_file($filePath) && str_starts_with($filePath, $baseDir)) {
                    return true;
                }
            }
        }
        return false;
    }

    function post(string $url, callable $createFuntion, ?array $createParameters = [])
    {
        $this->addPath(method: AcEnumHttpMethod::POST, url: $url, createFunction: $createFuntion, createParameters: $createParameters);
    }

    function put(string $url, callable $createFuntion, ?array $createParameters = [])
    {
        $this->addPath(method: AcEnumHttpMethod::PUT, url: $url, createFunction: $createFuntion, createParameters: $createParameters);
    }

    function request(string $url, callable $createFuntion, ?array $createParameters = [])
    {
        $this->addPath(method: 'request', url: $url, createFunction: $createFuntion, createParameters: $createParameters);
    }

    function serve(string $urlPrefix = "")
    {
        $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if (str_starts_with($uri, $urlPrefix)) {
            $uri = substr($uri, strlen($urlPrefix));
        }
        $foundPath = false;
        if (AcExtensionMethods::arrayContainsKey($uri, $this->paths)) {
            $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
            $pathHandlers = $this->paths[$uri];
            $pathHandler = $pathHandlers->getPathHandler($uri, $requestMethod);
            if ($pathHandler != null) {
                $pathHandler->handleRequest();
                $webResponse = $pathHandler->acWebResponse;
                $foundPath = true;
            }
        } else if ($this->isValidStaticPath($uri)) {
            $this->serveStaticFiles($uri);
            $foundPath = true;
        }
        if (!$foundPath) {
            echo "Path not found";
        }
    }

    private function serveStaticFiles(string $uri): bool
    {
        foreach ($this->staticFiles as $staticConfig) {
            $prefix = $staticConfig["prefix"];
            $baseDir = $staticConfig["directory"];
            if (str_starts_with($uri, $prefix)) {
                $relativePath = substr($uri, strlen($prefix));
                $filePath = realpath($baseDir . DIRECTORY_SEPARATOR . ltrim($relativePath, '/'));
                if ($filePath && str_starts_with($filePath, $baseDir) && is_file($filePath)) {
                    $mimeType = AcUtilsFile::getMimeTypeFromPath($filePath);
                    header("Content-Type: $mimeType");
                    header("Content-Length: " . filesize($filePath));
                    readfile($filePath);
                    return true;
                }
            }
        }
        return false;
    }

    function staticFiles(string $directory, string $urlPrefix = "")
    {
        if (!str_starts_with($urlPrefix, "/")) {
            $urlPrefix = "/" . $urlPrefix;
        }
        $this->staticFiles[] = [
            "directory" => realpath($directory),
            "prefix" => rtrim($urlPrefix, "/")
        ];
    }
}

?>
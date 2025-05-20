<?php
namespace AcWeb\Core;
require_once __DIR__ . './../../../autocode/vendor/autoload.php';
require_once __DIR__ . './../../../autocode-extensions/vendor/autoload.php';
require_once __DIR__ . './../Annotations/AcWebController.php';
require_once __DIR__ . './../Annotations/AcWebRepository.php';
require_once __DIR__ . './../Annotations/AcWebRoute.php';
require_once __DIR__ . './../Annotations/AcWebService.php';
require_once __DIR__ . './../Annotations/AcWebValueFromBody.php';
require_once __DIR__ . './../Annotations/AcWebValueFromPath.php';
require_once __DIR__ . './../Annotations/AcWebValueFromQuery.php';
require_once __DIR__ . './../Annotations/AcWebView.php';
require_once __DIR__ . './../ApiDocs/Swagger/AcApiSwagger.php';
require_once __DIR__ . './../ApiDocs/Utils/AcApiDocUtils.php';
require_once __DIR__ . './../Models/AcWebRequest.php';
require_once __DIR__ . './../Models/AcWebResponse.php';
use AcExtensions\AcExtensionMethods;
use AcWeb\Annotaions\AcWebRouteMeta;
use AcWeb\Models\AcWebResponse;
use AcWeb\Annotaions\AcWebController;
use AcWeb\Annotaions\AcWebRepository;
use AcWeb\Annotaions\AcWebRoute;
use AcWeb\Annotaions\AcWebService;
use AcWeb\Annotaions\AcWebValueFromBody;
use AcWeb\Annotaions\AcWebValueFromPath;
use AcWeb\Annotaions\AcWebView;
use AcWeb\ApiDocs\Models\AcApiDoc;
use AcWeb\ApiDocs\Models\AcApiDocContent;
use AcWeb\ApiDocs\Models\AcApiDocParameter;
use AcWeb\ApiDocs\Models\AcApiDocPath;
use AcWeb\ApiDocs\Models\AcApiDocRoute;
use AcWeb\ApiDocs\Models\AcApiDocRequestBody;
use AcWeb\ApiDocs\Models\AcApiDocServer;
use AcWeb\ApiDocs\Swagger\AcApiSwagger;
use AcWeb\Enums\AcEnumWebHook;
use AcWeb\Models\AcWebHookCreatedArgs;
use AcWeb\Models\AcWebRequest;
use AcWeb\Models\AcWebRouteDefinition;
use ApiDocs\Utils\AcApiDocUtils;
use Autocode\AcHooks;
use Autocode\Enums\AcEnumHttpMethod;
use Autocode\Utils\AcFileUtils;
use Autocode\Utils\AcJsonUtils;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
class AcWeb
{
    public AcApiDoc $acApiDoc;
    private array $routeDefinitions = [];
    private array $staticFiles = [];
    public string $urlPrefix = "";

    public function __construct(array $paths = [])
    {
        $this->acApiDoc = new AcApiDoc();
        $hookArgs = new AcWebHookCreatedArgs(acWeb: $this);
        AcHooks::execute(AcEnumWebHook::AC_WEB_CREATED, $this);
        $this->staticFiles(__DIR__ . "./../ApiDocs/Swagger/SwaggerUI", "swagger");
        $this->get(url: "/swagger/swagger.json", handler: function () {
            $acApiSwagger = new AcApiSwagger();
            $this->acApiDoc->paths = [];
            $paths = [];
            foreach ($this->routeDefinitions as $routeDefinition) {
                $url = $routeDefinition->url;
                if ($url != "/swagger/swagger.json") {
                    if (!isset($paths[$url])) {
                        $paths[$url] = new AcApiDocPath();
                        $paths[$url]->url = $url;
                    }
                    $acApiDocPath = $paths[$url];
                    $acApiDocRoute = $routeDefinition->documentation;
                    if ($routeDefinition->method == AcEnumHttpMethod::CONNECT) {
                        $acApiDocPath->connect = $acApiDocRoute;
                    } else if ($routeDefinition->method == AcEnumHttpMethod::DELETE) {
                        $acApiDocPath->delete = $acApiDocRoute;
                    } else if ($routeDefinition->method == AcEnumHttpMethod::GET) {
                        $acApiDocPath->get = $acApiDocRoute;
                    } else if ($routeDefinition->method == AcEnumHttpMethod::HEAD) {
                        $acApiDocPath->head = $acApiDocRoute;
                    } else if ($routeDefinition->method == AcEnumHttpMethod::OPTIONS) {
                        $acApiDocPath->options = $acApiDocRoute;
                    } else if ($routeDefinition->method == AcEnumHttpMethod::PATCH) {
                        $acApiDocPath->patch = $acApiDocRoute;
                    } else if ($routeDefinition->method == AcEnumHttpMethod::POST) {
                        $acApiDocPath->post = $acApiDocRoute;
                    } else if ($routeDefinition->method == AcEnumHttpMethod::PUT) {
                        $acApiDocPath->put = $acApiDocRoute;
                    } else if ($routeDefinition->method == AcEnumHttpMethod::TRACE) {
                        $acApiDocPath->trace = $acApiDocRoute;
                    }
                }
            }
            $this->acApiDoc->paths = $paths;
            $acApiSwagger->acApiDoc = $this->acApiDoc;
            return AcWebResponse::json($acApiSwagger->generateJson());
        });
    }

    function addHostUrl(string $url): static{
        $server = new AcApiDocServer();
        $server->url = $url;
        $this->acApiDoc->addServer($server);
        return $this;
    }

    public function connect(string $url, callable $handler,?AcApiDocRoute $acApiDocRoute = null): static{
        $this->route(url: $url, handler: $handler, method: AcEnumHttpMethod::CONNECT,acApiDocRoute: $acApiDocRoute);
        return $this;
    }

    public function delete(string $url, callable $handler,?AcApiDocRoute $acApiDocRoute = null): static{
        $this->route(url: $url, handler: $handler, method: AcEnumHttpMethod::DELETE,acApiDocRoute: $acApiDocRoute);
        return $this;
    }

    private function extractPathParams(string $routePath, string $uri): array
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = "@^" . rtrim($pattern, '/') . "$@";
        preg_match($pattern, rtrim($uri, '/'), $matches);
        return array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
    }

    public function get(string $url, callable $handler,?AcApiDocRoute $acApiDocRoute = null): static{
        $this->route(url: $url, handler: $handler, method: AcEnumHttpMethod::GET,acApiDocRoute: $acApiDocRoute);
        return $this;
    }

    private function getRouteDocFromHandlerReflection(ReflectionMethod|ReflectionFunction $handlerReflection,?AcApiDocRoute $acApiDocRoute = new AcApiDocRoute()): AcApiDocRoute {
        if($acApiDocRoute == null){
            $acApiDocRoute = new AcApiDocRoute();
        }
        foreach ($handlerReflection->getParameters() as $param) {
            $attrs = $param->getAttributes();
            foreach ($attrs as $attribute) {
                $instance = $attribute->newInstance();
                $key = $attribute->getArguments()[0] ?? $param->getName();
                if ($instance instanceof AcWebValueFromPath) {
                    $parameter = new AcApiDocParameter();
                    $parameter->name = $key;
                    $parameter->required = true;
                    $parameter->in = "path";
                    $acApiDocRoute->addParameter(parameter: $parameter);
                } elseif ($instance instanceof AcWebValueFromQuery) {
                    $parameter = new AcApiDocParameter();
                    $parameter->name = $key;
                    $parameter->required = true;
                    $parameter->in = "query";
                    $acApiDocRoute->addParameter(parameter: $parameter);
                } elseif ($instance instanceof AcWebValueFromForm) {
                } elseif ($instance instanceof AcWebValueFromBody) {
                    $type = $param->getType()?->getName();
                    if ($type && class_exists($type)) {
                        $schema = AcApiDocUtils::getApiModelRefFromClass(className: $type,acApiDoc: $this->acApiDoc);
                        $content = new AcApiDocContent();
                        $content->encoding = "application/json";
                        $content->schema = $schema;
                        $requestBody = new AcApiDocRequestBody();
                        $requestBody->addContent(content: $content);
                        $acApiDocRoute->requestBody = $requestBody;
                    }
                }
                elseif($instance instanceof AcWebRouteMeta){
                    foreach ($instance->tags as $tag) {
                        $acApiDocRoute->addTag(tag: $tag);
                    }
                    if($instance->summary!=null && $instance->summary!=""){
                        $acApiDocRoute->summary = $instance->summary;
                    }      
                    if($instance->description!=null && $instance->description!=""){
                        $acApiDocRoute->description = $instance->description;
                    }   
                }
            }
        }
        return $acApiDocRoute;
    }

    private function isValidStaticPath(string $uri): bool {
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

    public function head(string $url, callable $handler,?AcApiDocRoute $acApiDocRoute = null): static{
        $this->route(url: $url, handler: $handler, method: AcEnumHttpMethod::HEAD,acApiDocRoute: $acApiDocRoute);
        return $this;
    }

    private function matchPath(string $routePath, string $uri): bool{
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = "@^" . rtrim($pattern, '/') . "$@";
        return AcExtensionMethods::stringRegexMatch(pattern: $pattern, subject: rtrim($uri, '/'));
    }

    public function options(string $url, callable $handler,?AcApiDocRoute $acApiDocRoute = null): static{
        $this->route(url: $url, handler: $handler, method: AcEnumHttpMethod::OPTIONS,acApiDocRoute: $acApiDocRoute);
        return $this;
    }

    public function patch(string $url, callable $handler,?AcApiDocRoute $acApiDocRoute = null): static{
        $this->route(url: $url, handler: $handler, method: AcEnumHttpMethod::PATCH,acApiDocRoute: $acApiDocRoute);
        return $this;
    }

    public function post(string $url, callable $handler,?AcApiDocRoute $acApiDocRoute = null): static{
        $this->route(url: $url, handler: $handler, method: AcEnumHttpMethod::POST,acApiDocRoute: $acApiDocRoute);
        return $this;
    }

    public function put(string $url, callable $handler,?AcApiDocRoute $acApiDocRoute = null): static{
        $this->route(url: $url, handler: $handler, method: AcEnumHttpMethod::PUT,acApiDocRoute: $acApiDocRoute);
        return $this;
    }

    public function registerController(string $controllerClass): static{
        $refClass = new ReflectionClass($controllerClass);
        $classRoute = '';
        foreach ($refClass->getAttributes(name: AcWebRoute::class) as $attr) {
            $instance = $attr->newInstance();
            $classRoute = rtrim($instance->path, '/');
        }

        foreach ($refClass->getMethods() as $handlerMethod) {
            foreach ($handlerMethod->getAttributes(AcWebRoute::class) as $attr) {
                $instance = $attr->newInstance();
                $fullPath = $classRoute . '/' . trim($instance->path, '/');
                $httpMethod = strtolower($instance->method);
                $routeKey = $httpMethod . '>' . $fullPath;                
                $handlerReflection = new ReflectionMethod($controllerClass, $handlerMethod->getName());
                $acApiDocRoute = $this->getRouteDocFromHandlerReflection(handlerReflection:$handlerReflection);
                $this->routeDefinitions[$routeKey] = AcWebRouteDefinition::instanceFromJson(jsonData: [
                    AcWebRouteDefinition::KEY_URL => $fullPath,
                    AcWebRouteDefinition::KEY_METHOD => $httpMethod,
                    AcWebRouteDefinition::KEY_CONTROLLER => $controllerClass,
                    AcWebRouteDefinition::KEY_HANDLER => $handlerMethod->getName(),
                    AcWebRouteDefinition::KEY_DOCUMENTATION => $acApiDocRoute
                ]);
            }
        }
        return $this;
    }

    public function registerControllersDirectory(string $baseDir): static{
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir)) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php')
                continue;
            require_once $file;
            $classes = get_declared_classes();
            foreach ($classes as $class) {
                $ref = new ReflectionClass($class);
                if ($ref->isAbstract() || $ref->isInterface())
                    continue;
                if ($ref->getAttributes(AcWebController::class)) {
                    $this->registerController(controllerClass: $class);
                }
            }
        }
        return $this;
    }

    public function registerRepositoriesDirectory(string $baseDir): static{
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir)) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php')
                continue;
            require_once $file;
            $classes = get_declared_classes();
            foreach ($classes as $class) {
                $ref = new ReflectionClass($class);
                if ($ref->isAbstract() || $ref->isInterface())
                    continue;
                if ($ref->getAttributes(AcWebRepository::class)) {
                    $this->getContainer()->get($class);
                }
            }
        }
        return $this;
    }

    public function registerServicesDirectory(string $baseDir): static{
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir)) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php')
                continue;
            require_once $file;
            $classes = get_declared_classes();
            foreach ($classes as $class) {
                $ref = new ReflectionClass($class);
                if ($ref->isAbstract() || $ref->isInterface())
                    continue;
                if ($ref->getAttributes(AcWebService::class)) {
                    $this->getContainer()->get($class);
                }
            }
        }
        return $this;
    }

    public function registerViewsDirectory(string $baseDir): static{
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir)) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php')
                continue;
            require_once $file;
            $classes = get_declared_classes();
            foreach ($classes as $class) {
                $ref = new ReflectionClass($class);
                if ($ref->isAbstract() || $ref->isInterface())
                    continue;
                if ($ref->getAttributes(AcWebView::class)) {
                    $this->getContainer()->get($class);
                }
            }
        }
        return $this;
    }

    public function route(string $url, callable $handler, string $method,?AcApiDocRoute $acApiDocRoute = null): static{
        $routeKey = $method . '>' . $url;
        $handlerReflection = new ReflectionFunction($handler);
        $acApiDocRoute = $this->getRouteDocFromHandlerReflection(handlerReflection:$handlerReflection,acApiDocRoute: $acApiDocRoute);
        $this->routeDefinitions[$routeKey] = AcWebRouteDefinition::instanceFromJson(jsonData: [
            AcWebRouteDefinition::KEY_URL => $url,
            AcWebRouteDefinition::KEY_METHOD => strtolower($method),
            AcWebRouteDefinition::KEY_HANDLER => $handler,
            AcWebRouteDefinition::KEY_DOCUMENTATION => $acApiDocRoute
        ]);
        return $this;
    }

    public function serve(): static{
        $request = new AcWebRequest();
        $url = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if (str_starts_with($url, $this->urlPrefix)) {
            $url = substr($url, strlen($this->urlPrefix));
        }
        $request->url = $url;
        $request->method = strtolower($_SERVER['REQUEST_METHOD']);
        $request->headers = getallheaders();
        $request->get = $_GET;
        $request->post = $_POST;
        $request->cookies = $_COOKIE;
        $request->body = json_decode(file_get_contents('php://input'), true) ?? [];
        $foundHandler = false;
        $routeKey = $request->method . '>' . $url;
        $routeDefinition = null;
        if (isset($this->routeDefinitions[$routeKey])) {
            $routeDefinition = $this->routeDefinitions[$routeKey];
        }
        foreach ($this->routeDefinitions as $route) {
            if (!$foundHandler && $route->method === $request->method && $this->matchPath($route->url, $request->url)) {
                $routeDefinition = $route;
                break;
            }
        }
        if ($routeDefinition != null) {
            $foundHandler = true;
            $controller = null;
            $method = null;
            if ($routeDefinition->controller != null) {
                $controller = new $routeDefinition->controller;
                $method = new ReflectionMethod($controller, $routeDefinition->handler);
            } else {
                $method = new ReflectionFunction($routeDefinition->handler);
            }
            $args = [];
            $request->pathParameters = $this->extractPathParams($routeDefinition->url, $request->url);
            foreach ($method->getParameters() as $parameter) {
                $valueSet = false;
                $parameterType = $parameter->getType()?->getName();
                $attrs = $parameter->getAttributes();
                if($parameterType == AcWebRequest::class) {
                    $args[] = $request;
                    $valueSet = true;
                }
                else{
                    foreach ($attrs as $attribute) {
                        $instance = $attribute->newInstance();
                        $key = $attribute->getArguments()[0] ?? $parameter->getName();
                        if ($instance instanceof AcWebValueFromPath) {
                            if (isset($request->pathParameters[$key])) {
                                $args[] = $request->pathParameters[$key];
                                $valueSet = true;
                            }
                        } elseif ($instance instanceof AcWebValueFromQuery) {
                            if (isset($request->get[$key])) {
                                $args[] = $request->get[$key];
                                $valueSet = true;
                            }
                        } elseif ($instance instanceof AcWebValueFromForm) {
                            if (isset($request->post[$key])) {
                                $args[] = $request->post[$key];
                                $valueSet = true;
                            }
                        } elseif ($instance instanceof AcWebValueFromHeader) {
                            if (isset($request->headers[$key])) {
                                $args[] = $request->headers[$key];
                                $valueSet = true;
                            }
                        } elseif ($instance instanceof AcWebValueFromCookie) {
                            if (isset($request->cookies[$key])) {
                                $args[] = $request->cookies[$key];
                                $valueSet = true;
                            }
                        } elseif ($instance instanceof AcWebValueFromBody) {
                            if ($parameterType && class_exists($parameterType)) {
                                $object = new $parameterType();
                                AcJsonUtils::setInstancePropertiesFromJsonData(instance: $object, jsonData: $request->body);
                                $args[] = $object;
                                $valueSet = true;
                            }
                        }
                    }
                }                
                if (!$valueSet) {
                    $args[] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
                }
            }
            if ($controller != null) {
                call_user_func_array([$controller, $routeDefinition->handler], $args);
            } else {
                call_user_func_array($routeDefinition->handler, $args);
            }
        }
        if (!$foundHandler) {
            if ($this->isValidStaticPath($url)) {
                $this->serveStaticFiles($url);
                $foundHandler = true;
            }
        }
        if (!$foundHandler) {
            http_response_code(404);
            echo json_encode(['error' => 'Route not found','routes']);
        }
        return $this;
    }

    private function serveStaticFiles(string $uri): bool {
        foreach ($this->staticFiles as $staticConfig) {
            $prefix = $staticConfig["prefix"];
            $baseDir = $staticConfig["directory"];
            if (str_starts_with($uri, $prefix)) {
                $relativePath = substr($uri, strlen($prefix));
                $filePath = realpath($baseDir . DIRECTORY_SEPARATOR . ltrim($relativePath, '/'));
                if ($filePath && str_starts_with($filePath, $baseDir) && is_file($filePath)) {
                    $mimeType = AcFileUtils::getMimeTypeFromPath($filePath);
                    header("Content-Type: $mimeType");
                    header("Content-Length: " . filesize($filePath));
                    readfile($filePath);
                    return true;
                }
            }
        }
        return false;
    }

    public function staticFiles(string $directory, string $urlPrefix = ""): static {
        if (!str_starts_with($urlPrefix, "/")) {
            $urlPrefix = "/" . $urlPrefix;
        }
        $this->staticFiles[] = [
            "directory" => realpath($directory),
            "prefix" => rtrim(string: $urlPrefix, characters: "/")
        ];
        return $this;
    }

    public function trace(string $url, callable $handler,?AcApiDocRoute $acApiDocRoute = null): static {
        $this->route(url: $url, handler: $handler, method: AcEnumHttpMethod::TRACE,acApiDocRoute: $acApiDocRoute);
        return $this;
    }

}

?>
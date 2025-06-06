<?php
namespace AcWeb\Models;

use AcWeb\Enums\AcEnumWebResponseType;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Enums\AcEnumHttpResponseCode;
use Autocode\Utils\AcJsonUtils;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';

class AcWebResponse {    
    const KEY_COOKIES = 'cookies';
    const KEY_CONTENT = 'content';
    const KEY_HEADERS = 'headers';
    const KEY_RESPONSE_CODE = 'response_code';
    const KEY_RESPONSE_TYPE = 'response_type';
    const KEY_SESSION = 'session';
    public array $cookies=[];  
    public mixed $content;
    public array $headers = [];

    #[AcBindJsonProperty(key: AcWebResponse::KEY_RESPONSE_CODE)]
    public int $responseCode = 0;

    #[AcBindJsonProperty(key: AcWebResponse::KEY_RESPONSE_TYPE)]
    public string $responseType = AcEnumWebResponseType::TEXT;
    public array $session=[];

    public static function internalError(mixed $data, ?int $responseCode = AcEnumHttpResponseCode::INTERNAL_SERVER_ERROR): AcWebResponse {
        $response = new AcWebResponse();
        $response->responseCode = $responseCode;
        return $response;
    }
    

    public static function json(mixed $data, ?int $responseCode = AcEnumHttpResponseCode::OK): AcWebResponse {
        $response = new AcWebResponse();
        $response->responseCode = $responseCode;
        $response->responseType = AcEnumWebResponseType::JSON;
        $response->data = $data;
        $response->headers['Content-Type'] = "application/json";
        header('Content-Type: application/json');
        echo json_encode($data);
        return $response;
    }

    public static function notFound(): AcWebResponse {
        $response = new AcWebResponse();
        $response->responseCode = AcEnumHttpResponseCode::NOT_FOUND;
        $response->responseType = AcEnumWebResponseType::NOT_FOUND;
        return $response;
    }
    
    public static function raw(mixed $content, ?int $responseCode = AcEnumHttpResponseCode::OK,?array $headers = []): AcWebResponse {
        $response = new AcWebResponse();
        $response->responseCode = $responseCode;
        $response->responseType = AcEnumWebResponseType::RAW;
        $response->content = $content;
        return $response;
    }

    public static function redirect(string $url, ?int $responseCode = AcEnumHttpResponseCode::TEMPORARY_REDIRECT): AcWebResponse {
        header("Location: $url");
        return new AcWebResponse();
    }

    public static function view(string $template, array $vars = []): AcWebResponse {
        extract($vars);
        include __DIR__ . "/views/$template.php";
        return new AcWebResponse();
    }    

    public function fromJson(array $jsonData = []): static {
        AcJsonUtils::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcJsonUtils::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

}

?>
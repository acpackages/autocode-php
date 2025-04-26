<?php
namespace AcWeb\ApiDocs\Models;

use AcWeb\ApiDocs\Modelss\AcApiDocLicense;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;
class AcApiDoc {
    const KEY_CONTACT = "contact";
    const KEY_COMPONENTS = "components";
    const KEY_DESCRIPTION = "description";
    const KEY_LICENSE = "license";
    const KEY_MODELS = "models";
    const KEY_PATHS = "paths";
    const KEY_SERVERS = "servers";
    const KEY_TAGS = "tags";    
    const KEY_TERMS_OF_SERVICE = "termsOfService";
    const KEY_TITLE = "title";
    const KEY_VERSION = "version";
    public ?AcApiDocContact $contact = null;
    public array $components = [];
    public string $description = "";
    public ?AcApiDocLicense $license = null;   
    
    #[AcBindJsonProperty(key: AcApiDoc::KEY_MODELS,arrayType:AcApiDocModel::class)]
    public array $models = [];

    #[AcBindJsonProperty(key: AcApiDoc::KEY_PATHS,arrayType:AcApiDocPath::class)]
    public array $paths = [];

    #[AcBindJsonProperty(key: AcApiDoc::KEY_SERVERS,arrayType:AcApiDocServer::class)]
    public array $servers = [];

    #[AcBindJsonProperty(key: AcApiDoc::KEY_TAGS,arrayType:AcApiDocServer::class)]
    public array $tags = [];

    #[AcBindJsonProperty(key: AcApiDoc::KEY_TERMS_OF_SERVICE)]
    public string $termsOfService = "";
    public string $title = "";
    public string $version = "";
    
    public static function instanceFromJson(array $jsonData): static {
        $instance = new self();
        $instance->fromJson(jsonData: $jsonData);
        return $instance;
    }

    public function addModel(AcApiDocModel $model): static{
        $this->models[$model->name] = $model;
        return $this;
    }

    public function addPath(AcApiDocPath $path): static{
        $this->paths[] = $path;
        return $this;
    }

    public function addServer(AcApiDocServer $server): static{
        $this->servers[] = $server;
        return $this;
    }

    public function addTag(AcApiDocTag $tag): static{
        $this->tags[] = $tag;
        return $this;
    }

    public function fromJson(array $jsonData): static {
        AcUtilsJson::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcUtilsJson::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

}

?>
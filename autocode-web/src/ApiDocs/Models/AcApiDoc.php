<?php
namespace AcWeb\ApiDocs\Models;

use AcWeb\ApiDocs\Modelss\AcApiDocLicense;
use Autocode\Models\AcJsonBindConfig;
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
    public AcJsonBindConfig $acJsonBindConfig;
    public ?AcApiDocContact $contact = null;
    public array $components = [];
    public string $description = "";
    public ?AcApiDocLicense $license = null;   
    public array $models = [];
    public array $paths = [];
    public array $servers = [];
    public array $tags = [];
    public string $termsOfService = "";
    public string $title = "";
    public string $version = "";
    
    public static function instanceFromJson(array $jsonData): AcApiDoc {
        $instance = new AcApiDoc();
        $instance->fromJson(jsonData: $jsonData);
        return $instance;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::instanceFromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_CONTACT => "contact",
                self::KEY_COMPONENTS => "components",
                self::KEY_DESCRIPTION => "description",
                self::KEY_LICENSE => "license",
                self::KEY_MODELS => "models",
                self::KEY_PATHS => "paths",
                self::KEY_SERVERS => "servers",
                self::KEY_TAGS => "tags",
                self::KEY_TERMS_OF_SERVICE => "termsOfService",
                self::KEY_TITLE => "title",
                self::KEY_VERSION => "version",
            ]        
        ]);
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
        if (isset($jsonData[self::KEY_CONTACT])) {
            $this->contact = AcApiDocContact::instanceFromJson($jsonData[self::KEY_CONTACT]);
            unset($jsonData[self::KEY_CONTACT]);
        }
        if (isset($jsonData[self::KEY_LICENSE])) {
            $this->url = AcApiDocLicense::instanceFromJson($jsonData[self::KEY_LICENSE]);
            unset($jsonData[self::KEY_LICENSE]);
        }
        if (isset($jsonData[self::KEY_MODELS])) {
            $models = [];
            foreach ($jsonData[self::KEY_MODELS] as $modelJson) {
                $model = AcApiDocModel::instanceFromJson($modelJson);
                $models[$model->name] = $model;
            }
            $this->models = $models;
            unset($jsonData[self::KEY_MODELS]);
        }
        if (isset($jsonData[self::KEY_PATHS])) {
            $paths = [];
            foreach ($jsonData[self::KEY_PATHS] as $pathJson) {
                $paths[] = AcApiDocPath::instanceFromJson($pathJson);
            }
            $this->paths = $paths;
            unset( $jsonData[self::KEY_PATHS]);
        }
        if (isset($jsonData[self::KEY_SERVERS])) {
            $servers = [];
            foreach ($jsonData[self::KEY_SERVERS] as $serverJson) {
                $servers[] = AcApiDocServer::instanceFromJson($serverJson);
            }
            $this->servers = $servers;
            unset($jsonData[self::KEY_SERVERS]);
        }
        if (isset($jsonData[self::KEY_TAGS])) {
            $tags = [];
            foreach ($jsonData[self::KEY_TAGS] as $tagJson) {
                $tags[] = AcApiDocTag::instanceFromJson($tagJson);
            }
            $this->tags = $tags;
            unset( $jsonData[self::KEY_TAGS]);
        }
        AcUtilsJson::bindInstancePropertiesFromJson(instance: $this, data: $jsonData);
        return $this;
    }

    public function toJson(): array {
        $result = [];
        if($this->contact!=null){
            $result[self::KEY_CONTACT] = $this->contact->toJson();
        }
        if($this->description!=""){
            $result[self::KEY_DESCRIPTION] = $this->description;
        }
        if($this->license!=null){
            $result[self::KEY_LICENSE] = $this->license->toJson();
        }
        if(sizeof($this->models)>0){
            $models = [];
            foreach($this->models as $model){
                $models[] = $model->toJson();
            }
            $result[self::KEY_MODELS] = $models;
        }
        if(sizeof($this->paths)>0){
            $paths = [];
            foreach($this->paths as $path){
                $paths[] = $path->toJson();
            }
            $result[self::KEY_PATHS] = $paths;
        }
        if(sizeof($this->servers)>0){
            $servers = [];
            foreach($this->servers as $server){
                $servers[] = $server->toJson();
            }
            $result[self::KEY_SERVERS] = $servers;
        }
        if($this->termsOfService!=""){
            $result[self::KEY_TERMS_OF_SERVICE] = $this->termsOfService;
        }
        if(sizeof($this->tags)>0){
            $result[self::KEY_TAGS] = $this->tags;
        }
        if($this->termsOfService!=""){
            $result[self::KEY_TITLE] = $this->title;
        }
        if($this->termsOfService!=""){
            $result[self::KEY_VERSION] = $this->version;
        }
        return $result;
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}

?>
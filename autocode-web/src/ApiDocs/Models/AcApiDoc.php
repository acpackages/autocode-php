<?php
namespace AcWeb\ApiDocs\Models;

use AcWeb\ApiDocs\Modelss\AcApiDocLicense;
class AcApiDoc {
    const KEY_CONTACT = "contact";
    const KEY_COMPONENTS = "components";
    const KEY_DESCRIPTION = "description";
    const KEY_LICENSE = "license";
    const KEY_MODELS = "models";
    const KEY_PATHS = "paths";
    const KEY_SERVERS = "servers";
    const KEY_TERMS_OF_SERVICE = "termsOfService";
    const KEY_TITLE = "title";
    const KEY_TAGS = "tags";    
    const KEY_VERSION = "version";

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
    
    public static function fromJson(array $jsonData): AcApiDoc {
        $instance = new AcApiDoc();
        $instance->setValuesFromJson(jsonData: $jsonData);
        return $instance;
    }

    public function addModel(AcApiDocModel $model){
        $this->models[$model->name] = $model;
    }

    public function addPath(AcApiDocPath $path){
        $this->paths[] = $path;
    }

    public function addServer(AcApiDocServer $server){
        $this->servers[] = $server;
    }

    public function addTag(AcApiDocTag $tag){
        $this->tags[] = $tag;
    }

    public function setValuesFromJson(array $jsonData) {
        if (isset($jsonData[self::KEY_CONTACT])) {
            $this->contact = AcApiDocContact::fromJson($jsonData[self::KEY_CONTACT]);
        }
        if (isset($jsonData[self::KEY_DESCRIPTION])) {
            $this->description = $jsonData[self::KEY_DESCRIPTION];
        }
        if (isset($jsonData[self::KEY_LICENSE])) {
            $this->url = AcApiDocLicense::fromJson($jsonData[self::KEY_LICENSE]);
        }
        if (isset($jsonData[self::KEY_MODELS])) {
            $models = [];
            foreach ($jsonData[self::KEY_MODELS] as $modelJson) {
                $model = AcApiDocModel::fromJson($modelJson);
                $models[$model->name] = $model;
            }
            $this->models = $models;
        }
        if (isset($jsonData[self::KEY_PATHS])) {
            $paths = [];
            foreach ($jsonData[self::KEY_PATHS] as $pathJson) {
                $paths[] = AcApiDocPath::fromJson($pathJson);
            }
            $this->paths = $paths;
        }
        if (isset($jsonData[self::KEY_SERVERS])) {
            $servers = [];
            foreach ($jsonData[self::KEY_SERVERS] as $serverJson) {
                $servers[] = AcApiDocServer::fromJson($serverJson);
            }
            $this->servers = $servers;
        }
        if (isset($jsonData[self::KEY_TAGS])) {
            $tags = [];
            foreach ($jsonData[self::KEY_TAGS] as $tagJson) {
                $tags[] = AcApiDocTag::fromJson($tagJson);
            }
            $this->tags = $tags;
        }
        if (isset($jsonData[self::KEY_TERMS_OF_SERVICE])) {
            $this->termsOfService = $jsonData[self::KEY_TERMS_OF_SERVICE];
        }
        if (isset($jsonData[self::KEY_TITLE])) {
            $this->title = $jsonData[self::KEY_TITLE];
        }
        if (isset($jsonData[self::KEY_VERSION])) {
            $this->version = $jsonData[self::KEY_VERSION];
        }
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
<?php
namespace AcWeb\ApiDocs\Model;

use AcWeb\ApiDocs\Models\AcApiDocLicense;
class AcApiDoc {
    const KEY_CONTACT = "contact";
    const KEY_DESCRIPTION = "description";
    const KEY_LICENSE = "license";
    const KEY_SERVERS = "servers";
    const KEY_TERMS_OF_SERVICE = "termsOfService";
    const KEY_TITLE = "title";
    const KEY_VERSION = "version";

    public AcApiDocContact $contact = new AcApiDocContact();
    public string $description = "";
    public AcApiDocLicense $license = new AcApiDocLicense();    
    public array $servers = "";
    public string $termsOfService = "";
    public string $title = "";
    public string $version = "0.0.0";
    
    public static function fromJson(array $jsonData): AcApiDoc {
        $instance = new AcApiDoc();
        $instance->setValuesFromJson(jsonData: $jsonData);
        return $instance;
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
        if (isset($jsonData[self::KEY_SERVERS])) {
            $servers = [];
            foreach ($jsonData[self::KEY_SERVERS] as $serverJson) {
                $servers[] = AcApiDocServer::fromJson($serverJson);
            }
            $this->servers = $servers;
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
        $servers = [];
        foreach ($this->servers as $server) {
            $servers[] = $server->toJson();
        }
        return [
            self::KEY_CONTACT => $this->contact->toJson(),
            self::KEY_DESCRIPTION => $this->description,
            self::KEY_LICENSE => $this->license->toJson(),
            self::KEY_SERVERS => $servers,
            self::KEY_TERMS_OF_SERVICE => $this->termsOfService,
            self::KEY_TITLE => $this->title,
            self::KEY_VERSION => $this->version,
        ];
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}

?>
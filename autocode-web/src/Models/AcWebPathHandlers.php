<?php
namespace AcWeb\Models;

use Autocode\Enums\AcEnumHttpMethod;


class AcWebPathHandlers {
    public array $delete = [];
    public array $get = [];
    public array $post = [];
    public array $put = [];
    public array $request = [];

    function getPathHandler(string $url,string $method){
        $result = null;
        $function = null;
        $parameters = [];
        if($method == AcEnumHttpMethod::DELETE){
            if(sizeof($this->delete) > 0){
                $function = $this->delete[0]['function'];
                $parameters = $this->delete[0]['parameters'];
            }
        }
        if($method == AcEnumHttpMethod::GET){
            if(sizeof($this->get) > 0){
                $function = $this->get[0]['function'];
                $parameters = $this->get[0]['parameters'];
            }
        }
        if($method == AcEnumHttpMethod::POST){
            if(sizeof($this->post) > 0){
                $function = $this->post[0]['function'];
                $parameters = $this->post[0]['parameters'];
            }
        }
        if($method == AcEnumHttpMethod::PUT){
            if(sizeof($this->put) > 0){
                $function = $this->put[0]['function'];
                $parameters = $this->put[0]['parameters'];
            }
        }
        if($function == null){
            if(sizeof($this->request) > 0){
                $function = $this->request[0]['function'];
                $parameters = $this->request[0]['parameters'];
            }
        }
        if($function!=null){
            $result = $function($parameters);
        }
        return $result;
    }

}

?>
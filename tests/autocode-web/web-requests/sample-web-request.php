<?php 

use AcWeb\Core\AcWebPath;
    class SampleWebRequest extends AcWebPath {
        function handleRequest(){
            $this->responseHtml("Test Sample Web Request");
        }
    }
?>
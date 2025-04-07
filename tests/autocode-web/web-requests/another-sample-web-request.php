<?php 

use AcWeb\Core\AcWebPath;
    class AnotherSampleWebRequest extends AcWebPath {
        function handleRequest(){
            $this->responseHtml("Test Another Sample Web Request");
        }
    }
?>
<?php 
require '../../autocode-documentation/vendor/autoload.php';
use AcDoc\Core\AcDocParser;
$docParser = new AcDocParser();
print_r(json_encode($docParser->parseFile(__DIR__."./../../autocode-documentation/src/Models/AcDocExample.php"), JSON_PRETTY_PRINT));
?>
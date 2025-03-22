Ac-Extensions Test
<?php 
require '../../autocode-extensions/vendor/autoload.php';
use AcExtensions\AcExtensionMethods;

$variable = "1";
echo "<br>";
if(AcExtensionMethods::stringIsEmpty($variable)){
    echo "$variable is empty";
}
if(AcExtensionMethods::stringIsNotEmpty($variable)){
    echo "$variable is not empty";
}
?>
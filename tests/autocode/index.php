Autocode Tests
<br>
<?php 
require '../../autocode/vendor/autoload.php';
use Autocode\Autocode;
use Autocode\AcEvents;
$acLogger = new AcEvents();
if(Autocode::isBrowser()){
    echo "Is Browser";
}
else{
    echo "Not Browser";
}

?>
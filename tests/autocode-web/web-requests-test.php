Database Tests<br><br>
<?php 
require_once '../../autocode-data-dictionary/vendor/autoload.php';
require_once '../../autocode-web/vendor/autoload.php';
require_once __DIR__.'./web-requests/another-sample-web-request.php';
require_once __DIR__.'./web-requests/sample-web-request.php';
use AcDataDictionary\AcDataDictionary;
use AcWeb\DataDictionary\AcDataDictionaryAutoApi;
use AcWeb\Core\AcWeb;
use AcWeb\Core\AcWebPath;

function executeRoutes() {
    $acWeb = new AcWeb();
    $acWeb->get('/api/sample-web-request',function ():AcWebPath{
        return new SampleWebRequest();
    });
    $acWeb->request('/api/another-web-request',function ():AcWebPath{
        return new AnotherSampleWebRequest();
    });    

    $dataDictionaryJson = file_get_contents('../assets/data_dictionary.json');
    AcDataDictionary::registerDataDictionaryJsonString($dataDictionaryJson);
    $acDataDictionaryAutoApi = new AcDataDictionaryAutoApi(acWeb: $acWeb);
    $acDataDictionaryAutoApi->urlPrefix = '/api';
    $acDataDictionaryAutoApi->generate();
    $acWeb->serve("/tests/autocode-web");
    
}
executeRoutes();
// print_r(AcDataDictionary::$dataDictionaries);
?>


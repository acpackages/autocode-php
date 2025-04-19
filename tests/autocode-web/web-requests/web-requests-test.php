<?php 
require_once __DIR__.'./../../autocode-data-dictionary/vendor/autoload.php';
require_once __DIR__.'./../../autocode-web/vendor/autoload.php';
require_once __DIR__.'./web-requests/another-sample-web-request.php';
require_once __DIR__.'./web-requests/sample-web-request.php';
require_once __DIR__.'./web-requests/account-save-request.php';
use AcDataDictionary\Models\AcDataDictionary;
use AcWeb\ApiDocs\Core\AcApiDocs;
use AcWeb\ApiDocs\Swagger\AcApiSwagger;
use AcWeb\DataDictionary\AcDataDictionaryAutoApi;
use AcWeb\Core\AcWeb;
use AcWeb\Core\AcWebPath;

function executeRoutes() {    
    $acApiSwagger = new AcApiSwagger();
    $acWeb = new AcWeb();
    $acWeb->addHostUrl('http://autocode.localhost/tests/autocode-web');
    $acWeb->get('/api/sample-web-request',function ():AcWebPath{
        return new SampleWebRequest();
    });
    $acWeb->post('/api/another-web-request',function ():AcWebPath{
        return new AnotherSampleWebRequest();
    });   
    $acWeb->post('/api/account-save',function ():AcWebPath{
        return new AccountSaveRequest();
    });    

    $dataDictionaryJson = file_get_contents('../assets/data_dictionary.json');
    AcDataDictionary::registerDataDictionaryJsonString($dataDictionaryJson);
    // $acDataDictionaryAutoApi = new AcDataDictionaryAutoApi(acWeb: $acWeb);
    // $acDataDictionaryAutoApi->urlPrefix = '/api';
    // $acDataDictionaryAutoApi->generate();
    $acWeb->serve("/tests/autocode-web");
    $acApiDoc = $acWeb->getApiDoc();
    // print_r($acApiDoc->toJson());
    $acApiSwagger->acApiDoc = $acApiDoc;
    // print_r($acApiSwagger->generateJson());
}
executeRoutes();
// print_r(AcDataDictionary::$dataDictionaries);
?>


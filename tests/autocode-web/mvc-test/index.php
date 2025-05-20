<?php
require_once __DIR__ . "./../../../autocode-web/vendor/autoload.php";
require_once __DIR__ . "./../../../autocode-data-dictionary/vendor/autoload.php";
require_once __DIR__ . "./../../../autocode-sql/vendor/autoload.php";
require_once __DIR__ . "./Controllers/UserController.php";
use AcDataDictionary\Models\AcDataDictionary;
use AcSql\Database\AcSqlDatabase;
use Autocode\Enums\AcEnumSqlDatabaseType;
use AcSql\Models\AcSqlConnection;
use AcWeb\Core\AcWeb;
use AcWeb\DataDictionary\AcDataDictionaryAutoApi;
$app = new AcWeb();
$app->urlPrefix = "/tests/autocode-web/mvc-test";
$app->registerController(UserController::class);
$app->addHostUrl('http://autocode.localhost/tests/autocode-web/mvc-test');

/* Data Dictionary Start */

$dataDictionaryJson = file_get_contents(filename: __DIR__ . './../../assets/data_dictionary.json');
AcDataDictionary::registerDataDictionaryJsonString($dataDictionaryJson);
$acDataDictionaryAutoApi = new AcDataDictionaryAutoApi(acWeb: $app);
$acDataDictionaryAutoApi->urlPrefix = '/api';
$acDataDictionaryAutoApi->includeTable('accounts');
$acDataDictionaryAutoApi->includeTable('apis');
$acDataDictionaryAutoApi->includeTable('companies');
$acDataDictionaryAutoApi->generate();

/* Data Dictionary End */

/* Database SQL Start */

AcSqlDatabase::$databaseType = AcEnumSqlDatabaseType::MYSQL;
$acSqlConnection = AcSqlConnection::instanceFromJson([
    "username" => 'root',
    "password" => '',
    "hostname" => 'localhost',
    "port" => 3306,
    "database" => 'unifi_tradeops',
]);
AcSqlDatabase::$sqlConnection = $acSqlConnection;

/* Database SQL End */

$app->serve();
// print_r($app);
?>
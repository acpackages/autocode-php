Schema Manager<br><br>
<?php 
require_once __DIR__.'./../../autocode-sql/vendor/autoload.php';
require __DIR__.'./../../autocode-data-dictionary/vendor/autoload.php';
use AcDataDictionary\Models\AcDataDictionary;
use AcSql\Database\AcSqlDatabase;
use AcSql\Database\AcSqlDbSchemaManager;
use Autocode\Enums\AcEnumSqlDatabaseType;
use AcSql\Models\AcSqlConnection;
$dataDictionaryJson = file_get_contents('../assets/data_dictionary.json');
AcDataDictionary::registerDataDictionaryJsonString($dataDictionaryJson);
AcSqlDatabase::$databaseType = AcEnumSqlDatabaseType::MYSQL;
$acSqlConnection = AcSqlConnection::instanceFromJson([
    "username" => 'root',
    "password" => '',
    "hostname" => 'localhost',
    "port" => 3306,
    "database" => 'test_schema_db',
]);
AcSqlDatabase::$sqlConnection = $acSqlConnection;
$acSqlDbSchemaManager = new AcSqlDbSchemaManager();
$initResult = $acSqlDbSchemaManager->initDatabase();
print_r($initResult);
echo "<br><br>";
// print_r(AcDataDictionary::$dataDictionaries);
?>
Schema Manager<br><br>
<?php 
require_once '../../autocode-sql/vendor/autoload.php';
require '../../autocode-data-dictionary/vendor/autoload.php';
use AcDataDictionary\AcDataDictionary;
use AcSql\Database\AcSqlDatabase;
use AcSql\Database\AcSqlDbSchemaManager;
use AcSql\Enums\AcEnumSqlDatabaseType;
use AcSql\Models\AcSqlConnection;
$dataDictionaryJson = file_get_contents('../assets/data_dictionary.json');
AcDataDictionary::registerDataDictionaryJsonString($dataDictionaryJson);
AcSqlDatabase::$databaseType = AcEnumSqlDatabaseType::MYSQL;
$acSqlConnection = AcSqlConnection::fromJson([
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
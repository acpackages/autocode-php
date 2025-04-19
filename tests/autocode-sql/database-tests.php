Database Tests<br><br>
<?php 
require_once __DIR__.'./../../autocode-sql/vendor/autoload.php';
require __DIR__.'./../../autocode-data-dictionary/vendor/autoload.php';
use AcDataDictionary\Models\AcDataDictionary;
use AcSql\Database\AcSqlDatabase;
use AcSql\Database\AcSqlDbSchemaManager;
use AcSql\Daos\AcBaseSqlDao;
use AcSql\Daos\AcMysqlDao;
use AcSql\Database\AcSqlDbTable;
use AcSql\Enums\AcEnumSqlDatabaseType;
use AcSql\Models\AcSqlConnection;

function executeTests() {
    
    $operationIndex = 0;
    $operationIndex++;
    $data = [
        "account_name"=>"Cash",
        "account_target"=>"Balance Sheet",
        "account_type"=>"Cash & Equivalents",
    ];
    echo "<br><br><br>". "$operationIndex : Inserting cash account in accounts table...";
    $acSqlTblAccounts = new AcSqlDbTable(tableName:'accounts');
    $result = $acSqlTblAccounts->insertRow(data:$data);
    echo "<br><br><br>". $result->toString();
    if($result->isSuccess()){
        $primaryKeyValue = $result->lastInsertedId;
        echo "<br><br><br>". "Inserted primary key value $primaryKeyValue";
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Updating cash account to bank account...";
        $data = $result->rows[0];
        $data["account_name"] = "Bank";
        $result = $acSqlTblAccounts->updateRow(data:$data);
        echo "<br><br><br>". $result->toString();
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting accounts...";
        $result = $acSqlTblAccounts->getRows();
        echo "<br><br><br>". $result->toString();
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Deleting account...";
        $result = $acSqlTblAccounts->deleteRows(primaryKeyValue:$primaryKeyValue);
        echo "<br><br><br>". $result->toString();
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting accounts...";
        $result = $acSqlTblAccounts->getRows();
        echo "<br><br><br>". $result->toString();
    }
    
    
}



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
executeTests();
// print_r(AcDataDictionary::$dataDictionaries);
?>


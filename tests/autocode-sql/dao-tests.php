<?php 
require_once '../../autocode-sql/vendor/autoload.php';
require_once 'tests.php';
use AcSql\Models\AcSqlConnection;
use AcSql\Daos\AcBaseSqlDao;
use AcSql\Daos\AcMysqlDao;
function executeTests(AcBaseSqlDao $dao) {
    
    $operationIndex = 0;
    $operationIndex++;
    echo "<br><br><br>". "$operationIndex : Checking database...";
    $result = $dao->checkDatabaseExist();
    echo "<br><br><br>". $result->toString();
    
    if ($result->isSuccess()) {
        if (!$result->value) {
            $operationIndex++;
            echo "<br><br><br>". "$operationIndex : Creating database...";
            $result = $dao->createDatabase();
            echo "<br><br><br>". $result->toString();
        }
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Checking test_table exist...";
        $result = $dao->checkTableExist(table:'test_table');
        echo "<br><br><br>". $result->toString();
        
        if ($result->isSuccess()) {
            if (!$result->value) {
                $operationIndex++;
                echo "<br><br><br>". "$operationIndex : Creating test_table...";
                $result = $dao->sqlStatement(statement:'CREATE TABLE test_table (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50));' );
                echo "<br><br><br>". $result->toString();
            }
        }
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Checking test_table exist...";
        $result = $dao->checkTableExist(table:'test_table' );
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Inserting row in test_table...";
        $result = $dao->insertRows(table:'test_table', values:[ 'name' => 'test' ]);
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Inserting row in test_table...";
        $result = $dao->insertRows(table:'test_table', values:[ 'name' => 'another test' ]);
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting rows from test_table...";
        $result = $dao->selectStatement(statement:'SELECT * FROM test_table');
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting rows from test_table with parameter name test...";
        $result = $dao->selectStatement(statement:'SELECT * FROM test_table', parameters:[ ':name' => 'test' ], condition:'name = :name' );
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Updating row in test_table...";
        $result = $dao->updateRows(table:'test_table', values:[ 'name' => 'test_modified' ], condition:'id = :id', parameters:[ ':id' => 1 ] );
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting rows from test_table...";
        $result = $dao->selectStatement(statement:'SELECT * FROM test_table' );
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting rows from test_table with parameter name test_modified...";
        $result = $dao->selectStatement(statement:'SELECT * FROM test_table', parameters:[ ':name' => 'test_modified' ], condition:'name = :name' );
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Deleting row in test_table...";
        $result = $dao->deleteRows(table:'test_table', condition:'id = :id', parameters:[ ':id' => 1 ] );
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting rows from test_table...";
        $result = $dao->selectStatement(statement:'SELECT * FROM test_table' );
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting tables list...";
        $result = $dao->getDatabaseTables();
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting table definition...";
        $result = $dao->getTableDefinition(table:'test_table' );
        echo "<br><br><br>". $result->toString();
    }
    else{
        
    }
}

$dao = new AcMysqlDao();
$acSqlConnection = AcSqlConnection::fromJson([
    "username" => 'root',
    "password" => '',
    "hostname" => 'localhost',
    "port" => 3306,
    "database" => 'test_db',
]);
print_r($acSqlConnection->toJson());
$dao->setSqlConnection($acSqlConnection);
executeTests($dao);
?>
<?php 
require_once __DIR__.'./../../autocode-sql/vendor/autoload.php';
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
        $result = $dao->checkTableExist(tableName:'test_table');
        echo "<br><br><br>". $result->toString();
        
        if ($result->isSuccess()) {
            if (!$result->value) {
                $operationIndex++;
                echo "<br><br><br>". "$operationIndex : Creating test_table...";
                $result = $dao->executeStatement(statement:'CREATE TABLE test_table (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50), row_index INT DEFAULT 0);' );
                echo "<br><br><br>". $result->toString();
                $operationIndex++;
                echo "<br><br><br>". "$operationIndex : Checking test_table exist...";
                $result = $dao->checkTableExist(tableName:'test_table' );
                echo "<br><br><br>". $result->toString();
            }
        }
        
        
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Inserting row in test_table...";
        $result = $dao->insertRow(tableName:'test_table', row:[ 'name' => 'Record 1',"row_index"=>1]);
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Inserting rows in test_table...";
        $result = $dao->insertRows(tableName:'test_table', rows:[
            [ 'name' => 'Record 2',"row_index"=>2],
            [ 'name' => 'Record 3',"row_index"=>3],
            [ 'name' => 'Record 4',"row_index"=>4]
        ]);
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting rows from test_table...";
        $result = $dao->getRows(statement:'SELECT * FROM test_table');
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting rows from test_table with parameter row_index = 1...";
        $result = $dao->getRows(statement:'SELECT * FROM test_table', parameters:[ ':row_index' => 1 ], condition:'row_index = :row_index' );
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Updating row in test_table...";
        $result = $dao->updateRow(tableName:'test_table', row:[ 'name' => 'Record 1 Modified' ], condition:'row_index = @index', parameters:[ '@index' => 1 ] );
        echo "<br><br><br>". $result->toString();

        // $operationIndex++;
        // echo "<br><br><br>". "$operationIndex : Updating rows in test_table...";
        // $result = $dao->updateRow(tableName:'test_table', row:[ 'name' => '> Modified' ], condition:'index = @index', parameters:[ '@index' => 1 ] );
        // echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting rows from test_table...";
        $result = $dao->getRows(statement:'SELECT * FROM test_table' );
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting rows from test_table with parameter row_idex = 1...";
        $result = $dao->getRows(statement:'SELECT * FROM test_table', parameters:[ ':row_index' => 1 ], condition:'row_index = :row_index' );
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Deleting row in test_table...";
        $result = $dao->deleteRows(tableName:'test_table', condition:'row_index = :id', parameters:[ ':id' => 1 ] );
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting rows from test_table...";
        $result = $dao->getRows(statement:'SELECT * FROM test_table' );
        echo "<br><br><br>". $result->toString();
    }
    else{
        
    }
}

function executeSchemaTests(AcBaseSqlDao $dao) {
    
    $operationIndex = 0;
    $operationIndex++;
    echo "<br><br><br>". "$operationIndex : Checking database...";
    $result = $dao->checkDatabaseExist();
    echo "<br><br><br>". $result->toString();
    
    if ($result->isSuccess()) {
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting database tables...";
        $result = $dao->getDatabaseTables();
        echo "<br><br><br>". $result->toString();
        
        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting database views...";
        $result = $dao->getDatabaseViews();
        echo "<br><br><br>". $result->toString();

        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting database triggers...";
        $result = $dao->getDatabaseTriggers();
        echo "<br><br><br>". $result->toString();

        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting database stored procedures...";
        $result = $dao->getDatabaseStoredProcedures();
        echo "<br><br><br>". $result->toString();

        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting database functions...";
        $result = $dao->getDatabaseFunctions();
        echo "<br><br><br>". $result->toString();

        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting table details of companies...";
        $result = $dao->getTableColumns(tableName:'companies');
        echo "<br><br><br>". $result->toString();

        $operationIndex++;
        echo "<br><br><br>". "$operationIndex : Getting view details of vw_companies...";
        $result = $dao->getViewColumns(viewName:'vw_companies');
        echo "<br><br><br>". $result->toString();
    }
    else{
        
    }
}

$dao = new AcMysqlDao();
$acSqlConnection = AcSqlConnection::instanceFromJson([
    "username" => 'root',
    "password" => '',
    "hostname" => 'localhost',
    "port" => 3306,
    "database" => 'test_db',
]);
$dao->setSqlConnection($acSqlConnection);
executeTests($dao);
// executeSchemaTests($dao);
?>
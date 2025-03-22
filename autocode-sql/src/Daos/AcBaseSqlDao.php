<?php

namespace AcSql\Daos;

require_once __DIR__.' ./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../Enums/AcEnumRowOperation.php';
require_once __DIR__.'./../Enums/AcEnumSelectMode.php';
require_once __DIR__.'./../Enums/AcEnumTableFieldFormat.php';
require_once __DIR__.'./../Models/AcSqlConnection.php';
require_once __DIR__.'./../Models/AcSqlDaoResult.php';

use Autocode\AcLogger;
use Autocode\AcResult;
use Autocode\Enums\AcEnumLogType;
use AcSql\Enums\AcEnumRowOperation;
use AcSql\Enums\AcEnumSelectMode;
use AcSql\Enums\AcEnumTableFieldFormat;
use AcSql\Models\AcSqlConnection;
use AcSql\Models\AcSqlDaoResult;

use Exception;
use PDO;

class AcBaseSqlDao {
    protected AcLogger $logger;
    protected AcSqlConnection $sqlConnection;

    public function __construct() {
        $this->logger = new AcLogger(logType: AcEnumLogType::HTML);
        $this->sqlConnection = new AcSqlConnection();
    }

    public function checkDatabaseExist(): AcResult {
        $result = new AcResult();
        try {
            // Database existence check logic
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function createDatabase(): AcResult {
        $result = new AcResult();
        try {
            // Database creation logic
        } catch (Exception $ex) {
            $this->logger->log("Error in createDatabase: " . $ex->getMessage());
        }
        return $result;
    }

    public function checkTableExist(string $table): AcResult {
        $result = new AcResult();
        try {
            // Table existence check logic
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function deleteRows(string $table, string $condition = "", array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            // Row deletion logic
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function formatRow(array $row, array $formatColumns = []): array {
        return $row;
    }

    public function getConnectionObject() {
        try {
            return $this->sqlConnection->getPdo();
        } catch (Exception $ex) {
            $this->logger->log("Error in getConnectionObject: " . $ex->getMessage());
        }
        return null;
    }

    public function getDatabaseTables(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            // Fetch database tables logic
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function getTableDefinition(string $table): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            // Table definition logic
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function insertRows(string $table, array $values, ?string $primaryKeyColumn): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            // Row insertion logic
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function selectStatement(string $statement, ?string $mode = AcEnumSelectMode::LIST, ?string $condition = "", ?array $parameters = [], ?array $formatColumns = [], ?bool $firstRowOnly = false): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            // Select statement logic
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function setSqlConnection(AcSqlConnection $sqlConnection): AcResult {
        $result = new AcResult();
        try {
            $this->sqlConnection = $sqlConnection;
            $result->setSuccess(["value" => true]);
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function setSqlConnectionFromJson(array $jsonData): AcResult {
        $result = new AcResult();
        try {
            $this->sqlConnection->setValuesFromJson($jsonData);
            $result->setSuccess(["value" => true]);
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function setSqlStatementParameters(string &$statement, array &$parameters, array &$values) {
        // foreach ($parameters as $key => $value) {
        //     if(strpos($statement, $key) > 0){
        //         $values[$key] = $value;
        //     }
            
        // }
            while (strpos($statement, $key) !== false) {
                $valueIndex = sizeof($values);
                $beforeQueryString = substr($statement, 0, strpos($statement, $key));
                $parameterIndex = substr_count($beforeQueryString, '?');
                if (is_array($value)) {
                    $valueStrings = [];
                    for($i=0;$i<sizeof($value);$i++){
                        $valueStrings[] = ":$valueIndex";
                        $valueIndex++;
                        $values[$valueIndex] = $value;
                    }
                    $statement = str_replace($key, implode(",",$valueStrings), $statement);
                } else {
                    $statement = str_replace($key, ":$valueIndex", $statement);
                    $values[$valueIndex] = $value;
                }
            }
        // }
    }

    public function sqlStatement(string $statement, ?string $operation = AcEnumRowOperation::UNKNOWN, ?array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            // SQL statement execution logic
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function sqlBatchStatement(array $statements, array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            // Batch execution logic
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function updateRows(string $table, array $values, string $condition = "", array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            // Update rows logic
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }
}


?>
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
        return $result;
    }

    public function checkFunctionExist(string $functionName): AcResult {
        $result = new AcResult();
        return $result;
    }

    public function checkStoredProcedureExist(string $stroedProcedureName): AcResult {
        $result = new AcResult();
        return $result;
    }

    public function checkTableExist(string $tableName): AcResult {
        $result = new AcResult();
        return $result;
    }

    public function checkTriggerExist(string $triggerName): AcResult {
        $result = new AcResult();
        return $result;
    }

    public function checkViewExist(string $viewName): AcResult {
        $result = new AcResult();
        return $result;
    }

    public function createDatabase(): AcResult {
        $result = new AcResult();
        return $result;
    }

    public function deleteRows(string $tableName, string $condition = "", array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        return $result;
    }

    public function executeMultipleSqlStatements(array $statements, array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        return $result;
    }

    public function executeStatement(string $statement, ?string $operation = AcEnumRowOperation::UNKNOWN, ?array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
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

    public function getDatabaseFuntions(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        return $result;
    }

    public function getDatabaseStoredProcedures(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        return $result;
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

    public function getDatabaseTriggers(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        return $result;
    }

    public function getDatabaseViews(): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        return $result;
    }

    public function getRows(string $statement, ?string $condition = "", ?array $parameters = [],?string $mode = AcEnumSelectMode::LIST, ?array $formatColumns = [], ?int $startIndex = -1, ?int $rowCount = -1 ): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        return $result;
    }

    public function getTableColumns(string $tableName): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        return $result;
    }

    public function getViewColumns(string $viewName): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        return $result;
    }

    public function insertRows(string $table, array $values): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        return $result;
    }

    public function setSqlConnection(AcSqlConnection $sqlConnection): AcResult {
        $result = new AcResult();
        $this->sqlConnection = $sqlConnection;
        $result->setSuccess();
        return $result;
    }

    public function setSqlConnectionFromJson(array $jsonData): AcResult {
        $result = new AcResult();
        $this->sqlConnection = AcSqlConnection::fromJson($jsonData);
        return $result;
    }

    public function setSqlStatementParameters(string &$statement, array &$values,array $parameters) {
        $keys = array_keys($parameters);
        foreach ($keys as $key) {
            $value = $parameters[$key];
            while (strpos($statement, $key) !== false) {
                $this->logger->log("Searching For Key: " . $key);
                $this->logger->log("Key Value: " . json_encode($value));
                $beforeQueryString = substr($statement, 0, strpos($statement, $key));
                $this->logger->log("Before Query String: " . $beforeQueryString);
                $parameterIndex = substr_count($beforeQueryString, '?');
                $this->logger->log("Parameter Index: " . $parameterIndex);
                $this->logger->log("Values Before: " . json_encode($values));
                if (is_array($value)) {
                    $statement = preg_replace('/' . preg_quote($key, '/') . '/', implode(',', array_fill(0, count($value), '?')), $statement, 1);
                    array_splice($values, $parameterIndex, 0, $value);
                } else {
                    $statement = preg_replace('/' . preg_quote($key, '/') . '/', '?', $statement, 1);
                    array_splice($values, $parameterIndex, 0, $value);
                }
                $this->logger->log("Query: " . $statement);
                $this->logger->log("Values After: " . json_encode($values));
            }
        }
    }    

    public function updateRows(string $table, array $values, string $condition = "", array $parameters = []): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        return $result;
    }
}


?>
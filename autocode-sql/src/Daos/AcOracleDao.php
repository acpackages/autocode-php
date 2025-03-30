<?php

namespace AcSql\Daos;

class AcOracleDao extends AcBaseSqlDao {
    private ?PDO $db = null;

    /**
     * Check if the Oracle database schema exists.
     */
    public function checkDatabaseExist(): AcResult {
        $result = new AcResult();
        try {
            $db = $this->getConnectionObject();
            $query = "SELECT username FROM dba_users WHERE username = UPPER(:databaseName)";
            $stmt = $db->prepare($query);
            $stmt->execute(['databaseName' => $this->sqlConnection['username']]);
            $rows = $stmt->fetchAll();
            
            if (count($rows) > 0) {
                $result->setSuccess(['value' => true, 'message' => 'Database schema exists']);
            } else {
                $result->setSuccess(['value' => false, 'message' => 'Database schema does not exist']);
            }
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    /**
     * Get a connection object to Oracle DB.
     */
    private function getConnectionObject(): ?PDO {
        if ($this->db) {
            return $this->db;
        }
        try {
            $dsn = "oci:dbname={$this->sqlConnection['hostname']}:{$this->sqlConnection['port']}/{$this->sqlConnection['database']}";
            $this->db = new PDO($dsn, $this->sqlConnection['username'], $this->sqlConnection['password']);
        } catch (Exception $ex) {
            error_log("Database Connection Error: " . $ex->getMessage());
            $this->db = null;
        }
        return $this->db;
    }

    /**
     * Check if a table exists in the Oracle schema.
     */
    public function checkTableExist(string $table): AcResult {
        $result = new AcResult();
        try {
            $db = $this->getConnectionObject();
            $query = "SELECT table_name FROM user_tables WHERE table_name = UPPER(:tableName)";
            $stmt = $db->prepare($query);
            $stmt->execute(['tableName' => $table]);
            $rows = $stmt->fetchAll();
            
            if (count($rows) > 0) {
                $result->setSuccess(['value' => true, 'message' => 'Table exists']);
            } else {
                $result->setSuccess(['value' => false, 'message' => 'Table does not exist']);
            }
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    /**
     * Insert rows into a table.
     */
    public function insertRows(string $table, array $values): AcSqlDaoResult {
        $result = new AcSqlDaoResult();
        try {
            $db = $this->getConnectionObject();
            $columns = implode(", ", array_keys($values));
            $placeholders = ":" . implode(", :", array_keys($values));
            $statement = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $db->prepare($statement);
            $stmt->execute($values);
            $result->setSuccess();
        } catch (Exception $ex) {
            $result->setException($ex);
        }
        return $result;
    }

    public function getRows(string $statement, array $parameters = [], bool $firstRowOnly = false): array {
        try {
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            $stmt->execute($parameters);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $firstRowOnly ? ($rows[0] ?? []) : $rows;
        } catch (PDOException $ex) {
            error_log("Select Statement Error: " . $ex->getMessage());
            return [];
        }
    }

    public function executeStatement(string $statement, array $parameters = []): bool {
        try {
            $db = $this->getConnectionObject();
            $stmt = $db->prepare($statement);
            return $stmt->execute($parameters);
        } catch (PDOException $ex) {
            error_log("SQL Statement Error: " . $ex->getMessage());
            return false;
        }
    }

    public function sqlBatchStatement(array $statements, array $parameters = []): bool {
        try {
            $db = $this->getConnectionObject();
            $db->beginTransaction();
            foreach ($statements as $statement) {
                $stmt = $db->prepare($statement);
                $stmt->execute($parameters);
            }
            $db->commit();
            return true;
        } catch (PDOException $ex) {
            $db->rollBack();
            error_log("Batch Statement Error: " . $ex->getMessage());
            return false;
        }
    }

    public function updateRows(string $table, array $values, string $condition = "", array $parameters = []): bool {
        try {
            $db = $this->getConnectionObject();
            $setClause = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($values)));
            $statement = "UPDATE $table SET $setClause" . ($condition ? " WHERE $condition" : "");
            $stmt = $db->prepare($statement);
            return $stmt->execute(array_merge($values, $parameters));
        } catch (PDOException $ex) {
            error_log("Update Rows Error: " . $ex->getMessage());
            return false;
        }
    }
}

<?php

namespace AcSql\Daos;

class AcPostgresDao {
    private ?PDO $pdo = null;
    private array $sqlConnection;

    public function __construct(array $sqlConnection) {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * Get a connection object to PostgreSQL.
     */
    private function getConnectionObject(): ?PDO {
        if ($this->pdo === null) {
            try {
                $dsn = "pgsql:host={$this->sqlConnection['hostname']};port={$this->sqlConnection['port']};dbname={$this->sqlConnection['database']}";
                $this->pdo = new PDO($dsn, $this->sqlConnection['username'], $this->sqlConnection['password']);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $ex) {
                error_log("Database Connection Error: " . $ex->getMessage());
                $this->pdo = null;
            }
        }
        return $this->pdo;
    }

    /**
     * Check if the PostgreSQL database exists.
     */
    public function checkDatabaseExist(): bool {
        try {
            $pdo = $this->getConnectionObject();
            $stmt = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
            $stmt->execute([$this->sqlConnection['database']]);
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            return false;
        }
    }

    /**
     * Check if a table exists in the PostgreSQL database.
     */
    public function checkTableExist(string $table): bool {
        try {
            $pdo = $this->getConnectionObject();
            $stmt = $pdo->prepare("SELECT to_regclass(?) AS table_name");
            $stmt->execute([$table]);
            return $stmt->fetchColumn() !== null;
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            return false;
        }
    }

    /**
     * Delete rows from a table based on a condition.
     */
    public function deleteRows(string $table, string $condition = "", array $parameters = []): int {
        try {
            $pdo = $this->getConnectionObject();
            $sql = "DELETE FROM $table" . ($condition ? " WHERE $condition" : "");
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($parameters));
            return $stmt->rowCount();
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            return 0;
        }
    }

    /**
     * Insert rows into a table.
     */
    public function insertRows(string $table, array $values): bool {
        try {
            $pdo = $this->getConnectionObject();
            $columns = implode(", ", array_keys($values));
            $placeholders = implode(", ", array_fill(0, count($values), "?"));
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute(array_values($values));
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            return false;
        }
    }

    /**
     * Execute a SELECT statement with optional parameters.
     */
    public function selectStatement(string $statement, array $parameters = [], bool $firstRowOnly = false): array {
        try {
            $pdo = $this->getConnectionObject();
            $stmt = $pdo->prepare($statement);
            $stmt->execute(array_values($parameters));
            return $firstRowOnly ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            return [];
        }
    }

    /**
     * Execute an arbitrary SQL statement (INSERT, UPDATE, DELETE, etc.).
     */
    public function sqlStatement(string $statement, array $parameters = []): bool {
        try {
            $pdo = $this->getConnectionObject();
            $stmt = $pdo->prepare($statement);
            return $stmt->execute(array_values($parameters));
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            return false;
        }
    }

    /**
     * Update rows in a table based on a condition.
     */
    public function updateRows(string $table, array $values, string $condition = "", array $parameters = []): int {
        try {
            $pdo = $this->getConnectionObject();
            $setClause = implode(", ", array_map(fn($key) => "$key = ?", array_keys($values)));
            $sql = "UPDATE $table SET $setClause" . ($condition ? " WHERE $condition" : "");
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge(array_values($values), array_values($parameters)));
            return $stmt->rowCount();
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            return 0;
        }
    }
}

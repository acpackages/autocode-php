<?php
namespace AcSql\Models;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';

use Autocode\AcResult;

class AcSqlDaoResult extends AcResult {
    const KEY_ROWS = 'rows';
    const KEY_ROW = 'row';
    const KEY_AFFECTED_ROWS_COUNT = 'affected_rows_count';
    const KEY_LAST_INSERTED_ID = 'last_inserted_id';

    public array $rows = [];
    public array $row = [];
    public int $affectedRowsCount = 0;
    public ?int $lastInsertedId = null;

    public function hasAffectedRows(): bool {
        return $this->affectedRowsCount > 0;
    }

    public function hasRow(): bool {
        return !empty($this->row);
    }

    public function hasRows(): bool {
        return count($this->rows) > 0;
    }

    public function rowsCount(): int {
        return count($this->rows);
    }

    public function toJson(): array {
        $result = parent::toJson();
        $result[self::KEY_AFFECTED_ROWS_COUNT] = $this->affectedRowsCount;
        $result[self::KEY_LAST_INSERTED_ID] = $this->lastInsertedId;
        $result[self::KEY_ROW] = $this->row;
        $result[self::KEY_ROWS] = $this->rows;
        return $result;
    }
}

?>
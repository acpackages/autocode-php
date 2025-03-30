<?php
namespace AcSql\Models;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../Enums/AcEnumRowOperation.php';
use Autocode\AcResult;
use AcSql\Enums\AcEnumRowOperation;

class AcSqlDaoResult extends AcResult {
    const KEY_ROWS = 'rows';
    const KEY_AFFECTED_ROWS_COUNT = 'affected_rows_count';
    const KEY_LAST_INSERTED_ID = 'last_inserted_id';
    const KEY_OPERATION = 'operation';
    const KEY_PRIMARY_KEY_FIELD = 'primary_key_field';
    const KEY_PRIMARY_KEY_VALUE = 'primary_key_value';

    public array $rows = [];
    public int $affectedRowsCount = 0;
    public mixed $lastInsertedId = null;
    public string $operation = AcEnumRowOperation::UNKNOWN;
    public ?string $primaryKeyField = "";
    public mixed $primaryKeyValue = null;

    public function __construct(?string $operation = AcEnumRowOperation::UNKNOWN) {
        $this->operation = $operation;
        parent::__construct();
    }

    public function hasAffectedRows(): bool {
        return $this->affectedRowsCount > 0;
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
        $result[self::KEY_OPERATION] = $this->operation;
        $result[self::KEY_PRIMARY_KEY_FIELD] = $this->primaryKeyField;
        $result[self::KEY_PRIMARY_KEY_VALUE] = $this->primaryKeyValue;
        $result[self::KEY_ROWS] = $this->rows;   
        return $result;
    }
}

?>
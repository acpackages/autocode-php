<?php
namespace AcSql\Models;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Models\AcResult;
use AcDataDictionary\Enums\AcEnumDDRowOperation;


class AcSqlDaoResult extends AcResult {
    const KEY_ROWS = 'rows';
    const KEY_AFFECTED_ROWS_COUNT = 'affected_rows_count';
    const KEY_LAST_INSERTED_ID = 'last_inserted_id';
    const KEY_OPERATION = 'operation';
    const KEY_PRIMARY_KEY_COLUMN = 'primary_key_column';
    const KEY_PRIMARY_KEY_VALUE = 'primary_key_value';
    const KEY_TOTAL_ROWS = 'total_rows';

    public array $rows = [];

    #[AcBindJsonProperty(key: AcSqlDaoResult::KEY_AFFECTED_ROWS_COUNT)]
    public ?int $affectedRowsCount = null;

    #[AcBindJsonProperty(key: AcSqlDaoResult::KEY_LAST_INSERTED_ID)]
    public ?int $lastInsertedId = null;

    #[AcBindJsonProperty(key: AcSqlDaoResult::KEY_LAST_INSERTED_ID)]
    public mixed $lastInsertedIds = null;
    public string $operation = AcEnumDDRowOperation::UNKNOWN;

    #[AcBindJsonProperty(key: AcSqlDaoResult::KEY_PRIMARY_KEY_COLUMN)]
    public ?string $primaryKeyColumn = null;

    #[AcBindJsonProperty(key: AcSqlDaoResult::KEY_PRIMARY_KEY_VALUE)]
    public mixed $primaryKeyValue = null;

    #[AcBindJsonProperty(key: AcSqlDaoResult::KEY_TOTAL_ROWS)]
    public int $totalRows = 0;

    public function __construct(?string $operation = AcEnumDDRowOperation::UNKNOWN) {        
        $this->operation = $operation;       
    }

    public function hasAffectedRows(): bool {
        return $this->affectedRowsCount > 0;
    }


    public function hasRows(): bool {
        return count($this->rows) > 0;
    }

    public function rowsCount(): int {
        return count(value: $this->rows);
    }
}

?>
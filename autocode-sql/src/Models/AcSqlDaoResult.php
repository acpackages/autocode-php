<?php
namespace AcSql\Models;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../Enums/AcEnumRowOperation.php';
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Models\AcResult;
use Autocode\Utils\AcUtilsJson;
use AcSql\Enums\AcEnumRowOperation;


class AcSqlDaoResult extends AcResult {
    const KEY_ROWS = 'rows';
    const KEY_AFFECTED_ROWS_COUNT = 'affected_rows_count';
    const KEY_LAST_INSERTED_ID = 'last_inserted_id';
    const KEY_OPERATION = 'operation';
    const KEY_PRIMARY_KEY_FIELD = 'primary_key_field';
    const KEY_PRIMARY_KEY_VALUE = 'primary_key_value';

    public array $rows = [];

    #[AcBindJsonProperty(key: AcSqlDaoResult::KEY_AFFECTED_ROWS_COUNT)]
    public ?int $affectedRowsCount = null;

    #[AcBindJsonProperty(key: AcSqlDaoResult::KEY_LAST_INSERTED_ID)]
    public ?int $lastInsertedId = null;

    #[AcBindJsonProperty(key: AcSqlDaoResult::KEY_LAST_INSERTED_ID)]
    public mixed $lastInsertedIds = null;
    public string $operation = AcEnumRowOperation::UNKNOWN;

    #[AcBindJsonProperty(key: AcSqlDaoResult::KEY_PRIMARY_KEY_FIELD)]
    public ?string $primaryKeyField = null;

    #[AcBindJsonProperty(key: AcSqlDaoResult::KEY_PRIMARY_KEY_VALUE)]
    public mixed $primaryKeyValue = null;

    public function __construct(?string $operation = AcEnumRowOperation::UNKNOWN) {        
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
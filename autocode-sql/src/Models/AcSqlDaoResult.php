<?php
namespace AcSql\Models;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../Enums/AcEnumRowOperation.php';
use Autocode\Models\AcJsonBindConfig;
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

    public AcJsonBindConfig $acJsonBindConfig;
    public array $rows = [];
    public int $affectedRowsCount = 0;
    public mixed $lastInsertedId = null;
    public mixed $lastInsertedIds = null;
    public string $operation = AcEnumRowOperation::UNKNOWN;
    public ?string $primaryKeyField = "";
    public mixed $primaryKeyValue = null;

    public function __construct(?string $operation = AcEnumRowOperation::UNKNOWN) {        
        parent::__construct();
        $this->acJsonBindConfig->propertyBindings["rows"]="rows";
        $this->acJsonBindConfig->propertyBindings["affected_rows_count"]="affectedRowsCount";
        $this->acJsonBindConfig->propertyBindings["last_inserted_id"]="affectedRowsCount";
        $this->acJsonBindConfig->propertyBindings["operation"]="operation";
        $this->acJsonBindConfig->propertyBindings["primary_key_field"]="primaryKeyField";
        $this->acJsonBindConfig->propertyBindings["primary_key_value"]="primaryKeyValue";
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
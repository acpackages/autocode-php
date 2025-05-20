<?php

namespace AcSql\Database;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../../../autocode-data-dictionary/vendor/autoload.php';

use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableColumn;
use AcDataDictionary\Models\AcDDTableColumnProperty;
use AcDataDictionary\Models\AcDDTableProperty;
use AcDataDictionary\Models\AcDDTableRowEvent;
use Autocode\Enums\AcEnumSqlDatabaseType;
use AcSql\Models\AcSqlConnection;
use Autocode\AcLogger;

class AcSqlDatabase {
    public static string $dataDictionaryName = "default";
    public static string $databaseType = AcEnumSqlDatabaseType::UNKNOWN;
    public static ?AcSqlConnection $sqlConnection = null;    
}
